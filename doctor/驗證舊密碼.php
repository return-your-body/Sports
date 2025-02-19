<?php
require '../db.php'; // 連接資料庫

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $old_password = $_POST['old_password'];

    // 查詢使用者的密碼
    $sql = "SELECT password FROM user WHERE user_id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_password);
        $stmt->fetch();

        if ($old_password === $db_password) { // 直接比對明文密碼
            echo "success"; // 舊密碼正確
        } else {
            echo "error"; // 舊密碼錯誤
        }
    } else {
        echo "error"; // 使用者不存在
    }

    $stmt->close();
    $link->close();
}
?>
