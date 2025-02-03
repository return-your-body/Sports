<?php
// 引入資料庫連接
include '../db.php';

// 確保請求方式為 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "非法請求";
    exit;
}

// 取得表單數據
$duration = isset($_POST['duration']) ? intval($_POST['duration']) : 1;
$unit = isset($_POST['unit']) ? strtoupper($_POST['unit']) : 'HOUR';
$updateExisting = isset($_POST['updateExisting']) ? intval($_POST['updateExisting']) : 0;

// 限制時間單位只能是 HOUR、DAY 或 MONTH
$allowedUnits = ['HOUR', 'DAY', 'MONTH'];
if (!in_array($unit, $allowedUnits)) {
    echo "時間單位錯誤";
    exit;
}

// 1️⃣ **更新 `settings`，影響未來進入黑名單的使用者**
$update_setting_sql = "
    INSERT INTO settings (setting_key, setting_value) VALUES 
    ('default_blacklist_duration', ?), 
    ('default_blacklist_unit', ?) 
    ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value)";
$stmt = mysqli_prepare($link, $update_setting_sql);
mysqli_stmt_bind_param($stmt, "is", $duration, $unit);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// 2️⃣ **更新現有黑名單內的使用者（如果 `updateExisting = 1`）**
if ($updateExisting) {
    // 影響所有已經進黑名單的使用者
    $sql = "
        UPDATE people
        SET blacklist_end_date = DATE_ADD(blacklist_start_date, INTERVAL $duration $unit)
        WHERE black >= 3";
    
    if (mysqli_query($link, $sql)) {
        echo "黑名單時間已更新，已在黑名單內的使用者時間已修改";
    } else {
        echo "更新失敗: " . mysqli_error($link);
    }
} else {
    echo "黑名單預設時間已更新，但未影響現有的黑名單使用者";
}

// 關閉連線
mysqli_close($link);
?>
