<?php
header("Content-Type: application/json"); // 確保回傳 JSON
include '../db.php';

$doctor_id = $_POST['doctor_id'];
$today = date('Y-m-d');

// 取得該醫生的排班時間
$sql = "SELECT st1.shifttime AS shift_start FROM doctorshift ds 
        LEFT JOIN shifttime st1 ON ds.go = st1.shifttime_id 
        WHERE ds.doctor_id = ? AND ds.date = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("is", $doctor_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$shift = $result->fetch_assoc();

// 檢查是否已經打過上班卡
$sql = "SELECT * FROM attendance WHERE doctor_id = ? AND work_date = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("is", $doctor_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();

if ($existing) {
    echo json_encode(["message" => "今天已經打過上班卡"]);
} else {
    // 插入上班時間
    $sql = "INSERT INTO attendance (doctor_id, work_date, clock_in, status) VALUES (?, ?, NOW(), 'working')";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("is", $doctor_id, $today);
    $stmt->execute();

    // 計算遲到時間
    $clockIn = date('H:i');
    $shiftStart = $shift['shift_start'] ?? null;
    $late = ($shiftStart && strtotime($clockIn) > strtotime($shiftStart)) 
            ? gmdate("H:i", strtotime($clockIn) - strtotime($shiftStart)) 
            : null;

    echo json_encode(["message" => "上班打卡成功" . ($late ? "，遲到 $late" : "")]);
}
?>
