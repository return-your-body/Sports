<?php
require '../db.php';

header('Content-Type: application/json; charset=UTF-8'); // 確保回傳 JSON

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$day = isset($_GET['day']) ? intval($_GET['day']) : date('d');
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

$sql = "SELECT doctor_name, work_date, work_hours, overtime_hours, revenue, project_name 
        FROM attendance 
        WHERE YEAR(work_date) = ? AND MONTH(work_date) = ? AND DAY(work_date) = ?";
$params = [$year, $month, $day];

if ($doctor_id != 0) {
    $sql .= " AND doctor_id = ?";
    $params[] = $doctor_id;
}

$stmt = $link->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$result) {
    echo json_encode(["error" => "查無數據"]);
} else {
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>
