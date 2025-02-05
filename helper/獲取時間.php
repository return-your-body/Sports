<?php
// 開啟錯誤顯示（Debug 模式）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../db.php'; // 確保 db.php 存在

header("Content-Type: application/json; charset=UTF-8");

// 取得參數
$date = isset($_GET['date']) ? $_GET['date'] : null;
$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;

// 檢查必要參數
if (!$date || !$appointment_id) {
    echo json_encode(["error" => "❌ 缺少 date 或 appointment_id"]);
    exit;
}

// 透過 `doctorshift` 獲取 `doctor_id` 和對應班表
$sql = "
    SELECT ds.doctor_id, ds.go, ds.off 
    FROM appointment a
    JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    WHERE a.appointment_id = ?
";
$stmt = $link->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "❌ SQL 錯誤 (appointment 查詢): " . $link->error]);
    exit;
}
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || empty($row['doctor_id'])) {
    echo json_encode(["error" => "❌ 找不到對應的醫生"]);
    exit;
}

$doctor_id = (int)$row['doctor_id'];
$go = (int)$row['go'];
$off = (int)$row['off'];
$current_time = date("H:i");

// **獲取可用時段**
$sql = "
    SELECT s.shifttime_id, s.shifttime
    FROM shifttime s
    WHERE s.shifttime_id BETWEEN ? AND ?
    AND s.shifttime_id NOT IN (
        SELECT shifttime_id FROM appointment 
        WHERE doctorshift_id IN (SELECT doctorshift_id FROM doctorshift WHERE doctor_id = ? AND date = ?)
    )
";

// **如果是今天，則過濾掉已過時間**
if ($date == date("Y-m-d")) {
    $sql .= " AND TIME_FORMAT(s.shifttime, '%H:%i') > ?";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => "❌ SQL 錯誤 (shifttime 查詢): " . $link->error]);
        exit;
    }
    $stmt->bind_param("iiiss", $go, $off, $doctor_id, $date, $current_time);
} else {
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => "❌ SQL 錯誤 (shifttime 查詢): " . $link->error]);
        exit;
    }
    $stmt->bind_param("iiis", $go, $off, $doctor_id, $date);
}

$stmt->execute();
$result = $stmt->get_result();
$availableTimes = [];

while ($row = $result->fetch_assoc()) {
    $availableTimes[] = $row;
}

// **確保回傳 JSON**
if (empty($availableTimes)) {
    echo json_encode(["error" => "⚠️ 無可用時段"]);
    exit;
}

echo json_encode($availableTimes);
?>
