<?php
require '../db.php'; // 連接資料庫

$posts_per_page = 18; // 每頁顯示 18 筆資料
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

$query = "SELECT image_data, title, description, embed_code FROM instagram_posts WHERE igpost_class_id = 1 ORDER BY created_at DESC LIMIT ?, ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "ii", $offset, $posts_per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$posts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $posts[] = [
        'image_data' => base64_encode($row['image_data']),
        'title' => htmlspecialchars($row['title']),
        'description' => mb_strimwidth(htmlspecialchars($row['description']), 0, 50, "..."),
        'embed_code' => htmlspecialchars($row['embed_code'])
    ];
}

header('Content-Type: application/json');
echo json_encode(['posts' => $posts]);
?>
