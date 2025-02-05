<?php
require '../db.php';

// 啟用錯誤記錄（Debug）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

// 確保請求為 POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "無效的請求方式"]);
    exit;
}

// 確保所有必要參數都存在
if (!isset($_POST['id'], $_POST['status'], $_POST['date'], $_POST['time'])) {
    echo json_encode(["error" => "缺少必要參數"]);
    exit;
}

// 取得參數
$appointment_id = intval($_POST['id']);
$status_name = trim($_POST['status']);
$date = $_POST['date'];
$shifttime_id = intval($_POST['time']); // 確保是數字

// 確保 `$link` 變數正確連接資料庫
if (!$link) {
    echo json_encode(["error" => "資料庫連接失敗"]);
    exit;
}

// **取得 status_id**
$status_stmt = $link->prepare("SELECT status_id FROM status WHERE status_name = ?");
$status_stmt->bind_param("s", $status_name);
$status_stmt->execute();
$status_result = $status_stmt->get_result();
$status_row = $status_result->fetch_assoc();
$status_stmt->close();

if (!$status_row) {
    echo json_encode(["error" => "無效的狀態"]);
    exit;
}
$status_id = $status_row['status_id'];

// **取得醫生的 doctorshift_id**
$doctorshift_stmt = $link->prepare("
    SELECT doctorshift_id FROM doctorshift 
    WHERE doctor_id = (SELECT doctor_id FROM appointment WHERE appointment_id = ?) 
    AND date = ? 
    AND ? BETWEEN go AND off
");
$doctorshift_stmt->bind_param("isi", $appointment_id, $date, $shifttime_id);
$doctorshift_stmt->execute();
$doctorshift_result = $doctorshift_stmt->get_result();
$doctorshift_row = $doctorshift_result->fetch_assoc();
$doctorshift_stmt->close();

if (!$doctorshift_row) {
    echo json_encode(["error" => "該時段不在醫生的班表範圍內"]);
    exit;
}
$doctorshift_id = $doctorshift_row['doctorshift_id'];

// **更新 appointment**
$update_query = "UPDATE appointment SET status_id = ?, shifttime_id = ?, doctorshift_id = ?, created_at = NOW() WHERE appointment_id = ?";
$update_stmt = $link->prepare($update_query);
$update_stmt->bind_param("iiii", $status_id, $shifttime_id, $doctorshift_id, $appointment_id);

if ($update_stmt->execute()) {
    echo json_encode(["success" => "預約更新成功"]);
} else {
    echo json_encode(["error" => "SQL 更新失敗: " . $link->error]);
}

$update_stmt->close();
$link->close();
?>
