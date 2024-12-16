<?php
session_start();
include "../db.php"; // 引入資料庫連線

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 接收表單資料
    $people_id = $_POST['people_id'] ?? null;
    $date = $_POST['date'] ?? null;
    $time = $_POST['time'] ?? null;
    $doctor_id = $_POST['doctor'] ?? null;
    $note = $_POST['note'] ?? null;

    // 驗證必填欄位
    if (empty($people_id) || empty($date) || empty($time) || empty($doctor_id)) {
        echo "<script>alert('所有欄位均為必填，請重新填寫！'); window.history.back();</script>";
        exit;
    }

    // 1. 取得 shifttime_id
    $stmt = mysqli_prepare($link, "SELECT shifttime_id FROM shifttime WHERE shifttime = ?");
    mysqli_stmt_bind_param($stmt, "s", $time);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $shifttime_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$shifttime_id) {
        echo "<script>alert('無效的預約時間，請重新選擇！'); window.history.back();</script>";
        exit;
    }

    // 2. 驗證該醫生在指定日期與時間段的班表 (doctorshift)
    $stmt = mysqli_prepare($link, "SELECT doctor_id FROM doctorshift WHERE doctor_id = ? AND date = ? AND shifttime_id = ?");
    mysqli_stmt_bind_param($stmt, "isi", $doctor_id, $date, $shifttime_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 0) {
        echo "<script>alert('所選醫生在該日期與時間段沒有排班，請重新選擇！'); window.history.back();</script>";
        mysqli_stmt_close($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);

    // 3. 插入資料到 appointment 表
    $insert_sql = "INSERT INTO appointment (people_id, appointment_date, shifttime_id, doctor_id, note) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $insert_sql);
    mysqli_stmt_bind_param($stmt, "isiss", $people_id, $date, $shifttime_id, $doctor_id, $note);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('預約成功！'); window.location.href='d_appointment.php';</script>";
    } else {
        echo "<script>alert('發生錯誤：" . mysqli_error($link) . "'); window.history.back();</script>";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link); // 關閉資料庫連接
}
?>
