<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../db.php';
header('Content-Type: application/json');

// 驗證 date 參數
if (!isset($_GET['date']) || empty($_GET['date'])) {
    echo json_encode(['error' => '請提供日期']);
    exit;
}

$date = $_GET['date'];
$currentTime = date('H:i'); // 現在的時間
$currentDate = date('Y-m-d'); // 今天日期

// 查詢當天醫生的排班
$stmt = $link->prepare("
    SELECT shifttime.shifttime_id, shifttime.shifttime 
    FROM shifttime
    JOIN doctorshift ON doctorshift.date = ?
    WHERE shifttime.shifttime_id NOT IN (
        SELECT shifttime_id FROM appointment WHERE doctorshift_id IN (
            SELECT doctorshift_id FROM doctorshift WHERE date = ?
        )
    )
    ORDER BY shifttime.shifttime ASC
");

$stmt->bind_param("ss", $date, $date);
$stmt->execute();
$result = $stmt->get_result();

$availableTimes = [];

// 過濾掉過去時間
while ($row = $result->fetch_assoc()) {
    if ($date > $currentDate || ($date == $currentDate && $row['shifttime'] > $currentTime)) {
        $availableTimes[] = ['id' => $row['shifttime_id'], 'time' => $row['shifttime']];
    }
}

// 如果沒有可用時段
if (empty($availableTimes)) {
    echo json_encode(['error' => '無可用時段']);
} else {
    echo json_encode($availableTimes);
}
?>
