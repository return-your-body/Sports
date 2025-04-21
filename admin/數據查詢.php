
<?php
require '../db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'day';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$day = $_GET['day'] ?? date('d');
$doctor_id = $_GET['doctor_id'] ?? 0;

// 組合條件
if ($type === 'day') {
    $dateCondition = "DATE(a.work_date) = '$year-$month-$day'";
    $dateMedical = "DATE(m.created_at) = '$year-$month-$day'";
} elseif ($type === 'month') {
    $dateCondition = "YEAR(a.work_date) = '$year' AND MONTH(a.work_date) = '$month'";
    $dateMedical = "YEAR(m.created_at) = '$year' AND MONTH(m.created_at) = '$month'";
} elseif ($type === 'year') {
    $dateCondition = "YEAR(a.work_date) = '$year'";
    $dateMedical = "YEAR(m.created_at) = '$year'";
}

// ==================== 工作時數 ====================
$work = [];
$sql = "
SELECT d.doctor AS doctor_name, d.doctor_id, a.work_date,
  TIME_FORMAT(a.clock_in, '%H:%i') AS clock_in_time,
  TIME_FORMAT(a.clock_out, '%H:%i') AS clock_out_time,
  ROUND(TIMESTAMPDIFF(MINUTE, a.clock_in, IFNULL(a.clock_out, NOW())) / 60, 2) AS total_hours,
  CASE WHEN TIME(a.clock_in) > '07:00:00' THEN TIMESTAMPDIFF(MINUTE, '07:00:00', TIME(a.clock_in)) ELSE 0 END AS late_minutes,
  CASE WHEN TIME(a.clock_out) > '16:00:00' THEN TIMESTAMPDIFF(MINUTE, '16:00:00', TIME(a.clock_out)) ELSE 0 END AS overtime_minutes
FROM attendance a
JOIN doctor d ON a.doctor_id = d.doctor_id
WHERE a.clock_in IS NOT NULL AND $dateCondition
" . ($doctor_id ? " AND a.doctor_id = $doctor_id" : "") . "
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
            'late_minutes' => 0,
            'overtime_minutes' => 0,
            'details' => []
        ];
    }
    $r['late_minutes'] = max(0, $r['late_minutes']);
    $r['overtime_minutes'] = max(0, $r['overtime_minutes']);
    $r['total_hours'] = max(0, $r['total_hours']);
    $temp[$docId]['total_hours'] += $r['total_hours'];
    $temp[$docId]['late_minutes'] += $r['late_minutes'];
    $temp[$docId]['overtime_minutes'] += $r['overtime_minutes'];
    $temp[$docId]['details'][] = $r;
}
$work = array_values($temp);

// ==================== 請假資料 ====================
$leave_types = [];
$leave = [];
$res2 = mysqli_query($link, "
  SELECT d.doctor AS doctor_name, d.doctor_id, l.leave_type, l.start_date, l.end_date, l.reason,
    TIMESTAMPDIFF(MINUTE, l.start_date, l.end_date) AS leave_minutes
  FROM leaves l
  JOIN doctor d ON l.doctor_id = d.doctor_id
  WHERE l.is_approved = 1 AND " . str_replace("a.work_date", "l.start_date", $dateCondition) .
  ($doctor_id ? " AND l.doctor_id = $doctor_id" : ""));

$temp2 = [];
while ($r = mysqli_fetch_assoc($res2)) {
    if ($r['leave_minutes'] <= 0 || is_null($r['leave_minutes'])) continue;
    $docId = $r['doctor_id'];
    $type = $r['leave_type'];
    $leave_types[$type] = true;
    if (!isset($temp2[$docId])) {
        $temp2[$docId] = ['doctor_name' => $r['doctor_name'], 'doctor_id' => $docId, 'details' => []];
    }
    if (!isset($temp2[$docId]['details'][$type])) {
        $temp2[$docId]['details'][$type] = [];
    }
    $temp2[$docId]['details'][$type][] = [
        'start' => $r['start_date'],
        'end' => $r['end_date'],
        'reason' => $r['reason'],
        'minutes' => $r['leave_minutes']
    ];
}
$leave = array_values($temp2);
$leave_types = array_keys($leave_types);
foreach ($leave as &$l) {
    foreach ($leave_types as $lt) {
        if (!isset($l['details'][$lt])) $l['details'][$lt] = [];
    }
}

// ==================== 圓餅圖 1：項目數比例 ====================
$itemsChartData = [];
$sql_items = "
  SELECT i.item AS item, COUNT(*) AS count
  FROM medicalrecord m
  JOIN item i ON m.item_id = i.item_id
  JOIN appointment a ON a.appointment_id = m.appointment_id
  JOIN doctorshift s ON a.doctorshift_id = s.doctorshift_id
  WHERE $dateMedical " . ($doctor_id ? " AND s.doctor_id = $doctor_id" : "") . "
  GROUP BY i.item
";
$res_items = mysqli_query($link, $sql_items);
while ($r = mysqli_fetch_assoc($res_items)) {
    $itemsChartData[] = $r;
}

// ==================== 圓餅圖 2：預約人數比例 ====================

// 用排班表 s.date 做為圖表查詢依據
if ($filterType == 'day') {
    $dateShift = "DATE(s.date) = '$selectedDate'";
} elseif ($filterType == 'month') {
    $dateShift = "DATE_FORMAT(s.date, '%Y-%m') = '$selectedMonth'";
} elseif ($filterType == 'year') {
    $dateShift = "YEAR(s.date) = '$selectedYear'";
}

$appointmentChartData = [];

$sql_appointments = "
  SELECT d.doctor AS doctor, COUNT(*) AS count
  FROM appointment a
  JOIN doctorshift s ON a.doctorshift_id = s.doctorshift_id
  JOIN doctor d ON s.doctor_id = d.doctor_id
  WHERE $dateShift " . ($doctor_id ? " AND d.doctor_id = $doctor_id" : "") . "
  GROUP BY d.doctor_id
";
$res_appointments = mysqli_query($link, $sql_appointments);
while ($r = mysqli_fetch_assoc($res_appointments)) {
    $appointmentChartData[] = $r;
}

// ==================== 圓餅圖 3：收入統計 ====================
$incomeChartData = [];
$sql_income = "
  SELECT i.item AS item, SUM(i.price) AS total
  FROM medicalrecord m
  JOIN item i ON m.item_id = i.item_id
  JOIN appointment a ON a.appointment_id = m.appointment_id
  JOIN doctorshift s ON a.doctorshift_id = s.doctorshift_id
  WHERE $dateMedical " . ($doctor_id ? " AND s.doctor_id = $doctor_id" : "") . "
  GROUP BY i.item
";
$res_income = mysqli_query($link, $sql_income);
while ($r = mysqli_fetch_assoc($res_income)) {
    $incomeChartData[] = $r;
}

// ==================== 回傳 JSON ====================
echo json_encode([
  "work" => $work,
  "leave" => $leave,
  "leave_types" => $leave_types,
  "itemsChartData" => $itemsChartData,
  "appointmentChartData" => $appointmentChartData,
  "incomeChartData" => $incomeChartData
]);
?>
