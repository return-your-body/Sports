<?php
session_start();
include "db.php";

$user_id = $_POST['user_id'];
$token = $_POST['token'];
$email = $_POST['email']; // 從前端傳入

// 查詢驗證碼是否正確
$stmt = $link->prepare("SELECT * FROM email_verification WHERE user_id = ? AND token = ?");
$stmt->bind_param("is", $user_id, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // 驗證成功，插入 `people` 表
    $stmtInsert = $link->prepare("INSERT INTO people (`user_id`, `email`) VALUES (?, ?)");
    $stmtInsert->bind_param("is", $user_id, $email);
    if ($stmtInsert->execute()) {
        // 刪除 `email_verification` 記錄
        $deleteStmt = $link->prepare("DELETE FROM email_verification WHERE user_id = ?");
        $deleteStmt->bind_param("i", $user_id);
        $deleteStmt->execute();

        echo json_encode(["status" => "success", "message" => "驗證成功！請重新登入。"]);
    } else {
        echo json_encode(["status" => "error", "message" => "驗證成功，但存入 email 失敗！"]);
    }
} else {
    // 驗證失敗，刪除 `user` 表的資料
    $deleteUser = $link->prepare("DELETE FROM user WHERE user_id = ?");
    $deleteUser->bind_param("i", $user_id);
    $deleteUser->execute();

    echo json_encode(["status" => "error", "message" => "驗證碼錯誤，帳號已刪除，請重新註冊！"]);
}
?>
