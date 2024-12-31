<?php
session_start();
require '../db.php';  // 引入資料庫連線

// 查詢 leaves 表資料，並通過 doctor_id 關聯 doctor 表
$sql = "
    SELECT 
        l.leaves_id, 
        l.doctor_id, 
        d.doctor AS doctor_name, 
        l.leave_type, 
        l.leave_type_other, 
        l.start_date, 
        l.end_date, 
        l.reason, 
        l.created_at
    FROM 
        leaves AS l
    INNER JOIN 
        doctor AS d
    ON 
        l.doctor_id = d.doctor_id
";

// 執行查詢
$result = mysqli_query($link, $sql);

// 檢查是否有資料
if (mysqli_num_rows($result) > 0) {
    // 顯示查詢結果
    echo "<table border='1' cellpadding='10'>";
    echo "<tr>
            <th>請假編號</th>
            <th>醫生編號</th>
            <th>醫生名稱</th>
            <th>請假類別</th>
            <th>其他請假原因</th>
            <th>請假起始日期</th>
            <th>請假結束日期</th>
            <th>請假原因</th>
            <th>提交時間</th>
          </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>" . htmlspecialchars($row['leaves_id']) . "</td>
                <td>" . htmlspecialchars($row['doctor_id']) . "</td>
                <td>" . htmlspecialchars($row['doctor_name']) . "</td>
                <td>" . htmlspecialchars($row['leave_type']) . "</td>
                <td>" . htmlspecialchars($row['leave_type_other']) . "</td>
                <td>" . htmlspecialchars($row['start_date']) . "</td>
                <td>" . htmlspecialchars($row['end_date']) . "</td>
                <td>" . htmlspecialchars($row['reason']) . "</td>
                <td>" . htmlspecialchars($row['created_at']) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "沒有找到相關資料。";
}

// 關閉連線
mysqli_close($link);
?>
