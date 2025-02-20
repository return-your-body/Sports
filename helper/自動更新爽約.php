<?php
require '../db.php';

// 取得當前時間
$current_time = date('H:i:s');
$current_date = date('Y-m-d');

$query = "
    UPDATE appointment 
    SET status_id = 5 
    WHERE status_id = 1 -- 仍是預約狀態
    AND CONCAT(date, ' ', shifttime) < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
";

$stmt = $link->prepare($query);
if ($stmt->execute()) {
    echo "✅ 已更新逾時預約為爽約";
} else {
    echo "❌ 更新失敗：" . $stmt->error;
}
?>
