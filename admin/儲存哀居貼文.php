<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../db.php'; // 連接資料庫

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_url = trim($_POST['post_url']);
    $post_title = trim($_POST['post_title']);
    $post_desc = trim($_POST['post_desc']);
    $igpost_class_id = intval($_POST['igpost_class_id']);

    if (empty($post_url) || empty($post_title) || empty($post_desc) || empty($igpost_class_id)) {
        header("Location: a_igadd.php?message=錯誤：所有欄位皆必填！");
        exit;
    }

    // ✅ 允許的 Instagram 連結格式
    if (!preg_match('/^https:\\/\\/www\\.instagram\\.com\\/(?:[\\w.-]+\\/)?p\\/[a-zA-Z0-9_-]+\\/?$/', $post_url)) {
        header("Location: a_igadd.php?message=錯誤：請輸入正確的 Instagram 貼文網址！");
        exit;
    }

    // ✅ 檢查分類是否存在
    $check_class = mysqli_prepare($link, "SELECT COUNT(*) FROM igpost_class WHERE igpost_class_id = ?");
    mysqli_stmt_bind_param($check_class, "i", $igpost_class_id);
    mysqli_stmt_execute($check_class);
    mysqli_stmt_bind_result($check_class, $class_count);
    mysqli_stmt_fetch($check_class);
    mysqli_stmt_close($check_class);

    if ($class_count == 0) {
        header("Location: a_igadd.php?message=錯誤：無效的分類 ID！");
        exit;
    }

    // ✅ 檢查網址是否已存在
    $check_url = mysqli_prepare($link, "SELECT COUNT(*) FROM instagram_posts WHERE embed_code = ?");
    mysqli_stmt_bind_param($check_url, "s", $post_url);
    mysqli_stmt_execute($check_url);
    mysqli_stmt_bind_result($check_url, $url_count);
    mysqli_stmt_fetch($check_url);
    mysqli_stmt_close($check_url);

    if ($url_count > 0) {
        header("Location: a_igadd.php?message=錯誤：該 Instagram 貼文已存在，請勿重複儲存！");
        exit;
    }

    // ✅ 取得 Instagram 貼文圖片
    $image_url = getInstagramImage($post_url);
    if (!$image_url) {
        header("Location: a_igadd.php?message=錯誤：無法取得 Instagram 貼文的圖片！");
        exit;
    }

    // ✅ 存入資料庫
    $stmt = mysqli_prepare($link, "INSERT INTO instagram_posts (embed_code, image_data, title, description, igpost_class_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "ssssi", $post_url, $image_url, $post_title, $post_desc, $igpost_class_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success) {
        header("Location: a_igadd.php?message=貼文儲存成功！");
    } else {
        header("Location: a_igadd.php?message=錯誤：資料庫插入失敗！SQL錯誤：" . mysqli_error($link));
    }
    exit;
}

// 🔹 **自動取得 Instagram 貼文的圖片網址**
function getInstagramImage($post_url) {
    $api_url = "https://www.instagram.com/oembed/?url=" . urlencode($post_url);

    // 使用 cURL 抓取 API 資料
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        return $data['thumbnail_url'] ?? null;
    }
    return null;
}
?>
