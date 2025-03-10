<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../db.php';

header('Content-Type: application/json; charset=UTF-8');

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$day = isset($_GET['day']) ? intval($_GET['day']) : 0;
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

$sql = "SELECT 
            d.doctor_id, 
            d.doctor AS doctor_name, 
            a.work_date,
            SUM(TIMESTAMPDIFF(HOUR, a.clock_in, a.clock_out)) AS work_hours,
            SUM(CASE WHEN a.clock_in > s.go THEN 1 ELSE 0 END) AS late_count,
            SUM(CASE WHEN a.clock_out > s.off THEN TIMESTAMPDIFF(HOUR, s.off, a.clock_out) ELSE 0 END) AS overtime_hours,
            (SELECT COUNT(*) FROM leaves l WHERE l.doctor_id = d.doctor_id AND YEAR(l.start_date) = ? AND MONTH(l.start_date) = ?) AS leave_count,
            COUNT(ap.appointment_id) AS total_appointments,
            COUNT(mr.item_id) AS total_items,
            SUM(i.price) AS revenue
        FROM attendance a
        JOIN doctor d ON a.doctor_id = d.doctor_id
        LEFT JOIN doctorshift s ON a.doctor_id = s.doctor_id AND a.work_date = s.date
        LEFT JOIN appointment ap ON ap.doctorshift_id = s.doctorshift_id
        LEFT JOIN medicalrecord mr ON ap.appointment_id = mr.appointment_id
        LEFT JOIN item i ON mr.item_id = i.item_id
        WHERE YEAR(a.work_date) = ? AND MONTH(a.work_date) = ?";

$params = [$year, $month, $year, $month];

if ($day != 0) {
    $sql .= " AND DAY(a.work_date) = ?";
    $params[] = $day;
}

if ($doctor_id != 0) {
    $sql .= " AND d.doctor_id = ?";
    $params[] = $doctor_id;
}

$sql .= " GROUP BY d.doctor_id, a.work_date";

$stmt = $link->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => "SQL 準備失敗", "sql_error" => $link->error]));
}

// 修正 bind_param 參數
$type_str = str_repeat('i', count($params));
$stmt->bind_param($type_str, ...$params);

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
