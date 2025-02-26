<?php
// 顯示所有錯誤以利偵錯
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../db.php'; // 確保 db.php 檔案存在且正確連接資料庫

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$day = isset($_GET['day']) ? intval($_GET['day']) : 0;
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

// 調整 SQL 查詢以確保 `item` 表的欄位名稱正確
$query = "
    SELECT 
        d.doctor AS doctor_name, 
        ds.date AS work_date, 
        st1.shifttime AS start_time, 
        st2.shifttime AS end_time, 
        SUM(a.status_id = 6) AS overtime_hours,
        i.item AS project_name, 
        SUM(i.price) AS revenue
    FROM doctor d
    JOIN doctorshift ds ON d.doctor_id = ds.doctor_id
    JOIN shifttime st1 ON ds.go = st1.shifttime_id
    JOIN shifttime st2 ON ds.off = st2.shifttime_id
    LEFT JOIN appointment a ON ds.doctorshift_id = a.doctorshift_id
    LEFT JOIN item i ON a.shifttime_id = i.item_id
    WHERE YEAR(ds.date) = ? AND MONTH(ds.date) = ?
";

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

$stmt = $link->prepare($query);
$stmt->bind_param(str_repeat("i", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
