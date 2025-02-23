<?php
session_start();
require '../db.php'; // 引入資料庫連線

if (!isset($_SESSION['帳號']) || !isset($_POST['appointment_id'])) {
    echo json_encode(["status" => "error", "message" => "未登入或缺少參數"]);
    exit;
}

$帳號 = $_SESSION['帳號'];
$appointment_id = intval($_POST['appointment_id']);
$violation_type = 1; // `black` 資料表中 `black_id = 1` 表示請假

// 取得取消限制時間 (從 settings 表)
$query_cancel_settings = "
    SELECT 
        (SELECT setting_value FROM settings WHERE setting_key = 'cancel_limit_duration') AS cancel_duration,
        (SELECT setting_value FROM settings WHERE setting_key = 'cancel_limit_unit') AS cancel_unit
";
$result_cancel_settings = mysqli_query($link, $query_cancel_settings);
$cancel_settings = mysqli_fetch_assoc($result_cancel_settings);

$cancel_duration = intval($cancel_settings['cancel_duration']);
$cancel_unit = $cancel_settings['cancel_unit'];

// 取得該預約的 people_id 和 預約時間
$query_appointment = "
    SELECT a.people_id, ds.date AS appointment_date, st.shifttime 
    FROM appointment a
    JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    JOIN shifttime st ON a.shifttime_id = st.shifttime_id
    JOIN user u ON a.people_id = (SELECT people_id FROM people WHERE user_id = (SELECT user_id FROM user WHERE account = ?))
    WHERE a.appointment_id = ?
";
$stmt = mysqli_prepare($link, $query_appointment);
mysqli_stmt_bind_param($stmt, "si", $帳號, $appointment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $people_id = $row['people_id'];
    $appointment_datetime = $row['appointment_date'] . ' ' . $row['shifttime'];

    // 計算允許的最晚取消時間
    $cancel_deadline = date('Y-m-d H:i:s', strtotime("$appointment_datetime -$cancel_duration $cancel_unit"));

    // 取得當前時間
    $current_time = date('Y-m-d H:i:s');

    // 檢查是否超過可取消時間
    if ($current_time > $cancel_deadline) {
        echo json_encode(["status" => "error", "message" => "已超過可取消的時間，無法取消預約"]);
        exit;
    }

    // 取得當前違規次數
    $query_people = "SELECT COALESCE(black, 0) AS black_count FROM people WHERE people_id = ?";
    $stmt1 = mysqli_prepare($link, $query_people);
    mysqli_stmt_bind_param($stmt1, "i", $people_id);
    mysqli_stmt_execute($stmt1);
    $result1 = mysqli_stmt_get_result($stmt1);
    $people_data = mysqli_fetch_assoc($result1);
    $current_black_count = $people_data['black_count'] + 0.5; // 增加 0.5 違規次數

    // 1. 更新 appointment 狀態為請假 (status_id = 4)
    $update_appointment = "UPDATE appointment SET status_id = 4 WHERE appointment_id = ?";
    $stmt2 = mysqli_prepare($link, $update_appointment);
    mysqli_stmt_bind_param($stmt2, "i", $appointment_id);
    mysqli_stmt_execute($stmt2);

    // 2. 更新 people 違規紀錄 +0.5
    $update_people = "UPDATE people SET black = ? WHERE people_id = ?";
    $stmt3 = mysqli_prepare($link, $update_people);
    mysqli_stmt_bind_param($stmt3, "di", $current_black_count, $people_id);
    mysqli_stmt_execute($stmt3);

    // 3. 插入 blacklist 紀錄
    $insert_blacklist = "INSERT INTO blacklist (people_id, black_id, violation_date) VALUES (?, ?, NOW())";
    $stmt4 = mysqli_prepare($link, $insert_blacklist);
    mysqli_stmt_bind_param($stmt4, "ii", $people_id, $violation_type);
    mysqli_stmt_execute($stmt4);

    // 4. 若違規超過 3，則開始黑名單計時
    if ($current_black_count >= 3) {
        // 取得黑名單時間設定
        $query_blacklist_settings = "
            SELECT 
                (SELECT setting_value FROM settings WHERE setting_key = 'default_blacklist_duration') AS blacklist_duration,
                (SELECT setting_value FROM settings WHERE setting_key = 'default_blacklist_unit') AS blacklist_unit
        ";
        $result_blacklist_settings = mysqli_query($link, $query_blacklist_settings);
        $blacklist_settings = mysqli_fetch_assoc($result_blacklist_settings);

        $blacklist_duration = intval($blacklist_settings['blacklist_duration']);
        $blacklist_unit = $blacklist_settings['blacklist_unit'];

        // 計算黑名單結束時間
        $blacklist_end_time = date('Y-m-d H:i:s', strtotime("+$blacklist_duration $blacklist_unit"));

        // 更新 people 表，設定黑名單時間
        $update_blacklist_time = "UPDATE people SET blacklist_start_date = NOW(), blacklist_end_date = ? WHERE people_id = ?";
        $stmt5 = mysqli_prepare($link, $update_blacklist_time);
        mysqli_stmt_bind_param($stmt5, "si", $blacklist_end_time, $people_id);
        mysqli_stmt_execute($stmt5);
    }

    echo json_encode(["status" => "success", "message" => "預約已取消，並記錄違規"]);
} else {
    echo json_encode(["status" => "error", "message" => "找不到對應的預約"]);
}
?>
