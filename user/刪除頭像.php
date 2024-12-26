<?php
include '../db.php'; // 引入資料庫連接檔案

session_start();
if (!isset($_SESSION['帳號'])) {
    echo json_encode(['success' => false, 'message' => '用戶未登入！']);
    exit;
}

// 獲取當前用戶帳號
$帳號 = $_SESSION['帳號'];

// 查詢用戶 ID
$query = "SELECT user_id FROM user WHERE account = '$帳號'";
$result = mysqli_query($link, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $user_id = $row['user_id'];
} else {
    echo json_encode(['success' => false, 'message' => '未找到用戶資料！']);
    exit;
}

// 刪除頭像
$query = "UPDATE people SET images = NULL WHERE user_id = $user_id";

if (mysqli_query($link, $query)) {
    echo json_encode(['success' => true, 'message' => '頭像刪除成功！']);
} else {
    echo json_encode(['success' => false, 'message' => '頭像刪除失敗！']);
}
?>