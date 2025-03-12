<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../db.php';

header('Content-Type: application/json; charset=UTF-8');

// 取得 GET 參數
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$day = isset($_GET['day']) ? intval($_GET['day']) : 0;
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$chart_type = isset($_GET['chart']) ? $_GET['chart'] : '';

if (!$year || !$month) {
    die(json_encode(["error" => "缺少必要參數"], JSON_UNESCAPED_UNICODE));
}

$data = [];
$sql = "";
$params = [];
$type_str = "";

switch ($chart_type) {
    case '項目數比例':
        $sql = "SELECT d.doctor AS doctor_name, i.item, COUNT(DISTINCT mr.appointment_id) AS item_count
                FROM medicalrecord mr
                JOIN appointment ap ON mr.appointment_id = ap.appointment_id
                JOIN doctorshift ds ON ap.doctorshift_id = ds.doctorshift_id
                JOIN doctor d ON ds.doctor_id = d.doctor_id
                JOIN item i ON mr.item_id = i.item_id
                WHERE YEAR(ds.date) = ? AND MONTH(ds.date) = ?";
        $params = [$year, $month];
        $type_str = "ii";

        if ($day != 0) {
            $sql .= " AND DAY(ds.date) = ?";
            $params[] = $day;
            $type_str .= "i";
        }

        if ($doctor_id != 0) {
            $sql .= " AND d.doctor_id = ?";
            $params[] = $doctor_id;
            $type_str .= "i";
        }

        $sql .= " GROUP BY d.doctor, i.item";
        break;

    case '治療師預約人數比例':
        $sql = "SELECT d.doctor AS doctor_name, COUNT(DISTINCT ap.appointment_id) AS total_appointments
                FROM appointment ap
                JOIN doctorshift ds ON ap.doctorshift_id = ds.doctorshift_id
                JOIN doctor d ON ds.doctor_id = d.doctor_id
                WHERE YEAR(ds.date) = ? AND MONTH(ds.date) = ?";
        $params = [$year, $month];
        $type_str = "ii";

        if ($day != 0) {
            $sql .= " AND DAY(ds.date) = ?";
            $params[] = $day;
            $type_str .= "i";
        }

        if ($doctor_id != 0) {
            $sql .= " AND d.doctor_id = ?";
            $params[] = $doctor_id;
            $type_str .= "i";
        }

        $sql .= " GROUP BY d.doctor";
        break;

    case '收入統計':
        $sql = "SELECT d.doctor AS doctor_name, i.item, SUM(i.price) AS total_revenue
                FROM medicalrecord mr
                JOIN appointment ap ON mr.appointment_id = ap.appointment_id
                JOIN doctorshift ds ON ap.doctorshift_id = ds.doctorshift_id
                JOIN doctor d ON ds.doctor_id = d.doctor_id
                JOIN item i ON mr.item_id = i.item_id
                WHERE YEAR(ds.date) = ? AND MONTH(ds.date) = ?";
        $params = [$year, $month];
        $type_str = "ii";

        if ($day != 0) {
            $sql .= " AND DAY(ds.date) = ?";
            $params[] = $day;
            $type_str .= "i";
        }

        if ($doctor_id != 0) {
            $sql .= " AND d.doctor_id = ?";
            $params[] = $doctor_id;
            $type_str .= "i";
        }

        $sql .= " GROUP BY d.doctor, i.item";
        break;

    case '總工作時數':
        $sql = "SELECT d.doctor AS doctor_name, a.work_date, 
                           SUM(TIMESTAMPDIFF(HOUR, a.clock_in, a.clock_out)) AS total_work_hours,
                           SUM(CASE WHEN l.leave_type = '加班' THEN TIMESTAMPDIFF(HOUR, l.start_date, l.end_date) ELSE 0 END) AS overtime_hours,
    SUM(CASE WHEN l.leave_type = '請假' THEN TIMESTAMPDIFF(HOUR, l.start_date, l.end_date) ELSE 0 END) AS leave_hours
    
                    FROM attendance a
                    JOIN doctor d ON a.doctor_id = d.doctor_id
                    LEFT JOIN leaves l ON a.doctor_id = l.doctor_id AND l.start_date = a.work_date
                    WHERE YEAR(a.work_date) = ? AND MONTH(a.work_date) = ?";
        $params = [$year, $month];
        $type_str = "ii";

        if ($day != 0) {
            $sql .= " AND DAY(a.work_date) = ?";
            $params[] = $day;
            $type_str .= "i";
        }

        if ($doctor_id != 0) {
            $sql .= " AND d.doctor_id = ?";
            $params[] = $doctor_id;
            $type_str .= "i";
        }

        $sql .= " GROUP BY d.doctor, a.work_date";
        break;

}

// 執行 SQL 查詢
$stmt = $link->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => "SQL 準備失敗: " . $link->error], JSON_UNESCAPED_UNICODE));
}

$stmt->bind_param($type_str, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// 取得查詢結果
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>