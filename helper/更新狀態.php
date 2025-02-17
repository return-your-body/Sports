<?php
require '../db.php';

// 接收前端傳來的參數
$id = $_POST['id'];
$doctorshift_id = $_POST['doctorshift_id'];
$shifttime_id = $_POST['shifttime_id'];

// 紀錄傳入參數，方便 Debug
error_log("Received - doctorshift_id: " . $doctorshift_id . ", shifttime_id: " . $shifttime_id . ", appointment_id: " . $id);

// **檢查 doctorshift_id 是否存在**
$check_shift = $link->prepare("
    SELECT go, off FROM doctorshift WHERE doctorshift_id = ?
");
$check_shift->bind_param("i", $doctorshift_id);
$check_shift->execute();
$check_shift->store_result();

if ($check_shift->num_rows == 0) {
    echo json_encode(["success" => false, "error" => "無效的班表 ID"]);
    exit;
}

$check_shift->bind_result($go, $off);
$check_shift->fetch();
$check_shift->close();

// **檢查 shifttime_id 是否有效 並且 在 go 和 off 範圍內**
$check_time = $link->prepare("
    SELECT shifttime_id FROM shifttime WHERE shifttime_id = ? 
");
$check_time->bind_param("i", $shifttime_id);
$check_time->execute();
$check_time->store_result();

if ($check_time->num_rows == 0) {
    echo json_encode(["success" => false, "error" => "無效的時間 ID"]);
    exit;
}
$check_time->close();

// 確保選擇的時段在 `go` 和 `off` 範圍內
if ($shifttime_id < $go || $shifttime_id > $off) {
    echo json_encode(["success" => false, "error" => "選擇的時間超出排班範圍"]);
    exit;
}

// **更新 appointment 資料**
$update_stmt = $link->prepare("
    UPDATE appointment 
    SET doctorshift_id = ?, shifttime_id = ? 
    WHERE appointment_id = ?
");
$update_stmt->bind_param("iii", $doctorshift_id, $shifttime_id, $id);
$success = $update_stmt->execute();
$update_stmt->close();

if ($success) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "資料更新失敗: " . $link->error]);
}
exit;
?>
