<?php
// session_start();
// require '../db.php'; // 引入資料庫連線

// // 接收表單資料
// $people_id = trim($_POST['people_id']);
// $date = trim($_POST['date']);
// $shifttime_id = trim($_POST['time']);
// $doctor_id = trim($_POST['doctor']);
// $note = trim($_POST['note']);

// // 驗證必填欄位
// if (empty($people_id) || empty($date) || empty($shifttime_id) || empty($doctor_id)) {
//     echo "<script>
//             alert('所有欄位均為必填，請重新填寫！');
//             window.history.back();
//           </script>";
//     exit;
// }

// // 防止 SQL 注入
// $people_id = mysqli_real_escape_string($link, $people_id);
// $date = mysqli_real_escape_string($link, $date);
// $shifttime_id = mysqli_real_escape_string($link, $shifttime_id);
// $doctor_id = mysqli_real_escape_string($link, $doctor_id);
// $note = mysqli_real_escape_string($link, $note);

// // 檢查醫生排班是否存在
// $check_schedule_sql = "
//     SELECT doctorshift_id FROM doctorshift 
//     WHERE doctor_id = '$doctor_id' 
//       AND date = '$date' 
//       AND shifttime_id = '$shifttime_id'
// ";
// $result_schedule = mysqli_query($link, $check_schedule_sql) or die(mysqli_error($link));
// if (mysqli_num_rows($result_schedule) == 0) {
//     echo "<script>
//             alert('所選醫生在該日期與時間段沒有排班，請重新選擇！');
//             window.history.back();
//           </script>";
//     exit;
// }

// // 獲取 doctorshift_id
// $row = mysqli_fetch_assoc($result_schedule);
// $doctorshift_id = $row['doctorshift_id'];

// // 檢查是否有重複預約（針對相同病人、醫生、時間段）
// $check_appointment_sql = "
//     SELECT 1 FROM appointment a
//     JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
//     WHERE a.people_id = '$people_id' 
//       AND ds.date = '$date' 
//       AND ds.shifttime_id = '$shifttime_id' 
//       AND ds.doctor_id = '$doctor_id'
// ";
// $result_appointment = mysqli_query($link, $check_appointment_sql) or die(mysqli_error($link));
// if (mysqli_num_rows($result_appointment) > 0) {
//     echo "<script>
//             alert('您已在該時間段預約過該醫生，請重新選擇！');
//             window.history.back();
//           </script>";
//     exit;
// }

// // 檢查該時段是否已有其他預約者
// $check_shift_appointment_sql = "
//     SELECT 1 FROM appointment 
//     WHERE doctorshift_id = '$doctorshift_id'
// ";
// $result_shift_appointment = mysqli_query($link, $check_shift_appointment_sql) or die(mysqli_error($link));
// if (mysqli_num_rows($result_shift_appointment) > 0) {
//     echo "<script>
//             alert('本時段已有預約，請重新選擇其他時段！');
//             window.history.back();
//           </script>";
//     exit;
// }

// // 插入預約資料
// $insert_sql = "
//     INSERT INTO appointment (people_id, doctorshift_id, note)
//     VALUES ('$people_id', '$doctorshift_id', '$note')
// ";
// if (mysqli_query($link, $insert_sql)) {
//     echo "<script>
//             alert('預約成功！');
//             window.location.href = 'd_appointment.php';
//           </script>";
// } else {
//     echo "<script>
//             alert('發生錯誤：" . mysqli_error($link) . "'');
//             window.history.back();
//           </script>";
// }

// // 關閉資料庫連線
// mysqli_close($link);


// 啟動會話，檢查用戶是否已登入
session_start();
// 載入資料庫連接設定
require '../db.php';

// 確認前端是否傳遞了必須的參數（醫生名稱、日期、時間）
if (isset($_POST['doctor'], $_POST['date'], $_POST['time'])) {
    $doctor = $_POST['doctor']; // 醫生名稱
    $date = $_POST['date'];     // 預約日期
    $time = $_POST['time'];     // 預約時間

    // 獲取當前用戶的帳號，用於查詢其 people_id
    $account = $_SESSION['帳號'];
    $query = "SELECT people_id FROM user JOIN people ON user.user_id = people.user_id WHERE user.account = ?";
    $stmt = mysqli_prepare($link, $query); // 使用預處理語句，防止 SQL 注入
    mysqli_stmt_bind_param($stmt, "s", $account); // 綁定參數
    mysqli_stmt_execute($stmt); // 執行查詢
    $result = mysqli_stmt_get_result($stmt); // 獲取查詢結果

    // 如果能找到對應的 people_id
    if ($row = mysqli_fetch_assoc($result)) {
        $people_id = $row['people_id']; // 獲取當前用戶的 people_id

        // 查詢對應的 doctorshift_id（醫生班表 ID）和 shifttime_id（時間段 ID）
        $query_shift = "
            SELECT ds.doctorshift_id, st.shifttime_id
            FROM doctorshift ds
            JOIN doctor d ON ds.doctor_id = d.doctor_id
            JOIN shifttime st ON st.shifttime = ?
            WHERE d.doctor = ? AND ds.date = ?";
        $stmt_shift = mysqli_prepare($link, $query_shift); // 預處理語句
        mysqli_stmt_bind_param($stmt_shift, "sss", $time, $doctor, $date); // 綁定時間、醫生名稱和日期參數
        mysqli_stmt_execute($stmt_shift); // 執行查詢
        $result_shift = mysqli_stmt_get_result($stmt_shift); // 獲取查詢結果

        // 如果找到對應的班表和時間段
        if ($shift_row = mysqli_fetch_assoc($result_shift)) {
            $doctorshift_id = $shift_row['doctorshift_id']; // 醫生班表 ID
            $shifttime_id = $shift_row['shifttime_id'];     // 時間段 ID

            // 插入預約資料到 appointment 表
            $insert_query = "
                INSERT INTO appointment (people_id, doctorshift_id, shifttime_id, note, created_at) 
                VALUES (?, ?, ?, ?, NOW())"; // 使用 NOW() 記錄預約時間
            $stmt_insert = mysqli_prepare($link, $insert_query); // 預處理語句
            $note = ""; // 添加備註，方便後續查看
            mysqli_stmt_bind_param($stmt_insert, "iiis", $people_id, $doctorshift_id, $shifttime_id, $note); // 綁定參數

            // 如果插入成功
            if (mysqli_stmt_execute($stmt_insert)) {
                echo json_encode(['success' => true]); // 回傳成功訊息
            } else {
                // 插入失敗，可能是資料庫問題
                echo json_encode(['success' => false, 'message' => '無法新增預約資料。']);
            }
        } else {
            // 未找到對應的班表或時間段
            echo json_encode(['success' => false, 'message' => '無對應的排班或時段資料。']);
        }
    } else {
        // 無法找到用戶的 people_id，可能是帳號問題
        echo json_encode(['success' => false, 'message' => '用戶資訊錯誤，請重新登入。']);
    }
} else {
    // 缺少必要的參數，回傳錯誤訊息
    echo json_encode(['success' => false, 'message' => '缺少必要的參數。']);
}


?>
