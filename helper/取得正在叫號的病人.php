<?php
include 'db.php';

$sql = "SELECT a.appointment_id, p.name, d.name AS therapist, s.shift_time
        FROM appointments a
        JOIN people p ON a.people_id = p.id
        JOIN doctorshift ds ON a.doctorshift_id = ds.id
        JOIN doctor d ON ds.doctor_id = d.id
        JOIN shifttime s ON a.shifttime_id = s.id
        WHERE a.status_id = 8";

$result = $link->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    // 病人姓名轉換成「林O明」
    $row['name'] = mb_substr($row['name'], 0, 1) . 'O' . mb_substr($row['name'], -1, 1);
    $data[] = $row;
}

echo json_encode($data);
?>
