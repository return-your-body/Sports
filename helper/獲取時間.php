<?php
require '../db.php';

$doctor_id = $_GET['doctor_id'] ?? null;
$date = $_GET['date'] ?? null;

if (!$doctor_id || !$date) {
    echo json_encode([]);
    exit;
}

// 查詢對應醫生的班表時段
$sql = "SELECT s.shifttime 
        FROM doctorshift ds
        JOIN shifttime s ON s.shifttime_id BETWEEN ds.go AND ds.off
        WHERE ds.doctor_id = ? AND ds.date = ?";

$stmt = $link->prepare($sql);
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$available_times = [];
while ($row = $result->fetch_assoc()) {
    $available_times[] = $row['shifttime'];
}

echo json_encode($available_times);
$link->close();
?>
