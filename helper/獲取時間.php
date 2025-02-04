<?php
require '../db.php';

if (!isset($_GET['date']) || empty($_GET['date']) || !isset($_GET['appointment_id'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];
$appointment_id = $_GET['appointment_id'];

// 查詢預約的醫生 ID
$queryDoctor = "SELECT doctor_id FROM appointment WHERE appointment_id = ?";
$stmt = $link->prepare($queryDoctor);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    echo json_encode([]);
    exit;
}

$doctor_id = $doctor['doctor_id'];

// 查詢該醫生當天可用的時段
$query = "SELECT s.shifttime_id, s.shifttime
          FROM shifttime s
          JOIN doctorshift d ON s.shifttime_id BETWEEN d.go AND d.off
          WHERE d.date = ? AND d.doctor_id = ?";

$stmt = $link->prepare($query);
$stmt->bind_param("si", $date, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

$times = [];
while ($row = $result->fetch_assoc()) {
    $times[] = $row;
}

// 回傳 JSON
echo json_encode($times);
?>
