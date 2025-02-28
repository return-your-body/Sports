<?php
include '../db.php';

// SQL 查詢當天的叫號患者
$sql = "SELECT a.appointment_id, p.name AS patient_name, d.doctor AS therapist, s.shifttime
        FROM appointment a
        JOIN people p ON a.people_id = p.people_id
        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        JOIN doctor d ON ds.doctor_id = d.doctor_id
        JOIN shifttime s ON a.shifttime_id = s.shifttime_id
        WHERE a.status_id = 7
        AND ds.date = CURDATE()";

$result = $link->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    // 病人姓名遮蔽：林O明
    // $row['patient_name'] = mb_substr($row['patient_name'], 0, 1) . 'O' . mb_substr($row['patient_name'], -1, 1);
    $data[] = $row;
}

// 回傳 JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
