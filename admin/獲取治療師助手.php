<?php
require '../db.php';
$sql = "SELECT doctor_id, doctor FROM doctor";
$result = mysqli_query($link, $sql);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
	if (!empty($row['doctor_id']) && !empty($row['doctor'])) {
		$data[] = $row;
	}
}
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
