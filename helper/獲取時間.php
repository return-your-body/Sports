<?php
require '../db.php';

data_default_timezone_set('Asia/Taipei'); // 設定時區

if (!isset($_GET['doctor_id']) || !isset($_GET['date'])) {
    echo json_encode(["error" => "缺少 doctor_id 或 date"]);
    exit;
}

doctor_id = intval($_GET['doctor_id']);
date = $_GET['date'];

// 取得目前時間，防止選擇過期時段
$currentTime = date('H:i');
$currentDate = date('Y-m-d');

// 查詢該醫生當天的排班時段
$sql = "SELECT s.shifttime_id, s.shifttime 
        FROM shifttime s
        JOIN doctorshift d ON s.shifttime_id BETWEEN d.go AND d.off
        WHERE d.doctor_id = ? AND d.date = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$availableTimes = [];
while ($row = $result->fetch_assoc()) {
    $shifttime = $row['shifttime'];
    $shifttimeId = $row['shifttime_id'];

    // 檢查是否已被預約
    $checkAppointment = "SELECT COUNT(*) as count FROM appointment WHERE doctorshift_id = (SELECT doctorshift_id FROM doctorshift WHERE doctor_id = ? AND date = ?) AND shifttime_id = ?";
    $stmtCheck = $link->prepare($checkAppointment);
    $stmtCheck->bind_param("isi", $doctor_id, $date, $shifttimeId);
    $stmtCheck->execute();
    $appointmentResult = $stmtCheck->get_result();
    $appointmentCount = $appointmentResult->fetch_assoc()['count'];
    
    // 檢查是否該時段醫生請假
    $checkLeave = "SELECT COUNT(*) as count FROM leaves WHERE doctor_id = ? AND start_date <= ? AND end_date >= ?";
    $stmtLeave = $link->prepare($checkLeave);
    $stmtLeave->bind_param("iss", $doctor_id, "$date $shifttime", "$date $shifttime");
    $stmtLeave->execute();
    $leaveResult = $stmtLeave->get_result();
    $leaveCount = $leaveResult->fetch_assoc()['count'];

    // 過濾條件：已被預約或醫生請假或過期時間不顯示
    if ($appointmentCount == 0 && $leaveCount == 0 && ($date > $currentDate || ($date == $currentDate && $shifttime > $currentTime))) {
        $availableTimes[] = [
            'shifttime_id' => $shifttimeId,
            'shifttime' => $shifttime
        ];
    }
}

$stmt->close();
$link->close();

echo json_encode($availableTimes);
?>
