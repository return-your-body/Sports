<?php
require '../db.php';

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "無效的請求方式"]);
    exit;
}

$date = $_POST["date"] ?? '';
$doctor_id = $_POST["doctor_id"] ?? '';

if (empty($date) || empty($doctor_id)) {
    echo json_encode(["success" => false, "message" => "缺少必要參數"]);
    exit;
}

// 取得當前時間，確保過期時段不顯示
$current_time = date("H:i");

// 查詢醫生班表可用時段
$sql = "
    SELECT s.shifttime_id, s.shifttime
    FROM shifttime s
    JOIN doctorshift ds ON s.shifttime_id BETWEEN ds.go AND ds.off
    WHERE ds.date = ? AND ds.doctor_id = ?
    ORDER BY s.shifttime_id
";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "si", $date, $doctor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$available_times = [];
while ($row = mysqli_fetch_assoc($result)) {
    $available_times[$row["shifttime_id"]] = $row["shifttime"];
}

// **移除當天已過期的時段**
$available_times = array_filter($available_times, function ($time) use ($current_time, $date) {
    return ($date > date("Y-m-d")) || ($time >= $current_time);
});

// **排除請假時段**
$sql_leaves = "
    SELECT s.shifttime_id
    FROM shifttime s
    JOIN leaves l ON s.shifttime_id BETWEEN l.start_date AND l.end_date
    WHERE l.doctor_id = ? AND DATE(l.start_date) = ?
";
$stmt_leaves = mysqli_prepare($link, $sql_leaves);
mysqli_stmt_bind_param($stmt_leaves, "is", $doctor_id, $date);
mysqli_stmt_execute($stmt_leaves);
$result_leaves = mysqli_stmt_get_result($stmt_leaves);

while ($row = mysqli_fetch_assoc($result_leaves)) {
    unset($available_times[$row["shifttime_id"]]);
}

// **排除已預約的時段**
$sql_appointments = "
    SELECT a.shifttime_id
    FROM appointment a
    JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    WHERE ds.date = ? AND ds.doctor_id = ?
";
$stmt_appointments = mysqli_prepare($link, $sql_appointments);
mysqli_stmt_bind_param($stmt_appointments, "si", $date, $doctor_id);
mysqli_stmt_execute($stmt_appointments);
$result_appointments = mysqli_stmt_get_result($stmt_appointments);

while ($row = mysqli_fetch_assoc($result_appointments)) {
    unset($available_times[$row["shifttime_id"]]);
}

// **輸出結果**
if (!empty($available_times)) {
    echo json_encode(["success" => true, "times" => array_values(array_map(function ($id, $time) {
        return ["shifttime_id" => $id, "shifttime" => $time];
    }, array_keys($available_times), $available_times))]);
} else {
    echo json_encode(["success" => false, "message" => "無可用時段"]);
}

mysqli_close($link);
?>
