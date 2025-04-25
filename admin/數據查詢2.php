<?php
require '../db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'day';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$day = $_GET['day'] ?? date('d');
$doctor_id = $_GET['doctor_id'] ?? 0;

$dateTarget = "$year-$month-$day";
$where = "1";
$dsWhere = "1";

switch ($type) {
  case 'year':
    $dsWhere .= " AND YEAR(ds.date) = $year";
    break;
  case 'month':
    $dsWhere .= " AND YEAR(ds.date) = $year AND MONTH(ds.date) = $month";
    break;
  case 'day':
  default:
    $dsWhere .= " AND ds.date = '$dateTarget'";
    break;
}
if ($doctor_id != 0) {
  $dsWhere .= " AND ds.doctor_id = $doctor_id";
}

// 項目數比例
$item_counts = [];
$res1 = mysqli_query($link, "
  SELECT d.doctor AS doctor_name, i.item AS item_name, COUNT(*) AS count
  FROM appointment ap
  JOIN doctorshift ds ON ds.doctorshift_id = ap.doctorshift_id
  JOIN doctor d ON ds.doctor_id = d.doctor_id
  JOIN medicalrecord mr ON mr.appointment_id = ap.appointment_id
  JOIN item i ON mr.item_id = i.item_id
  WHERE $dsWhere
  GROUP BY d.doctor_id, i.item_id
");
while ($r = mysqli_fetch_assoc($res1)) {
  $item_counts[] = $r;
}

// 預約人數比例
$appointment_counts = [];
$res2 = mysqli_query($link, "
  SELECT d.doctor AS doctor_name, COUNT(*) AS count
  FROM appointment ap
  JOIN doctorshift ds ON ds.doctorshift_id = ap.doctorshift_id
  JOIN doctor d ON ds.doctor_id = d.doctor_id
  WHERE $dsWhere
  GROUP BY d.doctor_id
");
while ($r = mysqli_fetch_assoc($res2)) {
  $appointment_counts[] = $r;
}

// 收入統計
$income_data = [];
$res3 = mysqli_query($link, "
  SELECT d.doctor AS doctor_name, i.item AS item_name, SUM(i.price) AS income
  FROM appointment ap
  JOIN doctorshift ds ON ds.doctorshift_id = ap.doctorshift_id
  JOIN doctor d ON ds.doctor_id = d.doctor_id
  JOIN medicalrecord mr ON mr.appointment_id = ap.appointment_id
  JOIN item i ON mr.item_id = i.item_id
  WHERE $dsWhere
  GROUP BY d.doctor_id, i.item_id
");
while ($r = mysqli_fetch_assoc($res3)) {
  $income_data[] = $r;
}

echo json_encode([
  'item_counts' => $item_counts,
  'appointment_counts' => $appointment_counts,
  'income_data' => $income_data
]);
?>