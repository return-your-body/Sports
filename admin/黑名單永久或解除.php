<?php
// 連接資料庫
include '../db.php';

// 確保請求方式為 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "非法請求";
    exit;
}

// 取得請求參數
$action = $_POST['action'] ?? '';
$userId = intval($_POST['userId'] ?? 0);

if ($userId <= 0) {
    echo "使用者 ID 無效";
    exit;
}

// **讀取 `settings` 設定的黑名單時長**
$blacklistDuration = 1;  // 預設 1 天
$blacklistUnit = 'DAY';   // 預設時間單位

// **從 `settings` 表獲取 `blacklist_duration` 和 `blacklist_unit`**
$setting_sql = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('default_blacklist_duration', 'default_blacklist_unit')";
$result = mysqli_query($link, $setting_sql);

while ($row = mysqli_fetch_assoc($result)) {
    if ($row['setting_key'] === 'default_blacklist_duration') {
        $blacklistDuration = intval($row['setting_value']);
    } elseif ($row['setting_key'] === 'default_blacklist_unit') {
        $blacklistUnit = $row['setting_value'];
    }
}

// **確保 `blacklistUnit` 是合法的時間單位**
$allowedUnits = ['HOUR', 'DAY', 'MONTH'];
if (!in_array($blacklistUnit, $allowedUnits)) {
    $blacklistUnit = 'DAY';  // 預設為 "DAY"
}

// **設置 SQL 的時間間隔格式**
$intervalSQL = "INTERVAL $blacklistDuration $blacklistUnit";

// **處理黑名單操作**
switch ($action) {
    case 'permanent':
        $sql = "UPDATE people SET black = 3,blacklist_start_date = NOW(), blacklist_end_date = '9999-12-31' WHERE people_id = ?";
        break;

    case 'remove':
        $sql = "UPDATE people SET black = 0, blacklist_start_date = NULL, blacklist_end_date = NULL WHERE people_id = ?";
        break;

    case 'clear_violation':
        $sql = "UPDATE people SET black = 0 WHERE people_id = ?";
        break;

    case 'add_blacklist':
        // **取得使用者的違規次數**
        $sql = "SELECT black FROM people WHERE people_id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $currentBlackCount = $row['black'] ?? 0;

        if ($currentBlackCount >= 3) {
            echo "此使用者已在黑名單內！";
            exit;
        }

        if ($currentBlackCount == 2) {
            // **更新 `people` 表，將 `black` 設為 3，並設定黑名單時間**
            $update_sql = "UPDATE people SET black = 3, blacklist_start_date = NOW(), blacklist_end_date = DATE_ADD(NOW(), $intervalSQL) WHERE people_id = ?";
            $stmt = mysqli_prepare($link, $update_sql);
            mysqli_stmt_bind_param($stmt, "i", $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // **從 `black` 表獲取管理者操作的 `black_id`**
            $black_id_sql = "SELECT black_id FROM black WHERE black = '管理者操作' LIMIT 1";
            $result = mysqli_query($link, $black_id_sql);
            $blackRow = mysqli_fetch_assoc($result);
            $black_id = $blackRow['black_id'] ?? null;

            if (!$black_id) {
                echo "錯誤：找不到管理員操作的黑名單類型";
                exit;
            }

            // **新增到 `blacklist` 表**
            $insert_sql = "INSERT INTO blacklist (people_id, black_id, violation_date) VALUES (?, ?, NOW())";
            $stmt = mysqli_prepare($link, $insert_sql);
            mysqli_stmt_bind_param($stmt, "ii", $userId, $black_id);
            if (mysqli_stmt_execute($stmt)) {
                echo "使用者已成功加入黑名單";
            } else {
                echo "錯誤：無法加入黑名單！";
            }
            mysqli_stmt_close($stmt);
        } 
        elseif ($currentBlackCount < 2) {
            $update_sql = "UPDATE people SET black = black + 1 WHERE people_id = ?";
            $stmt = mysqli_prepare($link, $update_sql);
            mysqli_stmt_bind_param($stmt, "i", $userId);
            if (mysqli_stmt_execute($stmt)) {
                echo "使用者違規次數 +1";
            } else {
                echo "錯誤：無法更新違規次數！";
            }
            mysqli_stmt_close($stmt);
        }
        exit;

    default:
        echo "無效的操作！";
        exit;
}

// **執行 SQL**
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
if (mysqli_stmt_execute($stmt)) {
    echo "操作成功！";
} else {
    echo "操作失敗：" . mysqli_error($link);
}
mysqli_stmt_close($stmt);
mysqli_close($link);
?>
