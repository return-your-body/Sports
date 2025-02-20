<?php
include "../db.php";

$data = json_decode(file_get_contents("php://input"), true);
$doctor_id = $data['doctor_id'];
$date = $data['date'];
$go_time = $data['go_time'];
$off_time = $data['off_time'];

// 取得對應的 shifttime_id
$get_go_id = mysqli_query($link, "SELECT shifttime_id FROM shifttime WHERE shifttime = '$go_time'");
$get_off_id = mysqli_query($link, "SELECT shifttime_id FROM shifttime WHERE shifttime = '$off_time'");

if ($row_go = mysqli_fetch_assoc($get_go_id)) {
    $go_id = $row_go['shifttime_id'];
} else {
    echo json_encode(["success" => false, "message" => "無效的上班時間"]);
    exit;
}

if ($row_off = mysqli_fetch_assoc($get_off_id)) {
    $off_id = $row_off['shifttime_id'];
} else {
    echo json_encode(["success" => false, "message" => "無效的下班時間"]);
    exit;
}

// 更新班表
$query = "UPDATE doctorshift SET go = '$go_id', off = '$off_id' WHERE doctor_id = '$doctor_id' AND date = '$date'";

if (mysqli_query($link, $query)) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "更新失敗"]);
}
?>
