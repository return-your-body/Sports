<?php
require '../db.php';

header('Content-Type: application/json');

$year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");
$month = isset($_GET['month']) ? str_pad(intval($_GET['month']), 2, "0", STR_PAD_LEFT) : date("m");
$day = isset($_GET['day']) ? str_pad(intval($_GET['day']), 2, "0", STR_PAD_LEFT) : "";
$doctor = isset($_GET['doctor']) ? $_GET['doctor'] : "";

// 建立 SQL 查詢條件
$whereClause = " WHERE YEAR(work_date) = '$year' AND MONTH(work_date) = '$month' ";
if (!empty($day)) {
    $whereClause .= "AND DAY(work_date) = '$day' ";
}
if (!empty($doctor)) {
    $whereClause .= "AND doctor_name = '$doctor' ";
}

// 查詢資料
$sql = "SELECT doctor_name, work_date, work_hours, overtime_hours FROM doctor_schedule " . $whereClause;
$result = mysqli_query($link, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>
