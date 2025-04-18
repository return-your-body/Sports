<?php
require '../db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'day';
$year = $_GET['year'];
$month = $_GET['month'];
$day = $_GET['day'];
$doctor_id = $_GET['doctor_id'];

$where = "1";
switch ($type) {
  case 'year':
    $where .= " AND YEAR(a.work_date) = $year";
    break;
  case 'month':
    $where .= " AND YEAR(a.work_date) = $year AND MONTH(a.work_date) = $month";
    break;
  case 'day':
  default:
    $where .= " AND a.work_date = '$year-$month-$day'";
    break;
}
if ($doctor_id != 0) {
  $where .= " AND a.doctor_id = $doctor_id";
}

$work = [];
$sql = "
  SELECT d.doctor AS doctor_name, d.doctor_id,
  ROUND(SUM(TIMESTAMPDIFF(MINUTE, a.clock_in, IFNULL(a.clock_out, NOW())))/60, 2) AS total_hours,
  ROUND(SUM(GREATEST(TIMESTAMPDIFF(MINUTE, TIME(ds.go), a.clock_in), 0))/60, 2) AS late_hours,
  ROUND(SUM(GREATEST(TIMESTAMPDIFF(MINUTE, a.clock_out, ds.off), 0))/60, 2) AS overtime_hours
  FROM attendance a
  JOIN doctor d ON a.doctor_id = d.doctor_id
  JOIN doctorshift ds ON ds.doctor_id = d.doctor_id AND ds.date = a.work_date
  WHERE a.clock_in IS NOT NULL AND $where
  GROUP BY a.doctor_id
";

$res = mysqli_query($link, $sql);
while ($r = mysqli_fetch_assoc($res)) {
  foreach (['late_hours', 'overtime_hours'] as $k) {
    if ($r[$k] < 0 || is_null($r[$k])) $r[$k] = 0;
  }
  $work[] = $r;
}

// Leave 統計
$whereLeave = str_replace('a.', 'l.', $where);
$leave_types = [];
$leave = [];
$res2 = mysqli_query($link, "
  SELECT d.doctor AS doctor_name, d.doctor_id, l.leave_type,
  ROUND(SUM(TIMESTAMPDIFF(MINUTE, l.start_date, l.end_date))/60, 2) AS leave_hours
  FROM leaves l
  JOIN doctor d ON l.doctor_id = d.doctor_id
  WHERE l.is_approved = 1 AND $whereLeave
  GROUP BY l.doctor_id, l.leave_type
");

$temp = [];
while ($r = mysqli_fetch_assoc($res2)) {
  $leave_types[$r['leave_type']] = true;
  $docId = $r['doctor_id'];
  if (!isset($temp[$docId])) {
    $temp[$docId] = ['doctor_name' => $r['doctor_name'], 'doctor_id' => $docId, 'total_hours' => 0, 'details' => []];
  }
  $temp[$docId]['details'][$r['leave_type']] = $r['leave_hours'];
  $temp[$docId]['total_hours'] += $r['leave_hours'];
}
$leave = array_values($temp);
$leave_types = array_keys($leave_types);

echo json_encode(['work' => $work, 'leave' => $leave, 'leave_types' => $leave_types]);
?>