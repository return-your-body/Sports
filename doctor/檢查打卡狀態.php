<?php
session_start();
header("Content-Type: application/json");
include '../db.php';

// 檢查是否登入
if (!isset($_SESSION["帳號"])) {
    echo json_encode(["error" => "未登入"]);
    exit;
}

$account = $_SESSION["帳號"]; // 從 Session 取得登入帳號

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
$sql = "SELECT st1.shifttime AS shift_start, st2.shifttime AS shift_end 
        FROM doctorshift ds
        LEFT JOIN shifttime st1 ON ds.go = st1.shifttime_id
        LEFT JOIN shifttime st2 ON ds.off = st2.shifttime_id
        WHERE ds.doctor_id = ? AND ds.date = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("is", $doctor_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$shift = $result->fetch_assoc();
$shift_start = $shift['shift_start'] ?? null;
$shift_end = $shift['shift_end'] ?? null;

// 取得當天打卡記錄
$sql = "SELECT clock_in, clock_out FROM attendance WHERE doctor_id = ? AND work_date = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("is", $doctor_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$attendance = $result->fetch_assoc();

$response = [
    "clock_in" => $attendance['clock_in'] ?? null,
    "clock_out" => $attendance['clock_out'] ?? null,
    "shift_start" => $shift_start,
    "shift_end" => $shift_end,
    "late" => null,
    "work_duration" => null
];

// 計算遲到時間
if (!empty($attendance['clock_in']) && !empty($shift_start)) {
    $clock_in_time = strtotime($attendance['clock_in']);
    $shift_start_time = strtotime($shift_start);
    if ($clock_in_time > $shift_start_time) {
        $late_seconds = $clock_in_time - $shift_start_time;
        $late_hours = floor($late_seconds / 3600);
        $late_minutes = floor(($late_seconds % 3600) / 60);
        $response["late"] = ($late_hours > 0 ? "{$late_hours} 小時 " : "") . "{$late_minutes} 分鐘";
    }
}

// 計算工時
if (!empty($attendance['clock_in']) && !empty($attendance['clock_out'])) {
    $clock_in_time = strtotime($attendance['clock_in']);
    $clock_out_time = strtotime($attendance['clock_out']);
    $work_seconds = $clock_out_time - $clock_in_time;
    $hours = floor($work_seconds / 3600);
    $minutes = floor(($work_seconds % 3600) / 60);
    $response["work_duration"] = "{$hours} 小時 {$minutes} 分";
}

echo json_encode($response);
?>
