<?php
// 連接資料庫
include '../db.php';

// 確保請求方式為 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "非法請求";
    exit;
}

$action = $_POST['action'] ?? '';
$userId = intval($_POST['userId'] ?? 0);

if ($userId <= 0) {
    echo "使用者 ID 無效";
    exit;
}

// **設為永久黑名單**
if ($action === 'permanent') {
    $sql = "UPDATE people SET blacklist_start_date = NOW(), blacklist_end_date = '9999-12-31' WHERE people_id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    if (mysqli_stmt_execute($stmt)) {
        echo "已設為永久黑名單";
    } else {
        echo "設置失敗：" . mysqli_error($link);
    }
    mysqli_stmt_close($stmt);
}

// **解除黑名單**
elseif ($action === 'remove') {
    $sql = "UPDATE people SET black = 0, blacklist_start_date = NULL, blacklist_end_date = NULL WHERE people_id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    if (mysqli_stmt_execute($stmt)) {
        echo "已解除黑名單";
    } else {
        echo "解除失敗：" . mysqli_error($link);
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($link);
?>
