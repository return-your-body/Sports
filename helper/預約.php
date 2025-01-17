<?php
session_start();
require '../db.php';

$date = mysqli_real_escape_string($link, $_POST['date']);
$shifttime_id = intval($_POST['time']);
$doctor_id = 1; // 假設固定醫生 ID
$note = mysqli_real_escape_string($link, $_POST['note']);
$user_id = $_SESSION['user_id'];

// 檢查時段是否已被預約
$query_check = "
  SELECT 1 FROM appointment a
  JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
  WHERE ds.date = '$date' AND ds.doctor_id = '$doctor_id' AND a.shifttime_id = '$shifttime_id'
";
$result_check = mysqli_query($link, $query_check);

if (mysqli_num_rows($result_check) > 0) {
    echo "<script>alert('該時段已被預約，請選擇其他時段！');window.history.back();</script>";
    exit;
}

// 獲取 doctorshift_id
$query_shift = "
  SELECT doctorshift_id FROM doctorshift
  WHERE doctor_id = '$doctor_id' AND date = '$date' AND go <= '$shifttime_id' AND off >= '$shifttime_id'
";
$result_shift = mysqli_query($link, $query_shift);

if ($row = mysqli_fetch_assoc($result_shift)) {
    $doctorshift_id = $row['doctorshift_id'];

    $insert_query = "
      INSERT INTO appointment (people_id, doctorshift_id, shifttime_id, note)
      VALUES ('$user_id', '$doctorshift_id', '$shifttime_id', '$note')
    ";

    if (mysqli_query($link, $insert_query)) {
        echo "<script>alert('預約成功！');window.location.href='d_people.php';</script>";
    } else {
        echo "<script>alert('預約失敗，請稍後再試！');window.history.back();</script>";
    }
} else {
    echo "<script>alert('醫生排班不存在，請重新選擇！');window.history.back();</script>";
}

mysqli_close($link);
?>
