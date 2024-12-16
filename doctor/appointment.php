<?php
session_start();
include "../db.php"; // 引入資料庫連接

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 接收 POST 資料
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $gender = mysqli_real_escape_string($link, $_POST['gender']);
    $phone = mysqli_real_escape_string($link, $_POST['phone']);
    $date = mysqli_real_escape_string($link, $_POST['date']);
    $time = mysqli_real_escape_string($link, $_POST['time']);
    $doctor_id = mysqli_real_escape_string($link, $_POST['doctor']);
    $note = mysqli_real_escape_string($link, $_POST['note']);

    // 驗證必填欄位
    if (empty($name) || empty($gender) || empty($phone) || empty($date) || empty($time) || empty($doctor_id)) {
        die("所有欄位都是必填的，請確認後再提交！");
    }

    // 查詢時間 ID (shifttime 表)
    $time_query = "SELECT shifttime_id FROM shifttime WHERE shifttime = '$time'";
    $time_result = mysqli_query($link, $time_query);
    if ($time_row = mysqli_fetch_assoc($time_result)) {
        $shifttime_id = $time_row['shifttime_id'];
    } else {
        die("無效的預約時間！");
    }

    // 插入到 appointment 表
    $sql = "INSERT INTO appointment (people_id, appointment_date, shifttime_id, doctor_id, note) 
            VALUES (
                (SELECT people_id FROM people WHERE name = '$name' LIMIT 1), 
                '$date', 
                '$shifttime_id', 
                '$doctor_id', 
                '$note'
            )";

    if (mysqli_query($link, $sql)) {
        echo "<p>預約成功！</p>";
    } else {
        echo "錯誤: " . mysqli_error($link);
    }

    mysqli_close($link); // 關閉資料庫連線
}
?>
