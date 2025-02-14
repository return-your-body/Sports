<?php
require '../db.php'; // 引入資料庫連線

header('Content-Type: application/json'); // 設定回應 JSON

$response = ["status" => "error", "message" => ""];

// 確保請求方式為 POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;
    $education = isset($_POST['education_final']) ? $_POST['education_final'] : '';
    $current_position = isset($_POST['current_position_final']) ? $_POST['current_position_final'] : '';
    $specialty = isset($_POST['specialty_final']) ? $_POST['specialty_final'] : '';
    $certifications = isset($_POST['certifications_final']) ? $_POST['certifications_final'] : '';

    // **處理圖片**
    $image_data = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_data = file_get_contents($image_tmp_name);
    }

    // **檢查 `doctor_id` 是否有效**
    if ($doctor_id === 0) {
        $response["message"] = "請選擇治療師";
        echo json_encode($response);
        exit;
    }

    // **準備 SQL 語句**
    $query = "INSERT INTO doctorprofile (doctor_id, education, current_position, specialty, certifications, image) 
              VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($link, $query);
    if (!$stmt) {
        $response["message"] = "SQL 準備失敗：" . mysqli_error($link);
        echo json_encode($response);
        exit;
    }

    // **綁定參數**
    mysqli_stmt_bind_param($stmt, "isssss", $doctor_id, $education, $current_position, $specialty, $certifications);

    // **處理 BLOB（圖片二進位數據）**
    if ($image_data !== null) {
        mysqli_stmt_send_long_data($stmt, 5, $image_data);
    }

    // **執行 SQL 並檢查是否成功**
    if (mysqli_stmt_execute($stmt)) {
        $response["status"] = "success";
        $response["message"] = "✅ 治療師資料新增成功";
    } else {
        $response["message"] = "❌ 錯誤：" . mysqli_error($link);
    }

    // **關閉連線**
    mysqli_stmt_close($stmt);
    mysqli_close($link);
} else {
    // **防止非 POST 請求**
    $response["message"] = "請使用 POST 方法";
}

echo json_encode($response);
?>
