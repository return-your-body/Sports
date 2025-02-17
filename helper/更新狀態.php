<?php
require '../db.php';

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$current_time = date('H:i'); // 當前時間

if ($doctor_id === 0) {
    echo json_encode([]);
    exit;
}

// SQL 查詢：篩選符合 `go~off` 並且未被預約、未請假的時段
$sql = "
    SELECT st.shifttime_id, st.shifttime
    FROM shifttime st
    JOIN doctorshift ds 
        ON ds.go <= st.shifttime_id 
        AND ds.off >= st.shifttime_id
    WHERE ds.date >= ? 
    AND ds.doctor_id = ?
    AND NOT EXISTS (
        SELECT 1 FROM appointment a 
        WHERE a.doctorshift_id = ds.doctorshift_id 
        AND a.shifttime_id = st.shifttime_id
    )
    AND NOT EXISTS (
        SELECT 1 FROM leaves l
        WHERE l.doctor_id = ds.doctor_id 
        AND ds.date BETWEEN DATE(l.start_date) AND DATE(l.end_date)
    )
";

// 當查詢日期是今天時，排除已過期時段
if ($date == date('Y-m-d')) {
    $sql .= " AND STR_TO_DATE(st.shifttime, '%H:%i') > STR_TO_DATE(?, '%H:%i')";
}

$sql .= " ORDER BY st.shifttime_id ASC";

$stmt = $link->prepare($sql);

// 綁定參數
if ($date == date('Y-m-d')) {
    $stmt->bind_param("sis", $date, $doctor_id, $current_time);
} else {
    $stmt->bind_param("si", $date, $doctor_id);
}

$stmt->execute();
$result = $stmt->get_result();

$available_times = [];
while ($row = $result->fetch_assoc()) {
    $available_times[] = $row;
}

echo json_encode($available_times);
?>
