<?php
// 啟用錯誤顯示，方便除錯
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 啟用 Session，確保跨頁傳遞變數
session_start();

// 引入資料庫連接
require 'db.php';

// **確保請求方法為 POST**
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 取得表單提交的 `email` 和 `code`
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $code = isset($_POST['code']) ? trim($_POST['code']) : null;

    // 取得使用者輸入的新密碼
    $new_password = trim($_POST['passwd']);
    $confirm_password = trim($_POST['confirm-password']);

    // **第一步：檢查 email、驗證碼和密碼是否正確**
    if (empty($email) || empty($code)) {
        die("<script>alert('錯誤：缺少電子郵件或驗證碼！'); window.history.back();</script>");
    }
    if ($new_password !== $confirm_password) {
        die("<script>alert('錯誤：密碼與確認密碼不一致！'); window.history.back();</script>");
    }

    // **第二步：檢查驗證碼是否有效**
    $stmt = mysqli_prepare($link, "SELECT user_id FROM email WHERE verification_code = ? AND expires_at > NOW()");
    if (!$stmt) {
        die("SQL 錯誤 (查詢驗證碼)：" . mysqli_error($link));
    }
    mysqli_stmt_bind_param($stmt, "s", $code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $user_id = $row["user_id"];

        // **第三步：確認 `user_id` 是否存在於 `user` 表**
        $stmt = mysqli_prepare($link, "SELECT password FROM user WHERE user_id = ?");
        if (!$stmt) {
            die("SQL 錯誤 (查找 user 表)：" . mysqli_error($link));
        }
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // **第四步：更新 `user` 表的密碼**
            $stmt = mysqli_prepare($link, "UPDATE user SET password = ? WHERE user_id = ?");
            if (!$stmt) {
                die("SQL 錯誤 (更新密碼)：" . mysqli_error($link));
            }
            mysqli_stmt_bind_param($stmt, "si", $new_password, $user_id);
            mysqli_stmt_execute($stmt);

            // **第五步：刪除 `email` 表中的驗證碼**
            $stmt = mysqli_prepare($link, "DELETE FROM email WHERE user_id = ?");
            if (!$stmt) {
                die("SQL 錯誤 (刪除驗證碼)：" . mysqli_error($link));
            }
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);

            // **第六步：成功提示並跳轉**
            session_destroy();
            echo "<script>
                    alert('密碼變更成功！請使用新密碼登入。');
                    window.location.href = 'index.html';
                  </script>";
        } else {
            die("<script>alert('錯誤：未找到對應的使用者！'); window.history.back();</script>");
        }
    } else {
        die("<script>alert('錯誤：驗證碼無效或已過期！'); window.location.href = 'forget.html';</script>");
    }
} else {
    die("<script>alert('錯誤：非法訪問！'); window.location.href = 'index.html';</script>");
}

// 關閉資料庫
mysqli_close($link);
?>
