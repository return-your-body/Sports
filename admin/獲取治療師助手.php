<?php
require '../db.php';

header('Content-Type: application/json');

$query = "SELECT doctor_id, doctor FROM doctor";
$result = mysqli_query($link, $query);

if (!$result) {
    echo json_encode(["error" => mysqli_error($link)]);
    exit;
}

$doctors = [];
while ($row = mysqli_fetch_assoc($result)) {
    $doctors[] = $row;
}

echo json_encode($doctors);
?>
