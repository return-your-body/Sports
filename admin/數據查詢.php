<?php
require '../db.php';

$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$date = isset($_GET['date']) && $_GET['date'] !== 'all' ? $_GET['date'] : null;
$doctor = isset($_GET['doctor']) ? $_GET['doctor'] : 'all';

// SQL 條件
$conditions = "YEAR(ds.date) = ? AND MONTH(ds.date) = ?";
$params = [$year, $month];
$types = "ii";

if ($date) {
    $conditions .= " AND DAY(ds.date) = ?";
    $params[] = $date;
    $types .= "i";
}

if ($doctor !== 'all') {
    $conditions .= " AND d.doctor_id = ?";
    $params[] = $doctor;
    $types .= "i";
}

// 查詢治療數據
$stmt = $link->prepare("SELECT d.doctor, COUNT(a.appointment_id) as count FROM appointment a
    JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    JOIN doctor d ON ds.doctor_id = d.doctor_id
    WHERE $conditions
    GROUP BY d.doctor");

if (!$stmt) {
    die("SQL 錯誤: " . $link->error);
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$data = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['doctor'];
    $data[] = $row['count'];
}

// 回傳 JSON 給前端
header('Content-Type: application/json');
echo json_encode(["labels" => $labels, "data" => $data]);
?>
