<?php
require '../db.php';
session_start();

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['帳號'])) {
    echo json_encode(["success" => false, "message" => "未登入"]);
    exit;
}

$帳號 = $_SESSION['帳號'];

// 獲取grade_id, doctor_id
$sql_user = "SELECT user.grade_id, doctor.doctor_id FROM user 
JOIN doctor ON user.user_id = doctor.user_id 
WHERE user.account = ?";
$stmt = mysqli_prepare($link, $sql_user);
mysqli_stmt_bind_param($stmt, "s", $帳號);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);

if ($user_row = mysqli_fetch_assoc($user_result)) {
    $grade_id = $user_row['grade_id'];
    $doctor_id = $user_row['doctor_id'];
} else {
    echo json_encode(["success" => false, "message" => "用戶不存在"]);
    exit;
}

$date = $_GET['date'];
$doctor = $_GET['doctor'];

// 當天時間檢查
if ($grade_id == 3 && $date == date('Y-m-d')) {
    $currentTime = date('H:i:s');
    $check_sql = "SELECT MAX(s.shifttime) AS end_time 
                  FROM doctorshift d
                  JOIN shifttime s ON d.off = s.shifttime_id 
                  WHERE d.date = ? AND d.doctor_id = ?";
    $stmt_check = mysqli_prepare($link, $check_sql);
    mysqli_stmt_bind_param($stmt_check, "si", $date, $doctor_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if ($shift_row = mysqli_fetch_assoc($result_check)) {
        if ($currentTime > $shift_row['end_time']) {
            echo json_encode(["success" => false, "message" => "當天已超過上班時間，無法請假"]);
            exit;
        }
    }
}

// 查詢可請假的時間
$sql = "SELECT s.shifttime FROM doctorshift d
        JOIN shifttime s ON d.go = s.shifttime_id
        WHERE d.date = ? AND d.doctor_id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "si", $date, $doctor);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$times = mysqli_fetch_all($result, MYSQLI_ASSOC);

if (count($times) > 0) {
    echo json_encode(["success" => true, "times" => $times]);
} else {
    echo json_encode(["success" => false, "message" => "無可請假時段"]);
}
