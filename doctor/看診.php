<?php
require '../db.php';
session_start();

// **開啟錯誤報告**
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// **確保使用者已登入**
if (!isset($_SESSION["帳號"])) {
    echo "<script>alert('使用者未登入！'); window.location.href='login.php';</script>";
    exit;
}

$帳號 = $_SESSION["帳號"];

// **確保 `id` 存在**
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('無法取得預約 ID！'); window.location.href='d_appointment-records.php';</script>";
    exit;
}
$appointment_id = intval($_GET['id']);

// **查詢該帳號的治療師 ID**
$sql_doctor = "SELECT d.doctor_id FROM user u JOIN doctor d ON u.user_id = d.user_id WHERE u.account = ?";
$stmt_doctor = mysqli_prepare($link, $sql_doctor);
mysqli_stmt_bind_param($stmt_doctor, "s", $帳號);
mysqli_stmt_execute($stmt_doctor);
$result_doctor = mysqli_stmt_get_result($stmt_doctor);
$doctor = mysqli_fetch_assoc($result_doctor);

if (!$doctor) {
    echo "<script>alert('無法找到您的治療師資料！'); window.location.href='d_appointment-records.php';</script>";
    exit;
}

$doctor_id = $doctor['doctor_id'];

// **處理表單提交**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_ids']) && is_array($_POST['item_ids'])) {
    $item_ids = array_map('intval', $_POST['item_ids']); // 確保 item_ids 為整數
    $note_d = isset($_POST['note_d']) ? trim($_POST['note_d']) : '';

    // **DEBUG：記錄 note_d 內容**
    $debug_log_path = __DIR__ . "/debug_log.txt";
    file_put_contents($debug_log_path, "Received note_d: " . json_encode($note_d) . "\n", FILE_APPEND);

    if (empty($item_ids)) {
        echo "<script>alert('請至少選擇一項診療項目！');</script>";
    } else {
        $sql_insert_medicalrecord = "INSERT INTO medicalrecord (appointment_id, item_id, note_d, created_at) 
                                     VALUES (?, ?, ?, NOW())";
        $stmt_insert_medicalrecord = mysqli_prepare($link, $sql_insert_medicalrecord);

        if (!$stmt_insert_medicalrecord) {
            die("SQL 準備失敗：" . mysqli_error($link));
        }

        $success = false;

        foreach ($item_ids as $item_id) {
            mysqli_stmt_bind_param($stmt_insert_medicalrecord, "iis", $appointment_id, $item_id, $note_d);

            if (!mysqli_stmt_execute($stmt_insert_medicalrecord)) {
                die("診療紀錄新增失敗：" . mysqli_error($link));
            }

            $success = true;
        }

        // **確認 `note_d` 是否存入**
        $last_id = mysqli_insert_id($link);
        $sql_check = "SELECT note_d FROM medicalrecord WHERE medicalrecord_id = ?";
        $stmt_check = mysqli_prepare($link, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "i", $last_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $row = mysqli_fetch_assoc($result_check);

        file_put_contents($debug_log_path, "DB Stored note_d: " . json_encode($row['note_d']) . "\n", FILE_APPEND);

        if ($success) {
            // **更新 `appointment` 的 `status_id` 為 6（已看診）**
            $sql_update_status = "UPDATE appointment SET status_id = 6 WHERE appointment_id = ?";
            $stmt_update_status = mysqli_prepare($link, $sql_update_status);
            mysqli_stmt_bind_param($stmt_update_status, "i", $appointment_id);
            if (!mysqli_stmt_execute($stmt_update_status)) {
                die("狀態更新失敗：" . mysqli_error($link));
            }

            echo "<script>alert('診療紀錄新增成功，狀態更新為已看診！'); window.location.href='d_appointment-records.php';</script>";
        }
    }
}
?>
