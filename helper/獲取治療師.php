<?php
require '../db.php';
header('Content-Type: application/json');

// 確保有 appointment_id 參數
if (!isset($_GET['appointment_id'])) {
    echo json_encode(["success" => false, "error" => "缺少預約 ID"]);
    exit;
}

$appointment_id = intval($_GET['appointment_id']);

// 查詢對應的 doctor_id
$query = "SELECT ds.doctor_id 
          FROM appointment a 
          JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
          WHERE a.appointment_id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(["success" => true, "doctor_id" => $row['doctor_id']]);
} else {
    echo json_encode(["success" => false, "error" => "未找到醫生 ID"]);
}

$stmt->close();
$link->close();
?>
