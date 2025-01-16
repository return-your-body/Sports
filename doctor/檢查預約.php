<?php
session_start();

if (!isset($_SESSION["登入狀態"])) {
    echo json_encode(['success' => false, 'message' => '未登入，請先登入後操作。']);
    exit;
}

require '../db.php';

if (!isset($_POST['doctor_id'], $_POST['date'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數。']);
    exit;
}

$doctor_id = intval($_POST['doctor_id']);
$date = $_POST['date'];

$query = "
    SELECT st.shifttime, 
           CASE 
               WHEN a.appointment_id IS NOT NULL THEN '額滿' 
               ELSE '預約' 
           END AS status
    FROM shifttime st
    LEFT JOIN doctorshift ds 
      ON ds.go <= st.shifttime_id 
      AND ds.off >= st.shifttime_id 
      AND ds.doctor_id = ? 
      AND ds.date = ?
    LEFT JOIN appointment a 
      ON a.shifttime_id = st.shifttime_id 
      AND a.doctorshift_id = ds.doctorshift_id
    WHERE st.shifttime >= '07:00' AND st.shifttime <= '16:00'
    ORDER BY st.shifttime ASC
";

$stmt = $link->prepare($query);
$stmt->bind_param('is', $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$timeslots = [];
while ($row = $result->fetch_assoc()) {
    $timeslots[] = [
        'time' => $row['shifttime'],
        'status' => $row['status'],
    ];
}

// 返回 JSON
echo json_encode(['success' => true, 'timeslots' => $timeslots]);
$link->close();
?>
