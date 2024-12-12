<?php
session_start();
include "db.php";

$姓名 = $_POST["Username"];
$帳號 = $_POST["name"];
$密碼 = $_POST["password"];
$電子郵件 = $_POST["email"];
$身分 = $_POST["identity"];

// 檢查是否已經存在該帳號
$SQL檢查 = "SELECT COUNT(*) as cnt FROM `user` WHERE `name` = '$帳號'";
$result = mysqli_query($link, $SQL檢查);
$row = mysqli_fetch_assoc($result);

if ($row['cnt'] > 0) {
    echo "<script>
            alert('帳號已存在，請選擇其他帳號。');
            window.location.href = 'register.php';
        </script>";
    exit();
}

if ($姓名 == "") {
    echo "<script>
            alert('姓名未輸入');
            window.location.href = 'register.php';
        </script>";
    exit; // 添加 exit; 確保不繼續執行
}
if ($帳號 == "") {
    echo "<script>
            alert('帳號未輸入');
            window.location.href = 'register.php';
        </script>";
    exit; // 添加 exit; 確保不繼續執行
}
if ($密碼 == "") {
    echo "<script>
            alert('密碼未輸入');
            window.location.href = 'register.php';
        </script>";
    exit; // 添加 exit; 確保不繼續執行
}
if ($電子郵件 == "") {
    echo "<script>
            alert('電子郵件未輸入');
            window.location.href = 'register.php';
        </script>";
    exit; // 添加 exit; 確保不繼續執行
}
if ($身分 == "") {
    echo "<script>
            alert('等級未輸入');
            window.location.href = 'register.php';
        </script>";
    exit; // 添加 exit; 確保不繼續執行
}

// 插入使用者資料到資料庫
$SQL指令 = "INSERT INTO `user` (`username`, `name`, `password`, `email`, `grade`) VALUES ('$姓名', '$帳號', '$密碼', '$電子郵件', '$身分');";

// 檢查是否成功執行插入指令
if ($ret = mysqli_query($link, $SQL指令)) {
    // 插入成功，查詢剛註冊的使用者，確認身分等級
    $SQL查詢 = "SELECT `grade` FROM `user` WHERE `name` = '$帳號';";  
    if ($result = mysqli_query($link, $SQL查詢)) {
        if ($row = mysqli_fetch_assoc($result)) {
            // 根據等級導向不同頁面
            if ($row["grade"] == "0") {  
                header("Location: login.php?帳號=$帳號"); // 0 使用者
            } elseif ($row["grade"] == "1") {
                header("Location: login.html?帳號=$帳號"); // 1 醫生
            } elseif ($row["grade"] == "2") {
                header("Location: login.php?帳號=$帳號"); // 2 護士
            } else {
                echo "<script>
                    alert('無效的使用者等級。');
                    window.location.href = 'register.php';
                </script>";
            }
            exit();
        } else {
            echo "<script>
                    alert('無法取得使用者資訊，請稍後再試！');
                    window.location.href = 'register.php';
                </script>";
        }
    } else {
        echo "<script>
                    alert('查詢使用者資料時發生錯誤');
                    window.location.href = 'register.php';
                </script>" . mysqli_error($link);
    }
} else {
    echo "<script>
                    alert('註冊失敗，請稍後再試！');
                    window.location.href = 'register.php';
                </script>" . mysqli_error($link);
}
?>