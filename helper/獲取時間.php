<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../db.php';
header('Content-Type: application/json');

if (!isset($_GET['date']) || empty($_GET['date'])) {
    echo json_encode(['error' => '請提供日期']);
    exit;
}

$date = $_GET['date'];
$currentTime = date('H:i'); // 現在時間
$currentDate = date('Y-m-d'); // 今天日期

// 查詢當天醫生的上班 `shifttime_id` (go ~ off)
$stmt = $link->prepare("
    SELECT MIN(go) as start_id, MAX(off) as end_id 
    FROM doctorshift 
    WHERE date = ?
");
$stmt->bind_param("s", $date);
$stmt->execute();
$stmt->bind_result($start_id, $end_id);
$stmt->fetch();
$stmt->close();

if (!$start_id || !$end_id) {
    echo json_encode(['error' => '該日期無醫生排班']);
    exit;
}

// 查詢對應 `shifttime_id` 的時間，並排除已預約
$stmt = $link->prepare("
    SELECT shifttime.shifttime_id, shifttime.shifttime 
    FROM shifttime
    WHERE shifttime.shifttime_id BETWEEN ? AND ?
    AND shifttime.shifttime_id NOT IN (
        SELECT shifttime_id FROM appointment WHERE doctorshift_id IN (
            SELECT doctorshift_id FROM doctorshift WHERE date = ?
        )
    )
    ORDER BY shifttime.shifttime_id ASC
");
$stmt->bind_param("iis", $start_id, $end_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$availableTimes = [];

// 過濾掉過去時間
while ($row = $result->fetch_assoc()) {
    if ($date > $currentDate || ($date == $currentDate && $row['shifttime'] > $currentTime)) {
        $availableTimes[] = [
            'id' => $row['shifttime_id'],
            'time' => $row['shifttime']
        ];
    }
}

echo json_encode($availableTimes);
?>