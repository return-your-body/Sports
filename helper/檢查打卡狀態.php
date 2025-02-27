<?php
header("Content-Type: application/json");
include '../db.php';

$doctor_id = $_POST['doctor_id'] ?? null;
$today = date('Y-m-d');

if (!$doctor_id) {
    echo json_encode(["error" => "缺少 doctor_id"]);
    exit;
}

// 取得排班時間 (應該的上班時間)
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
