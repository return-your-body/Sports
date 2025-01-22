<?php
// 顯示 PHP 錯誤（僅開發環境使用，正式環境請移除）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 引入資料庫連線
require '../db.php';

// 檢查資料庫連線
if (!$link) {
    echo "<script>alert('資料庫連線失敗：" . mysqli_connect_error() . "');</script>";
    exit;
}

// 確認提交方法是否為 POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 獲取表單數據
    $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
    $item_ids = isset($_POST['item_ids']) ? $_POST['item_ids'] : [];

    // 檢查表單數據是否完整
    if ($appointment_id === 0 || empty($item_ids)) {
        echo "<script>alert('請確認已填寫預約 ID 並選擇診療項目！');</script>";
        exit;
    }

    // 檢查 `medicalrecord` 是否已存在該 `appointment_id`
    $check_query = "SELECT * FROM medicalrecord WHERE appointment_id = ?";
    $check_stmt = mysqli_prepare($link, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $appointment_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('此預約 ID 的診療紀錄已存在，無法重複新增！');</script>";
        exit;
    }

    // 插入每個選中的診療項目到資料表
    $insert_query = "INSERT INTO medicalrecord (appointment_id, item_id, created_at) VALUES (?, ?, NOW())";
    $insert_stmt = mysqli_prepare($link, $insert_query);

    foreach ($item_ids as $item_id) {
        $item_id = intval($item_id); // 確保 item_id 是整數
        mysqli_stmt_bind_param($insert_stmt, "ii", $appointment_id, $item_id);
        if (!mysqli_stmt_execute($insert_stmt)) {
            echo "<script>alert('新增診療紀錄失敗：" . mysqli_error($link) . "');</script>";
            exit;
        }
    }

    // 新增成功後返回提示
    echo "<script>alert('診療紀錄新增成功！');</script>";
    echo "<script>window.location.href = 'd_medical.php?id={$appointment_id}';</script>";
    exit;
} else {
    echo "<script>alert('無效的請求方式！');</script>";
    exit;
}
