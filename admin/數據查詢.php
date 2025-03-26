<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../db.php';
header('Content-Type: application/json; charset=UTF-8');

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$day = isset($_GET['day']) ? intval($_GET['day']) : 0;
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$chart_type = isset($_GET['chart']) ? $_GET['chart'] : '';
$item_name = isset($_GET['item']) ? $_GET['item'] : '';

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
        $sql = "SELECT d.doctor AS doctor_name,
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

        $sql .= " GROUP BY d.doctor";
        break;

    case '詳細數據':
        // 詳細資料表格 - 根據 item 名稱查詢醫師與數量、金額等
        $sql = "SELECT d.doctor AS doctor_name, i.item AS category, COUNT(*) AS value
                FROM medicalrecord mr
                JOIN appointment ap ON mr.appointment_id = ap.appointment_id
                JOIN doctorshift ds ON ap.doctorshift_id = ds.doctorshift_id
                JOIN doctor d ON ds.doctor_id = d.doctor_id
                JOIN item i ON mr.item_id = i.item_id
                WHERE i.item = ?";
        $params = [$item_name];
        $type_str = "s";
        break;

    default:
        echo json_encode(["error" => "未知的圖表類型"]);
        exit;
}

// 執行查詢
$stmt = $link->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "SQL 準備失敗: " . $link->error]);
    exit;
}

$stmt->bind_param($type_str, ...$params);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
