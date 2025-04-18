<?php
require '../db.php';
header('Content-Type: application/json');

$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('n');
$day = $_GET['day'] ?? '';
$doctor_id = $_GET['doctor_id'] ?? 0;
$chart = $_GET['chart'] ?? '';
$type = $_GET['type'] ?? '';
$item = $_GET['item'] ?? '';

function getCondition($year, $month, $day, $doctor_id) {
    $where = "";
    if ($year) $where .= " AND YEAR(a.work_date) = '$year'";
    if ($month) $where .= " AND MONTH(a.work_date) = '$month'";
    if ($day) $where .= " AND DAY(a.work_date) = '$day'";
    if ($doctor_id && $doctor_id != '0') $where .= " AND a.doctor_id = '$doctor_id'";
    return $where;
}

if ($chart === '總工作時數') {
    $sql = "
        SELECT 
            d.doctor AS doctor_name,
            ROUND(SUM(TIMESTAMPDIFF(MINUTE, a.clock_in, IFNULL(a.clock_out, NOW())) / 60), 2) AS total_hours,
            ROUND(SUM(CASE 
                WHEN TIMESTAMPDIFF(MINUTE, ds.go, a.clock_in) > 0 THEN TIMESTAMPDIFF(MINUTE, ds.go, a.clock_in) 
                ELSE 0 END) / 60, 2) AS late_hours,
            ROUND(SUM(CASE 
                WHEN TIMESTAMPDIFF(MINUTE, IFNULL(a.clock_out, NOW()), ds.off) > 0 THEN TIMESTAMPDIFF(MINUTE, ds.off, IFNULL(a.clock_out, NOW())) 
                ELSE 0 END) / 60, 2) AS overtime_hours
        FROM attendance a
        JOIN doctor d ON a.doctor_id = d.doctor_id
        JOIN doctorshift ds ON ds.doctor_id = d.doctor_id AND ds.date = a.work_date
        WHERE a.clock_in IS NOT NULL 
        " . getCondition($year, $month, $day, $doctor_id) . "
        GROUP BY d.doctor_id
    ";
    $result = mysqli_query($link, $sql);
    echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
    exit;
}

if ($chart === '請假統計') {
    $sql = "
        SELECT 
            d.doctor AS doctor_name,
            l.leave_type AS category,
            ROUND(SUM(TIMESTAMPDIFF(MINUTE, l.start_date, l.end_date) / 60), 2) AS value
        FROM leaves l
        JOIN doctor d ON l.doctor_id = d.doctor_id
        WHERE l.is_approved = 1
            AND DATE(l.start_date) = '$year-$month-$day'
            " . ($doctor_id ? " AND l.doctor_id = '$doctor_id'" : "") . "
        GROUP BY d.doctor_id, l.leave_type
    ";
    $res = mysqli_query($link, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

if ($chart === '詳細數據' && $type === 'leave') {
    $sql = "
        SELECT 
            d.doctor AS doctor_name,
            l.leave_type AS category,
            ROUND(SUM(TIMESTAMPDIFF(MINUTE, l.start_date, l.end_date) / 60), 2) AS value
        FROM leaves l
        JOIN doctor d ON l.doctor_id = d.doctor_id
        WHERE l.is_approved = 1
            AND l.leave_type = '$item'
            AND DATE(l.start_date) = '$year-$month-$day'
            " . ($doctor_id ? " AND l.doctor_id = '$doctor_id'" : "") . "
        GROUP BY d.doctor_id, l.leave_type
    ";
    $res = mysqli_query($link, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

echo json_encode([]);
exit;
?>