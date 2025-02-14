<?php
require '../db.php';
header('Content-Type: application/json');

if (!isset($_GET['appointment_id'])) {
    echo json_encode(["error" => "缺少 appointment_id"]);
    exit;
}

$appointmentId = intval($_GET['appointment_id']);

$sql = "
    SELECT d.doctor_id, ds.date 
    FROM appointment a
    JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    JOIN doctor d ON ds.doctor_id = d.doctor_id
    WHERE a.appointment_id = ?
";

$stmt = $link->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "SQL 錯誤: " . $link->error]);
    exit;
}

$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(["error" => "找不到該預約的治療師資料"]);
} else {
    echo json_encode($row);
}

$stmt->close();
$link->close();
?>
