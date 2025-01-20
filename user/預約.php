<?php
// 啟動會話，檢查用戶是否已登入
session_start();
// 載入資料庫連接設定
require '../db.php';

// 檢查前端是否傳遞了必要的參數，包括醫生名稱 (doctor)、日期 (date)、時間 (time) 和備註 (note)
if (isset($_POST['doctor'], $_POST['date'], $_POST['time'], $_POST['note'])) {
    // 從 POST 請求中接收參數並存入對應變數
    $doctor = $_POST['doctor']; // 醫生名稱
    $date = $_POST['date'];     // 預約日期
    $time = $_POST['time'];     // 預約時間
    $note = $_POST['note'];     // 備註內容

    // 從當前使用者的 session 中獲取帳號資訊
    $account = $_SESSION['帳號'];

    // 查詢當前使用者的 `people_id`
    $query = "SELECT people_id FROM user JOIN people ON user.user_id = people.user_id WHERE user.account = ?";
    $stmt = mysqli_prepare($link, $query); // 使用預處理語句以防止 SQL 注入
    mysqli_stmt_bind_param($stmt, "s", $account); // 綁定查詢參數
    mysqli_stmt_execute($stmt); // 執行查詢
    $result = mysqli_stmt_get_result($stmt); // 獲取查詢結果

    // 如果能找到對應的 `people_id`
    if ($row = mysqli_fetch_assoc($result)) {
        $people_id = $row['people_id']; // 獲取使用者的 `people_id`

        // 查詢醫生的班表 (`doctorshift_id`) 和對應的時間段 (`shifttime_id`)
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

            // 插入預約資料到 `appointment` 資料表
            $insert_query = "
                INSERT INTO appointment (people_id, doctorshift_id, shifttime_id, note, status_id, created_at) 
                VALUES (?, ?, ?, ?, 1, NOW())"; // `status_id` 預設為 1（表示預約），`created_at` 為當前時間
            $stmt_insert = mysqli_prepare($link, $insert_query); // 預處理語句
            mysqli_stmt_bind_param($stmt_insert, "iiis", $people_id, $doctorshift_id, $shifttime_id, $note); // 綁定參數

            // 執行插入操作
            if (mysqli_stmt_execute($stmt_insert)) {
                // 插入成功，回傳 JSON 格式的成功訊息給前端
                echo json_encode(['success' => true, 'message' => '預約成功！']);
            } else {
                // 插入失敗，回傳錯誤訊息
                echo json_encode(['success' => false, 'message' => '無法新增預約資料。']);
            }
        } else {
            // 找不到對應的班表或時間段，回傳錯誤訊息
            echo json_encode(['success' => false, 'message' => '無對應的排班或時段資料。']);
        }
    } else {
        // 無法找到對應的使用者資訊，回傳錯誤訊息
        echo json_encode(['success' => false, 'message' => '用戶資訊錯誤，請重新登入。']);
    }
} else {
    // 缺少必要的參數，回傳錯誤訊息
    echo json_encode(['success' => false, 'message' => '缺少必要的參數。']);
}

?>