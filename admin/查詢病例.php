<?php
include '../db.php'; // 連接資料庫

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['people_id'])) {
    $people_id = intval($_POST['people_id']); // 確保 ID 為數字

    // 查詢該使用者 (people_id) 的所有病歷，包含醫生、看診日期、時間、治療項目和總費用
    $sql = "
       SELECT 
           d.doctor AS 醫生,
           ds.date AS 看診日期,
           st.shifttime AS 時間,
           GROUP_CONCAT(DISTINCT i.item ORDER BY i.item_id SEPARATOR ', ') AS 治療項目,
           SUM(COALESCE(i.price, 0)) AS 總費用
       FROM appointment a
       JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
       JOIN doctor d ON ds.doctor_id = d.doctor_id
       JOIN shifttime st ON a.shifttime_id = st.shifttime_id
       LEFT JOIN medicalrecord m ON a.appointment_id = m.appointment_id
       LEFT JOIN item i ON m.item_id = i.item_id
       WHERE a.people_id = ?
       GROUP BY a.appointment_id, d.doctor, ds.date, st.shifttime
       ORDER BY ds.date DESC, st.shifttime ASC;
    ";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $people_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['醫生']) . "</td>";
            echo "<td>" . htmlspecialchars($row['看診日期']) . "</td>";
            echo "<td>" . htmlspecialchars($row['時間']) . "</td>";
            echo "<td>" . htmlspecialchars($row['治療項目']) . "</td>";
            echo "<td>$" . htmlspecialchars($row['總費用']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>無病歷紀錄</td></tr>";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "<tr><td colspan='5'>請求無效</td></tr>";
}
?>
