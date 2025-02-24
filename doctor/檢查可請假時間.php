<?php
session_start();

if (!isset($_SESSION["登入狀態"])) {
  header("Location: ../index.html");
  exit;
}

require '../db.php';

// 取得目前登入使用者資料
$account = $_SESSION['帳號'];
$sql_user = "SELECT u.user_id, d.doctor_id, d.doctor
             FROM user u
             JOIN doctor d ON u.user_id = d.user_id
             WHERE u.account = ? AND u.grade_id = 2";
$stmt_user = mysqli_prepare($link, $sql_user);
mysqli_stmt_bind_param($stmt_user, "s", $account);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);

if ($user = mysqli_fetch_assoc($result_user)) {
  $doctor_id = $user['doctor_id'];
  $doctor_name = $user['doctor'];
} else {
  echo "<script>alert('無權限訪問此頁面或資料錯誤！'); window.location.href='../index.html';</script>";
  exit;
}

// 查詢今天上班時間是否已超過
$today = date("Y-m-d");
$now_time = date("H:i:s");

$sql_shift = "SELECT st.shifttime
              FROM doctorshift ds
              JOIN shifttime st ON ds.go = st.shifttime_id
              WHERE ds.doctor_id = ? AND ds.date = ?";

$stmt_shift = mysqli_prepare($link, $sql_shift);
mysqli_stmt_bind_param($stmt_shift, "is", $doctor_id, $today);
mysqli_stmt_execute($stmt_shift);
$result_shift = mysqli_stmt_get_result($stmt_shift);

$allow_today_leave = true;  // 預設今天可請假

if ($shift = mysqli_fetch_assoc($result_shift)) {
  // 如果現在時間已超過上班時間則不可請假
  if ($now_time > $shift['shifttime']) {
    $allow_today_leave = false;
  }
}

// 返回允許的日期給前端
header('Content-Type: application/json');
echo json_encode(['allow_today_leave' => $allow_today_leave]);

mysqli_close($link);
?>