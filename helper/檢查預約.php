<?php
require '../db.php';

$doctor_id = intval($_POST['doctor_id']);
$date = mysqli_real_escape_string($link, $_POST['date']);

if (empty($doctor_id) || empty($date)) {
    echo json_encode(['success' => false, 'message' => '參數缺失']);
    exit;
}

$query = "
  SELECT st.shifttime_id, st.shifttime
  FROM shifttime st
  JOIN doctorshift ds ON ds.go <= st.shifttime_id AND ds.off >= st.shifttime_id
  WHERE ds.doctor_id = ? AND ds.date = ? 
  AND NOT EXISTS (
      SELECT 1 FROM appointment a WHERE a.doctorshift_id = ds.doctorshift_id AND a.shifttime_id = st.shifttime_id
  )
";
$stmt = $link->prepare($query);
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$times = [];
while ($row = $result->fetch_assoc()) {
    $times[] = $row;
}

if ($times) {
    echo json_encode(['success' => true, 'times' => $times]);
} else {
    echo json_encode(['success' => false, 'message' => '無可用時段']);
}

$stmt->close();
$link->close();
?>
