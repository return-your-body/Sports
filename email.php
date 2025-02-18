<?php
// 匯入 PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

// 引入 PHPMailer 類別
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * 發送驗證郵件
 * @param string $to 收件者的電子郵件地址
 * @param string $subject 郵件主題
 * @param string $message 郵件內容（HTML 格式）
 */
function send_email($to, $subject, $message) {
    // 建立 PHPMailer 物件
    $mail = new PHPMailer(true);

    try {
        // 設定 SMTP 伺服器
        $mail->IsSMTP();
        $mail->Host = 'smtp.gmail.com';  
        $mail->SMTPAuth = true;
        $mail->Username = 'llccyu24@gmail.com';  
        $mail->Password = 'dqxvjcysypoflftr';  
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // 設定郵件發送者
        $mail->SetFrom('llccyu24@gmail.com', '運動筋膜放鬆');
        $mail->AddAddress($to);

        // 設定郵件內容
        $mail->IsHTML(true);
        $mail->CharSet = "UTF-8";  // 確保郵件使用 UTF-8 編碼
        $mail->Subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
        $mail->Body    = $message;

        // 發送郵件
        return $mail->Send();
    } catch (Exception $e) {
        return false;
    }
}
?>
