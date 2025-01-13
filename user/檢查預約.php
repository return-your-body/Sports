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
if (isset($_POST['doctor'], $_POST['date'], $_POST['time'])) {
    $doctor = $_POST['doctor']; // 醫生名稱
    $date = $_POST['date'];     // 預約日期
    $time = $_POST['time'];     // 預約時間

    // 檢查該醫生、日期和時間段是否已被預約
    $check_query = "
        SELECT a.appointment_id 
        FROM appointment a
        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        JOIN shifttime st ON a.shifttime_id = st.shifttime_id
        JOIN doctor d ON ds.doctor_id = d.doctor_id
        WHERE d.doctor = ? AND ds.date = ? AND st.shifttime = ?
    ";
    $stmt = mysqli_prepare($link, $check_query);
    mysqli_stmt_bind_param($stmt, "sss", $doctor, $date, $time);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);

    // 如果資料庫中已有記錄，返回錯誤訊息
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['success' => false, 'message' => '此時段已有預約，請重新嘗試。']);
        exit;
    }

    // 如果該時段無預約，插入預約資料
    $insert_query = "
        INSERT INTO appointment (people_id, doctorshift_id, shifttime_id, created_at) 
        VALUES (
            (SELECT people_id FROM user JOIN people ON user.user_id = people.user_id WHERE user.account = ?),
            (SELECT ds.doctorshift_id FROM doctorshift ds JOIN doctor d ON ds.doctor_id = d.doctor_id WHERE d.doctor = ? AND ds.date = ?),
            (SELECT st.shifttime_id FROM shifttime st WHERE st.shifttime = ?),
            NOW()
        )
    ";
    $stmt_insert = mysqli_prepare($link, $insert_query);
    mysqli_stmt_bind_param($stmt_insert, "ssss", $_SESSION['帳號'], $doctor, $date, $time);

    // 執行插入操作
    if (mysqli_stmt_execute($stmt_insert)) {
        // 插入成功返回成功訊息
        echo json_encode(['success' => true, 'message' => '預約成功！']);
    } else {
        // 插入失敗返回錯誤訊息
        echo json_encode(['success' => false, 'message' => '無法完成預約，請稍後再試。']);
    }
} else {
    // 如果缺少必要的參數，返回錯誤訊息
    echo json_encode(['success' => false, 'message' => '缺少必要的參數']);
}
?>