<?php
session_start();

if (!isset($_SESSION["登入狀態"])) {
    echo json_encode(['success' => false, 'message' => '未登入，請先登入後操作。']);
    exit;
}

require '../db.php';

if (isset($_POST['doctor'], $_POST['date'], $_POST['time'])) {
    $doctor = $_POST['doctor'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // 檢查該時段是否已被預約
    $query = "
        SELECT COUNT(*) AS count
        FROM appointment a
        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        JOIN shifttime st ON a.shifttime_id = st.shifttime_id
        WHERE ds.date = ? AND st.shifttime = ? AND ds.doctor_id = ?
    ";

    $stmt = $link->prepare($query);
    $stmt->bind_param('ssi', $date, $time, $doctor);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    echo json_encode(['available' => $data['count'] == 0]);
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => '缺少必要參數。']);
}

$link->close();
?>
