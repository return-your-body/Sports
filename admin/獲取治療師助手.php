<?php
require '../db.php';  // 連接資料庫

header('Content-Type: application/json; charset=UTF-8');

$sql = "SELECT doctor_id, doctor FROM doctor";
$result = $link->query($sql);

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = [
        'id' => $row['doctor_id'],
        'name' => $row['doctor']
    ];
}

echo json_encode($doctors, JSON_UNESCAPED_UNICODE);
?>
