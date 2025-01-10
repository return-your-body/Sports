<?php
// 引入資料庫連線
require '../db.php';

// 如果不是 POST 請求，跳回表單頁面
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: a_doctorlistadd.php");
    exit;
}

// 接收表單資料
$doctor_id = isset($_POST['doctor_id']) ? (int) $_POST['doctor_id'] : 0;
$education = mysqli_real_escape_string($link, $_POST['education'] ?? '');
$current_position = mysqli_real_escape_string($link, $_POST['current_position'] ?? '');
$specialty = mysqli_real_escape_string($link, $_POST['specialty'] ?? '');
$certifications = mysqli_real_escape_string($link, $_POST['certifications'] ?? '');
$treatment_concept = mysqli_real_escape_string($link, $_POST['treatment_concept'] ?? '');
$created_at = mysqli_real_escape_string($link, $_POST['created_at'] ?? '');

// 如果必要欄位未填寫，返回表單頁面
if (!$doctor_id || !$education || !$current_position || !$specialty || !$certifications || !$treatment_concept) {
    echo "<script>
            alert('請填寫完整的表單資料。');
            window.location.href = 'a_doctorlistadd.php';
          </script>";
    exit;
}

// 處理圖片上傳
$image_name = '';
if (!empty($_FILES['image']['name'])) {
    $target_dir = "../uploads/";
    $image_name = time() . "_" . basename($_FILES['image']['name']); // 避免檔名重複
    $target_file = $target_dir . $image_name;

    // 確保目錄存在
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // 檢查並移動圖片
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        echo "<script>
                alert('圖片上傳失敗，請檢查目錄權限。');
                window.location.href = 'a_doctorlistadd.php';
              </script>";
        exit;
    }
}

// 新增資料到資料庫
$query = "INSERT INTO doctorprofile 
          (doctor_id, image, education, current_position, specialty, certifications, treatment_concept, created_at, updated_at) 
          VALUES ($doctor_id,$image_name ,$education ,$current_position , $specialty, $certifications, $treatment_concept, $created_at, NOW())";

$stmt = mysqli_prepare($link, $query);
if (!$stmt) {
    echo "<script>
            alert('資料庫查詢準備失敗：" . mysqli_error($link) . "');
            window.location.href = 'a_doctorlistadd.php';
          </script>";
    exit;
}

// 綁定參數並執行查詢
mysqli_stmt_bind_param($stmt, "issssss", $doctor_id, $image_name, $education, $current_position, $specialty, $certifications, $treatment_concept);
if (mysqli_stmt_execute($stmt)) {
    echo "<script>
            alert('醫生資料新增成功！');
            window.location.href = 'a_doctorlistadd.php';
          </script>";
} else {
    echo "<script>
            alert('資料新增失敗：" . mysqli_error($link) . "');
            window.location.href = 'a_doctorlistadd.php';
          </script>";
}

// 關閉連線
mysqli_stmt_close($stmt);
mysqli_close($link);
?>
