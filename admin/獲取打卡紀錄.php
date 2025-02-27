<?php
// 顯示錯誤訊息
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 連接資料庫
require '../db.php';

header('Content-Type: application/json');

// 取得查詢參數
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// 建立 SQL 查詢
$query = "SELECT attendance_id, doctor_id, work_date, clock_in, clock_out, status, created_at FROM attendance WHERE work_date = ?";
$params = [$date];

if ($doctor_id > 0) {
    $query .= " AND doctor_id = ?";
    $params[] = $doctor_id;
}

// 預備 SQL
$stmt = $link->prepare($query);
if (!$stmt) {
    echo json_encode(["error" => "SQL 預備失敗: " . $link->error]);
    exit;
}

// 綁定參數
$stmt->bind_param(str_repeat("s", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();

// 取得數據
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// 回傳 JSON
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
