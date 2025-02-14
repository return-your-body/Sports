<?php
include "../db.php";

date_default_timezone_set('Asia/Taipei');
$now = date('Y-m-d H:i:s');

// 查詢排班數據
$query = "
SELECT 
    d.doctor_id, 
    d.doctor, 
    ds.date, 
    st1.shifttime AS go_time, 
    st2.shifttime AS off_time,
    ds.go AS go_id,
    ds.off AS off_id
FROM 
    doctorshift ds
JOIN 
    doctor d ON ds.doctor_id = d.doctor_id
JOIN 
    shifttime st1 ON ds.go = st1.shifttime_id
JOIN 
    shifttime st2 ON ds.off = st2.shifttime_id
WHERE ds.date >= CURDATE() 
ORDER BY ds.date, d.doctor_id";

$result = mysqli_query($link, $query);
$schedule = [];
while ($row = mysqli_fetch_assoc($result)) {
    $schedule[$row['date']][] = [
        'doctor' => $row['doctor'],
        'go_time' => $row['go_time'],
        'off_time' => $row['off_time'],
        'doctor_id' => $row['doctor_id'],
        'go_id' => $row['go_id'],
        'off_id' => $row['off_id']
    ];
}

// 查詢請假數據
$query_leaves = "SELECT doctor_id, start_date, end_date FROM leaves WHERE is_approved = 1";
$result_leaves = mysqli_query($link, $query_leaves);
$leaves = [];
while ($row = mysqli_fetch_assoc($result_leaves)) {
    $leaves[] = [
        'doctor_id' => $row['doctor_id'],
        'start_date' => $row['start_date'],
        'end_date' => $row['end_date'],
    ];
}

// 查詢已預約時段
$query_appointments = "SELECT doctor_id, shifttime_id FROM appointment";
$result_appointments = mysqli_query($link, $query_appointments);
$appointments = [];
while ($row = mysqli_fetch_assoc($result_appointments)) {
    $appointments[] = [
        'doctor_id' => $row['doctor_id'],
        'shifttime_id' => $row['shifttime_id'],
    ];
}

// 回傳 JSON 數據
header('Content-Type: application/json');
echo json_encode([
    'schedule' => $schedule,
    'leaves' => $leaves,
    'appointments' => $appointments
], JSON_UNESCAPED_UNICODE);
?>
