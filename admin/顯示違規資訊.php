<?php
include '../db.php';  // 連接資料庫

// 檢查請求是否為 POST，並確保傳入了 people_id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['people_id'])) {
    $people_id = intval($_POST['people_id']); // 確保 ID 為數字，避免 SQL 注入風險

    // 查詢該使用者的違規紀錄
    $sql = "SELECT b.violation_date, r.black 
            FROM blacklist b 
            JOIN black r ON b.black_id = r.black_id 
            WHERE b.people_id = ? 
            ORDER BY b.violation_date DESC";

    $stmt = mysqli_prepare($link, $sql); // 預備 SQL 語句，防止 SQL 注入
    mysqli_stmt_bind_param($stmt, "i", $people_id); // 綁定變數
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // 檢查是否有違規紀錄
    if (mysqli_num_rows($result) > 0) {
        echo "<table class='violation-table'>";
        echo "<thead><tr>
                <th>內容</th>  <!-- 內容欄位 -->
                <th>時間</th>  <!-- 時間欄位 -->
              </tr></thead>";
        echo "<tbody>";

        // 遍歷查詢結果，輸出表格
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['black']) . "</td>"; // 顯示違規內容
            echo "<td>" . htmlspecialchars($row['violation_date']) . "</td>"; // 顯示違規時間
            echo "</tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<p>無違規記錄</p>"; // 若無違規紀錄則顯示提示
    }

    mysqli_stmt_close($stmt); // 關閉 SQL 語句
}
?>