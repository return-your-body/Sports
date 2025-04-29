<?php
require '../db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'day';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$day = $_GET['day'] ?? date('d');
$doctor_id = $_GET['doctor_id'] ?? 0;

$where = "1";
$leaveWhere = "1"; // <--- 新增 leave 查詢條件
$dateTarget = "$year-$month-$day";

// 時間篩選
switch ($type) {
  case 'year':
    $where .= " AND YEAR(ds.date) = $year";
    $leaveWhere .= " AND (YEAR(l.start_date) = $year OR YEAR(l.end_date) = $year)";
    break;
  case 'month':
    $where .= " AND YEAR(ds.date) = $year AND MONTH(ds.date) = $month";
    $leaveWhere .= " AND ((YEAR(l.start_date) = $year AND MONTH(l.start_date) = $month) OR (YEAR(l.end_date) = $year AND MONTH(l.end_date) = $month))";
    break;
  case 'day':
  default:
    $where .= " AND ds.date = '$dateTarget'";
    $leaveWhere .= " AND (DATE(l.start_date) <= '$dateTarget' AND DATE(l.end_date) >= '$dateTarget')";
    break;
}

// 治療師篩選
if ($doctor_id != 0) {
  $where .= " AND ds.doctor_id = $doctor_id";
  $leaveWhere .= " AND l.doctor_id = $doctor_id";
}

// 取得請假期間（做曠工判斷用）
$leaveDates = [];
$resLeave = mysqli_query($link, "
  SELECT doctor_id, start_date, end_date
  FROM leaves
  WHERE is_approved = 1
");
while ($r = mysqli_fetch_assoc($resLeave)) {
  $start = strtotime($r['start_date']);
  $end = strtotime($r['end_date']);
  for ($t = $start; $t <= $end; $t += 86400) {
    $date = date('Y-m-d', $t);
    $leaveDates[$r['doctor_id']][$date] = true;
  }
}

// 出勤資料（有打卡）
$work = [];
$resWork = mysqli_query($link, "
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
");

$temp = [];
while ($r = mysqli_fetch_assoc($resWork)) {
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
      'absent_count' => 0,
      'details' => []
    ];
  }

  $late = 0;
  if (!$isLeave && $r['in_time'] > $r['go_time']) {
    $late = (strtotime($r['in_time']) - strtotime($r['go_time'])) / 60;
  }

  $ot = 0;
  if ($r['out_time'] > $r['off_time']) {
    $ot = (strtotime($r['out_time']) - strtotime($r['off_time'])) / 60;
  }

  $total = max(0, $r['total_hours']);
  
  $temp[$docId]['total_hours'] += $total;
  $temp[$docId]['late_minutes'] += max(0, $late);
  $temp[$docId]['overtime_minutes'] += max(0, $ot);
  $temp[$docId]['details'][] = [
    'work_date' => $r['work_date'],
    'clock_in_time' => $r['clock_in_time'],
    'clock_out_time' => $r['clock_out_time'],
    'late_minutes' => $isLeave ? '-' : round($late),
    'overtime_minutes' => round($ot),
    'total_hours' => round($total, 2)
  ];
}

// 補上：排班有但沒打卡（判斷曠工）
$resAbsent = mysqli_query($link, "
  SELECT d.doctor AS doctor_name, d.doctor_id, ds.date AS work_date
  FROM doctorshift ds
  JOIN doctor d ON ds.doctor_id = d.doctor_id
  LEFT JOIN attendance a ON a.doctor_id = ds.doctor_id AND a.work_date = ds.date
  WHERE a.attendance_id IS NULL AND $where
");

while ($r = mysqli_fetch_assoc($resAbsent)) {
  $docId = $r['doctor_id'];
  $date = $r['work_date'];

  $isLeave = isset($leaveDates[$docId][$date]);
  if ($isLeave) continue; // 有請假，不算曠工

  if (!isset($temp[$docId])) {
    $temp[$docId] = [
      'doctor_name' => $r['doctor_name'],
      'doctor_id' => $docId,
      'total_hours' => 0,
      'late_minutes' => 0,
      'overtime_minutes' => 0,
      'absent_count' => 0,
      'details' => []
    ];
  }
  $temp[$docId]['absent_count']++;
  $temp[$docId]['details'][] = [
    'work_date' => $r['work_date'],
    'clock_in_time' => '-',
    'clock_out_time' => '-',
    'late_minutes' => '-',
    'overtime_minutes' => '-',
    'total_hours' => '曠工'
  ];
}

$work = array_values($temp);

// 請假資料
$leave = [];
$resLeaveData = mysqli_query($link, "
  SELECT l.*, d.doctor AS doctor_name
  FROM leaves l
  JOIN doctor d ON d.doctor_id = l.doctor_id
  WHERE l.is_approved = 1 AND ($leaveWhere)
");

$temp2 = [];
while ($r = mysqli_fetch_assoc($resLeaveData)) {
  $docId = $r['doctor_id'];
  $leave_type = $r['leave_type'] ?? '其他';
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

// 最後輸出 JSON
echo json_encode([
  'work' => $work,
  'leave' => $leave
]);
?>
