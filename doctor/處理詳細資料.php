<?php
require '../db.php';

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_GET['id'])) {
    echo json_encode(['error' => '缺少參數！']);
    exit;
}

$appointment_id = intval($_GET['id']);

$query_details = "
SELECT 
    d.doctor AS doctor_name,
    i.item AS treatment_item,
    i.price AS treatment_price,
    mr.created_at AS created_time
FROM medicalrecord mr
LEFT JOIN appointment a ON mr.appointment_id = a.appointment_id
LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
LEFT JOIN item i ON mr.item_id = i.item_id
WHERE mr.appointment_id = ?";

$stmt = mysqli_prepare($link, $query_details);
if (!$stmt) {
    echo json_encode(['error' => 'SQL 錯誤：' . mysqli_error($link)]);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $appointment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    echo json_encode(mysqli_fetch_assoc($result));
} else {
    echo json_encode(['error' => '目前無看診資料！']);
}
?>
