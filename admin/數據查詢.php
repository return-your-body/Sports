<?php
require '../db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'day';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$day = $_GET['day'] ?? date('d');
$doctor_id = $_GET['doctor_id'] ?? 0;

if ($type === 'day') {
    $selectedDate = "$year-$month-$day";
    $dateCondition = "DATE(a.work_date) = '$selectedDate'";
    $dateMedical = "DATE(m.created_at) = '$selectedDate'";
    $dateShift = "DATE(s.date) = '$selectedDate'";
} elseif ($type === 'month') {
    $selectedMonth = "$year-$month";
    $dateCondition = "YEAR(a.work_date) = '$year' AND MONTH(a.work_date) = '$month'";
    $dateMedical = "DATE_FORMAT(m.created_at, '%Y-%m') = '$selectedMonth'";
    $dateShift = "DATE_FORMAT(s.date, '%Y-%m') = '$selectedMonth'";
} elseif ($type === 'year') {
    $selectedYear = "$year";
    $dateCondition = "YEAR(a.work_date) = '$year'";
    $dateMedical = "YEAR(m.created_at) = '$year'";
    $dateShift = "YEAR(s.date) = '$year'";
}

// ==================== 總工作時數（clock_out 為 null 時也計算，但排除負數） ====================
$work = [];
$sql = "
  SELECT d.doctor AS doctor_name, d.doctor_id
  FROM doctor d
";
if ($doctor_id) {
    $sql .= " WHERE d.doctor_id = $doctor_id";
}
$res = mysqli_query($link, $sql);
while ($r = mysqli_fetch_assoc($res)) {
    $did = $r['doctor_id'];
    $doctorName = $r['doctor_name'];

    $details = [];
    $total_hours = 0;
    $late_minutes = 0;
    $ot_minutes = 0;

    $res2 = mysqli_query($link, "
      SELECT a.*, s.go, s.off, st.shiftTime AS shift_start, st2.shiftTime AS shift_end
      FROM attendance a
      JOIN doctorshift s ON a.doctor_id = s.doctor_id AND a.work_date = s.date
      JOIN shiftTime st ON s.go = st.shiftTime_id
      JOIN shiftTime st2 ON s.off = st2.shiftTime_id
      WHERE $dateAttendance AND a.doctor_id = $did
    ");

    while ($row = mysqli_fetch_assoc($res2)) {
        $in = $row['clock_in'];
        $out = $row['clock_out'];

        if (!$in || (!$out && $row['work_date'] !== date('Y-m-d'))) continue;

        $startShift = strtotime($row['work_date'] . ' ' . $row['shift_start']);
        $endShift = strtotime($row['work_date'] . ' ' . $row['shift_end']);
        $inTime = strtotime($in);
        $outTime = $out ? strtotime($out) : time();

        $late = max(0, round(($inTime - $startShift) / 60));
        $ot = max(0, round(($outTime - $endShift) / 60));
        $worked = max(0, round(($outTime - $inTime) / 3600, 2));
        if ($worked <= 0) continue;

        $total_hours += $worked;
        $late_minutes += $late;
        $ot_minutes += $ot;

        $details[] = [
            'work_date' => $row['work_date'],
            'clock_in_time' => date('H:i', $inTime),
            'clock_out_time' => $out ? date('H:i', $outTime) : '未下班',
            'late_minutes' => $late,
            'overtime_minutes' => $ot,
            'total_hours' => $worked
        ];
    }

    if ($total_hours > 0 || $late_minutes > 0 || $ot_minutes > 0) {
        $work[] = [
            'doctor_name' => $doctorName,
            'total_hours' => round($total_hours, 2),
            'late_minutes' => $late_minutes,
            'overtime_minutes' => $ot_minutes,
            'details' => $details
        ];
    }
}


// ==================== 請假資料 ====================
$leave_types = [];
$leave = [];
$res2 = mysqli_query($link, "
  SELECT d.doctor AS doctor_name, d.doctor_id, l.leave_type, l.start_date, l.end_date, l.reason,
    TIMESTAMPDIFF(MINUTE, l.start_date, l.end_date) AS leave_minutes
  FROM leaves l
  JOIN doctor d ON l.doctor_id = d.doctor_id
  WHERE l.is_approved = 1 AND " . str_replace("a.work_date", "l.start_date", $dateCondition) .
  ($doctor_id ? " AND l.doctor_id = $doctor_id" : "")
);
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

// ==================== 項目數比例（+ 哪位醫師） ====================
$itemsChartData = [];
$sql_items = "
  SELECT i.item AS item, d.doctor AS doctor, COUNT(*) AS count
  FROM medicalrecord m
  JOIN item i ON m.item_id = i.item_id
  JOIN appointment a ON a.appointment_id = m.appointment_id
  JOIN doctorshift s ON a.doctorshift_id = s.doctorshift_id
  JOIN doctor d ON s.doctor_id = d.doctor_id
  WHERE $dateShift " . ($doctor_id ? " AND s.doctor_id = $doctor_id" : "") . "
  GROUP BY i.item, d.doctor
";
$res_items = mysqli_query($link, $sql_items);
while ($r = mysqli_fetch_assoc($res_items)) {
    $itemsChartData[] = $r;
}

// ==================== 預約人數比例 ====================
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

// ==================== 收入統計（+ 哪位醫師） ====================
$incomeChartData = [];
$sql_income = "
  SELECT i.item AS item, d.doctor AS doctor, SUM(i.price) AS total
  FROM medicalrecord m
  JOIN item i ON m.item_id = i.item_id
  JOIN appointment a ON a.appointment_id = m.appointment_id
  JOIN doctorshift s ON a.doctorshift_id = s.doctorshift_id
  JOIN doctor d ON s.doctor_id = d.doctor_id
  WHERE $dateShift " . ($doctor_id ? " AND s.doctor_id = $doctor_id" : "") . "
  GROUP BY i.item, d.doctor
";
$res_income = mysqli_query($link, $sql_income);
while ($r = mysqli_fetch_assoc($res_income)) {
    $incomeChartData[] = $r;
}

// ==================== 回傳 JSON ====================
echo json_encode([
  "workChartData" => $work,
  "leave" => $leave,
  "leave_types" => $leave_types,
  "itemsChartData" => $itemsChartData,
  "appointmentChartData" => $appointmentChartData,
  "incomeChartData" => $incomeChartData
]);
?>
