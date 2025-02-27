<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
include '../db.php';

// 檢查是否登入
if (!isset($_SESSION["帳號"])) {
    echo json_encode(["error" => "未登入"]);
    exit;
}

$account = $_SESSION["帳號"];

// 取得 user_id
$sql = "SELECT user_id FROM user WHERE account = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("s", $account);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(["error" => "找不到使用者"]);
    exit;
}

$user_id = $user["user_id"];

// 取得 doctor_id
$sql = "SELECT doctor_id FROM doctor WHERE user_id = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    echo json_encode(["error" => "找不到對應的醫生/助手"]);
    exit;
}

$doctor_id = $doctor["doctor_id"];
$today = date('Y-m-d');

// 取得今天的打卡紀錄
$sql = "SELECT * FROM attendance WHERE doctor_id = ? AND work_date = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("is", $doctor_id, $today);
$stmt->execute();
$result = $stmt->get_result();
$attendance = $result->fetch_assoc();

if (!$attendance) {
    echo json_encode(["error" => "請先打上班卡"]);
    exit;
} elseif ($attendance['clock_out'] !== null) {
    echo json_encode(["message" => "已經打過下班卡"]);
    exit;
} else {
    // 確保 `attendance_id` 存在
    if (!isset($attendance['attendance_id'])) {
        echo json_encode(["error" => "找不到打卡記錄"]);
        exit;
    }

    // 更新下班時間
    $sql = "UPDATE attendance SET clock_out = NOW(), status = 'off' WHERE attendance_id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("i", $attendance['attendance_id']);
    
    if ($stmt->execute()) {
        // 計算總工時
        $clockIn = strtotime($attendance['clock_in']);
        $clockOut = time();
        $workDuration = gmdate("H 小時 i 分", $clockOut - $clockIn);

        echo json_encode(["message" => "下班打卡成功，今日工時: $workDuration"]);
    } else {
        echo json_encode(["error" => "下班打卡失敗: " . $stmt->error]);
    }
}
?>
