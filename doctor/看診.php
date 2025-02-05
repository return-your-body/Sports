<?php
require '../db.php';

if (!$link) {
    echo "<script>alert('資料庫連線失敗：" . mysqli_connect_error() . "');</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
    $item_ids = isset($_POST['item_ids']) ? $_POST['item_ids'] : [];

    if ($appointment_id === 0 || empty($item_ids)) {
        echo "<script>alert('請確認已填寫預約 ID 並選擇診療項目！');</script>";
        exit;
    }

    // 檢查是否已存在該 `appointment_id` 的紀錄
    $check_query = "SELECT * FROM medicalrecord WHERE appointment_id = ?";
    $check_stmt = $link->prepare($check_query);
    $check_stmt->bind_param("i", $appointment_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('此預約 ID 的診療紀錄已存在，無法重複新增！');</script>";
        exit;
    }

    // 插入診療紀錄
    $insert_query = "INSERT INTO medicalrecord (appointment_id, item_id, created_at) VALUES (?, ?, NOW())";
    $insert_stmt = $link->prepare($insert_query);

    foreach ($item_ids as $item_id) {
        $item_id = intval($item_id);
        $insert_stmt->bind_param("ii", $appointment_id, $item_id);
        if (!$insert_stmt->execute()) {
            echo "<script>alert('新增診療紀錄失敗：" . $link->error . "');</script>";
            exit;
        }
    }

    echo "<script>alert('診療紀錄新增成功！');</script>";
    echo "<script>window.location.href = 'd_medical.php?id={$appointment_id}';</script>";
    exit;
} else {
    echo "<script>alert('無效的請求方式！');</script>";
    exit;
}
?>
