<?php
// 引入資料庫連線檔案
// 這段代碼包括資料庫連線的設定和建立連線，方便後續進行資料操作
include '../db.php';

// 檢查是否是透過 POST 方法提交的請求
// $_SERVER['REQUEST_METHOD'] 用於檢查當前請求的 HTTP 方法是否為 POST，
// 只有在表單提交時才執行下面的邏輯
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 取得表單中傳遞的資料，並使用 mysqli_real_escape_string 避免 SQL 注入攻擊
  // 將用戶輸入的值（項目名稱、項目說明、項目費用）安全地存入變數中
  $item = mysqli_real_escape_string($link, $_POST['item']);       // 項目名稱
  $caption = mysqli_real_escape_string($link, $_POST['caption']); // 項目說明
  $price = mysqli_real_escape_string($link, $_POST['price']);     // 項目費用

  // 構建插入資料的 SQL 語句
  // 該語句將表單提交的數據插入到資料表 `item` 的對應欄位中
  $sql = "INSERT INTO item (item, caption, price) VALUES ('$item', '$caption', '$price')";

  // 執行 SQL 語句，並檢查是否執行成功
  if (mysqli_query($link, $sql)) {
    // 如果執行成功，使用 JavaScript 彈出提示框告知用戶，並跳轉回主頁
    echo "<script>
                alert('新增成功！'); // 提示用戶新增成功
                window.location.href = 'a_treatment.php'; // 跳轉回主頁
              </script>";
  } else {
    // 如果執行失敗，使用 JavaScript 彈出錯誤訊息，並返回上一頁
    // mysqli_error($link) 用於獲取 SQL 語句執行失敗的詳細錯誤訊息
    echo "<script>
                alert('新增失敗：" . mysqli_error($link) . "'); // 提示用戶新增失敗，並顯示具體錯誤訊息
                window.history.back(); // 返回上一頁，讓用戶重新操作
              </script>";
  }
}
?>