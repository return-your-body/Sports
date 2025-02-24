<?php
// 啟動使用者 session
session_start();

// 載入資料庫連線設定
require '../db.php';

// 載入 PHPMailer
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 確認前端傳送的必要資料是否存在
if (isset($_POST['doctor'], $_POST['date'], $_POST['time'], $_POST['note'])) {
    // 接收並儲存POST參數
    $doctor = $_POST['doctor']; // 治療師名稱
    $date = $_POST['date'];     // 預約日期
    $time = $_POST['time'];     // 預約時間
    $note = $_POST['note'];     // 備註內容

    // 從session獲取登入帳號
    $account = $_SESSION['帳號'];

    // 查詢使用者的 people_id 與 email
    $query = "SELECT people.people_id, people.email FROM user 
              JOIN people ON user.user_id = people.user_id 
              WHERE user.account = ?";
    $stmt = mysqli_prepare($link, $query); // 預防SQL injection
    mysqli_stmt_bind_param($stmt, "s", $account);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // 確認使用者是否存在
    if ($row = mysqli_fetch_assoc($result)) {
        $people_id = $row['people_id'];  // 使用者的people_id
        $user_email = $row['email'];     // 使用者的email地址

        // 查詢治療師對應班表ID(doctorshift_id)及時間段ID(shifttime_id)
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

        // 如果查詢到排班資料
        if ($shift_row = mysqli_fetch_assoc($result_shift)) {
            $doctorshift_id = $shift_row['doctorshift_id']; // 治療師排班編號
            $shifttime_id = $shift_row['shifttime_id'];     // 預約時間段編號

            // 將預約資料寫入appointment資料表
            $insert_query = "
                INSERT INTO appointment (people_id, doctorshift_id, shifttime_id, note, status_id, created_at) 
                VALUES (?, ?, ?, ?, 1, NOW())"; // status_id: 1表示已預約
            $stmt_insert = mysqli_prepare($link, $insert_query);
            mysqli_stmt_bind_param($stmt_insert, "iiis", $people_id, $doctorshift_id, $shifttime_id, $note);

            // 執行預約資料插入
            if (mysqli_stmt_execute($stmt_insert)) {
                // 預約資料插入成功，開始發送郵件通知使用者

                // 建立PHPMailer實例
                $mail = new PHPMailer(true);

                try {
                    // SMTP郵件伺服器設定 (這裡以Gmail為例)
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // SMTP伺服器位置
                    $mail->SMTPAuth = true;             // 開啟SMTP認證
                    $mail->Username = 'llccyu24@gmail.com';  // 寄件人email帳號
                    $mail->Password = 'dqxvjcysypoflftr';         // 寄件人email密碼 (推薦用應用程式密碼)
                    $mail->SMTPSecure = 'tls';            // 加密方式
                    $mail->Port = 587;              // SMTP埠號                   
                    $mail->CharSet = 'UTF-8';   // 設定 UTF-8 編碼，防止亂碼

                    // 信件寄送人與收件人設定
                    $mail->setFrom('llccyu24@gmail.com', '運動筋膜放鬆 預約通知');
                    $mail->addAddress($user_email);       // 使用者的email地址

                    // 信件主題與內容設定
                    $mail->isHTML(true);                  // 啟用HTML內容格式
                    $mail->Subject = '預約成功通知';
                    $mail->Body = "
                        親愛的使用者您好，<br>
                        您已成功預約以下時段：<br><br>
                        <b>治療師：</b> {$doctor}<br>
                        <b>日期：</b> {$date}<br>
                        <b>時間：</b> {$time}<br><br>
                        感謝您的預約，期待您的光臨！";

                    // 發送郵件
                    $mail->send();

                    // 回傳成功訊息給前端
                    echo json_encode(['success' => true, 'message' => '預約成功，通知信已寄出！']);

                } catch (Exception $e) {
                    // 郵件發送失敗，但預約已成功
                    echo json_encode(['success' => true, 'message' => '預約成功，但通知信發送失敗：' . $mail->ErrorInfo]);
                }

            } else {
                // 資料庫預約資料插入失敗
                echo json_encode(['success' => false, 'message' => '無法新增預約資料。']);
            }
        } else {
            // 沒有找到對應的排班或時段資料
            echo json_encode(['success' => false, 'message' => '無對應的排班或時段資料。']);
        }
    } else {
        // 查無此使用者資料
        echo json_encode(['success' => false, 'message' => '用戶資訊錯誤，請重新登入。']);
    }
} else {
    // 前端缺少必要參數
    echo json_encode(['success' => false, 'message' => '缺少必要的參數。']);
}
?>