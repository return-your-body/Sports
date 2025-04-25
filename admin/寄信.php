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
    $leave_start = new DateTime($leaveData['start_date']);
    $leave_end = new DateTime($leaveData['end_date']);

    $query = "
        SELECT a.appointment_id, a.shifttime_id, ds.date, p.email, p.name
        FROM appointment a
        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        JOIN shifttime st ON a.shifttime_id = st.shifttime_id
        JOIN people p ON a.people_id = p.people_id
        WHERE ds.doctor_id = ? 
        AND ds.date BETWEEN ? AND ? 
        AND a.status_id = 1
    ";

    $start_str = $leave_start->format('Y-m-d');
    $end_str = $leave_end->format('Y-m-d');

    $stmt = $link->prepare($query);
    $stmt->bind_param("iss", $doctor_id, $start_str, $end_str);
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
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $updated_appointments = [];

    while ($row = $result->fetch_assoc()) {
        $appointment_id = $row['appointment_id'];
        $shifttime_id = $row['shifttime_id'];
        $orig_date = $row['date'];
        $email = $row['email'];
        $username = $row['name'];

        $updated = false;

        // ① 同天同時段換醫師
        $sql1 = "SELECT doctorshift_id FROM doctorshift
                 WHERE date = ? AND doctor_id != ? AND doctorshift_id NOT IN
                 (SELECT doctorshift_id FROM appointment WHERE shifttime_id = ?) LIMIT 1";
        $stmt1 = $link->prepare($sql1);
        $stmt1->bind_param("sii", $orig_date, $doctor_id, $shifttime_id);
        $stmt1->execute();
        $res1 = $stmt1->get_result();
        if ($r1 = $res1->fetch_assoc()) {
            $dsid = $r1['doctorshift_id'];
            $update = $link->prepare("UPDATE appointment SET doctorshift_id = ? WHERE appointment_id = ?");
            $update->bind_param("ii", $dsid, $appointment_id);
            $update->execute();
            $updated = true;
        }

        // ② 同醫生換時段（前後兩天）
        if (!$updated) {
            for ($i = -2; $i <= 2; $i++) {
                if ($i == 0) continue;
                $alt_date = (new DateTime($orig_date))->modify("$i days")->format('Y-m-d');
                $stmt2 = $link->prepare("SELECT doctorshift_id FROM doctorshift WHERE doctor_id = ? AND date = ?");
                $stmt2->bind_param("is", $doctor_id, $alt_date);
                $stmt2->execute();
                $ds2 = $stmt2->get_result();
                while ($r2 = $ds2->fetch_assoc()) {
                    $check = $link->prepare("SELECT 1 FROM appointment WHERE doctorshift_id = ? AND shifttime_id = ?");
                    $check->bind_param("ii", $r2['doctorshift_id'], $shifttime_id);
                    $check->execute();
                    if (!$check->get_result()->fetch_assoc()) {
                        $update = $link->prepare("UPDATE appointment SET doctorshift_id = ? WHERE appointment_id = ?");
                        $update->bind_param("ii", $r2['doctorshift_id'], $appointment_id);
                        $update->execute();
                        $updated = true;
                        break 2;
                    }
                }
            }
        }

        // ③ 換醫生與換時段（前 3 後 7）
        if (!$updated) {
            for ($i = -3; $i <= 7; $i++) {
                if ($i == 0) continue;
                $alt_date = (new DateTime($orig_date))->modify("$i days")->format('Y-m-d');
                $sql3 = "SELECT doctorshift_id FROM doctorshift
                         WHERE date = ? AND doctor_id != ? AND doctorshift_id NOT IN
                         (SELECT doctorshift_id FROM appointment WHERE shifttime_id = ?) LIMIT 1";
                $stmt3 = $link->prepare($sql3);
                $stmt3->bind_param("sii", $alt_date, $doctor_id, $shifttime_id);
                $stmt3->execute();
                $res3 = $stmt3->get_result();
                if ($r3 = $res3->fetch_assoc()) {
                    $update = $link->prepare("UPDATE appointment SET doctorshift_id = ? WHERE appointment_id = ?");
                    $update->bind_param("ii", $r3['doctorshift_id'], $appointment_id);
                    $update->execute();
                    break;
                }
            }
        }

        // 重新查詢調整後資料以發信
        $queryInfo = $link->prepare("SELECT ds.date, d.doctor, s.shifttime FROM appointment a
                                     JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
                                     JOIN doctor d ON ds.doctor_id = d.doctor_id
                                     JOIN shifttime s ON a.shifttime_id = s.shifttime_id
                                     WHERE a.appointment_id = ?");
        $queryInfo->bind_param("i", $appointment_id);
        $queryInfo->execute();
        $newData = $queryInfo->get_result()->fetch_assoc();

        $subject = "預約異動通知";
        $body = "
            <p>親愛的 $username 您好，</p>
            <p>由於原治療師請假，您的預約已重新安排如下：</p>
            <ul>
                <li>新日期：" . $newData['date'] . "</li>
                <li>新時間：" . $newData['shifttime'] . "</li>
                <li>新治療師：" . $newData['doctor'] . "</li>
            </ul>
            <p>若您需更改預約，請盡速與我們聯繫。感謝您的理解與配合。</p>
        ";

        try {
            // $mail->clearAddresses();
            $mail->setFrom('llccyu24@gmail.com', '運動筋膜放鬆');
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->isHTML(true);
            $mail->send();

            $updated_appointments[] = $appointment_id;
        } catch (Exception $e) {
            return ["status" => "error", "message" => "Email 發送失敗：" . $mail->ErrorInfo];
        }
    }

    return ["status" => "success", "message" => "成功調整並通知預約", "updated_appointments" => $updated_appointments];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leaves_id'])) {
    $leaves_id = $_POST['leaves_id'];
    $response = sendCancellationEmails($link, $leaves_id);
    header('Content-Type: application/json');
    echo json_encode($response);
}