<?php
require '../db.php';  // 確保你的資料庫連線有效
header("Content-Type: application/json; charset=UTF-8");

// 允許 CORS 請求（如果需要從不同的前端調用）
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "❌ 只接受 POST 請求"]);
    exit;
}

// 取得 POST 數據
$postData = file_get_contents("php://input");
$jsonData = json_decode($postData, true);

// 從 JSON 解析變數
$appointment_id = $jsonData['appointment_id'] ?? null;
$doctor_id = $jsonData['doctor_id'] ?? null;
$date = isset($jsonData['date']) ? date('Y-m-d', strtotime($jsonData['date'])) : null;
$time = $jsonData['time'] ?? null;

// 記錄錯誤日誌（方便偵錯）
error_log("接收到請求: appointment_id={$appointment_id}, doctor_id={$doctor_id}, date={$date}, time={$time}");

if (!$appointment_id || !$doctor_id || !$date || !$time) {
    echo json_encode(["success" => false, "message" => "❌ 參數錯誤，請檢查發送的數據！"]);
    exit;
}

// 先查詢對應的 doctorshift_id
$shift_sql = "SELECT doctorshift_id FROM doctorshift WHERE doctor_id = ? AND date = ?";
$stmt = $link->prepare($shift_sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "❌ SQL 準備失敗: " . $link->error]);
    exit;
}

$stmt->bind_param("is", $doctor_id, $date);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "❌ SQL 執行失敗: " . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$shift_row = $result->fetch_assoc();

if (!$shift_row) {
    echo json_encode([
        "success" => false,
        "message" => "❌ 未找到對應的班表，請確認是否有排班",
        "doctor_id" => $doctor_id,
        "date" => $date
    ]);
    exit;
}

$doctorshift_id = $shift_row['doctorshift_id'];

// 查詢對應的 shifttime_id
$time_sql = "SELECT shifttime_id FROM shifttime WHERE shifttime = ?";
$stmt = $link->prepare($time_sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "❌ SQL 準備失敗: " . $link->error]);
    exit;
}

$stmt->bind_param("s", $time);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "❌ SQL 執行失敗: " . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$time_row = $result->fetch_assoc();

if (!$time_row) {
    echo json_encode([
        "success" => false,
        "message" => "❌ 未找到對應的時段",
        "time" => $time
    ]);
    exit;
}

$shifttime_id = $time_row['shifttime_id'];

// 更新 appointment 記錄，並將狀態修改為 2（表示修改）
$update_sql = "UPDATE appointment 
               SET doctorshift_id = ?, shifttime_id = ?, status_id = 2 
               WHERE appointment_id = ?";
$stmt = $link->prepare($update_sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "❌ SQL 準備失敗: " . $link->error]);
    exit;
}

$stmt->bind_param("iii", $doctorshift_id, $shifttime_id, $appointment_id);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "❌ SQL 執行失敗: " . $stmt->error]);
    exit;
}

// 成功回應
echo json_encode(["success" => true, "message" => "✅ 預約修改成功"]);
exit;
?>
