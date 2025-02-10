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

// **取得 status_id**
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

// **請假**
if ($status_name === '請假') {
    $people_stmt = $link->prepare("SELECT people_id, black FROM appointment WHERE appointment_id = ?");
    $people_stmt->bind_param("i", $appointment_id);
    $people_stmt->execute();
    $people_result = $people_stmt->get_result();
    $people_row = $people_result->fetch_assoc();
    $people_stmt->close();

    if (!$people_row) {
        echo json_encode(["error" => "無效的預約記錄"]);
        exit;
    }

    $people_id = $people_row['people_id'];
    $new_black_score = $people_row['black'] + 0.5;

    $update_people_black = $link->prepare("UPDATE people SET black = ? WHERE people_id = ?");
    $update_people_black->bind_param("di", $new_black_score, $people_id);
    $update_people_black->execute();
    $update_people_black->close();

    $insert_blacklist = $link->prepare("INSERT INTO blacklist (people_id, black_id, violation_date) VALUES (?, 1, NOW())");
    $insert_blacklist->bind_param("i", $people_id);
    $insert_blacklist->execute();
    $insert_blacklist->close();

    echo json_encode(["success" => "狀態已更新為請假"]);

} elseif ($status_name === '爽約') {
    // 自動爽約處理
    require '自動黑名單.php'; // 呼叫自動爽約處理
}
$link->close();
?>
