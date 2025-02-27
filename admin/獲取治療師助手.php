<?php
// 顯示錯誤訊息
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 引入資料庫連線
require '../db.php';

header('Content-Type: application/json');

// 查詢治療師與助理
$query = "SELECT doctor_id, doctor FROM doctor";
$result = $link->query($query);

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = [
        "doctor_id" => $row["doctor_id"],
        "doctor" => $row["doctor"] // 確保返回正確的治療師名稱
    ];
}

// 輸出 JSON
echo json_encode($doctors, JSON_UNESCAPED_UNICODE);
?>
