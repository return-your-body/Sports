<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

function sendVerificationEmail($email, $token) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'llccyu24@gmail.com'; 
        $mail->Password = 'dqxvjcysypoflftr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // 設定 UTF-8 編碼，防止亂碼
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('llccyu24@gmail.com', '運動筋膜放鬆');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = '請驗證您的電子郵件';
        $mail->Body = "您的驗證碼為： <b>$token</b> ，請在網站上輸入以完成驗證。";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
