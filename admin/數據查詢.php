<?php
require '../db.php';
header('Content-Type: application/json');

$range = $_GET['range'] ?? 'day';
$year = $_GET['year'];
$month = $_GET['month'];
$day = $_GET['day'];
$doctor_id = $_GET['doctor_id'];

$where = "1=1";
if ($doctor_id != 0) $where .= " AND d.doctor_id = $doctor_id";

switch ($range) {
    case 'year':
        $where .= " AND YEAR(a.clock_in) = $year";
        $leaveDateCond = "YEAR(l.start_date) = $year";
        break;
    case 'month':
        $where .= " AND YEAR(a.clock_in) = $year AND MONTH(a.clock_in) = $month";
        $leaveDateCond = "YEAR(l.start_date) = $year AND MONTH(l.start_date) = $month";
        break;
    default:
        $where .= " AND DATE(a.clock_in) = '$year-$month-$day'";
        $leaveDateCond = "DATE(l.start_date) = '$year-$month-$day'";
        break;
}

// 🟦 工作時數/遲到/加班統計
$workData = [];
$sql = "
SELECT 
  d.doctor AS doctor_name,
  d.doctor_id,
  ROUND(SUM(TIMESTAMPDIFF(MINUTE, a.clock_in, IFNULL(a.clock_out, NOW())))/60, 2) AS total_hours,
  ROUND(SUM(CASE 
        WHEN TIME(a.clock_in) > TIME(ds.go) 
        THEN TIMESTAMPDIFF(MINUTE, ds.go, a.clock_in) ELSE 0 END)/60, 2) AS late_hours,
  ROUND(SUM(CASE 
        WHEN TIME(a.clock_out) > TIME(ds.off) 
        THEN TIMESTAMPDIFF(MINUTE, ds.off, a.clock_out) ELSE 0 END)/60, 2) AS overtime_hours
FROM attendance a
JOIN doctor d ON d.doctor_id = a.doctor_id
JOIN doctorshift ds ON ds.doctor_id = a.doctor_id AND ds.date = DATE(a.clock_in)
WHERE $where AND a.clock_in IS NOT NULL
GROUP BY d.doctor_id;
";
$res = mysqli_query($link, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $workData[] = $row;
}

// 🟨 請假統計
$leaveMap = [];
$leaveTypes = [];

$sql = "
SELECT 
    d.doctor AS doctor_name,
    d.doctor_id,
    l.leave_type,
    ROUND(SUM(TIMESTAMPDIFF(MINUTE, l.start_date, l.end_date))/60, 2) AS leave_hours
FROM leaves l
JOIN doctor d ON d.doctor_id = l.doctor_id
WHERE l.is_approved = 1 AND $leaveDateCond
GROUP BY d.doctor_id, l.leave_type
";
$res = mysqli_query($link, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $id = $row['doctor_id'];
    $type = $row['leave_type'];
    $hours = floatval($row['leave_hours']);
    if (!isset($leaveMap[$id])) {
        $leaveMap[$id] = [
            'doctor_name' => $row['doctor_name'],
            'doctor_id' => $id,
            'total_hours' => 0,
            'details' => []
        ];
    }
    $leaveMap[$id]['details'][$type] = $hours;
    $leaveMap[$id]['total_hours'] += $hours;

    if (!in_array($type, $leaveTypes)) $leaveTypes[] = $type;
}

// 按照順序重新整理
$leaveData = array_values($leaveMap);

echo json_encode([
    'work' => $workData,
    'leave' => $leaveData,
    'leave_types' => $leaveTypes
]);
?>
