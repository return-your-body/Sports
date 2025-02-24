<?php
// 啟動會話
session_start();

// 載入資料庫設定
require '../db.php';
// 載入PHPMailer自動加載
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 確認前端是否提供必要參數
if (isset($_POST['doctor'], $_POST['date'], $_POST['time'], $_POST['note'], $_SESSION['people_id'])) {

    $doctor = $_POST['doctor']; 
    $date = $_POST['date'];     
    $time = $_POST['time'];    
    $note = $_POST['note'];    
    $people_id = $_SESSION['people_id'];

    // 查詢使用者 email
    $email_query = "SELECT email FROM people WHERE people_id = ?";
    $email_stmt = mysqli_prepare($link, $email_query);
    mysqli_stmt_bind_param($email_stmt, "i", $people_id);
    mysqli_stmt_execute($email_stmt);
    $email_result = mysqli_stmt_get_result($email_stmt);
    
    if ($email_row = mysqli_fetch_assoc($email_result)) {
        $user_email = $email_row['email']; //取得使用者 email
    } else {
        echo json_encode(['success' => false, 'message' => '無法取得使用者Email資訊。']);
        exit;
    }

    // 查詢治療師班表和時段
    $query_shift = "
        SELECT ds.doctorshift_id, st.shifttime_id
        FROM doctorshift ds
        JOIN doctor d ON ds.doctor_id = d.doctor_id
        JOIN shifttime st ON st.shifttime = ?
        WHERE d.doctor = ? AND ds.date = ?";

    $stmt_shift = mysqli_prepare($link, $query_shift);
    mysqli_stmt_bind_param($stmt_shift, "sss", $time, $doctor, $date);
    mysqli_stmt_execute($stmt_shift);
    $result_shift = mysqli_stmt_get_result($stmt_shift);

    if ($shift_row = mysqli_fetch_assoc($result_shift)) {
        $doctorshift_id = $shift_row['doctorshift_id'];
        $shifttime_id = $shift_row['shifttime_id'];

        // 插入預約記錄
        $insert_query = "
            INSERT INTO appointment (people_id, doctorshift_id, shifttime_id, note, status_id, created_at) 
            VALUES (?, ?, ?, ?, 1, NOW())";

        $stmt_insert = mysqli_prepare($link, $insert_query);
        mysqli_stmt_bind_param($stmt_insert, "iiis", $people_id, $doctorshift_id, $shifttime_id, $note);

        if (mysqli_stmt_execute($stmt_insert)) {
            // 發送郵件通知
            $mail = new PHPMailer(true);

            try {
                // SMTP設定 (以Gmail為例)
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'llccyu24@gmail.com';   // 寄件人email
                $mail->Password   = 'dqxvjcysypoflftr';          // 寄件人密碼(建議使用應用程式密碼)
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;
                $mail->CharSet = 'UTF-8';   // 設定 UTF-8 編碼，防止亂碼

                // 寄件人與收件人設定
                $mail->setFrom('llccyu24@gmail.com', '預約通知系統');
                $mail->addAddress($user_email);

                // 信件內容設定
                $mail->isHTML(true);
                $mail->Subject = '預約成功通知';
                $mail->Body    = "
                    親愛的使用者您好，<br>
                    您已成功預約以下時段：<br><br>
                    <b>治療師：</b>{$doctor}<br>
                    <b>日期：</b>{$date}<br>
                    <b>時間：</b>{$time}<br><br>
                    感謝您的預約，期待您的光臨！";

                // 發送郵件
                $mail->send();

                echo json_encode(['success' => true, 'message' => '預約成功，通知信已寄出！']);

            } catch (Exception $e) {
                // 若寄信失敗，記錄錯誤原因
                error_log("郵件發送失敗: " . $mail->ErrorInfo);
                echo json_encode(['success' => true, 'message' => '預約成功，但通知信發送失敗：'.$mail->ErrorInfo]);
            }

        } else {
            error_log("插入預約失敗: " . mysqli_error($link));
            echo json_encode(['success' => false, 'message' => '無法新增預約資料。', 'error' => mysqli_error($link)]);
        }

    } else {
        error_log("無對應班表: doctor = $doctor, date = $date, time = $time");
        echo json_encode(['success' => false, 'message' => '無對應的排班或時段資料。']);
    }
} else {
    error_log("缺少必要參數: " . json_encode($_POST));
    echo json_encode(['success' => false, 'message' => '缺少必要的參數。']);
}
?>
