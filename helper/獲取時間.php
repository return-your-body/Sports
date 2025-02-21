<?php
require '../db.php';
header('Content-Type: application/json');

if (!isset($_GET['doctor_id']) || !isset($_GET['date'])) {
    echo json_encode(["error" => "缺少必要參數"]);
    exit();
}

$doctor_id = intval($_GET['doctor_id']);
$date = $_GET['date'];

// 查詢醫生當日的可用時段，確保未被預約
$query = "SELECT st.shifttime FROM shifttime st
          JOIN doctorshift ds ON ds.go <= st.shifttime_id AND ds.off >= st.shifttime_id
          LEFT JOIN appointment a ON ds.doctorshift_id = a.doctorshift_id AND a.shifttime_id = st.shifttime_id
          WHERE ds.doctor_id = ? AND ds.date = ? AND a.shifttime_id IS NULL
          ORDER BY st.shifttime ASC";

$stmt = $link->prepare($query);
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$available_times = [];
while ($row = $result->fetch_assoc()) {
    $available_times[] = $row['shifttime'];
}

echo json_encode($available_times, JSON_UNESCAPED_UNICODE);
?>
