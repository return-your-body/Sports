<?php
// 啟動會話，檢查用戶是否已登入
session_start();
// 載入資料庫連接設定
require '../db.php';

// 確認前端是否傳遞了必須的參數（醫生名稱、日期、時間）
if (isset($_POST['doctor'], $_POST['date'], $_POST['time'])) {
    $doctor = $_POST['doctor']; // 醫生名稱
    $date = $_POST['date'];     // 預約日期
    $time = $_POST['time'];     // 預約時間

    // 獲取當前用戶的帳號，用於查詢其 people_id
    $account = $_SESSION['帳號'];
    $query = "SELECT people_id FROM user JOIN people ON user.user_id = people.user_id WHERE user.account = ?";
    $stmt = mysqli_prepare($link, $query); // 使用預處理語句，防止 SQL 注入
    mysqli_stmt_bind_param($stmt, "s", $account); // 綁定參數
    mysqli_stmt_execute($stmt); // 執行查詢
    $result = mysqli_stmt_get_result($stmt); // 獲取查詢結果

    // 如果能找到對應的 people_id
    if ($row = mysqli_fetch_assoc($result)) {
        $people_id = $row['people_id']; // 獲取當前用戶的 people_id

        // 查詢對應的 doctorshift_id（醫生班表 ID）和 shifttime_id（時間段 ID）
        $query_shift = "
            SELECT ds.doctorshift_id, st.shifttime_id
            FROM doctorshift ds
            JOIN doctor d ON ds.doctor_id = d.doctor_id
            JOIN shifttime st ON st.shifttime = ?
            WHERE d.doctor = ? AND ds.date = ?";
        $stmt_shift = mysqli_prepare($link, $query_shift); // 預處理語句
        mysqli_stmt_bind_param($stmt_shift, "sss", $time, $doctor, $date); // 綁定時間、醫生名稱和日期參數
        mysqli_stmt_execute($stmt_shift); // 執行查詢
        $result_shift = mysqli_stmt_get_result($stmt_shift); // 獲取查詢結果

        // 如果找到對應的班表和時間段
        if ($shift_row = mysqli_fetch_assoc($result_shift)) {
            $doctorshift_id = $shift_row['doctorshift_id']; // 醫生班表 ID
            $shifttime_id = $shift_row['shifttime_id'];     // 時間段 ID

            // 插入預約資料到 appointment 表
            $insert_query = "
                INSERT INTO appointment (people_id, doctorshift_id, shifttime_id, note, created_at) 
                VALUES (?, ?, ?, ?, NOW())"; // 使用 NOW() 記錄預約時間
            $stmt_insert = mysqli_prepare($link, $insert_query); // 預處理語句
            $note = ""; // 添加備註，方便後續查看
            mysqli_stmt_bind_param($stmt_insert, "iiis", $people_id, $doctorshift_id, $shifttime_id, $note); // 綁定參數

            // 如果插入成功
            if (mysqli_stmt_execute($stmt_insert)) {
                echo json_encode(['success' => true]); // 回傳成功訊息
            } else {
                // 插入失敗，可能是資料庫問題
                echo json_encode(['success' => false, 'message' => '無法新增預約資料。']);
            }
        } else {
            // 未找到對應的班表或時間段
            echo json_encode(['success' => false, 'message' => '無對應的排班或時段資料。']);
        }
    } else {
        // 無法找到用戶的 people_id，可能是帳號問題
        echo json_encode(['success' => false, 'message' => '用戶資訊錯誤，請重新登入。']);
    }
} else {
    // 缺少必要的參數，回傳錯誤訊息
    echo json_encode(['success' => false, 'message' => '缺少必要的參數。']);
}
?>
