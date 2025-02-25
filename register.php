<?php
session_start();
include "db.php";
include "email2.php";

$帳號 = $_POST["account"];
$密碼 = $_POST["passwd"]; // 直接存入明文密碼
$電子郵件 = $_POST["email"];

// 檢查帳號是否已在 people 表註冊過
$SQL檢查帳號 = "
    SELECT COUNT(*) as cnt 
    FROM user u
    INNER JOIN people p ON u.user_id = p.user_id
    WHERE u.account = '$帳號'
";
$result = mysqli_query($link, $SQL檢查帳號);
$row = mysqli_fetch_assoc($result);

if ($row['cnt'] > 0) {
    echo json_encode(["status" => "error", "message" => "此帳號已完成註冊，請直接登入。"]);
    exit();
}


// 取得「使用者」的 grade_id
$SQL查詢使用者等級 = "SELECT grade_id FROM `grade` WHERE grade = '使用者'";
$result = mysqli_query($link, $SQL查詢使用者等級);
$row = mysqli_fetch_assoc($result);
$使用者等級ID = $row['grade_id'];

// 插入 `user` 表（不加密密碼）
$SQL插入使用者 = "INSERT INTO `user` (`account`, `password`, `grade_id`) 
                   VALUES ('$帳號', '$密碼', '$使用者等級ID')";

if (mysqli_query($link, $SQL插入使用者)) {
    $新使用者ID = mysqli_insert_id($link); // 取得 `user_id`
    $驗證碼 = rand(100000, 999999); // 產生 6 位數驗證碼

    // 存入 `email_verification` 表
    $SQL插入驗證碼 = "INSERT INTO email_verification (`user_id`, `token`) 
                       VALUES ('$新使用者ID', '$驗證碼')";
    if (mysqli_query($link, $SQL插入驗證碼)) {
        // 發送驗證信
        sendVerificationEmail($電子郵件, $驗證碼);
        echo json_encode(["status" => "success", "message" => "驗證信已發送", "user_id" => $新使用者ID, "email" => $電子郵件]);
    } else {
        // 如果 `email_verification` 存入失敗，就刪除 `user` 資料
        mysqli_query($link, "DELETE FROM user WHERE user_id = '$新使用者ID'");
        echo json_encode(["status" => "error", "message" => "註冊失敗，請稍後再試！"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "註冊失敗，請稍後再試！"]);
}
?>
