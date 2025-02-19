<?php
require '../db.php';

date_default_timezone_set('Asia/Taipei');

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$selected_date = isset($_GET['date']) ? $_GET['date'] : '';
$current_time = date('H:i');
$current_date = date('Y-m-d');

if (!$doctor_id || !$selected_date) {
    echo json_encode([]);
    exit;
}

// **1. 確認醫生當天是否有班表**
$sql_shift = "SELECT go, off FROM doctorshift WHERE doctor_id = ? AND date = ?";
$stmt_shift = mysqli_prepare($link, $sql_shift);
mysqli_stmt_bind_param($stmt_shift, "is", $doctor_id, $selected_date);
mysqli_stmt_execute($stmt_shift);
$result_shift = mysqli_stmt_get_result($stmt_shift);
$shift = mysqli_fetch_assoc($result_shift);

if (!$shift) {
    error_log("❌ 沒有找到醫生的班表: doctor_id = $doctor_id, date = $selected_date");
    echo json_encode([]);
    exit;
}

$go = intval($shift['go']);
$off = intval($shift['off']);

error_log("✅ 醫生班表: doctor_id = $doctor_id, date = $selected_date, go = $go, off = $off");

// **2. 取得可選時段 (避免選擇過期時段)**
$sql_times = "SELECT shifttime_id, shifttime FROM shifttime WHERE shifttime_id BETWEEN ? AND ?";

if ($selected_date == $current_date) {
    error_log("🔍 當天預約，過濾過去時間: $current_time");
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

error_log("✅ 可用時段數量: " . count($available_times));

// **3. 排除已被預約的時段**
$sql_booked = "SELECT shifttime_id FROM appointment WHERE doctorshift_id IN 
(SELECT doctorshift_id FROM doctorshift WHERE doctor_id = ? AND date = ?)";
$stmt_booked = mysqli_prepare($link, $sql_booked);
mysqli_stmt_bind_param($stmt_booked, "is", $doctor_id, $selected_date);
mysqli_stmt_execute($stmt_booked);
$result_booked = mysqli_stmt_get_result($stmt_booked);

$booked_times = [];

while ($row = mysqli_fetch_assoc($result_booked)) {
    $booked_times[] = $row['shifttime_id'];
}

// **4. 排除請假的時段**
$sql_leave = "SELECT start_date, end_date FROM leaves WHERE doctor_id = ? AND ? BETWEEN start_date AND end_date";
$stmt_leave = mysqli_prepare($link, $sql_leave);
mysqli_stmt_bind_param($stmt_leave, "is", $doctor_id, $selected_date);
mysqli_stmt_execute($stmt_leave);
$result_leave = mysqli_stmt_get_result($stmt_leave);

if (mysqli_fetch_assoc($result_leave)) {
    error_log("❌ 醫生請假: doctor_id = $doctor_id, date = $selected_date");
    echo json_encode([]); // 醫生當天請假，無可用時段
    exit;
}

// **5. 移除已預約和請假的時段**
foreach ($booked_times as $booked) {
    unset($available_times[$booked]);
}

error_log("✅ 最終可用時段數量: " . count($available_times));
echo json_encode(array_values($available_times)); // 回傳 JSON 格式的時段資料
?>
