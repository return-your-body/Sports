<?php
// 啟動 Session
session_start();

// 引入資料庫連線
include "db.php";

// 從 POST 獲取表單數據
$帳號 = $_POST["account"]; // 使用者帳號
$密碼 = $_POST["passwd"]; // 使用者密碼
$電子郵件 = $_POST["email"]; // 使用者電子郵件

// 驗證表單輸入
// if ($帳號 == "") {
//     echo "<script>
//             alert('帳號未輸入');
//             window.location.href = 'index.html';
//         </script>";
//     exit();
// }
// if ($密碼 == "") {
//     echo "<script>
//             alert('密碼未輸入');
//             window.location.href = 'index.html';
//         </script>";
//     exit();
// }

// 查詢「使用者」對應的 grade_id
$SQL查詢使用者等級 = "SELECT grade_id FROM `grade` WHERE grade = '使用者'";
$result = mysqli_query($link, $SQL查詢使用者等級);

if ($row = mysqli_fetch_assoc($result)) {
    $使用者等級ID = $row['grade_id']; // 取得「使用者」的 grade_id
} else {
    echo "<script>
            alert('系統錯誤，無法取得使用者等級。');
            window.location.href = 'index.html';
        </script>";
    exit();
}

// 檢查帳號是否已存在
$SQL檢查帳號 = "SELECT COUNT(*) as cnt FROM `user` WHERE `account` = '$帳號'";
$result = mysqli_query($link, $SQL檢查帳號);
$row = mysqli_fetch_assoc($result);

if ($row['cnt'] > 0) {
    // 如果帳號已存在，提示用戶
    echo "<script>
            alert('帳號已存在，請直接登入。');
            window.location.href = 'index.html';
        </script>";
    exit();
}

// 插入新使用者資料，使用動態查詢的 grade_id
$SQL插入使用者 = "INSERT INTO `user` (`account`, `password`, `grade_id`) 
                   VALUES ('$帳號', '$密碼', '$使用者等級ID')";

if (mysqli_query($link, $SQL插入使用者)) {
    // 取得剛插入的 user_id
    $新使用者ID = mysqli_insert_id($link);
    
    // 插入對應的電子郵件到 people 資料表
    $SQL插入人員資料 = "INSERT INTO `people` (`user_id`, `email`) 
                        VALUES ('$新使用者ID', '$電子郵件')";
    
    if (mysqli_query($link, $SQL插入人員資料)) {
        // 全部操作成功
        echo "<script>
                alert('註冊成功！請重新登入。');
                window.location.href = 'index.html';
            </script>";
    } else {
        // 插入 people 資料失敗
        echo "<script>
                alert('註冊失敗(插入 people 資料失敗)，請稍後再試！');
                window.location.href = 'index.html';
            </script>" . mysqli_error($link);
    }
} else {
    // 註冊失敗
    echo "<script>
            alert('註冊失敗，請稍後再試！');
            window.location.href = 'index.html';
        </script>" . mysqli_error($link);
}
?>
