<?php
require '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// **檢查請求資料是否正確**
if (!isset($data['appointment_id']) || !isset($data['status_id'])) {
    error_log("❌ 缺少參數: " . json_encode($data));
    echo json_encode(["success" => false, "error" => "缺少必要參數"]);
    exit;
}

$appointment_id = intval($data['appointment_id']);
$status_id = intval($data['status_id']);
$black_points = 0;

// **狀態邏輯**
if ($status_id == 4) { // 請假 +0.5
    $black_points = 0.5;
} elseif ($status_id == 5) { // 爽約 +1
    $black_points = 1;
}

// **更新 `appointment` 狀態**
$stmt = $link->prepare("UPDATE appointment SET status_id = ? WHERE appointment_id = ?");
$stmt->bind_param("ii", $status_id, $appointment_id);

if ($stmt->execute()) {
    // **處理違規記錄**
    if ($black_points > 0) {
        $update_black = $link->prepare("
            UPDATE people 
            SET black = black + ? 
            WHERE people_id = (SELECT people_id FROM appointment WHERE appointment_id = ?)
        ");
        $update_black->bind_param("di", $black_points, $appointment_id);
        $update_black->execute();
    }

    // **回應前端**
    $message = match ($status_id) {
        3 => "狀態已更新為報到！",
        4 => "狀態已更新為請假，已記錄違規！",
        5 => "狀態已更新為爽約，已記錄違規！",
        8 => "狀態已更新為看診中！",
        default => "狀態已成功更新！"
    };

    echo json_encode(["success" => true, "message" => $message]);
} else {
    error_log("❌ SQL 執行失敗: " . $stmt->error);
    echo json_encode(["success" => false, "error" => "更新失敗"]);
}
?>
