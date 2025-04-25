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
    $dsWhere = "YEAR(ds.date) = $year";
    break;
  case 'month':
    $where .= " AND YEAR(a.work_date) = $year AND MONTH(a.work_date) = $month";
    $leaveWhere = "(YEAR(l.start_date) = $year AND MONTH(l.start_date) = $month)
                    OR (YEAR(l.end_date) = $year AND MONTH(l.end_date) = $month)";
    $dsWhere = "YEAR(ds.date) = $year AND MONTH(ds.date) = $month";
    break;
  case 'day':
  default:
    $where .= " AND a.work_date = '$dateTarget'";
    $leaveWhere = "DATE(l.start_date) <= '$dateTarget' AND DATE(l.end_date) >= '$dateTarget'";
    $dsWhere = "ds.date = '$dateTarget'";
    break;
}
if ($doctor_id != 0) {
  $where .= " AND a.doctor_id = $doctor_id";
  $leaveWhere .= " AND l.doctor_id = $doctor_id";
  $dsWhere .= " AND ds.doctor_id = $doctor_id";
}

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

$work = [];
$sql = "
  SELECT d.doctor AS doctor_name, d.doctor_id, a.work_date,
    TIME_FORMAT(a.clock_in, '%H:%i') AS clock_in_time,
    TIME_FORMAT(a.clock_out, '%H:%i') AS clock_out_time,
    ROUND(TIMESTAMPDIFF(MINUTE, a.clock_in, IFNULL(a.clock_out, NOW())) / 60, 2) AS total_hours,
    TIME(a.clock_in) AS in_time,
    TIME(a.clock_out) AS out_time,
    st_go.shifttime AS go_time,
    st_off.shifttime AS off_time
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

  $late = 0;
  if (!$isLeave && $r['in_time'] > $r['go_time']) {
    $late = (strtotime($r['in_time']) - strtotime($r['go_time'])) / 60;
  }
  $ot = 0;
  if ($r['out_time'] > $r['off_time']) {
    $ot = (strtotime($r['out_time']) - strtotime($r['off_time'])) / 60;
  }

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
  $total = max(0, $r['total_hours']);
  $temp[$docId]['total_hours'] += $total;
  $temp[$docId]['late_minutes'] += max(0, $late);
  $temp[$docId]['overtime_minutes'] += max(0, $ot);
  $temp[$docId]['details'][] = [
    'work_date' => $r['work_date'],
    'clock_in_time' => $r['clock_in_time'],
    'clock_out_time' => $r['clock_out_time'],
    'late_minutes' => round($late),
    'overtime_minutes' => round($ot),
    'total_hours' => round($total, 2)
  ];
}
$work = array_values($temp);

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

$item_counts = [];
$res3 = mysqli_query($link, "
  SELECT d.doctor AS doctor_name, i.item AS item_name, COUNT(*) AS count
  FROM appointment ap
  JOIN doctorshift ds ON ds.doctorshift_id = ap.doctorshift_id
  JOIN doctor d ON ds.doctor_id = d.doctor_id
  JOIN medicalrecord mr ON mr.appointment_id = ap.appointment_id
  JOIN item i ON mr.item_id = i.item_id
  WHERE $dsWhere
  GROUP BY d.doctor_id, i.item_id
");
while ($r = mysqli_fetch_assoc($res3)) {
  $item_counts[] = $r;
}

$appointment_counts = [];
$res4 = mysqli_query($link, "
  SELECT d.doctor AS doctor_name, COUNT(*) AS count
  FROM appointment ap
  JOIN doctorshift ds ON ds.doctorshift_id = ap.doctorshift_id
  JOIN doctor d ON ds.doctor_id = d.doctor_id
  WHERE $dsWhere
  GROUP BY d.doctor_id
");
while ($r = mysqli_fetch_assoc($res4)) {
  $appointment_counts[] = $r;
}

$income_data = [];
$res5 = mysqli_query($link, "
  SELECT d.doctor AS doctor_name, i.item AS item_name, SUM(i.price) AS income
  FROM appointment ap
  JOIN doctorshift ds ON ds.doctorshift_id = ap.doctorshift_id
  JOIN doctor d ON ds.doctor_id = d.doctor_id
  JOIN medicalrecord mr ON mr.appointment_id = ap.appointment_id
  JOIN item i ON mr.item_id = i.item_id
  WHERE $dsWhere
  GROUP BY d.doctor_id, i.item_id
");
while ($r = mysqli_fetch_assoc($res5)) {
  $income_data[] = $r;
}

echo json_encode([
  'work' => $work,
  'leave' => $leave,
  'leave_types' => $leave_types,
  'item_counts' => $item_counts,
  'appointment_counts' => $appointment_counts,
  'income_data' => $income_data
]);
?>