<?php
session_start();
include "db.php"; // 假設你有一個 db.php 檔案用來建立資料庫連接

// 從表單中獲取帳號和密碼
$帳號 = $_POST["name"];
$密碼 = $_POST["passwd"];

// 移除空白字符
$帳號 = trim($帳號);
$密碼 = trim($密碼);

// 驗證帳號是否輸入
if ($帳號 == "") {
    echo "<script>
            alert('帳號未輸入');
            window.location.href = 'login.php';
        </script>";
    exit;
}

// 驗證密碼是否輸入
if ($密碼 == "") {
    echo "<script>
            alert('密碼未輸入');
            window.location.href = 'login.php';
        </script>";
    exit;
}

// 防止SQL注入
$帳號 = mysqli_real_escape_string($link, $帳號);
$密碼 = mysqli_real_escape_string($link, $密碼); // 建議對密碼也做相同處理

// 構造SQL查詢語句，根據帳號查找用戶
$SQL指令 = "SELECT * FROM `user` WHERE `name` = '$帳號' AND `password` = '$密碼';";
$ret = mysqli_query($link, $SQL指令) or die(mysqli_error($link));

// 檢查查詢結果
if ($row = mysqli_fetch_assoc($ret)) {
    // 成功登入，設置 session 變數
    $_SESSION["帳號"] = $row["name"]; // 假設資料庫的帳號字段是 `name`
    $_SESSION["姓名"] = $row["username"]; // 假設資料庫的姓名字段是 `username`
    $_SESSION["電子郵件"] = $row["email"]; // 假設資料庫的電子郵件字段是 `email`
    $_SESSION["登入狀態"] = true; // 設定登入狀態
    $_SESSION['is_logged_in'] = true; // 設置登入狀態
    echo "<script>sessionStorage.setItem('is_logged_in', 'true');</script>";
    // 根據使用者等級跳轉到不同頁面
    if ($row["grade"] == "0") {
        header("Location: u_profile.php?帳號=$帳號"); // 使用者
    } elseif ($row["grade"] == "1") {
        header("Location: d_profile.php?帳號=$帳號"); // 醫生
    } elseif ($row["grade"] == "2") {
        header("Location: n_profile.php?帳號=$帳號"); // 護士
    } elseif ($row["grade"] == "3") {
        header("Location: c_profile.php?帳號=$帳號"); // 管理者
    } else {
        echo "<script>
                alert('無效的使用者等級。');
                window.location.href = 'register.php';
              </script>";
    }
    exit; // 確保跳轉後停止執行其他代碼
} else {
    // 登入失敗處理
    echo "<script>
            alert('登入失敗，請檢查帳號或密碼。');
            window.location.href = 'login.php';
          </script>";
    exit;
}

// 關閉資料庫連接
mysqli_close($link);
?>
