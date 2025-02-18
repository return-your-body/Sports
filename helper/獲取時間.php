<?php
require '../db.php';

date_default_timezone_set('Asia/Taipei');

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
date_default_timezone_set('Asia/Taipei');
$selected_date = isset($_GET['date']) ? $_GET['date'] : '';
$current_time = date('H:i');
$current_date = date('Y-m-d');

if (!$doctor_id || !$selected_date) {
    echo json_encode([]);
    exit;
}

// 1. 檢查醫生該天的上班時段 (go ~ off)
$sql_shift = "SELECT go, off FROM doctorshift WHERE doctor_id = ? AND date = ?";
$stmt_shift = mysqli_prepare($link, $sql_shift);
mysqli_stmt_bind_param($stmt_shift, "is", $doctor_id, $selected_date);
mysqli_stmt_execute($stmt_shift);
$result_shift = mysqli_stmt_get_result($stmt_shift);
$shift = mysqli_fetch_assoc($result_shift);

if (!$shift) {
    echo json_encode([]); // 沒有班表，無可預約時間
    exit;
}

$go = intval($shift['go']);
$off = intval($shift['off']);

// 2. 取得當前時間之後的時段 (避免選擇過期時段)
$sql_times = "SELECT shifttime_id, shifttime FROM shifttime WHERE shifttime_id BETWEEN ? AND ?";
if ($selected_date == $current_date) {
    $sql_times .= " AND shifttime > ?";
}
$stmt_times = mysqli_prepare($link, $sql_times);
if ($selected_date == $current_date) {
    mysqli_stmt_bind_param($stmt_times, "iis", $go, $off, $current_time);
} else {
    mysqli_stmt_bind_param($stmt_times, "ii", $go, $off);
}
mysqli_stmt_execute($stmt_times);
$result_times = mysqli_stmt_get_result($stmt_times);
$available_times = [];

while ($row = mysqli_fetch_assoc($result_times)) {
    $available_times[$row['shifttime_id']] = $row['shifttime'];
}

// 3. 排除已有預約的時段
$sql_booked = "SELECT shifttime_id FROM appointment WHERE doctorshift_id IN (SELECT doctorshift_id FROM doctorshift WHERE doctor_id = ? AND date = ?)";
$stmt_booked = mysqli_prepare($link, $sql_booked);
mysqli_stmt_bind_param($stmt_booked, "is", $doctor_id, $selected_date);
mysqli_stmt_execute($stmt_booked);
$result_booked = mysqli_stmt_get_result($stmt_booked);

while ($row = mysqli_fetch_assoc($result_booked)) {
    unset($available_times[$row['shifttime_id']]);
}

// 4. 排除請假時間
$sql_leave = "SELECT start_date, end_date FROM leaves WHERE doctor_id = ? AND DATE(start_date) <= ? AND DATE(end_date) >= ?";
$stmt_leave = mysqli_prepare($link, $sql_leave);
mysqli_stmt_bind_param($stmt_leave, "iss", $doctor_id, $selected_date, $selected_date);
mysqli_stmt_execute($stmt_leave);
$result_leave = mysqli_stmt_get_result($stmt_leave);

while ($leave = mysqli_fetch_assoc($result_leave)) {
    foreach ($available_times as $id => $time) {
        if ($selected_date . ' ' . $time . ':00' >= $leave['start_date'] && $selected_date . ' ' . $time . ':00' <= $leave['end_date']) {
            unset($available_times[$id]);
        }
    }
}

// 輸出可預約時間
$output = [];
foreach ($available_times as $id => $time) {
    $output[] = ['shifttime_id' => $id, 'shifttime' => $time];
}

echo json_encode($output);
exit;

?>
