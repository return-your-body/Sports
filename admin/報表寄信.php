<?php
require '../db.php';
require 'PHPMailer/PHPMailerAutoload.php'; // 你可以用PHPMailer

header('Content-Type: application/json');

try {
  // 這裡你可以自己產生今天的統計數據，或匯出一個Excel附檔
  
  $mail = new PHPMailer;
  $mail->isSMTP();
  $mail->Host = 'smtp.example.com'; // 你的SMTP伺服器
  $mail->SMTPAuth = true;
  $mail->Username = 'your_email@example.com'; // 你的帳號
  $mail->Password = 'your_password';           // 你的密碼
  $mail->SMTPSecure = 'tls';
  $mail->Port = 587;

  $mail->setFrom('your_email@example.com', '系統通知');
  $mail->addAddress('receiver@example.com', '管理員');

  $mail->Subject = '今日治療師統計報表';
  $mail->Body    = '您好，今日的治療師統計報表已寄送，請查收附件！';

  // 附上今天匯出的Excel檔（如果要）
  // $mail->addAttachment('/path/to/治療師統計資料_2025-04-23.xlsx');

  if (!$mail->send()) {
    echo json_encode(['success' => false, 'message' => $mail->ErrorInfo]);
  } else {
    echo json_encode(['success' => true]);
  }
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
