<?php
require '../db.php'; // 連接資料庫

// 檢查是否為 POST 請求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 檢查是否有必填欄位未填寫
    if (empty($_POST['post_url']) || empty($_POST['image_url']) || empty($_POST['caption'])) {
        echo "<script>
                alert('⚠️ 所有欄位皆為必填！');
                history.back();
              </script>";
        exit;
    }

    // 取得貼文資料
    $post_url = trim($_POST['post_url']);   // 貼文網址
    $image_url = trim($_POST['image_url']); // 貼文圖片網址
    $caption = trim($_POST['caption']);     // 貼文文字內容

    // 準備 SQL 插入語句
    $query = "INSERT INTO instagram_posts (post_url, image_url, caption) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "sss", $post_url, $image_url, $caption);

    // 執行 SQL 語句
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
                alert('✅ 貼文已成功儲存！');
                window.location.href = 'a_igadd.php'; // 成功後跳轉到貼文列表頁
              </script>";
    } else {
        echo "<script>
                alert('❌ 儲存失敗：" . mysqli_error($link) . "');
                history.back();
              </script>";
    }

    // 關閉連線
    mysqli_stmt_close($stmt);
    mysqli_close($link);
}
?>
