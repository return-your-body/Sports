<?php
include "../db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(["error" => "無效的參數"]);
    exit;
}

$appointment_id = intval($_GET['id']);
$query_details = "
SELECT 
    d.doctor AS doctor_name,
    i.item AS treatment_item,
    i.price AS treatment_price,
    mr.created_at AS created_time
FROM medicalrecord mr
LEFT JOIN appointment a ON mr.appointment_id = a.appointment_id
LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
LEFT JOIN item i ON mr.item_id = i.item_id
WHERE mr.appointment_id = ?;
";

$stmt = mysqli_prepare($link, $query_details);
mysqli_stmt_bind_param($stmt, "i", $appointment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($data = mysqli_fetch_assoc($result)) {
    echo json_encode($data);
} else {
    echo json_encode(["error" => "未找到資料"]);
}
mysqli_close($link);
?>
