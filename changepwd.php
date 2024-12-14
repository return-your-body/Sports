<?php
// 開啟 session 用於跨頁面存取用戶資料
session_start();

// 引入資料庫連接檔案
require 'db.php';

// 檢查是否提交表單
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 從 session 中取得電子郵件
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : null; // 從 session 取得電子郵件
    $new_password = trim($_POST['passwd']); // 新密碼
    $confirm_password = trim($_POST['confirm-password']); // 確認密碼

    // 檢查電子郵件是否存在於 session 中
    if (is_null($email)) {
        echo "<script>
                alert('未找到電子郵件資訊，請重新驗證您的電子郵件！');
                window.location.href = 'verify.php'; // 返回驗證頁面
              </script>";
        exit;
    }

    // **查詢 people 表以獲取 user_id**
    $email_safe = mysqli_real_escape_string($link, $email); // 避免 SQL 注入
    $query_people = "SELECT user_id FROM people WHERE email = '$email_safe'";
    $result_people = mysqli_query($link, $query_people);

    if ($result_people && mysqli_num_rows($result_people) > 0) {
        // 獲取 user_id
        $row_people = mysqli_fetch_assoc($result_people);
        $user_id = $row_people['user_id'];

        // **更新 user 表的密碼**
        $new_password_safe = mysqli_real_escape_string($link, $new_password); // 避免 SQL 注入
        $query_update = "UPDATE user SET password = '$new_password_safe' WHERE user_id = $user_id";

        if (mysqli_query($link, $query_update)) {
            // 更新成功，清除 session 並跳轉到登入頁面
            session_destroy(); // 清除 session
            echo "<script>
                    alert('密碼變更成功！請使用新密碼登入。');
                    window.location.href = 'index.html'; // 跳轉到登入頁面
                  </script>";
        } else {
            // 更新失敗，顯示錯誤
            echo "<script>
                    alert('密碼變更失敗，請稍後再試！');
                    window.history.back(); // 返回上一頁
                  </script>";
        }
    } else {
        // 未找到對應的 user_id，顯示錯誤
        echo "<script>
                alert('無法找到與此電子郵件關聯的用戶！');
                window.location.href = 'verify.php'; // 返回驗證頁面
              </script>";
    }
} else {
    // 非表單提交，顯示錯誤訊息
    echo "<script>
            alert('非法訪問，請重新操作！');
            window.location.href = 'index.html'; // 返回首頁
          </script>";
}

// 關閉資料庫連接
mysqli_close($link);
?>
