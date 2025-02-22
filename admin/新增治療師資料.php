<?php
require '../db.php'; // 引入資料庫連線

header('Content-Type: application/json'); // 設定回應 JSON

$response = ["status" => "error", "message" => ""];

// 確保請求方式為 POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;
    $education = isset($_POST['education_final']) ? trim($_POST['education_final']) : '';
    $current_position = isset($_POST['current_position_final']) ? trim($_POST['current_position_final']) : '';
    $specialty = isset($_POST['specialty_final']) ? trim($_POST['specialty_final']) : '';
    $certifications = isset($_POST['certifications_final']) ? trim($_POST['certifications_final']) : '';
    $treatment_concept = isset($_POST['treatment_concept_final']) ? trim($_POST['treatment_concept_final']) : '';

    // **處理圖片**
    $image_data = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_data = file_get_contents($image_tmp_name);
    }

    if ($doctor_id === 0) {
        $response["message"] = "請選擇治療師";
        echo json_encode($response);
        exit;
    }

    // **檢查資料是否已存在**
    $check_query = "SELECT doctor_id FROM doctorprofile WHERE doctor_id = ?";
    $check_stmt = mysqli_prepare($link, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $doctor_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    $exists = mysqli_stmt_num_rows($check_stmt) > 0;
    mysqli_stmt_close($check_stmt);

    if ($exists) {
        // **執行 UPDATE**
        $query = "UPDATE doctorprofile SET 
                  education = ?, 
                  current_position = ?, 
                  specialty = ?, 
                  certifications = ?, 
                  treatment_concept = ?";

        // **如果有圖片才更新 image**
        if ($image_data !== null) {
            $query .= ", image = ?";
        }

        $query .= " WHERE doctor_id = ?";

        $stmt = mysqli_prepare($link, $query);
        if ($image_data !== null) {
            mysqli_stmt_bind_param($stmt, "ssssssi", 
                $education, 
                $current_position, 
                $specialty, 
                $certifications, 
                $treatment_concept, 
                $image_data, 
                $doctor_id
            );
        } else {
            mysqli_stmt_bind_param($stmt, "sssssi", 
                $education, 
                $current_position, 
                $specialty, 
                $certifications, 
                $treatment_concept, 
                $doctor_id
            );
        }
    } else {
        // **執行 INSERT**
        $query = "INSERT INTO doctorprofile (doctor_id, education, current_position, specialty, certifications, treatment_concept, image) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "isssssb", $doctor_id, $education, $current_position, $specialty, $certifications, $treatment_concept, $image_data);
    }

    if (mysqli_stmt_execute($stmt)) {
        $response["status"] = "success";
        $response["message"] = $exists ? "✅ 治療師資料已更新" : "✅ 治療師資料已新增";
    } else {
        $response["message"] = "❌ 錯誤：" . mysqli_error($link);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
} else {
    $response["message"] = "請使用 POST 方法";
}

echo json_encode($response);
?>
