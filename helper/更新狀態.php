<?php
include 'db_connect.php'; // 連接資料庫

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // 限制允許的狀態
    $valid_statuses = ['預約', '修改', '報到', '請假', '爽約'];
    if (!in_array($status, $valid_statuses)) {
        die("錯誤：無效的狀態");
    }

    // 更新資料庫
    $sql = "UPDATE appointment SET status_name = ? WHERE id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        echo "狀態更新成功";
    } else {
        echo "更新失敗：" . $stmt->error;
    }

    $stmt->close();
    $link->close();
}
?>
