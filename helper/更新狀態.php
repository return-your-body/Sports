<?php
require '../db.php';
header('Content-Type: application/json');

if (!isset($_POST['id']) ) {
    echo json_encode(["error" => "缺少id"]);
    exit;
}

if (!isset($_POST['doctorshift_id']) ) {
    echo json_encode(["error" => "缺少doctorshift_id"]);
    exit;
}

if (!isset($_POST['shifttime_id'])) {
    echo json_encode(["error" => "缺少shifttime_id"]);
    exit;
}



$appointmentId = intval($_POST['id']);
$doctorshiftId = intval($_POST['doctorshift_id']);
$shifttimeId = intval($_POST['shifttime_id']);

$sql = "UPDATE appointment SET doctorshift_id = ?, shifttime_id = ? WHERE appointment_id = ?";
$stmt = $link->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "SQL 錯誤: " . $link->error]);
    exit;
}
$stmt->bind_param("iii", $doctorshiftId, $shifttimeId, $appointmentId);
$stmt->execute();

echo json_encode(["success" => true]);
$stmt->close();
$link->close();
?>
