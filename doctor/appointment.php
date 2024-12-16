<?php
session_start(); // 啟用 Session
include "../db.php"; // 引入資料庫連線

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 接收表單資料
    $name = mysqli_real_escape_string($link, $_POST['name']);        // 預約者姓名
    $date = mysqli_real_escape_string($link, $_POST['date']);        // 預約日期
    $time = mysqli_real_escape_string($link, $_POST['time']);        // 預約時間
    $doctor_id = mysqli_real_escape_string($link, $_POST['doctor']); // 醫生 ID
    $note = mysqli_real_escape_string($link, $_POST['note']);        // 備註

    // 驗證必填欄位
    if (empty($name) || empty($date) || empty($time) || empty($doctor_id)) {
        echo "<p>所有欄位均為必填，請返回重新輸入。</p>";
        exit;
    }

    // 查詢對應的時間 ID（shifttime 表）
    $time_query = "SELECT shifttime_id FROM shifttime WHERE shifttime = '$time'";
    $time_result = mysqli_query($link, $time_query);
    if (!$time_result || mysqli_num_rows($time_result) == 0) {
        echo "<p>預約時間無效，請返回重新選擇。</p>";
        exit;
    }
    $time_row = mysqli_fetch_assoc($time_result);
    $shifttime_id = $time_row['shifttime_id'];

    // 查詢對應的病人 ID（people 表）
    $check_patient_query = "SELECT people_id FROM people WHERE name = '$name'";
    $patient_result = mysqli_query($link, $check_patient_query);

    if (!$patient_result || mysqli_num_rows($patient_result) == 0) {
        // 如果病人不存在，新增到 people 表
        $insert_patient_query = "INSERT INTO people (name) VALUES ('$name')";
        if (!mysqli_query($link, $insert_patient_query)) {
            die("新增病人失敗: " . mysqli_error($link));
        }
        $people_id = mysqli_insert_id($link); // 取得新插入的 people_id
    } else {
        $patient_row = mysqli_fetch_assoc($patient_result);
        $people_id = $patient_row['people_id'];
    }

    // 寫入 appointment 表
    $insert_query = "INSERT INTO appointment (people_id, appointment_date, shifttime_id, doctor_id, note) 
                     VALUES ('$people_id', '$date', '$shifttime_id', '$doctor_id', '$note')";

    if (mysqli_query($link, $insert_query)) {
        echo "<p>預約成功！</p>";
        echo "<a href='appointment_form.php'>返回預約表單</a>";
    } else {
        echo "資料寫入失敗: " . mysqli_error($link);
    }

    // 關閉資料庫連線
    mysqli_close($link);
} else {
    echo "非法存取！";
}
?>