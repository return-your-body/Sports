<?php
require '../db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'day';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$day = $_GET['day'] ?? date('d');
$doctor_id = $_GET['doctor_id'] ?? 0;

$where = "1";
$dateTarget = "$year-$month-$day";

switch ($type) {
  case 'year':
    $where .= " AND YEAR(a.work_date) = $year";
    $leaveWhere = "YEAR(l.start_date) = $year OR YEAR(l.end_date) = $year";
    break;
  case 'month':
    $where .= " AND YEAR(a.work_date) = $year AND MONTH(a.work_date) = $month";
    $leaveWhere = "(YEAR(l.start_date) = $year AND MONTH(l.start_date) = $month)
                    OR (YEAR(l.end_date) = $year AND MONTH(l.end_date) = $month)";
    break;
  case 'day':
  default:
    $where .= " AND a.work_date = '$dateTarget'";
    $leaveWhere = "DATE(l.start_date) <= '$dateTarget' AND DATE(l.end_date) >= '$dateTarget'";
    break;
}
if ($doctor_id != 0) {
  $where .= " AND a.doctor_id = $doctor_id";
  $leaveWhere .= " AND l.doctor_id = $doctor_id";
}

// 預先抓出請假資料用於排除遲到
$leaveDates = [];
$resLeaveForLate = mysqli_query($link, "
  SELECT doctor_id, start_date, end_date FROM leaves
  WHERE is_approved = 1
");
while ($r = mysqli_fetch_assoc($resLeaveForLate)) {
  $start = strtotime($r['start_date']);
  $end = strtotime($r['end_date']);
  for ($t = $start; $t <= $end; $t += 86400) {
    $date = date('Y-m-d', $t);
    $leaveDates[$r['doctor_id']][$date] = true;
  }
}

// ---------- 工作時數 ----------
$work = [];
$sql = "
  SELECT d.doctor AS doctor_name, d.doctor_id, a.work_date,
    TIME_FORMAT(a.clock_in, '%H:%i') AS clock_in_time,
    TIME_FORMAT(a.clock_out, '%H:%i') AS clock_out_time,
    ROUND(TIMESTAMPDIFF(MINUTE, a.clock_in, IFNULL(a.clock_out, NOW())) / 60, 2) AS total_hours,
    TIMESTAMPDIFF(MINUTE, TIME(st_go.shifttime), TIME(a.clock_in)) AS raw_late_minutes,
    TIMESTAMPDIFF(MINUTE, TIME(st_off.shifttime), TIME(a.clock_out)) AS raw_overtime_minutes
  FROM attendance a
  JOIN doctor d ON a.doctor_id = d.doctor_id
  JOIN doctorshift ds ON ds.doctor_id = d.doctor_id AND ds.date = a.work_date
  JOIN shifttime st_go ON st_go.shifttime_id = ds.go
  JOIN shifttime st_off ON st_off.shifttime_id = ds.off
  WHERE a.clock_in IS NOT NULL AND $where
  ORDER BY d.doctor_id, a.work_date
";

$res = mysqli_query($link, $sql);
$temp = [];
while ($r = mysqli_fetch_assoc($res)) {
  $docId = $r['doctor_id'];
  $date = $r['work_date'];
  $isLeave = isset($leaveDates[$docId][$date]);

  if (!isset($temp[$docId])) {
    $temp[$docId] = [
      'doctor_name' => $r['doctor_name'],
      'doctor_id' => $docId,
      'total_hours' => 0,
      'late_minutes' => 0,
      'overtime_minutes' => 0,
      'details' => []
    ];
  }
  $late = ($isLeave || $r['raw_late_minutes'] < 0) ? 0 : $r['raw_late_minutes'];
  $overtime = ($r['raw_overtime_minutes'] < 0) ? 0 : $r['raw_overtime_minutes'];
  $total = max(0, $r['total_hours']);

  $temp[$docId]['total_hours'] += $total;
  $temp[$docId]['late_minutes'] += $late;
  $temp[$docId]['overtime_minutes'] += $overtime;
  $temp[$docId]['details'][] = [
    'work_date' => $r['work_date'],
    'clock_in_time' => $r['clock_in_time'],
    'clock_out_time' => $r['clock_out_time'],
    'late_minutes' => $late,
    'overtime_minutes' => $overtime,
    'total_hours' => $total
  ];
}
$work = array_values($temp);

// ---------- 請假統計 ----------
$leave = [];
$leave_types = [];
$res2 = mysqli_query($link, "
  SELECT l.*, d.doctor AS doctor_name
  FROM leaves l
  JOIN doctor d ON d.doctor_id = l.doctor_id
  WHERE l.is_approved = 1 AND ($leaveWhere)
");
$temp2 = [];
while ($r = mysqli_fetch_assoc($res2)) {
  $docId = $r['doctor_id'];
  $leave_type = $r['leave_type'] ?? '其他';
  $leave_types[$leave_type] = true;
  $minutes = (strtotime($r['end_date']) - strtotime($r['start_date'])) / 60;
  if ($minutes <= 0) continue;

  if (!isset($temp2[$docId])) {
    $temp2[$docId] = [
      'doctor_id' => $docId,
      'doctor_name' => $r['doctor_name'],
      'total_minutes' => 0,
      'details' => []
    ];
  }
  if (!isset($temp2[$docId]['details'][$leave_type])) {
    $temp2[$docId]['details'][$leave_type] = [];
  }
  $temp2[$docId]['details'][$leave_type][] = [
    'start' => $r['start_date'],
    'end' => $r['end_date'],
    'reason' => $r['reason'],
    'minutes' => round($minutes)
  ];
  $temp2[$docId]['total_minutes'] += $minutes;
}
$leave = array_values($temp2);
$leave_types = array_keys($leave_types);
foreach ($leave as &$lv) {
  foreach ($leave_types as $lt) {
    if (!isset($lv['details'][$lt])) {
      $lv['details'][$lt] = [];
    }
  }
}

echo json_encode(['work' => $work, 'leave' => $leave, 'leave_types' => $leave_types]);
?>