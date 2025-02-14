<?php
include '../db.php'; // 連接資料庫

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['people_id'])) {
    $people_id = intval($_POST['people_id']); // ✅ 確保 `people_id` 為整數

    // ✅ 確保請求有效
    if ($people_id <= 0) {
        echo "<tr><td colspan='6'>請求無效</td></tr>";
        exit();
    }

    // ✅ 查詢該病人的所有預約記錄
    $sql = "
    SELECT 
        d.doctor AS 治療師,  
        ds.date AS 看診日期,  
        st.shifttime AS 看診時間, 
        s.status_name AS 狀態,  
        COALESCE(GROUP_CONCAT(DISTINCT CONCAT(i.item, ' ($', i.price, ')') ORDER BY i.item_id SEPARATOR ', '), '無') AS 治療項目, 
        COALESCE(SUM(i.price), 0) AS 總費用  
    FROM appointment a
    JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id 
    JOIN doctor d ON ds.doctor_id = d.doctor_id 
    JOIN shifttime st ON a.shifttime_id = st.shifttime_id 
    JOIN status s ON a.status_id = s.status_id 
    LEFT JOIN medicalrecord m ON a.appointment_id = m.appointment_id 
    LEFT JOIN item i ON m.item_id = i.item_id 
    WHERE a.people_id = ?  
    GROUP BY a.appointment_id, d.doctor, ds.date, st.shifttime, s.status_name  
    ORDER BY ds.date ASC, st.shifttime ASC;
    ";

    // 執行 SQL 查詢
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $people_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // 顯示結果
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['治療師']) . "</td>";
            echo "<td>" . htmlspecialchars($row['看診日期']) . "</td>";
            echo "<td>" . htmlspecialchars($row['看診時間']) . "</td>";
            echo "<td>" . htmlspecialchars($row['狀態']) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($row['治療項目'])) . "</td>";
            echo "<td>$" . htmlspecialchars($row['總費用']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>無病歷紀錄</td></tr>";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "<tr><td colspan='6'>請求無效</td></tr>";
}
?>