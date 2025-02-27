<?php
header("Content-Type: application/json"); // 確保回傳 JSON
include '../db.php';

$doctor_id = $_POST['doctor_id'];
$today = date('Y-m-d');

// 取得今天的打卡紀錄
$sql = "SELECT * FROM attendance WHERE doctor_id = ? AND work_date = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("is", $doctor_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$attendance = $result->fetch_assoc();

if (!$attendance) {
    echo json_encode(["message" => "請先打上班卡"]);
} elseif ($attendance['clock_out'] !== null) {
    echo json_encode(["message" => "已經打過下班卡"]);
} else {
    // 更新下班時間
    $sql = "UPDATE attendance SET clock_out = NOW(), status = 'off' WHERE id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("i", $attendance['id']);
    $stmt->execute();

    // 計算總工時
    $clockIn = strtotime($attendance['clock_in']);
    $clockOut = time();
    $workDuration = gmdate("H 小時 i 分", $clockOut - $clockIn);

    echo json_encode(["message" => "下班打卡成功，今日工時: $workDuration"]);
}
?>
