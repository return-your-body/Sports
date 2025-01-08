<?php
// 引入資料庫連線
include '../db.php';

// 確保請求方法為 POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 獲取表單提交的數據
    $doctor_id = $_POST['doctor_id']; // 醫生 ID
    $dates = $_POST['dates']; // 日期陣列 (格式為 YYYY-MM-DD)
    $go_times = $_POST['go_times']; // 上班時間
    $off_times = $_POST['off_times']; // 下班時間

    // 驗證資料完整性
    if (empty($doctor_id) || empty($dates) || empty($go_times) || empty($off_times)) {
        echo "<script>alert('請提供完整的班表資料！'); history.back();</script>";
        exit;
    }

    // 確保時間格式為 HH:MM
    function formatTime($time)
    {
        return str_pad($time, 2, '0', STR_PAD_LEFT) . ":00";
    }

    // 將時間轉換為 shifttime_id 的對應查詢
    function getShiftTimeId($time, $link)
    {
        // 如果時間包含秒數，去掉 ":00"
        $formatted_time = preg_replace('/:\d{2}$/', '', $time);

        $sql = "SELECT shifttime_id FROM shifttime WHERE shifttime = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 's', $formatted_time);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            return $row['shifttime_id'];
        }

        // 如果未找到，記錄錯誤日誌
        error_log("無法找到對應的 shifttime_id，時間為：$formatted_time");
        return null;
    }


    // 遍歷每一天的班表數據
    foreach ($dates as $index => $date) {
        $go_time = isset($go_times[$index]) ? $go_times[$index] : null; // 上班時間
        $off_time = isset($off_times[$index]) ? $off_times[$index] : null; // 下班時間

        // 確保時間格式為 HH:MM
        $formatted_go_time = formatTime($go_time);
        $formatted_off_time = formatTime($off_time);

        // 驗證日期格式
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo "<script>alert('日期格式錯誤，應為 YYYY-MM-DD！'); history.back();</script>";
            exit;
        }

        // 將上班與下班時間轉換為 shifttime_id
        $go_id = getShiftTimeId($formatted_go_time, $link);
        $off_id = getShiftTimeId($formatted_off_time, $link);

        if ($go_id === null || $off_id === null) {
            $missing_time = $go_id === null ? $formatted_go_time : $formatted_off_time;
            echo "<script>alert('無法找到對應的 shifttime_id！時間值為：$missing_time'); history.back();</script>";
            exit;
        }

        // 檢查該班表是否已存在
        $check_sql = "SELECT * FROM doctorshift WHERE doctor_id = ? AND date = ?";
        $check_stmt = mysqli_prepare($link, $check_sql);
        mysqli_stmt_bind_param($check_stmt, 'is', $doctor_id, $date);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($result) > 0) {
            // 如果班表已存在，更新數據
            $update_sql = "UPDATE doctorshift SET go = ?, off = ? WHERE doctor_id = ? AND date = ?";
            $update_stmt = mysqli_prepare($link, $update_sql);
            mysqli_stmt_bind_param($update_stmt, 'iiis', $go_id, $off_id, $doctor_id, $date);
            if (!mysqli_stmt_execute($update_stmt)) {
                echo "<script>alert('更新班表失敗：" . mysqli_error($link) . "'); history.back();</script>";
                exit;
            }
        } else {
            // 如果班表不存在，插入新的班表
            $insert_sql = "INSERT INTO doctorshift (doctor_id, date, go, off, created_at) VALUES (?, ?, ?, ?, NOW())";
            $insert_stmt = mysqli_prepare($link, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, 'isii', $doctor_id, $date, $go_id, $off_id);
            if (!mysqli_stmt_execute($insert_stmt)) {
                echo "<script>alert('新增班表失敗：" . mysqli_error($link) . "'); history.back();</script>";
                exit;
            }
        }
    }

    // 全部處理完成，顯示成功彈窗並返回上一頁
    echo "<script>alert('班表已成功儲存！'); window.location.href='a_addds.php';</script>";
    exit;
} else {
    echo "<script>alert('非法的請求方法！'); history.back();</script>";
    exit;
}
?>