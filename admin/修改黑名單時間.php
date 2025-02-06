<?php
include '../db.php';

// 確保請求方式為 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "非法請求";
    exit;
}

// 取得 POST 傳送的數據
$duration = isset($_POST['duration']) ? intval($_POST['duration']) : 1;
$unit = isset($_POST['unit']) ? strtoupper($_POST['unit']) : 'HOUR';
$errorMessage = isset($_POST['errorMessage']) ? trim($_POST['errorMessage']) : '您所顯示的頁面發生錯誤，請稍後再試！';
$updateExisting = isset($_POST['updateExisting']) ? intval($_POST['updateExisting']) : 0;

// 限制時間單位只能是 HOUR、DAY 或 MONTH
$allowedUnits = ['HOUR', 'DAY', 'MONTH'];
if (!in_array($unit, $allowedUnits)) {
    echo "時間單位錯誤";
    exit;
}

// 1️⃣ **更新 `settings` 表，影響未來進入黑名單的使用者**
$update_setting_sql = "
    INSERT INTO settings (setting_key, setting_value) VALUES 
    ('default_blacklist_duration', ?), 
    ('default_blacklist_unit', ?), 
    ('error_message', ?) 
    ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value)";
$stmt = mysqli_prepare($link, $update_setting_sql);
mysqli_stmt_bind_param($stmt, "iss", $duration, $unit, $errorMessage);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// 2️⃣ **如果 `updateExisting = 1`，更新目前已在黑名單內的使用者**
if ($updateExisting) {
    $sql = "
        UPDATE people
        SET blacklist_end_date = DATE_ADD(blacklist_start_date, INTERVAL $duration $unit)
        WHERE black >= 3";
    
    if (mysqli_query($link, $sql)) {
        echo "黑名單設定已更新，已在黑名單內的使用者時間已修改";
    } else {
        echo "更新失敗: " . mysqli_error($link);
    }
} else {
    echo "黑名單預設時間已更新，但未影響現有的黑名單使用者";
}

// 關閉連線
mysqli_close($link);
?>
