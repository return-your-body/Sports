<?php
include "../db.php";

header('Content-Type: application/json');

// 獲取前端傳來的數據
$data = json_decode(file_get_contents("php://input"), true);
$doctor_id = $data['doctor_id'];
$date = $data['date'];

if (!$doctor_id || !$date) {
    echo json_encode(["success" => false, "message" => "缺少必要參數"]);
    exit;
}

// 執行刪除操作
$query = "DELETE FROM doctorshift WHERE doctor_id = ? AND date = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "is", $doctor_id, $date);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "刪除失敗"]);
}
?>
