<?php
require '../db.php';
header('Content-Type: application/json');

if (!isset($_GET['date']) || empty($_GET['date'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];
$currentTime = date('H:i');
$currentDate = date('Y-m-d');

// 查詢是否有排班
$stmt = $link->prepare("SELECT shifttime_id, shifttime FROM shifttime WHERE shifttime_id NOT IN 
    (SELECT shifttime_id FROM appointment WHERE doctorshift_id IN 
    (SELECT doctorshift_id FROM doctorshift WHERE date = ?))");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$availableTimes = [];

while ($row = $result->fetch_assoc()) {
    if ($date > $currentDate || ($date == $currentDate && $row['shifttime'] > $currentTime)) {
        $availableTimes[] = $row['shifttime'];
    }
}

echo json_encode($availableTimes);
?>
