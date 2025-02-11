<?php
require '../db.php';

$date = $_GET['date'];
$doctor_name = $_GET['doctor'];

if (empty($date) || empty($doctor_name)) {
    echo json_encode(["success" => false, "times" => []]);
    exit;
}

// 取得醫生 ID
$sql_doctor = "SELECT doctor_id FROM doctor WHERE doctor = ?";
$stmt = mysqli_prepare($link, $sql_doctor);
mysqli_stmt_bind_param($stmt, "s", $doctor_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$doctor = mysqli_fetch_assoc($result);
if (!$doctor) {
    echo json_encode(["success" => false, "times" => []]);
    exit;
}
$doctor_id = $doctor['doctor_id'];

// 取得當日的班表時段
$sql_shift = "
    SELECT s.shifttime_id, s.shifttime
    FROM shifttime s
    JOIN doctorshift ds ON s.shifttime_id BETWEEN ds.go AND ds.off
    WHERE ds.doctor_id = ? AND ds.date = ?
";
$stmt = mysqli_prepare($link, $sql_shift);
mysqli_stmt_bind_param($stmt, "is", $doctor_id, $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$times = mysqli_fetch_all($result, MYSQLI_ASSOC);

// 若無班表，返回無可請假時段
if (empty($times)) {
    echo json_encode(["success" => false, "times" => []]);
    exit;
}

// 過濾已請假的時段
$sql_leaves = "
    SELECT shifttime_id
    FROM leaves
    WHERE doctor_id = ? AND ? BETWEEN DATE(start_date) AND DATE(end_date)
";
$stmt = mysqli_prepare($link, $sql_leaves);
mysqli_stmt_bind_param($stmt, "is", $doctor_id, $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$leave_times = mysqli_fetch_all($result, MYSQLI_ASSOC);

// 移除已請假的時段
$available_times = array_filter($times, function ($time) use ($leave_times) {
    foreach ($leave_times as $leave) {
        if ($time['shifttime_id'] == $leave['shifttime_id']) {
            return false;
        }
    }
    return true;
});

echo json_encode(["success" => true, "times" => array_values($available_times)]);
?>