<?php
session_start();
require '../db.php'; // 連接資料庫

$applicant_name = trim($_POST['name']);
$leave_type = trim($_POST['leave-type']);
$leave_type_other = isset($_POST['leave-type-other']) ? trim($_POST['leave-type-other']) : null;
$start_time_id = intval($_POST['start-time']);
$end_time_id = intval($_POST['end-time']);
$reason = trim($_POST['reason']);
$date = trim($_POST['date']);

if (empty($applicant_name) || empty($leave_type) || empty($start_time_id) || empty($end_time_id) || empty($reason) || empty($date)) {
    echo "<script>alert('所有欄位均為必填，請重新填寫！'); window.history.back();</script>";
    exit;
}

// 檢查醫生是否存在
$check_doctor_sql = "SELECT doctor_id FROM doctor WHERE doctor = ?";
$stmt_doctor = mysqli_prepare($link, $check_doctor_sql);
mysqli_stmt_bind_param($stmt_doctor, "s", $applicant_name);
mysqli_stmt_execute($stmt_doctor);
$result_doctor = mysqli_stmt_get_result($stmt_doctor);

if (mysqli_num_rows($result_doctor) == 0) {
    echo "<script>alert('找不到該醫生資料，請確認姓名是否正確！'); window.history.back();</script>";
    exit;
}
$doctor = mysqli_fetch_assoc($result_doctor);
$doctor_id = $doctor['doctor_id'];

// 確認是否可以請假
$check_shift_sql = "
    SELECT ds.doctorshift_id
    FROM doctorshift ds
    WHERE ds.doctor_id = ? AND ds.date = ? 
    AND ? BETWEEN ds.go AND ds.off 
    AND ? BETWEEN ds.go AND ds.off
";
$stmt_shift = mysqli_prepare($link, $check_shift_sql);
mysqli_stmt_bind_param($stmt_shift, "isii", $doctor_id, $date, $start_time_id, $end_time_id);
mysqli_stmt_execute($stmt_shift);
$result_shift = mysqli_stmt_get_result($stmt_shift);

if (mysqli_num_rows($result_shift) == 0) {
    echo "<script>alert('請假時間不在上班時間範圍內，請重新選擇！'); window.history.back();</script>";
    exit;
}

// 插入請假資料
$insert_leave_sql = "
    INSERT INTO leaves (doctor_id, leave_type, leave_type_other, start_date, end_date, reason)
    VALUES (?, ?, ?, ?, ?, ?)
";
$stmt_insert_leave = mysqli_prepare($link, $insert_leave_sql);
mysqli_stmt_bind_param($stmt_insert_leave, "isssis", $doctor_id, $leave_type, $leave_type_other, $start_time_id, $end_time_id, $reason);

if (mysqli_stmt_execute($stmt_insert_leave)) {
    echo "<script>alert('請假資料提交成功！'); window.location.href = 'd_leave.php';</script>";
} else {
    echo "<script>alert('提交失敗：" . mysqli_error($link) . "'); window.history.back();</script>";
}

mysqli_close($link);
?>
