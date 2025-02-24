<?php
require '../db.php';

$action = $_GET['action'] ?? '';

if ($action === 'fetch_appointment') {
    $appointment_id = $_GET['appointment_id'];
    $sql = "SELECT a.appointment_id, p.name AS user_name, d.doctor_id
            FROM appointment a
            JOIN people p ON a.people_id = p.people_id
            JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
            JOIN doctor d ON ds.doctor_id = d.doctor_id
            WHERE a.appointment_id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        echo json_encode(["success" => true, "data" => $data]);
    } else {
        echo json_encode(["success" => false, "message" => "找不到該預約資料"]);
    }
    exit;
}

if ($action === 'fetch_doctors') {
    // 只篩選治療師
    $sql = "SELECT doctor_id, doctor AS name 
            FROM doctor 
            JOIN user ON doctor.user_id = user.user_id 
            WHERE user.grade_id = 2";

    $result = $link->query($sql);
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }

    // 回傳 JSON
    if (!empty($doctors)) {
        echo json_encode(["success" => true, "data" => $doctors]);
    } else {
        echo json_encode(["success" => false, "message" => "沒有可用的治療師"]);
    }
    exit;
}



if ($action === 'fetch_dates') {
    // 取得該醫生有班的日期
    $doctor_id = $_GET['doctor_id'];
    $sql = "SELECT DISTINCT date FROM doctorshift WHERE doctor_id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dates = [];
    while ($row = $result->fetch_assoc()) {
        $dates[] = $row['date'];
    }
    echo json_encode($dates);
    exit;
}

if ($action === 'fetch_times') {
    $doctor_id = $_GET['doctor_id'];
    $date = $_GET['date'];

    // 取得該治療師當天的班表 (go ~ off)
    $sql = "SELECT go, off FROM doctorshift WHERE doctor_id = ? AND date = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("is", $doctor_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $shift = $result->fetch_assoc();

    if (!$shift) {
        echo json_encode([]); // 沒有找到該治療師的班表
        exit;
    }

    $go_time = $shift['go'];
    $off_time = $shift['off'];

    // 取得班表範圍內的所有時段
    $sql = "SELECT shifttime FROM shifttime WHERE shifttime_id BETWEEN ? AND ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("ii", $go_time, $off_time);
    $stmt->execute();
    $result = $stmt->get_result();

    $all_times = [];
    while ($row = $result->fetch_assoc()) {
        $all_times[] = $row['shifttime'];
    }

    // 取得已被預約的時段
    $sql = "SELECT st.shifttime FROM appointment a
            JOIN shifttime st ON a.shifttime_id = st.shifttime_id
            JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
            WHERE ds.doctor_id = ? AND ds.date = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("is", $doctor_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $booked_times = [];
    while ($row = $result->fetch_assoc()) {
        $booked_times[] = $row['shifttime'];
    }

    // 計算最終可預約時段 (排除已預約)
    $available_times = array_diff($all_times, $booked_times);

    echo json_encode(array_values($available_times)); // 回傳 JSON
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require '../db.php';

    $postData = file_get_contents("php://input");
    error_log("完整收到的 POST JSON: " . $postData); // 紀錄 JSON 內容
    $jsonData = json_decode($postData, true);

    if (!$jsonData) {
        echo json_encode(["success" => false, "message" => "❌ JSON 解析失敗，請確認格式"]);
        exit;
    }

    $appointment_id = $jsonData['appointment_id'] ?? null;
    $doctor_id = $jsonData['doctor_id'] ?? null;
    $date = isset($jsonData['date']) ? date('Y-m-d', strtotime($jsonData['date'])) : null;
    $time = $jsonData['time'] ?? null;

    error_log("解析後的數據: appointment_id={$appointment_id}, doctor_id={$doctor_id}, date={$date}, time={$time}");

    if (!$appointment_id || !$doctor_id || !$date || !$time) {
        echo json_encode([
            "success" => false,
            "message" => "❌ 參數錯誤，請檢查發送的數據！",
            "received_data" => $jsonData
        ]);
        exit;
    }
}


echo json_encode(["success" => false, "message" => "無效請求"]);
?>