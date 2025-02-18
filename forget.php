<?php
// 開啟錯誤顯示
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 引入郵件發送函式
require 'email.php';

// 連接資料庫
require 'db.php';

// 確保請求方法為 POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 取得使用者輸入的 Email
    $email = trim($_POST["email"]);

    // 驗證 Email 格式
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("電子郵件格式不正確！");
    }

    // 🔹 改為查詢 `people` 表，而不是 `user` 表
    $query = "SELECT user_id FROM people WHERE email = ?";
    $stmt = mysqli_prepare($link, $query);

    // 🔹 檢查 SQL 是否成功
    if (!$stmt) {
        die("SQL 錯誤：" . mysqli_error($link));
    }

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // 如果 Email 存在
    if ($row = mysqli_fetch_assoc($result)) {
        $user_id = $row["user_id"];

        // 產生隨機驗證碼
        $code = md5(uniqid(rand(), true));
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // 刪除舊的驗證碼（確保只有一個有效的驗證碼）
        $stmt = mysqli_prepare($link, "DELETE FROM email WHERE user_id = ?");
        if (!$stmt) {
            die("SQL 錯誤：" . mysqli_error($link));
        }
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        // 插入新的驗證碼
        $stmt = mysqli_prepare($link, "INSERT INTO email (user_id, verification_code, expires_at) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("SQL 錯誤：" . mysqli_error($link));
        }
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $code, $expires_at);
        mysqli_stmt_execute($stmt);

        // 發送驗證郵件，讓 `email` 和 `code` 一起傳遞
        $subject = "重設密碼驗證";
        $message = "請點擊以下連結來重設您的密碼：
        <a href='http://demo2.im.ukn.edu.tw/~Health24/change.php?code=$code&email=$email'>重設密碼連結</a>";

        if (send_email($email, $subject, $message)) {
            echo "驗證郵件已發送，請檢查您的信箱。";
        } else {
            echo "發送郵件失敗，請檢查 SMTP 設定。";
        }
    } else {
        echo "此電子郵件未註冊！";
    }
}
?>
