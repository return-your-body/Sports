<?php
require '../db.php'; // 連接資料庫

header('Content-Type: text/plain'); // AJAX 回應為純文字

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];

    if (empty($user_id) || empty($new_password)) {
        echo "請輸入完整資訊";
        exit;
    }

    // **確認用戶存在**
    $check_sql = "SELECT user_id FROM user WHERE user_id = ?";
    $check_stmt = $link->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows === 0) {
        echo "找不到該用戶";
        exit;
    }
    $check_stmt->close();

    // **更新密碼（不加密）**
    $sql = "UPDATE user SET password = ? WHERE user_id = ?";
    $stmt = $link->prepare($sql);
    
    if (!$stmt) {
        echo "SQL 錯誤：" . $link->error;
        exit;
    }

    $stmt->bind_param("si", $new_password, $user_id);
    
    if ($stmt->execute()) {
        echo "密碼變更成功";
    } else {
        echo "密碼變更失敗：" . $stmt->error;
    }

    $stmt->close();
    $link->close();
}
?>
