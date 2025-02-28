<?php
include '../db.php';

$appointment_id = $_POST['appointment_id'];

$sql = "UPDATE appointments SET status_id = 8 WHERE appointment_id = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();

echo json_encode(["success" => $stmt->affected_rows > 0]);
?>
