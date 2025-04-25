<?php
// 匯入資料庫與 PHPMailer
require '../db.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * 根據三種優先順序嘗試重新安排預約
 */
function findAvailableSlot($link, $original_date, $original_time, $original_doctor_id, $appointment_id) {
    $shifttime_id = null;

    // 取得原始預約的時段 ID
    $stmt = $link->prepare("SELECT shifttime_id FROM appointment WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $shifttime_id = $row['shifttime_id'];
    } else {
        return false; // 若查無預約時段，跳過處理
    }

    // ➊ 同日同時段 → 換醫生
    $sql = "
        SELECT ds.doctorshift_id, d.doctor_id, d.doctor, st.shifttime, ds.date
        FROM doctorshift ds
        JOIN doctor d ON ds.doctor_id = d.doctor_id
        JOIN shifttime st ON ds.shifttime_id = st.shifttime_id
        WHERE ds.date = ? 
          AND ds.shifttime_id = ? 
          AND ds.doctor_id != ? 
          AND NOT EXISTS (
              SELECT 1 FROM appointment a WHERE a.doctorshift_id = ds.doctorshift_id
          )
        LIMIT 1
    ";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("sii", $original_date, $shifttime_id, $original_doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) return $row;

    // ➋ 同醫生 → 前/後一天相同時段
    foreach ([-1, 1] as $offset) {
        $check_date = date('Y-m-d', strtotime("$original_date $offset day"));
        $sql = "
            SELECT ds.doctorshift_id, d.doctor_id, d.doctor, st.shifttime, ds.date
            FROM doctorshift ds
            JOIN doctor d ON ds.doctor_id = d.doctor_id
            JOIN shifttime st ON ds.shifttime_id = st.shifttime_id
            WHERE ds.date = ? 
              AND ds.shifttime_id = ? 
              AND ds.doctor_id = ? 
              AND NOT EXISTS (
                  SELECT 1 FROM appointment a WHERE a.doctorshift_id = ds.doctorshift_id
              )
            LIMIT 1
        ";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("sii", $check_date, $shifttime_id, $original_doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) return $row;
    }

    // ➌ 換醫生 + 任意時段（前最多 3 天，後最多 7 天）
    $offset_days = array_merge(range(-3, -1), range(1, 7));
    foreach ($offset_days as $i) {
        $check_date = date('Y-m-d', strtotime("$original_date $i day"));
        $sql = "
            SELECT ds.doctorshift_id, d.doctor_id, d.doctor, st.shifttime, ds.date
            FROM doctorshift ds
            JOIN doctor d ON ds.doctor_id = d.doctor_id
            JOIN shifttime st ON ds.shifttime_id = st.shifttime_id
            WHERE ds.date = ? 
              AND ds.doctor_id != ?
              AND NOT EXISTS (
                  SELECT 1 FROM appointment a WHERE a.doctorshift_id = ds.doctorshift_id
              )
            LIMIT 1
        ";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("si", $check_date, $original_doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) return $row;
    }

    return false; // 無法排入任何時段
}

/**
 * 主邏輯：針對請假治療師所影響的預約 → 進行調整與通知
 */
function sendPendingEmails($link, $leaves_id) {
    // 取得請假資料（僅使用起始日期）
    $leaveQuery = $link->prepare("SELECT doctor_id, start_date FROM leaves WHERE leaves_id = ?");
    $leaveQuery->bind_param("i", $leaves_id);
    $leaveQuery->execute();
    $leaveResult = $leaveQuery->get_result();
    if ($leaveResult->num_rows === 0) {
        return ["status" => "error", "message" => "找不到請假資訊"];
    }

    $leaveData = $leaveResult->fetch_assoc();
    $doctor_id = $leaveData['doctor_id'];
    $leave_date = date('Y-m-d', strtotime($leaveData['start_date']));

    // 查詢該日受影響預約（尚未改過的 status_id = 1）
    $query = "
        SELECT p.email, p.name, a.appointment_id, ds.date AS appointment_date, st.shifttime AS appointment_time
        FROM appointment a
        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        JOIN shifttime st ON a.shifttime_id = st.shifttime_id
        JOIN people p ON a.people_id = p.people_id
        WHERE ds.doctor_id = ? 
        AND ds.date = ? 
        AND a.status_id = 1
    ";
    $stmt = $link->prepare($query);
    $stmt->bind_param("is", $doctor_id, $leave_date);
    $stmt->execute();
    $result = $stmt->get_result();

    // 設定 PHPMailer 寄信
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'llccyu24@gmail.com';
    $mail->Password = 'dqxvjcysypoflftr';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $updated_appointments = [];

    // 逐筆處理每一筆受影響預約
    while ($row = $result->fetch_assoc()) {
        $appointment_id = $row['appointment_id'];
        $email = $row['email'];
        $name = $row['name'];
        $original_time = $row['appointment_time'];
        $original_date = $row['appointment_date'];

        // 執行時段替代邏輯
        $slot = findAvailableSlot($link, $original_date, $original_time, $doctor_id, $appointment_id);
        if ($slot) {
            // 成功找到替代方案 → 更新預約
            $updateStmt = $link->prepare("UPDATE appointment SET doctorshift_id = ?, status_id = 1 WHERE appointment_id = ?");
            $updateStmt->bind_param("ii", $slot['doctorshift_id'], $appointment_id);
            $updateStmt->execute();

            // 建立通知信內容
            $subject = "【預約異動通知】您的預約已重新安排";
            $body = "
                <p>親愛的 $name 您好，</p>
                <p>因治療師請假，您的預約已重新安排如下：</p>
                <ul>
                    <li>👨‍⚕️ 治療師：{$slot['doctor']}</li>
                    <li>📅 日期：{$slot['date']}</li>
                    <li>🕒 時間：{$slot['shifttime']}</li>
                </ul>
                <p>若有任何問題，請致電 03-xxxxxxx 與我們聯繫。</p>
            ";

            try {
                $mail->clearAddresses();
                $mail->setFrom('llccyu24@gmail.com', '運動筋膜放鬆');
                $mail->addAddress($email);
                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->isHTML(true);
                $mail->send();
            } catch (Exception $e) {
                return ["status" => "error", "message" => "Email 發送失敗：" . $mail->ErrorInfo];
            }

            $updated_appointments[] = $appointment_id;
        }
    }

    return [
        "status" => "success",
        "message" => "已完成重新安排與通知",
        "updated" => $updated_appointments
    ];
}

// 控制執行：從 POST 傳入 leaves_id 啟動處理流程
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leaves_id'])) {
    $leaves_id = $_POST['leaves_id'];
    $response = sendPendingEmails($link, $leaves_id);
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
