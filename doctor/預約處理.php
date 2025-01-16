<?php
session_start();

if (!isset($_SESSION["登入狀態"])) {
    echo json_encode(['success' => false, 'message' => '未登入，請先登入後操作。']);
    exit;
}

require '../db.php';

if (!isset($_POST['doctor_id'], $_POST['date'], $_POST['time'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數。']);
    exit;
}

$doctor_id = intval($_POST['doctor_id']);
$date = $_POST['date'];
$time = $_POST['time'];

// 檢查該時段是否已額滿
$checkQuery = "
    SELECT a.appointment_id 
    FROM shifttime st
    LEFT JOIN doctorshift ds 
      ON ds.go <= st.shifttime_id 
      AND ds.off >= st.shifttime_id 
      AND ds.doctor_id = ? 
      AND ds.date = ?
    LEFT JOIN appointment a 
      ON a.shifttime_id = st.shifttime_id 
      AND a.doctorshift_id = ds.doctorshift_id
    WHERE st.shifttime = ?
";

$stmt = $link->prepare($checkQuery);
$stmt->bind_param('iss', $doctor_id, $date, $time);
$stmt->execute();
$result = $stmt->get_result();

if ($result->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => '該時段已額滿。']);
    exit;
}

// 插入新的預約
$insertQuery = "
    INSERT INTO appointment (shifttime_id, doctorshift_id, user_id)
    SELECT st.shifttime_id, ds.doctorshift_id, ?
    FROM shifttime st
    JOIN doctorshift ds 
      ON ds.go <= st.shifttime_id 
      AND ds.off >= st.shifttime_id 
      AND ds.doctor_id = ? 
      AND ds.date = ?
    WHERE st.shifttime = ?
";

$stmt = $link->prepare($insertQuery);
$stmt->bind_param('iiss', $_SESSION['user_id'], $doctor_id, $date, $time);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '預約成功！']);
} else {
    echo json_encode(['success' => false, 'message' => '預約失敗，請稍後再試。']);
}

$link->close();
?>