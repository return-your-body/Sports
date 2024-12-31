<?php
session_start();
require '../db.php'; // 引入資料庫連線

// 接收表單資料
$people_id = trim($_POST['people_id']);
$date = trim($_POST['date']);
$shifttime_id = trim($_POST['time']);
$doctor_id = trim($_POST['doctor']);
$note = trim($_POST['note']);

// 驗證必填欄位
if (empty($people_id) || empty($date) || empty($shifttime_id) || empty($doctor_id)) {
    echo "<script>
            alert('所有欄位均為必填，請重新填寫！');
            window.history.back();
          </script>";
    exit;
}

// 防止 SQL 注入
$people_id = mysqli_real_escape_string($link, $people_id);
$date = mysqli_real_escape_string($link, $date);
$shifttime_id = mysqli_real_escape_string($link, $shifttime_id);
$doctor_id = mysqli_real_escape_string($link, $doctor_id);
$note = mysqli_real_escape_string($link, $note);

// 檢查醫生排班是否存在
$check_schedule_sql = "
    SELECT doctorshift_id FROM doctorshift 
    WHERE doctor_id = '$doctor_id' 
      AND date = '$date' 
      AND shifttime_id = '$shifttime_id'
";
$result_schedule = mysqli_query($link, $check_schedule_sql) or die(mysqli_error($link));
if (mysqli_num_rows($result_schedule) == 0) {
    echo "<script>
            alert('所選醫生在該日期與時間段沒有排班，請重新選擇！');
            window.history.back();
          </script>";
    exit;
}

// 獲取 doctorshift_id
$row = mysqli_fetch_assoc($result_schedule);
$doctorshift_id = $row['doctorshift_id'];

// 檢查是否有重複預約（針對相同病人、醫生、時間段）
$check_appointment_sql = "
    SELECT 1 FROM appointment a
    JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    WHERE a.people_id = '$people_id' 
      AND ds.date = '$date' 
      AND ds.shifttime_id = '$shifttime_id' 
      AND ds.doctor_id = '$doctor_id'
";
$result_appointment = mysqli_query($link, $check_appointment_sql) or die(mysqli_error($link));
if (mysqli_num_rows($result_appointment) > 0) {
    echo "<script>
            alert('您已在該時間段預約過該醫生，請重新選擇！');
            window.history.back();
          </script>";
    exit;
}

// 檢查該時段是否已有其他預約者
$check_shift_appointment_sql = "
    SELECT 1 FROM appointment 
    WHERE doctorshift_id = '$doctorshift_id'
";
$result_shift_appointment = mysqli_query($link, $check_shift_appointment_sql) or die(mysqli_error($link));
if (mysqli_num_rows($result_shift_appointment) > 0) {
    echo "<script>
            alert('本時段已有預約，請重新選擇其他時段！');
            window.history.back();
          </script>";
    exit;
}

// 插入預約資料
$insert_sql = "
    INSERT INTO appointment (people_id, doctorshift_id, note)
    VALUES ('$people_id', '$doctorshift_id', '$note')
";
if (mysqli_query($link, $insert_sql)) {
    echo "<script>
            alert('預約成功！');
            window.location.href = 'd_appointment.php';
          </script>";
} else {
    echo "<script>
            alert('發生錯誤：" . mysqli_error($link) . "'');
            window.history.back();
          </script>";
}

// 關閉資料庫連線
mysqli_close($link);
?>
