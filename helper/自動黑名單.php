<?php
require '../db.php'; // 連接資料庫

header('Content-Type: application/json');

if (!isset($_POST['id']) || !isset($_POST['action'])) {
    echo json_encode(["status" => "error", "message" => "缺少必要參數"]);
    exit;
}

$id = intval($_POST['id']); // 確保 ID 為數字
$action = $_POST['action'];

$blackScore = 0;
if ($action === "請假") {
    $blackScore = 0.5;
} elseif ($action === "爽約") {
    $blackScore = 1;
} else {
    echo json_encode(["status" => "error", "message" => "無效的行為"]);
    exit;
}

// 更新 blacklist 表
$stmt = $link->prepare("UPDATE people SET black = black + ? WHERE people_id = ?");
$stmt->bind_param("di", $blackScore, $id); // 修正 `di` 為 `dd`
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "違規記錄已更新"]);
} else {
    echo json_encode(["status" => "error", "message" => "無法更新違規記錄"]]);
}
?>
