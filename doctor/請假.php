<?php
require '../db.php';

$doctor_id = $_POST['doctor_id'] ?? '';
$leave_type = $_POST['leave-type'] ?? '';
$leave_type_other = $_POST['leave-type-other'] ?? '';
$date = $_POST['date'] ?? '';
$start_time = $_POST['start-time'] ?? '';
$end_time = $_POST['end-time'] ?? '';
$reason = $_POST['reason'] ?? '';

if (empty($doctor_id) || empty($leave_type) || empty($date) || empty($start_time) || empty($end_time) || empty($reason)) {
    echo json_encode(["success" => false, "message" => "部分欄位缺失，請重新填寫"]);
    exit;
}

$start_datetime = date("Y-m-d H:i:s", strtotime("$date $start_time"));
$end_datetime = date("Y-m-d H:i:s", strtotime("$date $end_time"));

$sql = "INSERT INTO leaves (doctor_id, leave_type, leave_type_other, start_date, end_date, reason, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "isssss", $doctor_id, $leave_type, $leave_type_other, $start_datetime, $end_datetime, $reason);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["success" => true, "message" => "請假資料提交成功，請去請假資料查詢查看是否請假通過~~"]);
} else {
    echo json_encode(["success" => false, "message" => "資料庫錯誤：" . mysqli_error($link)]);
}

mysqli_stmt_close($stmt);
mysqli_close($link);
?>
