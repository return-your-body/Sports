<?php
session_start();
require '../db.php'; // 引入資料庫連線

// 接收表單資料
$applicant_name = trim($_POST['name']); // 申請人姓名
$leave_type = trim($_POST['leave-type']); // 請假類別
$leave_type_other = isset($_POST['leave-type-other']) ? trim($_POST['leave-type-other']) : null; // 其他請假原因
$start_date = trim($_POST['start-date']); // 請假起始日期
$end_date = trim($_POST['end-date']); // 請假結束日期
$reason = trim($_POST['reason']); // 請假原因

// 驗證必填欄位
if (empty($applicant_name) || empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
    echo "<script>
            alert('所有欄位均為必填，請重新填寫！');
            window.history.back();
          </script>";
    exit;
}

// 防止 SQL 注入
$applicant_name = mysqli_real_escape_string($link, $applicant_name);
$leave_type = mysqli_real_escape_string($link, $leave_type);
$leave_type_other = $leave_type_other ? mysqli_real_escape_string($link, $leave_type_other) : null;
$start_date = mysqli_real_escape_string($link, $start_date);
$end_date = mysqli_real_escape_string($link, $end_date);
$reason = mysqli_real_escape_string($link, $reason);

// 確認申請人是否存在於 doctor 資料表
$check_doctor_sql = "
    SELECT doctor_id FROM doctor 
    WHERE doctor = '$applicant_name'
";
$result_doctor = mysqli_query($link, $check_doctor_sql) or die(mysqli_error($link));
if (mysqli_num_rows($result_doctor) == 0) {
    echo "<script>
            alert('找不到該申請人的醫生資料，請確認姓名是否正確！');
            window.history.back();
          </script>";
    exit;
}

// 獲取 doctor_id
$row = mysqli_fetch_assoc($result_doctor);
$doctor_id = $row['doctor_id'];

// 插入請假資料
$insert_leave_sql = "
    INSERT INTO leaves (doctor_id, leave_type, leave_type_other, start_date, end_date, reason)
    VALUES ('$doctor_id', '$leave_type', " . ($leave_type_other ? "'$leave_type_other'" : "NULL") . ", '$start_date', '$end_date', '$reason')
";
if (mysqli_query($link, $insert_leave_sql)) {
    echo "<script>
            alert('請假資料提交成功！');
            window.location.href = 'd_leave.php';
          </script>";
} else {
    echo "<script>
            alert('提交失敗：" . mysqli_error($link) . "');
            window.history.back();
          </script>";
}

// 關閉資料庫連線
mysqli_close($link);
?>
