<?php
header('Content-Type: application/json');
include '../db.php'; // 連接資料庫

$type = $_GET['type'] ?? '';

if ($type == 'hours') {
    $query = "SELECT d.doctor_id, d.doctor, SUM(TIMESTAMPDIFF(HOUR, ds.go, ds.off)) AS total_hours
              FROM doctorshift ds
              JOIN doctor d ON ds.doctor_id = d.doctor_id
              GROUP BY d.doctor_id, d.doctor";
} elseif ($type == 'appointments') {
    $query = "SELECT d.doctor_id, d.doctor, COUNT(a.appointment_id) AS total_appointments
              FROM appointment a
              JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
              JOIN doctor d ON ds.doctor_id = d.doctor_id
              GROUP BY d.doctor_id, d.doctor";
} elseif ($type == 'income') {
    $query = "SELECT d.doctor_id, d.doctor, SUM(i.price) AS total_income
              FROM medicalrecord m
              JOIN appointment a ON m.appointment_id = a.appointment_id
              JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
              JOIN doctor d ON ds.doctor_id = d.doctor_id
              JOIN item i ON m.item_id = i.item_id
              GROUP BY d.doctor_id, d.doctor";
} elseif ($type == 'calendar') {
    $query = "SELECT created_at AS start, COUNT(appointment_id) AS title
              FROM appointment
              GROUP BY DATE(created_at)";
} else {
    echo json_encode(['error' => 'Invalid type']);
    exit;
}

$result = mysqli_query($link, $query);
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);
echo json_encode($data);
?>
