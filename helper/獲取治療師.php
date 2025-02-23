<?php
require '../db.php';

$sql = "SELECT d.doctor_id, u.account AS doctor_name
        FROM doctor d
        JOIN user u ON d.user_id = u.user_id
        WHERE u.grade_id = 2";

$result = $link->query($sql);
$therapists = [];

while ($row = $result->fetch_assoc()) {
    $therapists[] = $row;
}

echo json_encode($therapists);
$link->close();
?>
