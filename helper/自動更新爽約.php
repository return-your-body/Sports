<?php
require '../db.php';

// 設定超時分鐘數
$overdue_minutes = 10;
$current_time = date('Y-m-d H:i:s');

// 更新超時未報到的預約為 "爽約"
$query = "UPDATE appointment 
          SET status_id = 5 
          WHERE status_id = 1 
          AND TIMESTAMPDIFF(MINUTE, CONCAT(date, ' ', appointment_time), ?) > ?";
$stmt = $link->prepare($query);
$stmt->bind_param("si", $current_time, $overdue_minutes);
$stmt->execute();
$stmt->close();

// 記錄違規 (爽約 +1)
$query = "SELECT people_id FROM appointment WHERE status_id = 5";
$result = $link->query($query);

while ($row = $result->fetch_assoc()) {
    $people_id = $row['people_id'];

    // 更新黑點
    $update_black = $link->prepare("UPDATE people SET black = black + 1 WHERE people_id = ?");
    $update_black->bind_param("i", $people_id);
    $update_black->execute();

    // 記錄違規到 blacklist
    $insert_blacklist = $link->prepare("INSERT INTO blacklist (people_id, black_id, violation_date) VALUES (?, 2, NOW())");
    $insert_blacklist->bind_param("i", $people_id);
    $insert_blacklist->execute();
}

echo json_encode(["success" => true, "message" => "已自動更新爽約狀態"]);
?>
