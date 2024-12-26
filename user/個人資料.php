<?php
session_start();
include "../db.php";

// 獲取 POST 資料
$帳號 = $_SESSION["帳號"];
$姓名 = $_POST["username"];
$出生年月日 = $_POST["userdate"];
$身分證字號 = $_POST["useridcard"];
$電話 = $_POST["userphone"];
$電子郵件 = $_POST["useremail"];
$緊急聯絡人 = $_POST["useremergencycontact"];
$緊急聯絡人電話 = $_POST["useremergencycontactphone"];
$profilePicture = $_FILES['profilePicture'];

// 資料驗證
if (empty($出生年月日) || empty($身分證字號) || empty($電話) || empty($緊急聯絡人) || empty($緊急聯絡人電話)) {
    echo "<script>alert('必要資料為空，請填寫所有欄位！'); window.location.href = 'u_profile.php';</script>";
    exit;
}


// 檢查資料庫中是否已有該使用者的資料
$SQL檢查 = "SELECT * FROM people WHERE name = '$帳號'";
$result = mysqli_query($link, $SQL檢查);
$userData = mysqli_fetch_assoc($result);

if ($userData) {
    // 如果資料已存在，則執行更新
    $SQL指令 = "UPDATE people SET username='$姓名', birthday='$出生年月日', idcard='$身分證字號', 
                phone='$電話', email='$電子郵件', ecname='$緊急聯絡人', ecphone='$緊急聯絡人電話'";

    // 如果有上傳新圖片，更新 image 欄位
    if (!empty($profilePicture['tmp_name']) && $profilePicture['error'] == 0) {
        $imageData = addslashes(file_get_contents($profilePicture['tmp_name']));
        $SQL指令 .= ", image='$imageData'";
    }

    $SQL指令 .= " WHERE name='$帳號'";
} else {
    // 如果資料不存在，則插入新資料
    $SQL指令 = "INSERT INTO people (name, username, birthday, idcard, phone, email, ecname, ecphone";

    if (!empty($profilePicture['tmp_name']) && $profilePicture['error'] == 0) {
        $imageData = addslashes(file_get_contents($profilePicture['tmp_name']));
        $SQL指令 .= ", image) VALUES ('$帳號', '$姓名', '$出生年月日', '$身分證字號', '$電話', '$電子郵件', '$緊急聯絡人', '$緊急聯絡人電話', '$imageData')";
    } else {
        $SQL指令 .= ") VALUES ('$帳號', '$姓名', '$出生年月日', '$身分證字號', '$電話', '$電子郵件', '$緊急聯絡人', '$緊急聯絡人電話')";
    }
}

// 執行資料庫操作
if (mysqli_query($link, $SQL指令)) {
    header("Location: u_profile.php?帳號=$帳號&success=資料已成功修改");
} else {
    error_log("SQL Error: " . mysqli_error($link));
    header("Location: u_profile.php?帳號=$帳號&error=修改失敗，請稍後再試");
}

mysqli_close($link);
?>