<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../db.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/SMTP.php';// PHPMailer


$appointment_id = $_POST['record_id'];
$status_id = $_POST['status_id'];

switch ($status_id) {
    case 3: // 報到
        $stmt = $link->prepare("UPDATE appointment SET status_id=3 WHERE appointment_id=?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        echo "狀態已更新為報到！";
        break;

    case 4: // 請假
        $stmt = $link->prepare("UPDATE appointment SET status_id=4 WHERE appointment_id=?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();

        // 更新違規記點
        $link->query("UPDATE people SET black=black+0.5 WHERE people_id=(SELECT people_id FROM appointment WHERE appointment_id='$appointment_id')");

        // 查詢用戶email, 預約日期和時間
        $query = "SELECT p.email, ds.date, st.shifttime, d.doctor
                  FROM appointment a
                  JOIN people p ON a.people_id = p.people_id
                  JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
                  JOIN shifttime st ON a.shifttime_id = st.shifttime_id
                  JOIN doctor d ON ds.doctor_id = d.doctor_id
                  WHERE a.appointment_id = ?";
        $stmt_mail = $link->prepare($query);
        $stmt_mail->bind_param("i", $appointment_id);
        $stmt_mail->execute();
        $result_mail = $stmt_mail->get_result();

        if ($row = $result_mail->fetch_assoc()) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'llccyu24@gmail.com';
                $mail->Password = 'dqxvjcysypoflftr';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                // 設定 UTF-8 編碼，防止亂碼
                $mail->CharSet = 'UTF-8';

                $mail->setFrom('llccyu24@gmail.com', '運動筋膜放鬆 預約取消通知');
                $mail->addAddress($row['email']);

                $mail->isHTML(true);
                $mail->Subject = '預約取消通知';
                $mail->Body = "您的預約已取消（請假）：<br>治療師：{$row['doctor']}<br>日期：{$row['date']}<br>時間：{$row['shifttime']}";

                $mail->send();
                echo "狀態已更新為請假，已記錄違規並寄送通知郵件！";
            } catch (Exception $e) {
                echo "狀態已更新為請假，但郵件發送失敗：", $mail->ErrorInfo;
            }
        }
        break;

    case 5: // 爽約
        $stmt = $link->prepare("UPDATE appointment SET status_id=5 WHERE appointment_id=?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();

        $link->query("UPDATE people SET black=black+1 WHERE people_id=(SELECT people_id FROM appointment WHERE appointment_id='$appointment_id')");
        echo "狀態已更新為爽約，已記錄違規！";
        break;

    case 8: // 看診中
        $stmt = $link->prepare("UPDATE appointment SET status_id=8 WHERE appointment_id=?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        echo "狀態已更新為看診中！";
        break;

    case 6: // 已看診
        echo "此預約已完成看診，無法變更狀態！";
        break;

    default:
        echo "無效操作！";
}

$link->close();
?>