<?php
require '../db.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => '更新失敗'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log(print_r($_POST, true)); // 記錄請求數據，方便 Debug

    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $status = intval($_POST['status'] ?? 0);
    $date = $_POST['date'] ?? null;
    $time = $_POST['time'] ?? null;

    if ($appointment_id > 0) {
        // 取得目前預約的使用者 ID 和當前狀態
        $stmt = $link->prepare("SELECT people_id, status_id FROM appointment WHERE appointment_id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $stmt->bind_result($people_id, $current_status);
        $stmt->fetch();
        $stmt->close();

        if ($people_id) {
            // 若狀態為已看診(6)，鎖死選單
            if ($current_status == 6) {
                $response = ['status' => 'error', 'message' => '此預約已看診，無法變更狀態'];
                echo json_encode($response);
                exit;
            }

            if ($status == 2 && !empty($date) && !empty($time)) {
                // 更新修改的日期與時間
                $update_stmt = $link->prepare("UPDATE appointment SET status_id = ?, date = ?, time = ? WHERE appointment_id = ?");
                $update_stmt->bind_param("issi", $status, $date, $time, $appointment_id);
            } else {
                // 只更新狀態
                $update_stmt = $link->prepare("UPDATE appointment SET status_id = ? WHERE appointment_id = ?");
                $update_stmt->bind_param("ii", $status, $appointment_id);
            }

            if ($update_stmt->execute()) {
                $response = ['status' => 'success', 'message' => '狀態已更新'];
            }
            $update_stmt->close();
        }
    }
}

echo json_encode($response);
?>
