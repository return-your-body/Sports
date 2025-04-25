<?php
require '../db.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendCancellationEmails($link, $leaves_id) {
    $leaveQuery = $link->prepare("SELECT doctor_id, start_date, end_date FROM leaves WHERE leaves_id = ?");
    $leaveQuery->bind_param("i", $leaves_id);
    $leaveQuery->execute();
    $leaveResult = $leaveQuery->get_result();
    
    if ($leaveResult->num_rows === 0) {
        return ["status" => "error", "message" => "找不到請假資訊"];
    }

    $leaveData = $leaveResult->fetch_assoc();
    $doctor_id = $leaveData['doctor_id'];
    $leave_date = date('Y-m-d', strtotime($leaveData['start_date']));
    $start_time = date('H:i', strtotime($leaveData['start_date']));
    $end_time = date('H:i', strtotime($leaveData['end_date']));

    $query = "
        SELECT p.email, p.name, a.appointment_id, ds.date AS appointment_date, st.shifttime AS appointment_time
        FROM appointment a
        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        JOIN shifttime st ON a.shifttime_id = st.shifttime_id
        JOIN people p ON a.people_id = p.people_id
        WHERE ds.doctor_id = ? 
        AND ds.date = ? 
        AND st.shifttime BETWEEN ? AND ? 
        AND a.status_id = 1
    ";

    $stmt = $link->prepare($query);
    $stmt->bind_param("isss", $doctor_id, $leave_date, $start_time, $end_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ["status" => "success", "message" => "沒有受影響的預約"];
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'llccyu24@gmail.com';
    $mail->Password = 'dqxvjcysypoflftr';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    // 設定 UTF-8 編碼，防止亂碼
    $mail->CharSet = 'UTF-8';

    $deleted_appointments = [];
    while ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $username = $row['name'];
        $appointment_id = $row['appointment_id'];
        $appointment_date = $row['appointment_date'];
        $appointment_time = $row['appointment_time'];

        $subject = "預約取消通知";
        $body = "
            <p>親愛的 $username 您好，</p>
            <p>由於治療師的請假，您的預約已被取消：</p>
            <ul>
                <li>日期：$appointment_date</li>
                <li>時間：$appointment_time</li>
            </ul>
            <p>請您重新安排預約，謝謝您的理解。</p>
        ";

        try {
            $mail->setFrom('llccyu24@gmail.com', '運動筋膜放鬆');
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->isHTML(true);
            $mail->send();

            $deleteStmt = $link->prepare("DELETE FROM appointment WHERE appointment_id = ?");
            $deleteStmt->bind_param("i", $appointment_id);
            $deleteStmt->execute();
            $deleted_appointments[] = $appointment_id;

        } catch (Exception $e) {
            return ["status" => "error", "message" => "Email 發送失敗：" . $mail->ErrorInfo];
        }
    }

    return ["status" => "success", "message" => "成功通知並刪除預約", "deleted_appointments" => $deleted_appointments];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leaves_id'])) {
    $leaves_id = $_POST['leaves_id'];
    $response = sendCancellationEmails($link, $leaves_id);
    header('Content-Type: application/json');
    echo json_encode($response);
}
