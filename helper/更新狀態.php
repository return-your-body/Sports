<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db.php';
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => '更新失敗'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $status = intval($_POST['status'] ?? 0);
    $date = $_POST['date'] ?? null;
    $time = $_POST['time'] ?? null;

    if ($appointment_id > 0) {
        $stmt = $link->prepare("SELECT people_id, status_id FROM appointment WHERE appointment_id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $stmt->bind_result($people_id, $current_status);
        $stmt->fetch();
        $stmt->close();

        if (!$people_id) {
            echo json_encode(['status' => 'error', 'message' => '無效的預約 ID']);
            exit;
        }

        if ($current_status == 6) {
            echo json_encode(['status' => 'error', 'message' => '此預約已看診，無法變更狀態']);
            exit;
        }

        if ($status == 2 && !empty($date) && !empty($time)) {
            $stmt = $link->prepare("UPDATE appointment SET status_id = ?, date = ?, time = ? WHERE appointment_id = ?");
            $stmt->bind_param("issi", $status, $date, $time, $appointment_id);
        } else {
            $stmt = $link->prepare("UPDATE appointment SET status_id = ? WHERE appointment_id = ?");
            $stmt->bind_param("ii", $status, $appointment_id);
        }

        if ($stmt->execute()) {
            if ($status == 4 || $status == 5) {
                $blacklistResponse = file_get_contents("http://localhost/自動黑名單.php?id=$people_id&action=" . ($status == 4 ? "請假" : "爽約"));
                if (!$blacklistResponse) {
                    error_log("黑名單 API 失敗: appointment_id $appointment_id");
                }
            }
            $response = ['status' => 'success', 'message' => '狀態已更新'];
        } else {
            $response['message'] = '資料庫更新失敗';
        }
        $stmt->close();
    }
}

echo json_encode($response);
?>
