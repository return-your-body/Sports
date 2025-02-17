<?php
// 啟動 session，確保能夠取得登入的使用者資訊
session_start();

// **🔹 檢查用戶是否已登入**
// 如果沒有 session 資訊，則無法預約，返回錯誤訊息
if (!isset($_SESSION["帳號"])) {
    echo json_encode(['available' => false, 'message' => '未登入，請先登入後操作。']);
    exit;
}

// **🔹 載入資料庫連接**
require '../db.php';

// **🔹 取得 `people_id`**
// 1️⃣ 先從 `user` 表透過 `account` 查找 `user_id`
$account = $_SESSION["帳號"]; // 從 session 取得使用者帳號
$query_user = "SELECT user_id FROM user WHERE account = ?";
$stmt_user = mysqli_prepare($link, $query_user);
mysqli_stmt_bind_param($stmt_user, "s", $account);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$row_user = mysqli_fetch_assoc($result_user);

// **🔹 如果查無 `user_id`，則回傳錯誤**
if (!$row_user) {
    echo json_encode(['available' => false, 'message' => '無法取得使用者資訊']);
    exit;
}

$user_id = $row_user['user_id']; // 取得 `user_id`

// 2️⃣ 再從 `people` 表透過 `user_id` 查找 `people_id`
$query_people = "SELECT people_id FROM people WHERE user_id = ?";
$stmt_people = mysqli_prepare($link, $query_people);
mysqli_stmt_bind_param($stmt_people, "i", $user_id);
mysqli_stmt_execute($stmt_people);
$result_people = mysqli_stmt_get_result($stmt_people);
$row_people = mysqli_fetch_assoc($result_people);

// **🔹 如果查無 `people_id`，則回傳錯誤**
if (!$row_people) {
    echo json_encode(['available' => false, 'message' => '無法取得對應的 people_id']);
    exit;
}

$people_id = $row_people['people_id']; // 取得 `people_id`

// **🔹 確保前端傳來必要的參數**
if (!isset($_POST['doctor'], $_POST['date'], $_POST['time'])) {
    echo json_encode(['available' => false, 'message' => '缺少必要的參數']);
    exit;
}

// **🔹 接收來自前端的預約請求**
$doctor = $_POST['doctor']; // 治療師名稱
$date = $_POST['date'];     // 預約日期 (YYYY-MM-DD)
$time = $_POST['time'];     // 預約時間 (HH:mm)

// **🔹 1️⃣ 檢查該 `people_id` 是否已在該日期預約**
// 透過 `appointment` 表與 `doctorshift` 連結，確保該人當天僅能預約一次
$check_people_query = "
    SELECT COUNT(*) as count
    FROM appointment a
    JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    WHERE a.people_id = ? AND ds.date = ?
";
$stmt_people_check = mysqli_prepare($link, $check_people_query);
mysqli_stmt_bind_param($stmt_people_check, "is", $people_id, $date);
mysqli_stmt_execute($stmt_people_check);
$result_people_check = mysqli_stmt_get_result($stmt_people_check);
$row_people_check = mysqli_fetch_assoc($result_people_check);

// **🔹 若該人已經預約過，則回傳錯誤**
if ($row_people_check['count'] > 0) {
    echo json_encode([
        'available' => false,
        'alreadyReserved' => true,
        'message' => '您今天已經預約過，無法再次預約'
    ]);
    exit;
}

// **🔹 2️⃣ 檢查該治療師的該時段是否已有預約**
// 避免同一時段內有多人同時預約
$check_doctor_query = "
    SELECT a.appointment_id 
    FROM appointment a
    JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    JOIN shifttime st ON a.shifttime_id = st.shifttime_id
    JOIN doctor d ON ds.doctor_id = d.doctor_id
    WHERE d.doctor = ? AND ds.date = ? AND st.shifttime = ?
";
$stmt_doctor = mysqli_prepare($link, $check_doctor_query);
mysqli_stmt_bind_param($stmt_doctor, "sss", $doctor, $date, $time);
mysqli_stmt_execute($stmt_doctor);
$check_doctor_result = mysqli_stmt_get_result($stmt_doctor);

// **🔹 若該時段已被預約，則回傳錯誤**
if (mysqli_num_rows($check_doctor_result) > 0) {
    echo json_encode([
        'available' => false,
        'message' => '此時段已有預約。'
    ]);
    exit;
}

// **🔹 若該時段未被預約，則回傳可用**
echo json_encode([
    'available' => true,
    'message' => '此時段可用。'
]);

?>
