<?php
require '../db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'day';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$day = $_GET['day'] ?? date('d');
$doctor_id = $_GET['doctor_id'] ?? 0;

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
  SELECT d.doctor AS doctor_name, d.doctor_id, a.work_date,
  TIME_FORMAT(ds.go, '%H:%i') AS shift_start,
  TIME_FORMAT(ds.off, '%H:%i') AS shift_end,
  TIME_FORMAT(a.clock_in, '%H:%i') AS clock_in_time,
  TIME_FORMAT(a.clock_out, '%H:%i') AS clock_out_time,
  ROUND(TIMESTAMPDIFF(MINUTE, a.clock_in, IFNULL(a.clock_out, NOW()))/60, 2) AS total_hours,
  ROUND(CASE WHEN a.clock_in > TIME(ds.go) THEN TIMESTAMPDIFF(MINUTE, TIME(ds.go), a.clock_in)/60 ELSE 0 END, 2) AS late_hours,
  ROUND(CASE WHEN a.clock_out > TIME(ds.off) THEN TIMESTAMPDIFF(MINUTE, TIME(ds.off), a.clock_out)/60 ELSE 0 END, 2) AS overtime_hours
  FROM attendance a
  JOIN doctor d ON a.doctor_id = d.doctor_id
  JOIN doctorshift ds ON ds.doctor_id = d.doctor_id AND ds.date = a.work_date
  WHERE a.clock_in IS NOT NULL AND $where
  ORDER BY d.doctor_id, a.work_date
";

$res = mysqli_query($link, $sql);
$temp = [];
while ($r = mysqli_fetch_assoc($res)) {
  $docId = $r['doctor_id'];
  if (!isset($temp[$docId])) {
    $temp[$docId] = [
      'doctor_name' => $r['doctor_name'],
      'doctor_id' => $docId,
      'total_hours' => 0,
      'late_hours' => 0,
      'overtime_hours' => 0,
      'details' => []
    ];
  }
  $r['total_hours'] = max(0, $r['total_hours']);
  $r['late_hours'] = max(0, $r['late_hours']);
  $r['overtime_hours'] = max(0, $r['overtime_hours']);

  $temp[$docId]['total_hours'] += $r['total_hours'];
  $temp[$docId]['late_hours'] += $r['late_hours'];
  $temp[$docId]['overtime_hours'] += $r['overtime_hours'];
  $temp[$docId]['details'][] = [
    'work_date' => $r['work_date'],
    'shift_start' => $r['shift_start'],
    'shift_end' => $r['shift_end'],
    'clock_in_time' => $r['clock_in_time'],
    'clock_out_time' => $r['clock_out_time'],
    'late_hours' => $r['late_hours'],
    'overtime_hours' => $r['overtime_hours'],
    'total_hours' => $r['total_hours']
  ];
}
$work = array_map(function($w) {
  $w['total_hours'] = round($w['total_hours'], 2) . ' 小時';
  $w['late_hours'] = round($w['late_hours'], 2) . ' 小時';
  $w['overtime_hours'] = round($w['overtime_hours'], 2) . ' 小時';
  return $w;
}, array_values($temp));

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

$temp2 = [];
while ($r = mysqli_fetch_assoc($res2)) {
  $leave_types[$r['leave_type']] = true;
  $docId = $r['doctor_id'];
  if (!isset($temp2[$docId])) {
    $temp2[$docId] = ['doctor_name' => $r['doctor_name'], 'doctor_id' => $docId, 'total_hours' => 0, 'details' => []];
  }
  $leave_hour = is_null($r['leave_hours']) || $r['leave_hours'] < 0 ? 0 : $r['leave_hours'];
  $temp2[$docId]['details'][$r['leave_type']] = $leave_hour . ' 小時';
  $temp2[$docId]['total_hours'] += $leave_hour;
}
foreach ($temp2 as &$v) {
  $v['total_hours'] = round($v['total_hours'], 2) . ' 小時';
}
$leave = array_values($temp2);
$leave_types = array_keys($leave_types);

echo json_encode(['work' => $work, 'leave' => $leave, 'leave_types' => $leave_types]);?>