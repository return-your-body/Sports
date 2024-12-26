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

// 檢查檔案是否上傳
if (isset($_FILES['profilePicture']['name']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profilePicture']['tmp_name'];
    $fileType = $_FILES['profilePicture']['type'];

    // 檢查檔案是否為圖片類型
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => '只允許上傳圖片檔案！']);
        exit;
    }

    // 儲存圖片到資料庫
    $imageData = addslashes(file_get_contents($fileTmpPath));
    $query = "UPDATE people SET images = '$imageData' WHERE user_id = $user_id";

    if (mysqli_query($link, $query)) {
        echo json_encode(['success' => true, 'message' => '頭像上傳成功！']);
    } else {
        echo json_encode(['success' => false, 'message' => '頭像上傳失敗！']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '上傳失敗，請重試！']);
}
?>
