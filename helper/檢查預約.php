<?php
// **🔹 啟動會話**
session_start();

// **🔹 檢查是否已登入**
if (!isset($_SESSION["登入狀態"])) {
    echo json_encode(['available' => false, 'message' => '未登入，請先登入後操作。']);
    exit;
}

// **🔹 檢查 `people_id` 是否存在**
if (!isset($_SESSION['people_id'])) {
    echo json_encode(['available' => false, 'message' => '缺少使用者資訊']);
    exit;
}

$people_id = $_SESSION['people_id']; // 從 Session 取得 `people_id`

// **🔹 載入資料庫連接**
require '../db.php';

// **🔹 確保前端傳遞了必要的參數**
if (!isset($_POST['doctor'], $_POST['date'], $_POST['time'])) {
    echo json_encode(['available' => false, 'message' => '缺少必要的參數']);
    exit;
}

$doctor = $_POST['doctor']; // 醫生名稱
$date = $_POST['date'];     // 預約日期 (YYYY-MM-DD)
$time = $_POST['time'];     // 預約時間 (HH:mm)

// **🔹 1️⃣ 檢查該 `people_id` 是否已經在該日期預約**
// $check_user_query = "
//     SELECT a.appointment_id 
//     FROM appointment a
//     JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
//     WHERE a.people_id = ? AND ds.date = ?
// ";

// $stmt_user = mysqli_prepare($link, $check_user_query);
// mysqli_stmt_bind_param($stmt_user, "is", $people_id, $date);
// mysqli_stmt_execute($stmt_user);
// $result_user = mysqli_stmt_get_result($stmt_user);

// // **🔹 如果該 `people_id` 已經在該日期預約，則回傳錯誤**
// if (mysqli_num_rows($result_user) > 0) {
//     echo json_encode([
//         'available' => false,
//         'alreadyReserved' => true,
//         'message' => '您今天已經預約過，無法再次預約'
//     ]);
//     exit;
// }

// **🔹 2️⃣ 檢查該醫生該時段是否已有預約**
$check_query = "
    SELECT a.appointment_id, a.status_id 
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

$allowBooking = true; // 預設允許預約

while ($row_doctor = mysqli_fetch_assoc($check_result)) {
    if ($row_doctor['status_id'] != 4) { // **狀態不是 "請假"**
        $allowBooking = false;
        break;
    }
}

// **🔹 若該時段已被預約且狀態不是 "請假"，則回傳錯誤**
if (!$allowBooking) {
    echo json_encode([
        'available' => false,
        'message' => '此時段已有預約。'
    ]);
    exit;
}

// **🔹 若該時段為 "請假"，則允許其他人預約**
echo json_encode([
    'available' => true,
    'message' => '此時段可用。'
]);
?>