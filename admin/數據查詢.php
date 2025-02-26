<?php
header("Content-Type: application/json; charset=UTF-8");
require '../db.php';  // 使用 db.php 連接 MySQL

// 接收篩選條件
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$day = isset($_GET['day']) ? $_GET['day'] : null;
$doctor = isset($_GET['doctor']) ? $_GET['doctor'] : null;

// 日期篩選
$dateFilter = "$year-$month" . ($day ? "-$day" : "");

// 取得治療師統計
$doctorSql = "
    SELECT d.doctor, SUM(ds.working_hours) AS total_hours
    FROM doctorshift ds
    JOIN doctor d ON ds.doctor_id = d.doctor_id
    WHERE DATE_FORMAT(ds.shift_date, '%Y-%m-%d') = ?";
if ($doctor) {
    $doctorSql .= " AND d.doctor_id = ?";
}
$doctorSql .= " GROUP BY d.doctor";

$doctorStmt = $link->prepare($doctorSql);
if ($doctor) {
    $doctorStmt->bind_param("si", $dateFilter, $doctor);
} else {
    $doctorStmt->bind_param("s", $dateFilter);
}
$doctorStmt->execute();
$doctorResult = $doctorStmt->get_result();

$doctorStats = ['labels' => [], 'data' => []];
while ($row = $doctorResult->fetch_assoc()) {
    $doctorStats['labels'][] = $row['doctor'];
    $doctorStats['data'][] = (int)$row['total_hours'];
}

// 取得治療項目統計
$itemSql = "
    SELECT i.item, COUNT(m.medicalrecord_id) AS total_usage
    FROM medicalrecord m
    JOIN item i ON m.item_id = i.item_id
    WHERE DATE_FORMAT(m.record_date, '%Y-%m-%d') = ?";
if ($doctor) {
    $itemSql .= " AND m.doctor_id = ?";
}
$itemSql .= " GROUP BY i.item";

$itemStmt = $link->prepare($itemSql);
if ($doctor) {
    $itemStmt->bind_param("si", $dateFilter, $doctor);
} else {
    $itemStmt->bind_param("s", $dateFilter);
}
$itemStmt->execute();
$itemResult = $itemStmt->get_result();

$itemStats = ['labels' => [], 'data' => []];
while ($row = $itemResult->fetch_assoc()) {
    $itemStats['labels'][] = $row['item'];
    $itemStats['data'][] = (int)$row['total_usage'];
}

// 回傳 JSON
echo json_encode([
    "doctorStats" => $doctorStats,
    "itemStats" => $itemStats
], JSON_PRETTY_PRINT);
?>
