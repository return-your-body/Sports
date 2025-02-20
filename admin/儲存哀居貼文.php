<?php
require '../db.php'; // 連接資料庫

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST['post_url']) || empty($_POST['image_url']) || empty($_POST['caption']) || empty($_POST['igpost_class_id'])) {
    echo "<script>alert('⚠️ 所有欄位皆為必填！'); history.back();</script>";
  } else {
    $post_url = trim($_POST['post_url']);
    $image_url = trim($_POST['image_url']);
    $caption = trim($_POST['caption']);
    $igpost_class_id = intval($_POST['igpost_class_id']); // 轉換為數字，避免 SQL 錯誤

    $query = "INSERT INTO instagram_posts (post_url, image_url, caption, igpost_class_id) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "sssi", $post_url, $image_url, $caption, $igpost_class_id);
    if (mysqli_stmt_execute($stmt)) {
      echo "<script>
              alert('✅ 貼文已成功儲存！');
              setTimeout(function() {
                  window.location.href='a_igadd.php';
              }, 1000); // 1秒延遲，確保 alert 顯示
          </script>";
    } else {
      $error_message = addslashes(mysqli_error($link));
      echo "<script>
              alert('❌ 儲存失敗：" . $error_message . "');
              setTimeout(function() {
                  history.back();
              }, 1000); 
          </script>";
    }


    mysqli_stmt_close($stmt);
    mysqli_close($link);
  }
}
?>