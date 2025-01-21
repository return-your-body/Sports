<?php
// 引入資料庫連線
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 接收表單資料
    $appointment_id = intval($_POST['appointment_id']);
    $item_ids = $_POST['item_ids']; // 多選項目會是一個陣列

    // 檢查是否有選擇診療項目
    if (empty($item_ids)) {
        die('請至少選擇一個診療項目。');
    }

    // 取得當前時間作為 created_at
    $created_at = date('Y-m-d H:i:s');

    // 插入每個診療項目到資料表
    $query = "INSERT INTO medicalrecord (appointment_id, item_id, created_at) VALUES (?, ?, ?)";
    $stmt = $link->prepare($query);

    foreach ($item_ids as $item_id) {
        $item_id = intval($item_id); // 確保 item_id 是整數
        $stmt->bind_param("iis", $appointment_id, $item_id, $created_at);

        if (!$stmt->execute()) {
            echo "插入失敗: " . $stmt->error;
            exit;
        }
    }

    // 關閉連線
    $stmt->close();
    $link->close();

    echo "診療紀錄新增成功！";
    echo '<a href="form_page.html">返回表單</a>'; // 提供返回表單連結
}
?>
