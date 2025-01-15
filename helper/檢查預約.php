<?php
// 啟動會話，檢查用戶是否已登入
session_start();

// 如果用戶未登入，返回錯誤訊息
if (!isset($_SESSION["登入狀態"])) {
    echo json_encode(['success' => false, 'message' => '未登入，請先登入後操作。']);
    exit;
}

// 載入資料庫連接設定
require '../db.php';

// 確認是否接收到前端傳來的參數
if (isset($_POST['doctorShiftId'], $_POST['shiftTimeId'])) {
    $doctorShiftId = $_POST['doctorShiftId']; // 醫生班表 ID
    $shiftTimeId = $_POST['shiftTimeId'];     // 時段 ID

    // 調試輸出檢查接收到的參數
    error_log("Received doctorShiftId: $doctorShiftId, shiftTimeId: $shiftTimeId");

    // 檢查該班表和時間段是否已被預約
    $check_query = "
        SELECT COUNT(*) AS count
        FROM appointment
        WHERE doctorshift_id = ? AND shifttime_id = ?
    ";

    $stmt = $link->prepare($check_query);
    $stmt->bind_param('ii', $doctorShiftId, $shiftTimeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    // 調試輸出 SQL 查詢和結果
    error_log("SQL Query: $check_query");
    error_log("Query Result: " . json_encode($data));

    // 如果查詢結果大於 0，表示該時間段已滿
    if ($data['count'] > 0) {
        echo json_encode(['available' => false]);
    } else {
        echo json_encode(['available' => true]);
    }

    $stmt->close();
} else {
    // 缺少必要的參數
    echo json_encode(['success' => false, 'message' => '缺少必要的參數。']);
    error_log("Missing parameters: " . json_encode($_POST));
}

$link->close();
?>
