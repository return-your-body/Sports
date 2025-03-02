<?php
header("Content-Type: application/json; charset=UTF-8");
include '../db.php'; // 連接資料庫

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $embed_code = trim($_POST['embed_code']);
    $igpost_class_id = trim($_POST['igpost_class_id']);
    
    if (!isset($_FILES['image_data']) || $_FILES['image_data']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "message" => "請上傳有效的圖片！"]);
        exit;
    }
    
    $image_data = file_get_contents($_FILES['image_data']['tmp_name']);
    
    if (empty($title) || empty($description) || empty($embed_code) || empty($igpost_class_id)) {
        echo json_encode(["success" => false, "message" => "請填寫所有欄位！"]);
        exit;
    }
    
    if (!filter_var($embed_code, FILTER_VALIDATE_URL)) {
        echo json_encode(["success" => false, "message" => "請輸入有效的 Instagram 連結！"]);
        exit;
    }
    
    // 檢查是否已經存在相同的貼文
    $check_stmt = $link->prepare("SELECT COUNT(*) FROM instagram_posts WHERE embed_code = ?");
    $check_stmt->bind_param("s", $embed_code);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();
    
    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "這篇貼文已經存在，請勿重複新增！"]);
        exit;
    }
    
    $stmt = $link->prepare("INSERT INTO instagram_posts (title, description, embed_code, image_data, igpost_class_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssi", $title, $description, $embed_code, $image_data, $igpost_class_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "貼文新增成功！"]);
    } else {
        echo json_encode(["success" => false, "message" => "新增失敗，請重試！"]);
    }
    
    $stmt->close();
    $link->close();
}
?>