<?php
session_start();
header("Content-Type: application/json");
include '../db.php';

// 檢查是否登入
if (!isset($_SESSION["帳號"])) {
    echo json_encode(["error" => "未登入"]);
    exit;
}

$account = $_SESSION["帳號"];

// 取得 user_id
$sql = "SELECT user_id FROM user WHERE account = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("s", $account);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(["error" => "找不到使用者"]);
    exit;
}

$user_id = $user["user_id"];

// 取得 doctor_id
$sql = "SELECT doctor_id FROM doctor WHERE user_id = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    echo json_encode(["error" => "找不到對應的醫生/助手"]);
    exit;
}

$doctor_id = $doctor["doctor_id"];
$today = date('Y-m-d');

// 取得排班時間
$sql = "SELECT st1.shifttime AS shift_start FROM doctorshift ds 
        LEFT JOIN shifttime st1 ON ds.go = st1.shifttime_id 
        WHERE ds.doctor_id = ? AND ds.date = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("is", $doctor_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$shift = $result->fetch_assoc();
$shift_start = $shift['shift_start'] ?? null;

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
    // 記錄上班時間
    $sql = "INSERT INTO attendance (doctor_id, work_date, clock_in, status) VALUES (?, ?, NOW(), 'working')";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("is", $doctor_id, $today);
    $stmt->execute();

    // 計算遲到時間
    $clockIn = date('H:i');
    $late = null;
    if ($shift_start && strtotime($clockIn) > strtotime($shift_start)) {
        $late_seconds = strtotime($clockIn) - strtotime($shift_start);
        $late_hours = floor($late_seconds / 3600);
        $late_minutes = floor(($late_seconds % 3600) / 60);
        $late = ($late_hours > 0 ? "{$late_hours} 小時 " : "") . "{$late_minutes} 分鐘";
    }

    echo json_encode(["message" => "上班打卡成功" . ($late ? "，遲到 $late" : "")]);
}
?>
