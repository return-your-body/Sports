<?php
require '../db.php';
session_start();

$people_name = mysqli_real_escape_string($link, trim($_POST['people_name']));
$doctor_id = intval($_POST['doctor_id']);
$date = mysqli_real_escape_string($link, trim($_POST['date']));
$time = intval($_POST['time']);
$note = mysqli_real_escape_string($link, trim($_POST['note']));

if (empty($people_name) || empty($doctor_id) || empty($date) || empty($time)) {
    echo "<script>alert('所有欄位為必填項！'); window.history.back();</script>";
    exit;
}

$query = "
  INSERT INTO appointment (people_name, doctor_id, date, shifttime_id, note) 
  VALUES (?, ?, ?, ?, ?)
";
$stmt = $link->prepare($query);
$stmt->bind_param("sisii", $people_name, $doctor_id, $date, $time, $note);

if ($stmt->execute()) {
    echo "<script>alert('預約成功！'); window.location.href = 'h_people.php';</script>";
} else {
    echo "<script>alert('預約失敗：" . $stmt->error . "'); window.history.back();</script>";
}

$stmt->close();
$link->close();
?>
