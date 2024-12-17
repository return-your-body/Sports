<?php
session_start();
require '../db.php';

// 確保用戶已登入
if (!isset($_SESSION['帳號'])) {
    echo "<script>
            alert('未登入或會話已過期，請重新登入！');
            window.location.href = '../index.html';
          </script>";
    exit;
}

// 檢查是否有傳入 appointment_id
if (!isset($_GET['appointment_id'])) {
    die("未指定預約資料");
}

$appointment_id = $_GET['appointment_id'];

// 查詢預約詳細資料
$query_details = "
SELECT a.appointment_id, p.name AS patient_name, ds.date, st.shifttime, a.note
FROM appointment a
JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
JOIN shifttime st ON ds.shifttime_id = st.shifttime_id
LEFT JOIN people p ON a.people_id = p.people_id
WHERE a.appointment_id = '$appointment_id'
";

$result_details = mysqli_query($link, $query_details);

// 檢查資料
if ($row = mysqli_fetch_assoc($result_details)) {
    $patient_name = htmlspecialchars($row['patient_name']);
    $appointment_date = htmlspecialchars($row['date']);
    $shifttime = htmlspecialchars($row['shifttime']);
    $note = htmlspecialchars($row['note']);
} else {
    die("找不到該預約資料");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>預約詳細資料</title>
</head>
<body>
    <h2 style="text-align: center;">預約詳細資料</h2>
    <table border="1" style="width: 50%; margin: auto; border-collapse: collapse;">
        <tr>
            <th>患者姓名</th>
            <td><?php echo $patient_name; ?></td>
        </tr>
        <tr>
            <th>預約日期</th>
            <td><?php echo $appointment_date; ?></td>
        </tr>
        <tr>
            <th>預約時段</th>
            <td><?php echo $shifttime; ?></td>
        </tr>
        <tr>
            <th>備註</th>
            <td><?php echo $note; ?></td>
        </tr>
    </table>
    <div style="text-align: center; margin-top: 20px;">
        <a href="javascript:history.back();" style="text-decoration: none; padding: 5px 10px; background-color: #007bff; color: white;">返回</a>
    </div>
</body>
</html>

<?php mysqli_close($link); ?>
