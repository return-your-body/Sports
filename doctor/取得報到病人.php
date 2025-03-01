<?php
session_start(); // 啟動 Session
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 確保使用者已登入
if (!isset($_SESSION["帳號"])) {
    die(json_encode(["error" => "未登入"]));
}

$帳號 = $_SESSION["帳號"]; // 取得當前登入帳號
$date = date('Y-m-d'); // 取得今日日期

// 先透過帳號取得 user_id
$sql_user = "SELECT user_id FROM user WHERE account = ?";
$stmt_user = $link->prepare($sql_user);
$stmt_user->bind_param("s", $帳號);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
if (!$user) {
    die(json_encode(["error" => "查無此使用者"]));
}
$user_id = $user['user_id'];

// 透過 user_id 查詢對應的 doctor_id
$sql_doctor = "SELECT doctor_id FROM doctor WHERE user_id = ?";
$stmt_doctor = $link->prepare($sql_doctor);
$stmt_doctor->bind_param("i", $user_id);
$stmt_doctor->execute();
$result_doctor = $stmt_doctor->get_result();
$doctor = $result_doctor->fetch_assoc();
if (!$doctor) {
    die(json_encode(["error" => "查無對應的治療師"]));
}
$doctor_id = $doctor['doctor_id'];

// 取得該治療師今日報到的患者資料
$sql = "SELECT a.appointment_id,
            p.name AS patient_name, 
            s.shifttime 
        FROM appointment a
        JOIN people p ON a.people_id = p.people_id
        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        JOIN shifttime s ON a.shifttime_id = s.shifttime_id
        WHERE ds.doctor_id = ?
          AND ds.date = ?
          AND a.status_id = 3"; // 狀態 3 = 報到

$stmt = $link->prepare($sql);
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // 取得患者姓名
    $patient_name = $row['patient_name'];
    $name_length = mb_strlen($patient_name);
    $masked_name = $patient_name;

    $row['patient_name'] = $masked_name;
    $row['call_button'] = '<button onclick="callPatient(\'' . $masked_name . '\', \'' . $row['shifttime'] . '\')">叫號</button>';
    $data[] = $row;
}

// 設定 JSON 回傳
header('Content-Type: application/json');
echo json_encode($data);
?>
