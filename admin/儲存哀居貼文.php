<?php
require '../db.php'; // 連接資料庫

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 取得使用者輸入的 IG 貼文網址和分類 ID
    $post_url = trim($_POST['post_url']);
    $igpost_class_id = intval($_POST['igpost_class_id']);

    // 驗證網址格式（確保是 Instagram 貼文）
    if (!preg_match('/https:\/\/www\.instagram\.com\/p\/[a-zA-Z0-9_-]+\/?/', $post_url)) {
        die("錯誤：請輸入有效的 Instagram 貼文網址！");
    }

    // 取得 Instagram 內嵌程式碼
    $embed_api_url = "https://api.instagram.com/oembed/?url=" . urlencode($post_url);
    $embed_data = file_get_contents($embed_api_url);
    
    if ($embed_data === FALSE) {
        die("錯誤：無法取得 Instagram 內嵌程式碼！");
    }

    // 解析 API 回傳的 JSON 資料
    $embed_json = json_decode($embed_data, true);
    if (!isset($embed_json['html'])) {
        die("錯誤：Instagram 內嵌資料獲取失敗！");
    }

    $embed_code = $embed_json['html']; // 取得內嵌程式碼

    // 存入資料庫
    $stmt = mysqli_prepare($link, "INSERT INTO instagram_posts (embed_code, igpost_class_id, created_at) VALUES (?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "si", $embed_code, $igpost_class_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "成功儲存 Instagram 內嵌貼文！";
    } else {
        echo "錯誤：儲存失敗，請重試！";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
}
?>
