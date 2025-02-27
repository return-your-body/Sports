<?php
// 開啟錯誤顯示
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 引入資料庫連線
require '../db.php';

header('Content-Type: application/json');

// 取得查詢參數
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$day = isset($_GET['day']) ? intval($_GET['day']) : 0;
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

// 檢查資料庫連線
if (!$link) {
    die(json_encode(["error" => "資料庫連線失敗: " . mysqli_connect_error()]));
}

// SQL 查詢
$query = "
    SELECT 
        d.doctor AS doctor_name, 
        ds.date AS work_date, 
        st1.shifttime AS start_time, 
        st2.shifttime AS end_time, 
        (st2.shifttime - st1.shifttime) AS work_hours, 
        COALESCE(SUM(CASE WHEN l.leave_type IS NOT NULL THEN 1 ELSE 0 END), 0) AS leave_hours,
        COALESCE(SUM(CASE WHEN a.status_id = 6 THEN 1 ELSE 0 END), 0) AS overtime_hours,
        i.item AS project_name, 
        COALESCE(SUM(i.price), 0) AS revenue
    FROM doctor d
    JOIN doctorshift ds ON d.doctor_id = ds.doctor_id
    JOIN shifttime st1 ON ds.go = st1.shifttime_id
    JOIN shifttime st2 ON ds.off = st2.shifttime_id
    LEFT JOIN appointment a ON ds.doctorshift_id = a.doctorshift_id
    LEFT JOIN medicalrecord m ON a.appointment_id = m.appointment_id
    LEFT JOIN item i ON m.item_id = i.item_id
    LEFT JOIN leaves l ON l.doctor_id = d.doctor_id AND l.start_date <= ds.date AND l.end_date >= ds.date
    WHERE YEAR(ds.date) = ? AND MONTH(ds.date) = ?
";

// 參數陣列
$params = [$year, $month];

if ($day > 0) {
    $query .= " AND DAY(ds.date) = ?";
    $params[] = $day;
}

if ($doctor_id > 0) {
    $query .= " AND d.doctor_id = ?";
    $params[] = $doctor_id;
}

$query .= " GROUP BY d.doctor, ds.date, i.item";

// 預備 SQL 語句
$stmt = $link->prepare($query);
if (!$stmt) {
    die(json_encode(["error" => "SQL 預備失敗: " . $link->error]));
}

// 綁定參數
$stmt->bind_param(str_repeat("i", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();

// 轉換 JSON
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
