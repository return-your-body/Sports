<?php
header('Content-Type: application/json; charset=utf-8');
require '../db.php';

// 測試假資料 (確保一定有資料)
$calendar = [];
for ($i = 1; $i <= 31; $i++) {
    $calendar[] = ['date' => $i, 'count' => rand(5, 20)];
}

$bar = [
    'labels' => ['治療師A', '治療師B', '治療師C'],
    'workingHours' => [160, 150, 170],
    'overtime' => [10, 5, 15]
];

$pie = [
    'labels' => ['項目1', '項目2', '項目3'],
    'counts' => [100, 200, 150]
];

echo json_encode(['calendar' => $calendar, 'bar' => $bar, 'pie' => $pie]);
