<?php
require '../db.php';
header('Content-Type: application/json');

// 啟用錯誤報告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 解析 JSON 請求
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['appointment_id']) || !isset($data['status_id'])) {
    echo json_encode(["success" => false, "error" => "缺少必要參數"]);
    exit;
}

$appointment_id = intval($data['appointment_id']);
$status_id = intval($data['status_id']);
$black_points = 0;
$black_id = null;  // 違規類別 ID

// 設定違規分數與違規類別 ID
if ($status_id == 4) { // 請假 +0.5
    $black_points = 0.5;
    $black_id = 1; // 假設 1 代表請假違規
} elseif ($status_id == 5) { // 爽約 +1
    $black_points = 1;
    $black_id = 2; // 假設 2 代表爽約違規
}

// 確保資料庫連線
if (!$link) {
    echo json_encode(["success" => false, "error" => "資料庫連線失敗"]);
    exit;
}

// 取得 people_id
$query = "SELECT people_id FROM appointment WHERE appointment_id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$people_id = ($row = $result->fetch_assoc()) ? $row['people_id'] : null;
$stmt->close();

if (!$people_id) {
    echo json_encode(["success" => false, "error" => "無效的預約 ID"]);
    exit;
}

// 更新 appointment 狀態
$stmt = $link->prepare("UPDATE appointment SET status_id = ? WHERE appointment_id = ?");
$stmt->bind_param("ii", $status_id, $appointment_id);

if ($stmt->execute()) {
    // 違規處理
    if ($black_points > 0) {
        // 更新 people.black
        $update_black = $link->prepare("UPDATE people SET black = black + ? WHERE people_id = ?");
        $update_black->bind_param("di", $black_points, $people_id);
        $update_black->execute();

        // 新增違規紀錄到 blacklist
        $insert_blacklist = $link->prepare("INSERT INTO blacklist (people_id, black_id, violation_date) VALUES (?, ?, NOW())");
        $insert_blacklist->bind_param("ii", $people_id, $black_id);
        $insert_blacklist->execute();
    }

    // 設定回應訊息
    $messages = [
        3 => "狀態已更新為報到！",
        4 => "狀態已更新為請假，已記錄違規！",
        5 => "狀態已更新為爽約，已記錄違規！",
        8 => "狀態已更新為看診中！",
        6 => "已看診，無法變更狀態"
    ];

    echo json_encode(["success" => true, "message" => $messages[$status_id] ?? "狀態已成功更新！"]);
} else {
    echo json_encode(["success" => false, "error" => "更新失敗"]);
}
?>
