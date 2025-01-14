<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>預約詳情</title>
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
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
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

        h3 {
            text-align: center;
            margin-top: 20px;
        }

        .center-button {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>   
    <h3>預約資料</h3>
    <?php
    require '../db.php';

    // 獲取預約ID
    $appointment_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

    // 檢查預約ID是否有效
    if ($appointment_id === 0) {
        die('無效的預約ID');
    }

    // 查詢預約詳情
    // 查詢預約詳情
    $query = "
SELECT 
    a.appointment_id, 
    st.shifttime AS 時段, 
    ds.date AS 日期, 
    d.doctor AS 治療師姓名, 
    p.name AS 使用者姓名, 
    a.note AS 備註
FROM 
    appointment a
JOIN 
    doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
JOIN 
    shifttime st ON a.shifttime_id = st.shifttime_id
LEFT JOIN 
    doctor d ON ds.doctor_id = d.doctor_id
LEFT JOIN 
    people p ON a.people_id = p.people_id
WHERE 
    a.appointment_id = ?";

    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'i', $appointment_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // 調試輸出
    if (mysqli_num_rows($result) === 0) {
        die('查無資料，請確認條件是否正確！');
    }

    $appointment_details = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($link);
    ?>
 
    <table>
        <!-- <tr>
            <th>預約ID</th>
            <td><?php echo htmlspecialchars($appointment_details['appointment_id']); ?></td>
        </tr> -->
        <tr>
            <th>日期</th>
            <td><?php echo htmlspecialchars($appointment_details['日期']); ?></td>
        </tr> 
        <tr>
            <th>時段</th>
            <td><?php echo htmlspecialchars($appointment_details['時段']); ?></td>
        </tr>
       
        <tr>
            <th>治療師</th>
            <td><?php echo htmlspecialchars($appointment_details['治療師姓名']); ?></td>
        </tr>
        <tr>
            <th>使用者</th>
            <td><?php echo htmlspecialchars($appointment_details['使用者姓名']); ?></td>
        </tr>
        <tr>
            <th>備註</th>
            <td><?php echo htmlspecialchars($appointment_details['備註'] ?? '無'); ?></td>
        </tr>
    </table>
    <div class="center-button">
        <button onclick="location.href='h_numberpeople.php'">返回</button>
    </div>
</body>

</html>