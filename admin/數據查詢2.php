<?php
require '../db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'day';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$day = $_GET['day'] ?? date('d');
$doctor_id = $_GET['doctor_id'] ?? 0;
$item_filter = $_GET['item_name'] ?? '';

$dateTarget = "$year-$month-$day";
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

// 取得每個 appointment_id 的所有使用項目
$item_map = [];
$res_item = mysqli_query($link, "
  SELECT mr.appointment_id, GROUP_CONCAT(i.item SEPARATOR ', ') AS items
  FROM medicalrecord mr
  JOIN item i ON mr.item_id = i.item_id
  GROUP BY mr.appointment_id
");
while ($r = mysqli_fetch_assoc($res_item)) {
  $item_map[$r['appointment_id']] = $r['items'];
}

// 項目數比例 (詳細)
$item_details = [];
$res1 = mysqli_query($link, "
  SELECT ds.date AS record_date, d.doctor AS doctor_name, i.item AS item_name, COUNT(*) AS count
  FROM appointment ap
  JOIN doctorshift ds ON ds.doctorshift_id = ap.doctorshift_id
  JOIN doctor d ON ds.doctor_id = d.doctor_id
  JOIN medicalrecord mr ON mr.appointment_id = ap.appointment_id
  JOIN item i ON mr.item_id = i.item_id
  WHERE $dsWhere
  GROUP BY ds.date, d.doctor_id, i.item_id
");
while ($r = mysqli_fetch_assoc($res1)) {
  $item_details[] = $r;
}

// 項目數比例 (合併版)
$item_summary = [];
foreach ($item_details as $row) {
  $item_name = $row['item_name'];
  if (!isset($item_summary[$item_name])) {
    $item_summary[$item_name] = 0;
  }
  $item_summary[$item_name] += $row['count'];
}
$item_summary = array_map(function($item_name) use ($item_summary) {
  return ['item_name' => $item_name, 'count' => $item_summary[$item_name]];
}, array_keys($item_summary));

// 預約人數比例 (詳細)
$appointment_details = [];
$res2 = mysqli_query($link, "
  SELECT ap.appointment_id, ds.date AS record_date, d.doctor AS doctor_name, p.name AS user_name, st.shifttime AS appointment_time
  FROM appointment ap
  JOIN doctorshift ds ON ds.doctorshift_id = ap.doctorshift_id
  JOIN doctor d ON ds.doctor_id = d.doctor_id
  JOIN shifttime st ON ap.shifttime_id = st.shifttime_id
  JOIN people p ON ap.people_id = p.people_id
  WHERE $dsWhere
");
while ($r = mysqli_fetch_assoc($res2)) {
  $r['item_names'] = $item_map[$r['appointment_id']] ?? '-';
  $appointment_details[] = $r;
}

// 預約人數比例 (合併版)
$appointment_counts = [];
foreach ($appointment_details as $row) {
  $doctor = $row['doctor_name'];
  if (!isset($appointment_counts[$doctor])) {
    $appointment_counts[$doctor] = 0;
  }
  $appointment_counts[$doctor]++;
}
$appointment_counts = array_map(function($doctor) use ($appointment_counts) {
  return ['doctor_name' => $doctor, 'count' => $appointment_counts[$doctor]];
}, array_keys($appointment_counts));

// 收入統計 (詳細)
$income_details = [];
$res3 = mysqli_query($link, "
  SELECT ds.date AS record_date, d.doctor AS doctor_name, i.item AS item_name, i.price AS unit_price, SUM(i.price) AS total_income
  FROM appointment ap
  JOIN doctorshift ds ON ds.doctorshift_id = ap.doctorshift_id
  JOIN doctor d ON ds.doctor_id = d.doctor_id
  JOIN medicalrecord mr ON mr.appointment_id = ap.appointment_id
  JOIN item i ON mr.item_id = i.item_id
  WHERE $dsWhere
  GROUP BY ds.date, d.doctor_id, i.item_id
");
while ($r = mysqli_fetch_assoc($res3)) {
  $income_details[] = $r;
}

// 收入統計 (合併版)
$income_summary = [];
foreach ($income_details as $row) {
  $item_name = $row['item_name'];
  if (!isset($income_summary[$item_name])) {
    $income_summary[$item_name] = 0;
  }
  $income_summary[$item_name] += $row['total_income'];
}
$income_summary = array_map(function($item_name) use ($income_summary) {
  return ['item_name' => $item_name, 'total_income' => $income_summary[$item_name]];
}, array_keys($income_summary));

// 最後輸出
echo json_encode([
  'item_summary' => $item_summary,
  'item_details' => $item_details,
  'appointment_counts' => $appointment_counts,
  'appointment_details' => $appointment_details,
  'income_summary' => $income_summary,
  'income_details' => $income_details
]);
?>
