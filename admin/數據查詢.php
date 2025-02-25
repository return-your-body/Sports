<?php
// statistics.php

header("Content-Type: application/json; charset=UTF-8");

$link = new mysqli("localhost", "帳號", "密碼", "Health24");

if ($link->connect_error) {
    die(json_encode(["error" => $link->connect_error]));
}

// 查詢醫生處理病人數
$doctorSql = "SELECT d.doctor AS doctor_name, COUNT(m.medicalrecord_id) AS total_patients
              FROM medicalrecord m
              LEFT JOIN appointment a ON m.appointment_id = a.appointment_id
              LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
              LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
              GROUP BY d.doctor
              ORDER BY total_patients DESC";
$doctorResult = $link->query($doctorSql);

$doctors = ['labels' => [], 'data' => []];
while($row = $doctorResult->fetch_assoc()){
    $doctors['labels'][] = $row['doctor_name'];
    $doctors['data'][] = (int)$row['total_patients'];
}

// 查詢治療項目使用數
$itemSql = "SELECT i.item AS treatment_name, COUNT(m.medicalrecord_id) AS total_usage
            FROM medicalrecord m
            LEFT JOIN item i ON m.item_id = i.item_id
            GROUP BY i.item
            ORDER BY total_usage DESC";
$itemResult = $link->query($itemSql);

$items = ['labels' => [], 'data' => []];
while($row = $itemResult->fetch_assoc()){
    $items['labels'][] = $row['treatment_name'];
    $items['data'][] = (int)$row['total_usage'];
}

$link->close();

echo json_encode([
    'doctorStats' => $doctors,
    'itemStats' => $items
]);
