<?php  
require '../db.php';
session_start();

// 確保使用者已登入
if (!isset($_SESSION["帳號"])) {
    echo "<script>alert('使用者未登入！'); window.location.href='login.php';</script>";
    exit;
}

$帳號 = $_SESSION["帳號"];

// 確保 `id` 存在
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('無法取得預約 ID！'); window.location.href='d_appointment-records.php';</script>";
    exit;
}
$appointment_id = intval($_GET['id']);

// 查詢該帳號的醫生 ID
$sql_doctor = "SELECT d.doctor_id FROM user u JOIN doctor d ON u.user_id = d.user_id WHERE u.account = ?";
$stmt_doctor = mysqli_prepare($link, $sql_doctor);
mysqli_stmt_bind_param($stmt_doctor, "s", $帳號);
mysqli_stmt_execute($stmt_doctor);
$result_doctor = mysqli_stmt_get_result($stmt_doctor);
if ($doctor = mysqli_fetch_assoc($result_doctor)) {
    $doctor_id = $doctor['doctor_id'];
} else {
    echo "<script>alert('無法找到您的醫生資料！'); window.location.href='d_appointment-records.php';</script>";
    exit;
}

// **處理表單提交**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_ids'])) {
    $item_ids = $_POST['item_ids'];
    $note_d = isset($_POST['note_d']) ? trim($_POST['note_d']) : '';

    // 確保 `note_d` 正確傳遞
    error_log("Received note_d: " . $note_d);

    if (empty($item_ids)) {
        echo "<script>alert('請至少選擇一項診療項目！');</script>";
    } else {
        $success = false;

        foreach ($item_ids as $item_id) {
            $item_id = intval($item_id);

            // **插入診療紀錄**
            $sql_insert_medicalrecord = "INSERT INTO medicalrecord (appointment_id, item_id, note_d, created_at) VALUES (?, ?, ?, NOW())";
            $stmt_insert_medicalrecord = mysqli_prepare($link, $sql_insert_medicalrecord);
            mysqli_stmt_bind_param($stmt_insert_medicalrecord, "iis", $appointment_id, $item_id, $note_d);

            if (!mysqli_stmt_execute($stmt_insert_medicalrecord)) {
                echo "<script>alert('診療紀錄新增失敗：" . mysqli_error($link) . "');</script>";
                exit;
            }
            $success = true;
        }

        if ($success) {
            echo "<script>alert('診療紀錄新增成功！'); window.location.href='d_appointment-records.php';</script>";
        }
    }
}
?>