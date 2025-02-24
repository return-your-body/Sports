<?php
header('Content-Type: application/json; charset=utf-8');
require '../db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => '無效的預約ID']);
    exit;
}

$appointment_id = (int)$_GET['id'];

// 查詢同一個 appointment_id 合併項目與費用
$query = "
SELECT 
    d.doctor AS doctor_name,
    GROUP_CONCAT(i.item SEPARATOR '、') AS treatment_items,
    SUM(i.price) AS total_price,
    DATE_FORMAT(MAX(mr.created_at), '%Y-%m-%d %H:%i:%s') AS latest_created_time
FROM medicalrecord mr
LEFT JOIN appointment a ON mr.appointment_id = a.appointment_id
LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
LEFT JOIN item i ON mr.item_id = i.item_id
WHERE mr.appointment_id = ?
GROUP BY mr.appointment_id";

$stmt = mysqli_prepare($link, $query);
if (!$stmt) {
    echo json_encode(['error' => 'SQL錯誤: ' . mysqli_error($link)]);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $appointment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'doctor_name' => $row['doctor_name'],
        'treatment_item' => $row['treatment_items'],
        'treatment_price' => $row['total_price'],
        'created_time' => $row['latest_created_time']
    ]);
} else {
    echo json_encode(['error' => '查無資料']);
}

mysqli_stmt_close($stmt);
$link->close();
