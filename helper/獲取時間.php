<?php
require '../db.php';
header('Content-Type: application/json');

if (!isset($_GET['doctor_id']) || !isset($_GET['date'])) {
    echo json_encode([]);
    exit();
}

$doctor_id = intval($_GET['doctor_id']);
$date = $_GET['date'];

$query = "
    SELECT st.shifttime 
    FROM shifttime st
    JOIN doctorshift ds ON ds.doctor_id = ? AND ds.date = ?
    WHERE EXISTS (
        SELECT 1 FROM doctorshift WHERE doctorshift.doctor_id = ds.doctor_id 
        AND doctorshift.date = ds.date 
        AND ds.go <= st.shifttime_id 
        AND ds.off >= st.shifttime_id
    )
    AND NOT EXISTS (
        SELECT 1 FROM appointment a 
        WHERE a.doctorshift_id = ds.doctorshift_id 
        AND a.shifttime_id = st.shifttime_id 
        AND a.status_id IN (1, 2, 4, 5, 6) -- 排除已預約、請假、爽約、已看診
    )
    ORDER BY st.shifttime ASC
";

$stmt = $link->prepare($query);
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$available_times = [];
while ($row = $result->fetch_assoc()) {
    $available_times[] = $row['shifttime'];
}

echo json_encode($available_times);
?>
