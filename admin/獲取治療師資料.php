<?php
// 開啟錯誤顯示
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 清除之前的輸出，確保 JSON 輸出正常
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// 引入資料庫連線
require '../db.php';

// 檢查資料庫連線是否正常
if (!$link) {
    die(json_encode(["status" => "error", "message" => "❌ 資料庫連線失敗：" . mysqli_connect_error()]));
}

// 檢查是否有傳入 doctor_id 參數
if ($_SERVER["REQUEST_METHOD"] !== "GET" || !isset($_GET["doctor_id"]) || empty($_GET["doctor_id"])) {
    die(json_encode(["status" => "error", "message" => "❌ 請求參數不正確，請提供 doctor_id"]));
}

// 取得 doctor_id 並轉為整數
$doctor_id = intval($_GET["doctor_id"]);

// 檢查 doctor_id 是否為有效數字
if ($doctor_id <= 0) {
    die(json_encode(["status" => "error", "message" => "❌ doctor_id 必須是有效數字"]));
}

// **測試輸出是否有接收到正確的 doctor_id**
// echo json_encode(["status" => "debug", "doctor_id" => $doctor_id]);
// exit;

// 查詢 `doctorprofile` 是否有該治療師的資料
$query = "SELECT * FROM doctorprofile WHERE doctor_id = ?";
$stmt = mysqli_prepare($link, $query);

// 檢查 SQL 語法是否正確
if (!$stmt) {
    die(json_encode(["status" => "error", "message" => "❌ SQL 語法錯誤：" . mysqli_error($link)]));
}

// 綁定參數並執行 SQL 查詢
mysqli_stmt_bind_param($stmt, "i", $doctor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 檢查 SQL 查詢是否成功
if (!$result) {
    die(json_encode(["status" => "error", "message" => "❌ SQL 執行錯誤：" . mysqli_error($link)]));
}

// 檢查是否有獲取到資料
if (mysqli_num_rows($result) > 0) {
    $doctorData = mysqli_fetch_assoc($result);
    
    // 如果圖片是 BLOB，轉換為 Base64 以便前端顯示
    if (!empty($doctorData["image"])) {
        $doctorData["image"] = base64_encode($doctorData["image"]);
    }

    echo json_encode(["status" => "success", "data" => $doctorData]);
} else {
    echo json_encode(["status" => "error", "message" => "❌ 無法獲取資料，請稍後再試"]);
}

// 釋放資源
mysqli_stmt_close($stmt);
mysqli_close($link);
?>
