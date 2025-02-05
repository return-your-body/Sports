<?php
// 引入資料庫連線
include '../db.php';

// 設定回應的內容類型為 HTML，確保網頁顯示正常
header('Content-Type: text/html; charset=UTF-8');

// 檢查請求是否為 POST，並確保 `people_id` 存在
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['people_id']) && !empty($_POST['people_id'])) {
    $people_id = intval($_POST['people_id']); // 轉換成整數，避免 SQL 注入

    // 如果 `people_id` 非法 (小於等於 0)，則直接回傳錯誤
    if ($people_id <= 0) {
        echo "<p>錯誤：無效的使用者 ID</p>";
        exit;
    }

    // 確保資料庫連線有效
    if (!$link) {
        echo "<p>錯誤：資料庫連線失敗</p>";
        exit;
    }

    // SQL 查詢：獲取該使用者的違規記錄
    $sql = "SELECT b.violation_date, r.black 
            FROM blacklist b 
            JOIN black r ON b.black_id = r.black_id 
            WHERE b.people_id = ? 
            ORDER BY b.violation_date DESC";

    // 準備 SQL 查詢，防止 SQL 注入攻擊
    $stmt = mysqli_prepare($link, $sql);
    if (!$stmt) {
        echo "<p>錯誤：SQL 查詢失敗 - " . mysqli_error($link) . "</p>";
        exit;
    }

    // 綁定參數，確保查詢安全
    mysqli_stmt_bind_param($stmt, "i", $people_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // 如果查詢到違規紀錄，則輸出 HTML 表格
    if (mysqli_num_rows($result) > 0) {
        echo "<table class='violation-table'>";
        echo "<thead><tr>
                <th>內容</th>  <!-- 違規內容 -->
                <th>時間</th>  <!-- 違規時間 -->
              </tr></thead>";
        echo "<tbody>";

        // 逐行輸出違規記錄
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['black']) . "</td>"; // 顯示違規內容
            echo "<td>" . htmlspecialchars($row['violation_date']) . "</td>"; // 顯示違規時間
            echo "</tr>";
        }

        echo "</tbody></table>";
    } else {
        // 如果沒有違規記錄，則顯示提示
        echo "<p>無違規記錄</p>";
    }

    // 關閉 SQL 語句
    mysqli_stmt_close($stmt);
} else {
    // 如果請求不符合條件，則回傳錯誤訊息
    echo "<p>請求無效，缺少必要參數</p>";
}
?>
