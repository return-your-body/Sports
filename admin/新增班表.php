<?php
include '../db.php'; // 引入資料庫連線

// 確認請求方法為 POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true); // 接收 JSON 格式的資料
    $doctor_id = $data['doctor_id']; // 醫生 ID
    $shifts = $data['shifts']; // 班表資料

    // 檢查資料是否完整
    if (!$doctor_id || !$shifts) {
        echo json_encode(['status' => 'error', 'message' => '資料不完整']);
        exit;
    }

    // 遍歷每個班表
    foreach ($shifts as $shift) {
        $date = $shift['date']; // 日期
        $start_time = $shift['start_time']; // 上班時間
        $end_time = $shift['end_time']; // 下班時間

        // 檢查是否有缺少必要的資料
        if (!$date || !$start_time || !$end_time) {
            continue; // 跳過不完整的資料
        }

        // 使用 DateTime 物件來計算每個 20 分鐘的時間區間
        $start_time_obj = new DateTime($start_time);
        $end_time_obj = new DateTime($end_time);

        while ($start_time_obj < $end_time_obj) {
            $time_slot_start = $start_time_obj->format('H:i');
            $start_time_obj->modify('+20 minutes');
            $time_slot_end = $start_time_obj->format('H:i');

            // 從 shifttime 資料表查找對應的 shifttime_id
            $query = "SELECT shifttime_id FROM shifttime WHERE shifttime = '$time_slot_start'";
            $result = mysqli_query($link, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $shifttime_id = $row['shifttime_id'];

                // 將資料插入 doctorshift 資料表
                $insert_query = "INSERT INTO doctorshift (doctor_id, date, shifttime_id, created_at) 
                                 VALUES ('$doctor_id', '$date', '$shifttime_id', NOW())";
                $insert_result = mysqli_query($link, $insert_query);

                if (!$insert_result) {
                    echo json_encode(['status' => 'error', 'message' => '插入失敗：' . mysqli_error($link)]);
                    exit;
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => "時段 '$time_slot_start' 未找到"]);
                exit;
            }
        }
    }

    // 所有資料插入成功
    echo json_encode(['status' => 'success', 'message' => '班表已新增']);
}
?>