<?php
require '../db.php';

// 取得治療師和助手列表
$sql = "SELECT doctor_id, doctor FROM doctor";
$result = $link->query($sql);

$doctors = [];
while ($row = $result->fetch_assoc()) {
    $doctors[] = ["id" => $row["doctor_id"], "name" => $row["doctor"]];
}

// 回傳 JSON
echo json_encode($doctors);
?>
