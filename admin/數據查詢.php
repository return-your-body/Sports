<?php
require '../db.php';  // 連接資料庫

header('Content-Type: application/json; charset=UTF-8');

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? str_pad(intval($_GET['month']), 2, '0', STR_PAD_LEFT) : date('m');

$sql = "SELECT d.doctor AS doctor_name, ds.date AS work_date, 
               st1.shifttime AS start_time, st2.shifttime AS end_time 
        FROM doctorshift ds
        JOIN doctor d ON ds.doctor_id = d.doctor_id
        JOIN shifttime st1 ON ds.go = st1.shifttime_id
        JOIN shifttime st2 ON ds.off = st2.shifttime_id
        WHERE YEAR(ds.date) = ? AND MONTH(ds.date) = ?
        ORDER BY ds.date, d.doctor_id";

$stmt = $link->prepare($sql);
$stmt->bind_param("ii", $year, $month);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'doctor_name' => $row['doctor_name'],
        'work_date' => $row['work_date'],
        'start_time' => $row['start_time'],
        'end_time' => $row['end_time']
    ];
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
