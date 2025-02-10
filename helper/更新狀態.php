<?php
require '../db.php';
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "無效的請求方式"]);
    exit;
}

$appointment_id = intval($_POST['id']);
$status_name = trim($_POST['status']);
$current_time = date('Y-m-d H:i:s');

if (!$link) {
    echo json_encode(["error" => "資料庫連接失敗"]);
    exit;
}

// 取得 status_id
$status_stmt = $link->prepare("SELECT status_id FROM status WHERE status_name = ?");
$status_stmt->bind_param("s", $status_name);
$status_stmt->execute();
$status_result = $status_stmt->get_result();
$status_row = $status_result->fetch_assoc();
$status_stmt->close();

if (!$status_row) {
    echo json_encode(["error" => "無效的狀態"]);
    exit;
}
$status_id = $status_row['status_id'];

// **處理請假與爽約**
if ($status_name === '請假' || $status_name === '爽約') {
    $black_increment = ($status_name === '請假') ? 0.5 : 1;

    // 獲取 people_id 並更新 black 欄位
    $people_stmt = $link->prepare("SELECT people_id FROM appointment WHERE appointment_id = ?");
    $people_stmt->bind_param("i", $appointment_id);
    $people_stmt->execute();
    $people_result = $people_stmt->get_result();
    $people_row = $people_result->fetch_assoc();
    $people_stmt->close();

    if ($people_row) {
        $people_id = $people_row['people_id'];
        $update_people_stmt = $link->prepare("UPDATE people SET black = black + ? WHERE people_id = ?");
        $update_people_stmt->bind_param("di", $black_increment, $people_id);
        $update_people_stmt->execute();
        $update_people_stmt->close();
    }
}

// **更新狀態**
$update_stmt = $link->prepare("UPDATE appointment SET status_id = ?, update_time = ? WHERE appointment_id = ?");
$update_stmt->bind_param("isi", $status_id, $current_time, $appointment_id);
$success = $update_stmt->execute();
$update_stmt->close();

if ($success) {
    echo json_encode(["success" => true, "message" => "狀態已更新為 $status_name"]);
} else {
    echo json_encode(["error" => "狀態更新失敗"]);
}

$link->close();
?>
