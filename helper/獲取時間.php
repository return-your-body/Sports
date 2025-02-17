<?php
require '../db.php';

date_default_timezone_set('Asia/Taipei'); // 設定時區

if (!isset($_GET['doctor_id']) || !isset($_GET['date'])) {
    echo json_encode([]);
    exit;
}

$doctor_id = intval($_GET['doctor_id']);
$date = $_GET['date'];

$currentTime = date('H:i');
$currentDate = date('Y-m-d');

$availableTimes = [];

// 取得醫生當天的上班時間（go ~ off）
$sql_shift = "SELECT go, off FROM doctorshift WHERE doctor_id = ? AND date = ?";
$stmt_shift = $link->prepare($sql_shift);
$stmt_shift->bind_param("is", $doctor_id, $date);
$stmt_shift->execute();
$result_shift = $stmt_shift->get_result();

if ($result_shift->num_rows === 0) {
    echo json_encode([]); // 醫生當天沒班
    exit;
}

$row_shift = $result_shift->fetch_assoc();
$go = intval($row_shift['go']);
$off = intval($row_shift['off']);

// 取得符合班表的時段
$sql_shifts = "SELECT shifttime_id, shifttime FROM shifttime WHERE shifttime_id BETWEEN ? AND ? ORDER BY shifttime_id";
$stmt_shifts = $link->prepare($sql_shifts);
$stmt_shifts->bind_param("ii", $go, $off);
$stmt_shifts->execute();
$result_shifts = $stmt_shifts->get_result();

while ($row = $result_shifts->fetch_assoc()) {
    $shifttime = $row['shifttime'];
    $shifttimeId = $row['shifttime_id'];

    // 排除已預約的時段
    $sql_appointment = "SELECT COUNT(*) as count FROM appointment 
                        WHERE doctorshift_id = (SELECT doctorshift_id FROM doctorshift WHERE doctor_id = ? AND date = ?) 
                        AND shifttime_id = ?";
    $stmt_appointment = $link->prepare($sql_appointment);
    $stmt_appointment->bind_param("isi", $doctor_id, $date, $shifttimeId);
    $stmt_appointment->execute();
    $appointmentResult = $stmt_appointment->get_result();
    $appointmentCount = $appointmentResult->fetch_assoc()['count'];

    // 排除醫生請假的時段
    $sql_leave = "SELECT COUNT(*) as count FROM leaves 
                  WHERE doctor_id = ? 
                  AND start_date <= ? 
                  AND end_date >= ?";
    $stmt_leave = $link->prepare($sql_leave);
    $stmt_leave->bind_param("iss", $doctor_id, "$date $shifttime", "$date $shifttime");
    $stmt_leave->execute();
    $leaveResult = $stmt_leave->get_result();
    $leaveCount = $leaveResult->fetch_assoc()['count'];

    // 排除過期時間（如果是當天日期）
    if ($date == $currentDate && $shifttime <= $currentTime) {
        continue;
    }

    // 只有符合條件的時段才顯示
    if ($appointmentCount == 0 && $leaveCount == 0) {
        $availableTimes[] = [
            'shifttime_id' => $shifttimeId,
            'shifttime' => $shifttime
        ];
    }
}

// 關閉連線
$stmt_shift->close();
$stmt_shifts->close();
$link->close();

// 確保回傳有效 JSON
echo json_encode($availableTimes);
?>
