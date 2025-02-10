<?php
require '../db.php';
header('Content-Type: application/json');

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;
$date = isset($_GET['date']) ? $_GET['date'] : null;

if (!$doctor_id || !$date) {
    echo json_encode(["error" => "缺少 doctor_id 或 date"]);
    exit;
}

// 檢查是否為過去日期
$current_date = date('Y-m-d');
if ($date < $current_date) {
    echo json_encode(["error" => "無法選擇過去的日期"]);
    exit;
}

// 1. 取得醫生當天的班表
$sql = "SELECT go, off FROM doctorshift WHERE doctor_id = ? AND date = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(["error" => "當天沒有班表"]);
    exit;
}

$go = intval($row['go']);
$off = intval($row['off']);

// 2. 查詢醫生請假的時間範圍
$leave_sql = "SELECT start_date, end_date FROM leaves WHERE doctor_id = ? AND ? BETWEEN start_date AND end_date";
$leave_stmt = $link->prepare($leave_sql);
$leave_stmt->bind_param("is", $doctor_id, $date);
$leave_stmt->execute();
$leave_result = $leave_stmt->get_result();

$leave_times = [];
while ($leave_row = $leave_result->fetch_assoc()) {
    $leave_times[] = $leave_row;
}

// 3. 取得該天已被預約的時段
$appointment_sql = "SELECT shifttime_id FROM appointment WHERE doctorshift_id IN (
    SELECT doctorshift_id FROM doctorshift WHERE doctor_id = ? AND date = ?
)";
$appointment_stmt = $link->prepare($appointment_sql);
$appointment_stmt->bind_param("is", $doctor_id, $date);
$appointment_stmt->execute();
$appointment_result = $appointment_stmt->get_result();

$booked_times = [];
while ($appointment_row = $appointment_result->fetch_assoc()) {
    $booked_times[] = intval($appointment_row['shifttime_id']);
}

// 4. 查詢符合班表範圍內的時段
$shift_sql = "SELECT shifttime_id, shifttime FROM shifttime WHERE shifttime_id BETWEEN ? AND ?";
$shift_stmt = $link->prepare($shift_sql);
$shift_stmt->bind_param("ii", $go, $off);
$shift_stmt->execute();
$shift_result = $shift_stmt->get_result();

$available_times = [];
while ($shift_row = $shift_result->fetch_assoc()) {
    $shifttime_id = intval($shift_row['shifttime_id']);

    // 過濾請假時段
    $is_on_leave = false;
    foreach ($leave_times as $leave) {
        if ($date >= $leave['start_date'] && $date <= $leave['end_date']) {
            $is_on_leave = true;
            break;
        }
    }
    if ($is_on_leave) continue;

    // 過濾已預約時段
    if (!in_array($shifttime_id, $booked_times)) {
        $available_times[] = $shift_row;
    }
}

// 回傳可預約的時段
echo json_encode($available_times);
?>
