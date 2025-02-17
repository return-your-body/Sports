<?php
// **🔹 啟動會話，確保可以存取登入狀態和 `people_id`**
session_start();

// **🔹 檢查用戶是否已登入**
if (!isset($_SESSION["登入狀態"])) {
    echo json_encode(['available' => false, 'message' => '未登入，請先登入後操作。']);
    exit; // 終止執行
}

// **🔹 檢查是否有 `people_id`，確保系統知道是誰要預約**
if (!isset($_SESSION['people_id'])) {
    echo json_encode(['available' => false, 'message' => '缺少使用者資訊']);
    exit; // 終止執行
}

$people_id = $_SESSION['people_id']; // 從 Session 取得 `people_id`

// **🔹 載入資料庫連接**
require '../db.php';

// **🔹 確保前端傳遞了必要的參數**
if (isset($_POST['doctor'], $_POST['date'], $_POST['time'])) {
    // **📌 取得來自前端的預約資料**
    $doctor = $_POST['doctor']; // 治療師名稱
    $date = $_POST['date'];     // 預約日期 (YYYY-MM-DD)
    $time = $_POST['time'];     // 預約時間 (HH:mm)

    // **🔹 1. 檢查該 `people_id` 是否已經在該日期預約**
    $check_user_query = "
        SELECT a.appointment_id 
        FROM appointment a
        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        WHERE a.people_id = ? AND ds.date = ?
    ";

    // **🔹 使用 `mysqli_prepare` 防止 SQL 注入**
    $stmt_user = mysqli_prepare($link, $check_user_query);
    mysqli_stmt_bind_param($stmt_user, "is", $people_id, $date);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);

    // **🔹 如果 `people_id` 已經在今天預約過，則返回 "已預約"**
    if (mysqli_num_rows($result_user) > 0) {
        echo json_encode([
            'available' => false,
            'alreadyReserved' => true,
            'message' => '此使用者今天已經預約過，無法再次預約'
        ]);
        exit; // 終止腳本
    }

    // **🔹 2. 檢查該治療師的該時段是否已有預約**
    $check_query = "
        SELECT a.appointment_id 
        FROM appointment a
        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        JOIN shifttime st ON a.shifttime_id = st.shifttime_id
        JOIN doctor d ON ds.doctor_id = d.doctor_id
        WHERE d.doctor = ? AND ds.date = ? AND st.shifttime = ?
    ";

    // **🔹 使用 `mysqli_prepare` 防止 SQL 注入**
    $stmt = mysqli_prepare($link, $check_query);
    mysqli_stmt_bind_param($stmt, "sss", $doctor, $date, $time);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);

    // **🔹 如果該時段已被預約，則回傳錯誤**
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode([
            'available' => false,
            'message' => '此時段已有預約。'
        ]);
        exit;
    }

    // **🔹 如果該時段未被預約，則回傳可用**
    echo json_encode([
        'available' => true,
        'message' => '此時段可用。'
    ]);
} else {
    // **🔹 如果前端傳來的資料不完整，返回錯誤**
    echo json_encode([
        'available' => false,
        'message' => '缺少必要的參數'
    ]);
}
?>
