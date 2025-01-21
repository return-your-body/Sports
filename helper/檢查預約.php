<?php
// 啟動會話，檢查用戶是否已登入
session_start();

// 如果用戶未登入，返回錯誤訊息
if (!isset($_SESSION["登入狀態"])) {
    // 回傳 JSON 格式的錯誤訊息，指示未登入狀態
    echo json_encode(['available' => false, 'message' => '未登入，請先登入後操作。']);
    exit; // 終止腳本執行
}

// 載入資料庫連接設定
require '../db.php';

// 檢查是否接收到前端傳遞的必要參數（醫生名稱、日期、時間）
if (isset($_POST['doctor'], $_POST['date'], $_POST['time'])) {
    // 接收前端傳遞的參數
    $doctor = $_POST['doctor']; // 醫生名稱
    $date = $_POST['date'];     // 預約日期
    $time = $_POST['time'];     // 預約時間

    // 建立查詢語句，檢查該醫生在指定日期與時間段是否已有預約
    $check_query = "
        SELECT a.appointment_id 
        FROM appointment a
        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        JOIN shifttime st ON a.shifttime_id = st.shifttime_id
        JOIN doctor d ON ds.doctor_id = d.doctor_id
        WHERE d.doctor = ? AND ds.date = ? AND st.shifttime = ?
    ";

    // 準備查詢語句以防止 SQL 注入
    $stmt = mysqli_prepare($link, $check_query);

    // 綁定參數到查詢語句
    mysqli_stmt_bind_param($stmt, "sss", $doctor, $date, $time);

    // 執行查詢語句
    mysqli_stmt_execute($stmt);

    // 獲取查詢結果
    $check_result = mysqli_stmt_get_result($stmt);

    // 如果查詢結果中已有記錄，表示該時段已被預約
    if (mysqli_num_rows($check_result) > 0) {
        // 回傳 JSON 格式的結果，表示該時段不可用
        echo json_encode(['available' => false, 'message' => '此時段已有預約。']);
        exit; // 終止腳本執行
    }

    // 如果沒有記錄，表示該時段可用
    // 回傳 JSON 格式的結果，表示該時段可用
    echo json_encode(['available' => true, 'message' => '此時段可用。']);
} else {
    // 如果缺少必要的參數，返回錯誤訊息
    echo json_encode(['available' => false, 'message' => '缺少必要的參數']);
}
?>