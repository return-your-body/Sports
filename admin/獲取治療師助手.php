<?php
header("Content-Type: application/json; charset=UTF-8");
require '../db.php';

// 取得所有治療師 & 助手
$sql = "SELECT doctor_id AS id, doctor AS name FROM doctor";
$result = $link->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
