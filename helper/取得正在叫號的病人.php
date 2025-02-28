<?php
include '../db.php';

// SQL 查詢當天的叫號患者
$sql = "SELECT a.appointment_id, p.name AS patient_name, d.doctor AS therapist, s.shifttime
        FROM appointment a
        JOIN people p ON a.people_id = p.people_id
        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        JOIN doctor d ON ds.doctor_id = d.doctor_id
        JOIN shifttime s ON a.shifttime_id = s.shifttime_id
        WHERE a.status_id = 8
        AND ds.date = CURDATE()";

$result = $link->query($sql);
$data = [];

// 姓名遮蔽函數
function maskName($name) {
    $len = mb_strlen($name, "UTF-8");
    if ($len == 2) {
        return mb_substr($name, 0, 1, "UTF-8") . "O";
    } elseif ($len == 3) {
        return mb_substr($name, 0, 1, "UTF-8") . "O" . mb_substr($name, 2, 1, "UTF-8");
    } elseif ($len >= 4) {
        return mb_substr($name, 0, 1, "UTF-8") . "OO" . mb_substr($name, $len - 1, 1, "UTF-8");
    }
    return "O";
}

while ($row = $result->fetch_assoc()) {
    // 病人姓名遮蔽
    // $row['patient_name'] = maskName($row['patient_name']);
    $data[] = $row;
}

// 回傳 JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
