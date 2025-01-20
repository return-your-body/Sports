<?php
require '../db.php'; // 引入資料庫連線

// 接收參數
$date = mysqli_real_escape_string($link, $_POST['date']);
$doctor_id = intval($_POST['doctor_id']);

// 檢查必要參數
if (empty($date) || empty($doctor_id)) {
    echo json_encode(['success' => false, 'message' => '參數缺失']);
    exit;
}

// 查詢醫生排班和可用時段
$query = "
    SELECT st.shifttime_id, st.shifttime
    FROM shifttime st
    JOIN doctorshift ds ON ds.go <= st.shifttime_id AND ds.off >= st.shifttime_id
    WHERE ds.doctor_id = ? 
      AND ds.date = ?
      AND NOT EXISTS (
          SELECT 1 
          FROM appointment a
          WHERE a.doctorshift_id = ds.doctorshift_id 
            AND a.shifttime_id = st.shifttime_id
      )
    ORDER BY st.shifttime_id ASC
";
$stmt = $link->prepare($query);
$stmt->bind_param("is", $doctor_id, $date); // 綁定參數
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $times = [];
    while ($row = $result->fetch_assoc()) {
        $times[] = $row; // 收集時段資料
    }
    if (count($times) > 0) {
        echo json_encode(['success' => true, 'times' => $times]); // 返回成功訊息和時段資料
    } else {
        echo json_encode(['success' => false, 'message' => '無可用時段']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '查詢失敗']);
}

// 關閉連線
$stmt->close();
$link->close();
?>
