<?php
session_start();
include '../db.php';

// ✅ 開啟錯誤顯示，方便偵錯
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ 確保有 `appointment_id`
if (!isset($_POST['appointment_id'])) {
    echo json_encode(["success" => false, "message" => "缺少 appointment_id"]);
    exit;
}

$appointment_id = intval($_POST['appointment_id']);

// ✅ 檢查資料庫連線
if (!$link) {
    echo json_encode(["success" => false, "message" => "資料庫連線失敗"]);
    exit;
}

// ✅ 更新 `status_id = 8`（叫號中）
$sql = "UPDATE appointment SET status_id = 8 WHERE appointment_id = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "叫號成功"]);
} else {
    echo json_encode(["success" => false, "message" => "叫號失敗，可能狀態已更新或受限制"]);
}


// ✅ 關閉連線
$stmt->close();
$link->close();
?>