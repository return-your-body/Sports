<?php
session_start();
include "../db.php"; // 引入資料庫連線

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 接收表單資料
    $people_id = mysqli_real_escape_string($link, $_POST['people_id']); // 病人 ID
    $date = mysqli_real_escape_string($link, $_POST['date']);           // 預約日期
    $time = mysqli_real_escape_string($link, $_POST['time']);           // 預約時間
    $doctor_id = mysqli_real_escape_string($link, $_POST['doctor']);    // 醫生 ID
    $note = mysqli_real_escape_string($link, $_POST['note']);           // 備註

    // 驗證必填欄位
    if (empty($people_id) || empty($date) || empty($time) || empty($doctor_id)) {
        die("所有欄位均為必填，請重新填寫！");
    }

    // 獲取對應的 shifttime_id
    $query = "SELECT shifttime_id FROM shifttime WHERE shifttime = '$time'";
    $result = mysqli_query($link, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $shifttime_id = $row['shifttime_id'];
    } else {
        die("無效的預約時間，請重新選擇！");
    }

    // 插入資料到 appointment 表
    $sql = "INSERT INTO appointment (people_id, appointment_date, shifttime_id, doctor_id, note) 
            VALUES ('$people_id', '$date', '$shifttime_id', '$doctor_id', '$note')";

    if (mysqli_query($link, $sql)) {
        echo "<p style='text-align: center; color: green;'>預約成功！</p>";
        echo "<a href='appointment_form.php'>返回預約表單</a>";
    } else {
        echo "錯誤：" . mysqli_error($link);
    }

    mysqli_close($link); // 關閉資料庫連接
}
?>
