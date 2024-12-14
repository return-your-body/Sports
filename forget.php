<?php
// 啟用 session
session_start();

// 引入資料庫連接
require 'db.php';

// 檢查是否提交表單
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    // 接收並清理電子郵件地址
    $email = trim($_POST['email']);

    // 防止 SQL 注入
    $email_safe = mysqli_real_escape_string($link, $email);

    // 查詢資料庫是否有該電子郵件
    $query = "SELECT * FROM people WHERE email = '$email_safe'";
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        // 找到該電子郵件，存入 session
        $_SESSION['email'] = $email;

        // 跳轉到 change.html
        header("Location: change.html");
        exit;
    } else {
        // 沒有找到該電子郵件，顯示錯誤訊息並返回上一頁
        echo "<script>
                alert('該電子郵件未在系統中註冊！');
                window.history.back();
              </script>";
        exit;
    }
} else {
    // 非表單提交，返回上一頁
    echo "<script>
            alert('請輸入您的電子郵件地址！');
            window.history.back();
          </script>";
    exit;
}

// 關閉資料庫連接
mysqli_close($link);
?>
