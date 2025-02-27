<?php
session_start();
require '../db.php';

$stmt = $link->prepare("
    SELECT d.doctor, COUNT(a.appointment_id) as total
    FROM appointment a
    JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    JOIN doctor d ON ds.doctor_id = d.doctor_id
    GROUP BY d.doctor
");
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['doctor']] = $row['total'];
}

echo json_encode($data);
?>
