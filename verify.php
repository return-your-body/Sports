<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['email'], $_POST['code'])) {
    $email = $_SESSION['email'];
    $code = trim($_POST['code']);

    // 備註：檢查驗證碼是否正確
    $email_safe = mysqli_real_escape_string($link, $email);
    $code_safe = mysqli_real_escape_string($link, $code);

    $check_code_sql = "SELECT * FROM email e JOIN people p ON e.people_id = p.people_id WHERE p.email = '$email_safe' AND e.code = '$code_safe'";
    $result = mysqli_query($link, $check_code_sql);

    if ($result && mysqli_num_rows($result) > 0) {
        // 備註：驗證成功，顯示成功訊息並跳轉到 `change.html`
        echo "<script>
            alert('驗證成功！請修改密碼。');
            window.location.href = 'change.html';
        </script>";
        exit;
    } else {
        // 備註：驗證失敗，顯示錯誤訊息
        echo "<script>
            alert('驗證碼錯誤或已過期！請重新嘗試。');
            window.history.back(); // 返回上一頁
        </script>";
        exit;
    }
} else {
    // 備註：未提供驗證碼或電子郵件，提示用戶
    echo "<script>
        alert('請輸入驗證碼！');
        window.history.back(); // 返回上一頁
    </script>";
    exit;
}

mysqli_close($link);
?>