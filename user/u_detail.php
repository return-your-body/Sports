<!--看診紀錄-->


<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>看診紀錄</title>
    <style>
        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            text-align: center;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .btn {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        p {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h1 style="text-align: center; margin-top: 20px;">看診紀錄</h1>
    <?php
    include "../db.php"; // 引入資料庫連線設定檔
    
    // 檢查 URL 中的 `id` 是否存在並有效
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo "<script>
            alert('無效的參數，請返回。');
            window.history.back();
          </script>";
        exit;
    }

    $appointment_id = intval($_GET['id']); // 確保 `id` 為整數
    
    // 查詢該預約的看診紀錄
    $query_medical_record = "
SELECT 
    mr.medicalrecord_id,
    p.name AS patient_name,
    d.doctor AS doctor_name,
    a.created_at AS appointment_date,
    i.item AS treatment_item,
    mr.notes AS notes,
    mr.created_at AS created_time
FROM medicalrecord mr
LEFT JOIN appointment a ON mr.appointment_id = a.appointment_id
LEFT JOIN people p ON a.people_id = p.people_id
LEFT JOIN doctor d ON mr.doctor_id = d.doctor_id
LEFT JOIN item i ON mr.item_id = i.item_id
WHERE mr.appointment_id = ?;
";

    $stmt = mysqli_prepare($link, $query_medical_record);
    mysqli_stmt_bind_param($stmt, "i", $appointment_id);
    mysqli_stmt_execute($stmt);
    $result_medical_record = mysqli_stmt_get_result($stmt);

    // 檢查是否有資料
    if (!$result_medical_record) {
        echo "<script>
            alert('查詢失敗：" . mysqli_error($link) . "');
            window.history.back();
          </script>";
        exit;
    }

    // 將資料存入陣列
    $medical_records = [];
    while ($row = mysqli_fetch_assoc($result_medical_record)) {
        $medical_records[] = $row;
    }

    mysqli_close($link);
    ?>
    <?php if (count($medical_records) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>病人姓名</th>
                    <th>醫生姓名</th>
                    <th>預約日期</th>
                    <th>治療項目</th>
                    <th>備註</th>
                    <th>建立時間</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($medical_records as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['doctor_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['appointment_date']); ?></td>
                        <td><?php echo htmlspecialchars($record['treatment_item']); ?></td>
                        <td><?php echo htmlspecialchars($record['notes']); ?></td>
                        <td><?php echo htmlspecialchars($record['created_time']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center;">目前沒有相關的看診紀錄。</p>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 20px;">
        <a href="u_history.php" class="btn">返回</a>
    </div>
</body>

</html>