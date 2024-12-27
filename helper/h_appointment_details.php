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
        </tr><?php
session_start();
require '../db.php'; // 引入資料庫連線設定檔

// 確保用戶已登入
if (!isset($_SESSION['帳號'])) {
    echo "<script>
            alert('未登入或會話已過期，請重新登入！');
            window.location.href = '../index.html';
          </script>";
    exit;
}

// 檢查 URL 是否提供有效的 id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
            alert('未指定或無效的預約資料');
            window.location.href = 'd_numberpeople.php';
          </script>";
    exit;
}

// 安全地獲取 id
$appointment_id = intval($_GET['id']);

// 查詢預約詳細資料
$query_details = "
SELECT a.appointment_id, 
       COALESCE(p.name, '未知') AS patient_name, -- 患者姓名
       COALESCE(g.gender, '未知') AS gender, -- 性別
       COALESCE(p.birthday, '未知') AS birthday, -- 生日
       COALESCE(ds.date, '未知') AS appointment_date, -- 預約日期
       COALESCE(st.shifttime, '未知') AS shifttime, -- 預約時段
       COALESCE(a.note, '無備註') AS note -- 備註
FROM appointment a
LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id -- 關聯班表
LEFT JOIN shifttime st ON ds.shifttime_id = st.shifttime_id -- 關聯時段
LEFT JOIN people p ON a.people_id = p.people_id -- 關聯患者
LEFT JOIN gender g ON p.gender_id = g.gender_id -- 關聯性別
WHERE a.appointment_id = ?
";

// 預處理查詢
$stmt = mysqli_prepare($link, $query_details);
if (!$stmt) {
    die("SQL 錯誤：" . mysqli_error($link));
}
mysqli_stmt_bind_param($stmt, 'i', $appointment_id);
mysqli_stmt_execute($stmt);
$result_details = mysqli_stmt_get_result($stmt);

// 檢查是否有查詢結果
if ($row = mysqli_fetch_assoc($result_details)) {
    // 處理結果
    $patient_name = htmlspecialchars($row['patient_name']);
    $gender = htmlspecialchars($row['gender']);
    $birthday = htmlspecialchars($row['birthday']);
    $appointment_date = htmlspecialchars($row['appointment_date']);
    $shifttime = htmlspecialchars($row['shifttime']);
    $note = htmlspecialchars($row['note']);
} else {
    // 無結果時顯示錯誤
    echo "<script>
            alert('找不到該預約資料，請確認預約是否存在！');
            window.location.href = 'd_numberpeople.php';
          </script>";
    exit;
}

// 關閉資料庫連線
mysqli_close($link);
?>


<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>預約詳細資料</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }
        table {
            width: 60%;
            margin: auto;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        a {
            text-decoration: none;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
        }
        a:hover {
            background-color: #0056b3;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h2>預約詳細資料</h2>
    <table>
        <tr>
            <th>姓名</th>
            <td><?php echo $patient_name; ?></td>
        </tr>
        <tr>
            <th>性別</th>
            <td><?php echo $gender; ?></td>
        </tr>
        <tr>
            <th>生日</th>
            <td><?php echo $birthday; ?></td>
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
        <a href="javascript:history.back();">返回</a>
    </div>
</body>
</html>

    </table>
    <div style="text-align: center; margin-top: 20px;">
        <a href="javascript:history.back();" style="text-decoration: none; padding: 5px 10px; background-color: #007bff; color: white;">返回</a>
    </div>
</body>
</html>

<?php mysqli_close($link); ?>
