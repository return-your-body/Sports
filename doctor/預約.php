<?php
session_start();
require '../db.php';

header('Content-Type: application/json'); // 確保回應 JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['people_id'], $_POST['doctor_id'], $_POST['date'], $_POST['time'], $_POST['note'])) {
    $people_id = intval($_POST['people_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $date = $_POST['date'];
    $shifttime_id = intval($_POST['time']);
    $note = $_POST['note'];

    // **檢查是否已經預約過相同時段**
    $query_check_time = "SELECT COUNT(*) FROM appointment a 
                         JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
                         WHERE a.shifttime_id = ? AND ds.doctor_id = ? AND ds.date = ?";
    $stmt_check_time = mysqli_prepare($link, $query_check_time);
    mysqli_stmt_bind_param($stmt_check_time, "iis", $shifttime_id, $doctor_id, $date);
    mysqli_stmt_execute($stmt_check_time);
    mysqli_stmt_bind_result($stmt_check_time, $existing_time_count);
    mysqli_stmt_fetch($stmt_check_time);
    mysqli_stmt_close($stmt_check_time);

    if ($existing_time_count > 0) {
        echo json_encode(['success' => false, 'message' => '此時段已被預約，請選擇其他時段。']);
        exit;
    }

    // **檢查使用者是否已經在同一天預約過其他時段**
    $query_check_day = "SELECT COUNT(*) FROM appointment a 
                        JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
                        WHERE a.people_id = ? AND ds.date = ?";
    $stmt_check_day = mysqli_prepare($link, $query_check_day);
    mysqli_stmt_bind_param($stmt_check_day, "is", $people_id, $date);
    mysqli_stmt_execute($stmt_check_day);
    mysqli_stmt_bind_result($stmt_check_day, $existing_day_count);
    mysqli_stmt_fetch($stmt_check_day);
    mysqli_stmt_close($stmt_check_day);

    if ($existing_day_count > 0) {
        echo json_encode(['success' => false, 'message' => '您已在這一天預約過其他時段，無法重複預約。']);
        exit;
    }

    // **查詢醫生班表 ID**
    $query_shift = "SELECT doctorshift_id FROM doctorshift WHERE doctor_id = ? AND date = ?";
    if ($stmt_shift = mysqli_prepare($link, $query_shift)) {
        mysqli_stmt_bind_param($stmt_shift, "is", $doctor_id, $date);
        mysqli_stmt_execute($stmt_shift);
        $result_shift = mysqli_stmt_get_result($stmt_shift);

        if ($shift_row = mysqli_fetch_assoc($result_shift)) {
            $doctorshift_id = $shift_row['doctorshift_id'];

            // **插入預約記錄**
            $insert_query = "INSERT INTO appointment (people_id, doctorshift_id, shifttime_id, note, status_id, created_at) VALUES (?, ?, ?, ?, 1, NOW())";
            if ($stmt_insert = mysqli_prepare($link, $insert_query)) {
                mysqli_stmt_bind_param($stmt_insert, "iiis", $people_id, $doctorshift_id, $shifttime_id, $note);
                
                if (mysqli_stmt_execute($stmt_insert)) {
                    echo json_encode(['success' => true, 'message' => '預約成功！']);
                } else {
                    echo json_encode(['success' => false, 'message' => '無法新增預約資料。', 'error' => mysqli_error($link)]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => '資料庫插入錯誤。']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => '無對應的排班資料。']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => '查詢班表失敗。']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '缺少必要的參數。']);
}
?>
