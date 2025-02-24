<?php
session_start();
require '../db.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/SMTP.php'; // PHPMailer套件載入

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 確認是否收到POST請求及所有必要參數
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['people_id'], $_POST['doctor_id'], $_POST['date'], $_POST['time'], $_POST['note'])) {
    $people_id = intval($_POST['people_id']); // 使用者ID
    $doctor_id = intval($_POST['doctor_id']); // 治療師ID
    $date = $_POST['date']; // 預約日期
    $shifttime_id = intval($_POST['time']); // 時段ID
    $note = $_POST['note']; // 備註

    // 檢查時段是否已被預約
    $query_check_time = "SELECT COUNT(*) FROM appointment a 
                         JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
                         WHERE a.shifttime_id = ? AND ds.doctor_id = ? AND ds.date = ?";
    $stmt_check_time = mysqli_prepare($link, $query_check_time);
    mysqli_stmt_bind_param($stmt_check_time, "iis", $shifttime_id, $doctor_id, $date);
    mysqli_stmt_execute($stmt_check_time);
    mysqli_stmt_bind_result($stmt_check_time, $existing_time_count);
    mysqli_stmt_fetch($stmt_check_time);
    mysqli_stmt_close($stmt_check_time);

    if ($existing_time_count > 0) {
        echo "<script>alert('此時段已被預約，請選擇其他時段！'); window.history.back();</script>";
        exit;
    }

    // 檢查使用者當天是否已有預約
    $query_check_day = "SELECT COUNT(*) FROM appointment a 
                        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
                        WHERE a.people_id = ? AND ds.date = ?";
    $stmt_check_day = mysqli_prepare($link, $query_check_day);
    mysqli_stmt_bind_param($stmt_check_day, "is", $people_id, $date);
    mysqli_stmt_execute($stmt_check_day);
    mysqli_stmt_bind_result($stmt_check_day, $existing_day_count);
    mysqli_stmt_fetch($stmt_check_day);
    mysqli_stmt_close($stmt_check_day);

    if ($existing_day_count > 0) {
        echo "<script>alert('您已在這一天預約過其他時段，無法重複預約！'); window.history.back();</script>";
        exit;
    }

    // 查詢治療師當天的班表ID
    $query_shift = "SELECT doctorshift_id FROM doctorshift WHERE doctor_id = ? AND date = ?";
    $stmt_shift = mysqli_prepare($link, $query_shift);
    mysqli_stmt_bind_param($stmt_shift, "is", $doctor_id, $date);
    mysqli_stmt_execute($stmt_shift);
    $result_shift = mysqli_stmt_get_result($stmt_shift);

    if ($shift_row = mysqli_fetch_assoc($result_shift)) {
        $doctorshift_id = $shift_row['doctorshift_id'];

        // 插入預約資料
        $insert_query = "INSERT INTO appointment (people_id, doctorshift_id, shifttime_id, note, status_id, created_at) 
                         VALUES (?, ?, ?, ?, 1, NOW())";
        $stmt_insert = mysqli_prepare($link, $insert_query);
        mysqli_stmt_bind_param($stmt_insert, "iiis", $people_id, $doctorshift_id, $shifttime_id, $note);

        if (mysqli_stmt_execute($stmt_insert)) {
            // 查詢使用者email和治療師姓名及時段
            $email_query = "SELECT p.email, d.doctor, st.shifttime 
                            FROM people p, doctor d, shifttime st
                            WHERE p.people_id = ? AND d.doctor_id = ? AND st.shifttime_id = ?";
            $stmt_email = mysqli_prepare($link, $email_query);
            mysqli_stmt_bind_param($stmt_email, "iii", $people_id, $doctor_id, $shifttime_id);
            mysqli_stmt_execute($stmt_email);
            $email_result = mysqli_stmt_get_result($stmt_email);
            $email_row = mysqli_fetch_assoc($email_result);

            $user_email = $email_row['email'];
            $doctor_name = $email_row['doctor'];
            $time_str = $email_row['shifttime'];

            // 發送通知郵件
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

                $mail->setFrom('llccyu24@gmail.com', '運動筋膜放鬆 預約通知');
                $mail->addAddress($user_email);

                $mail->isHTML(true);
                $mail->Subject = '預約成功通知';
                $mail->Body = "您的預約已成功！<br>治療師: {$doctor_name}<br>日期: {$date}<br>時間: {$time_str}";

                $mail->send();
                echo "<script>alert('預約成功，通知信已寄出！'); window.location.href='d_people.php';</script>";
            } catch (Exception $e) {
                echo "<script>alert('預約成功，但通知信發送失敗'); window.location.href='d_people.php';</script>";
            }
        } else {
            echo "<script>alert('無法新增預約資料！'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('無對應的排班資料！'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('缺少必要的參數！'); window.history.back();</script>";
}
?>