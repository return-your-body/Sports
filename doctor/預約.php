<?php
require '../db.php';

$date = mysqli_real_escape_string($link, $_POST['date']);
$doctor_id = intval($_POST['doctor_id']);

if (empty($date) || empty($doctor_id)) {
    echo json_encode(['success' => false, 'message' => '參數缺失']);
    exit;
}

// 查詢可用時段
$query = "
    SELECT st.shifttime_id, st.shifttime
    FROM shifttime st
    JOIN doctorshift ds ON ds.go <= st.shifttime_id AND ds.off >= st.shifttime_id
    WHERE ds.doctor_id = ? AND ds.date = ?
      AND NOT EXISTS (
          SELECT 1 
          FROM appointment a
          WHERE a.doctorshift_id = ds.doctorshift_id 
            AND a.shifttime_id = st.shifttime_id
      )
    ORDER BY st.shifttime_id ASC
";
$stmt = $link->prepare($query);
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $times = [];
    while ($row = $result->fetch_assoc()) {
        $times[] = $row;
    }
    if (count($times) > 0) {
        echo json_encode(['success' => true, 'times' => $times]);
    } else {
        echo json_encode(['success' => false, 'message' => '無可用時段']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '查詢失敗']);
}

$stmt->close();
$link->close();
?>
