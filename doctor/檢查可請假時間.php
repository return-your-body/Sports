<?php
require '../db.php';
header('Content-Type: application/json');

$doctor_id = $_GET['doctor'] ?? '';
$date = $_GET['date'] ?? '';

if (!$doctor_id || !$date) {
    echo json_encode(["success" => false, "message" => "缺少參數"]);
    exit;
}

// 查詢治療師班表
$sql = "SELECT go, off FROM doctorshift WHERE doctor_id = ? AND date = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "is", $doctor_id, $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo json_encode(["success" => false, "message" => "治療師當天無排班"]);
    exit;
}

$go = $row['go'];
$off = $row['off'];

$sql = "SELECT shifttime FROM shifttime WHERE shifttime_id BETWEEN ? AND ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "ii", $go, $off);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$available_times = [];
while ($row = mysqli_fetch_assoc($result)) {
    $available_times[] = $row;
}

echo json_encode(["success" => true, "times" => $available_times]);
?>
