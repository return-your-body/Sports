<?php
require '../db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'day';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$day = $_GET['day'] ?? date('d');
$doctor_id = $_GET['doctor_id'] ?? 0;

// 建立日期條件字串
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
$workQuery = "
SELECT d.doctor_id, d.doctor AS doctor_name,
       ROUND(SUM(TIMESTAMPDIFF(MINUTE, a.clock_in_time, a.clock_out_time)/60), 2) AS total_hours,
       SUM(CASE WHEN TIME(a.clock_in_time) > '07:00:00' THEN TIMESTAMPDIFF(MINUTE, '07:00:00', TIME(a.clock_in_time)) ELSE 0 END) AS late_minutes,
       SUM(CASE WHEN TIME(a.clock_out_time) > '16:00:00' THEN TIMESTAMPDIFF(MINUTE, '16:00:00', TIME(a.clock_out_time)) ELSE 0 END) AS overtime_minutes
FROM attendance a
JOIN doctor d ON d.doctor_id = a.doctor_id
WHERE $dateCondition
" . ($doctor_id ? " AND d.doctor_id = $doctor_id" : "") . "
GROUP BY d.doctor_id
";

$workRes = mysqli_query($link, $workQuery);
$workData = [];
while ($row = mysqli_fetch_assoc($workRes)) {
    // 詳細紀錄
    $detailSql = "
    SELECT a.work_date, a.clock_in_time, a.clock_out_time,
           ROUND(TIMESTAMPDIFF(MINUTE, a.clock_in_time, a.clock_out_time)/60, 2) AS total_hours,
           CASE WHEN TIME(a.clock_in_time) > '07:00:00' THEN TIMESTAMPDIFF(MINUTE, '07:00:00', TIME(a.clock_in_time)) ELSE 0 END AS late_minutes,
           CASE WHEN TIME(a.clock_out_time) > '16:00:00' THEN TIMESTAMPDIFF(MINUTE, '16:00:00', TIME(a.clock_out_time)) ELSE 0 END AS overtime_minutes
    FROM attendance a
    WHERE $dateCondition AND a.doctor_id = {$row['doctor_id']}
    ";

    $detailRes = mysqli_query($link, $detailSql);
    $details = [];
    while ($d = mysqli_fetch_assoc($detailRes)) {
        $details[] = $d;
    }

    $row['details'] = $details;
    $workData[] = $row;
}

// ==================== 請假資料 ====================
$leaveQuery = "
SELECT d.doctor_id, d.doctor AS doctor_name, l.leave_type, l.start_date, l.end_date, l.reason,
       TIMESTAMPDIFF(MINUTE, l.start_date, l.end_date) AS minutes
FROM leaves l
JOIN doctor d ON d.doctor_id = l.doctor_id
WHERE " . str_replace('a.work_date', 'l.start_date', $dateCondition) .
($doctor_id ? " AND d.doctor_id = $doctor_id" : "");

$leaveRes = mysqli_query($link, $leaveQuery);
$leaveData = [];
$leaveTypes = [];

while ($row = mysqli_fetch_assoc($leaveRes)) {
    $id = $row['doctor_id'];
    if (!isset($leaveData[$id])) {
        $leaveData[$id] = [
            'doctor_id' => $row['doctor_id'],
            'doctor_name' => $row['doctor_name'],
            'details' => []
        ];
    }

    if (!isset($leaveData[$id]['details'][$row['leave_type']])) {
        $leaveData[$id]['details'][$row['leave_type']] = [];
    }

    $leaveData[$id]['details'][$row['leave_type']][] = [
        'start' => $row['start_date'],
        'end' => $row['end_date'],
        'reason' => $row['reason'],
        'minutes' => $row['minutes']
    ];

    if (!in_array($row['leave_type'], $leaveTypes)) {
        $leaveTypes[] = $row['leave_type'];
    }
}
$leaveData = array_values($leaveData);

// ==================== 項目數比例 ====================
$itemSql = "
SELECT i.item AS item, COUNT(*) AS count
FROM medicalrecord m
JOIN item i ON m.item_id = i.item_id
WHERE $dateMedical
GROUP BY m.item_id
";
$itemRes = mysqli_query($link, $itemSql);
$itemsChartData = [];
while ($row = mysqli_fetch_assoc($itemRes)) {
    $itemsChartData[] = $row;
}

// ==================== 預約人數比例 ====================
$appointmentSql = "
SELECT d.doctor AS doctor, COUNT(*) AS count
FROM appointment a
JOIN doctorshift s ON a.doctorshift_id = s.doctorshift_id
JOIN doctor d ON s.doctor_id = d.doctor_id
WHERE $dateMedical
GROUP BY d.doctor_id
";
$appRes = mysqli_query($link, $appointmentSql);
$appointmentChartData = [];
while ($row = mysqli_fetch_assoc($appRes)) {
    $appointmentChartData[] = $row;
}

// ==================== 收入統計 ====================
$incomeSql = "
SELECT i.item AS item, SUM(i.price) AS total
FROM medicalrecord m
JOIN item i ON m.item_id = i.item_id
WHERE $dateMedical
GROUP BY m.item_id
";
$incomeRes = mysqli_query($link, $incomeSql);
$incomeChartData = [];
while ($row = mysqli_fetch_assoc($incomeRes)) {
    $incomeChartData[] = $row;
}

// ==================== 回傳 JSON ====================
echo json_encode([
    "work" => $workData,
    "leave" => $leaveData,
    "leave_types" => $leaveTypes,
    "itemsChartData" => $itemsChartData,
    "appointmentChartData" => $appointmentChartData,
    "incomeChartData" => $incomeChartData
]);
?>