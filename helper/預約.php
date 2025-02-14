<?php
// 啟動會話，檢查用戶是否已登入
session_start();

// 載入資料庫連接設定
require '../db.php';


// 檢查前端是否傳遞了必要的參數，包括治療師名稱 (doctor)、日期 (date)、時間 (time)、備註 (note)、以及使用者的 people_id
if (isset($_POST['doctor'], $_POST['date'], $_POST['time'], $_POST['note'], $_SESSION['people_id'])) {
    // 從 POST 請求中接收參數並存入對應變數
    $doctor = $_POST['doctor']; // 治療師名稱
    $date = $_POST['date'];     // 預約日期
    $time = $_POST['time'];     // 預約時間
    $note = $_POST['note'];     // 備註內容
    $people_id = $_SESSION['people_id']; // 將接收到的 people_id 轉換為整數，確保安全性

    // **調試步驟 1：檢查接收的參數是否正確**
    error_log("收到的參數: " . json_encode($_POST));

    // 查詢治療師的班表 (`doctorshift_id`) 和對應的時間段 (`shifttime_id`)
    $query_shift = "
        SELECT ds.doctorshift_id, st.shifttime_id
        FROM doctorshift ds
        JOIN doctor d ON ds.doctor_id = d.doctor_id
        JOIN shifttime st ON st.shifttime = ?
        WHERE d.doctor = ? AND ds.date = ?";

    // 預處理 SQL 查詢，防止 SQL 注入攻擊
    $stmt_shift = mysqli_prepare($link, $query_shift);

    // 綁定查詢參數：時間段、治療師名稱和日期
    mysqli_stmt_bind_param($stmt_shift, "sss", $time, $doctor, $date);

    // 執行查詢以獲取班表及時間段
    mysqli_stmt_execute($stmt_shift);

    // 獲取查詢結果
    $result_shift = mysqli_stmt_get_result($stmt_shift);

    // **調試步驟 2：檢查 SQL 執行結果**
    if (!$result_shift) {
        error_log("查詢班表 SQL 錯誤: " . mysqli_error($link));
        echo json_encode(['success' => false, 'message' => '查詢班表時發生錯誤。', 'error' => mysqli_error($link)]);
        exit;
    }

    // 檢查是否找到對應的班表和時間段
    if ($shift_row = mysqli_fetch_assoc($result_shift)) {
        $doctorshift_id = $shift_row['doctorshift_id']; // 治療師班表的 ID
        $shifttime_id = $shift_row['shifttime_id'];     // 時間段的 ID

        // **調試步驟 3：檢查查詢到的班表和時間段**
        error_log("找到的班表: doctorshift_id = $doctorshift_id, shifttime_id = $shifttime_id");

        // 插入預約資料到 `appointment` 資料表
        $insert_query = "
            INSERT INTO appointment (people_id, doctorshift_id, shifttime_id, note, status_id, created_at) 
            VALUES (?, ?, ?, ?, 1, NOW())"; // status_id 預設為 1（表示預約），created_at 為當前時間

        // 預處理插入語句
        $stmt_insert = mysqli_prepare($link, $insert_query);

        // 綁定插入參數：people_id、doctorshift_id、shifttime_id 和備註
        mysqli_stmt_bind_param($stmt_insert, "iiis", $people_id, $doctorshift_id, $shifttime_id, $note);

        // 執行插入操作
        if (mysqli_stmt_execute($stmt_insert)) {
            // 插入成功，返回 JSON 格式的成功訊息給前端
            echo json_encode(['success' => true, 'message' => '預約成功！']);
        } else {
            // 插入失敗，返回錯誤訊息
            $error_message = mysqli_error($link); // 獲取資料庫錯誤訊息
            error_log("插入預約失敗: " . $error_message);
            echo json_encode(['success' => false, 'message' => '無法新增預約資料。', 'error' => $error_message]);
        }

    } else {
        // 如果查詢不到對應的班表或時間段，返回錯誤訊息
        error_log("無對應的班表或時間段: doctor = $doctor, date = $date, time = $time");
        echo json_encode(['success' => false, 'message' => '無對應的排班或時段資料。']);
    }
} else {
    // 如果缺少必要的參數，返回錯誤訊息
    error_log("缺少必要的參數: " . json_encode($_POST));
    echo json_encode(['success' => false, 'message' => '缺少必要的參數。']);
}

?>