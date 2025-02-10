<?php
require '../db.php';
header('Content-Type: application/json');

// 紀錄 POST 內容（除錯用）
$rawPostData = file_get_contents("php://input");
parse_str($rawPostData, $postVars);
error_log("POST data: " . print_r($postVars, true));

// 檢查是否收到 id 和 shifttime_id
if (!isset($_POST['id']) || !isset($_POST['shifttime_id'])) {
    echo json_encode(["error" => "缺少必要參數", "post_data" => $_POST]);
    exit;
}

$appointmentId = intval($_POST['id']);
$newShifttimeId = intval($_POST['shifttime_id']);

// 確保數據正確
if ($appointmentId <= 0 || $newShifttimeId <= 0) {
    echo json_encode(["error" => "無效的 id 或 shifttime_id"]);
    exit;
}

// 檢查 appointment 是否存在並取得對應的 doctorshift_id
$sql = "SELECT doctorshift_id FROM appointment WHERE appointment_id = ?";
$stmt = $link->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "SQL 準備失敗: " . $link->error]);
    exit;
}
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "找不到對應的預約"]);
    exit;
}

$row = $result->fetch_assoc();
$doctorshiftId = $row['doctorshift_id'];

// 更新 appointment 的 shifttime_id
$sql = "UPDATE appointment SET shifttime_id = ? WHERE appointment_id = ?";
$stmt = $link->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "SQL 準備失敗: " . $link->error]);
    exit;
}
$stmt->bind_param("ii", $newShifttimeId, $appointmentId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "更新失敗: " . $stmt->error]);
}

$stmt->close();
?>
