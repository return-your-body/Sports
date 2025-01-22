<?php
// 引入資料庫連線檔案
// 包含資料庫連線設定，建立與資料庫的連線，以便進行後續的 SQL 操作
include '../db.php';

// 檢查是否是透過 POST 方法提交的請求
// $_SERVER['REQUEST_METHOD'] 用於檢查當前請求的 HTTP 方法是否為 POST
// 這裡確保只有透過表單提交的請求才會執行下面的邏輯
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 使用 mysqli_real_escape_string 處理表單提交的數據，防止 SQL 注入攻擊
    // 取得項目的唯一標識符 item_id
    $item_id = mysqli_real_escape_string($link, $_POST['item_id']);

    // 取得項目名稱，從表單的 item 欄位獲取值
    $item = mysqli_real_escape_string($link, $_POST['item']);

    // 取得項目說明，從表單的 caption 欄位獲取值
    $caption = mysqli_real_escape_string($link, $_POST['caption']);

    // 取得項目費用，從表單的 price 欄位獲取值
    $price = mysqli_real_escape_string($link, $_POST['price']);

    // 構建更新資料的 SQL 語句
    // 該語句將更新資料表 `item` 中與指定 item_id 匹配的資料行
    $sql = "UPDATE item 
            SET item = '$item', 
                caption = '$caption', 
                price = '$price' 
            WHERE item_id = '$item_id'";

    // 執行 SQL 語句，檢查是否執行成功
    if (mysqli_query($link, $sql)) {
        // 如果 SQL 執行成功，使用 JavaScript 彈出提示框通知用戶
        // 並將用戶導向返回主頁（例如 a_treatment.php）
        echo "<script>
                alert('修改成功！'); // 提示用戶修改成功
                window.location.href = 'a_treatment.php'; // 跳轉回主頁
              </script>";
    } else {
        // 如果 SQL 執行失敗，使用 JavaScript 彈出錯誤提示框，顯示具體的 SQL 錯誤信息
        // 並返回上一頁，讓用戶重新操作
        echo "<script>
                alert('修改失敗：" . mysqli_error($link) . "'); // 顯示錯誤原因
                window.history.back(); // 返回上一頁
              </script>";
    }
}
?>