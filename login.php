<?php
session_start();
include "db.php"; // 假設你有一個 db.php 檔案用來建立資料庫連接

// 從表單中獲取帳號和密碼
$帳號 = $_POST["account"];
$密碼 = $_POST["password"];

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
$密碼 = mysqli_real_escape_string($link, $密碼);

// 使用關聯式資料表進行查詢
$SQL指令 = "
    SELECT u.*, g.grade
    FROM `user` u
    JOIN `grade` g ON u.grade_id = g.grade_id
    WHERE u.account = '$帳號' AND u.password = '$密碼';
";
$ret = mysqli_query($link, $SQL指令) or die(mysqli_error($link));

// 檢查查詢結果
if ($row = mysqli_fetch_assoc($ret)) {
    // 成功登入，設置 session 變數
    $_SESSION["帳號"] = $row["account"]; // 用戶帳號
    $_SESSION["等級"] = $row["grade"]; // 用戶等級名稱（例如：醫生、護士等）
    $_SESSION['is_logged_in'] = true; // 設置登入狀態
    echo "<script>sessionStorage.setItem('is_logged_in', 'true');</script>";
    
    // 根據使用者等級跳轉到不同頁面
    switch ($row["grade_id"]) {
        case "1":
            header("Location: user/u_index.html?帳號=$帳號"); // 使用者頁面
            break;
        case "2":
            header("Location: doctor/d_index.html?帳號=$帳號"); // 醫生頁面
            break;
        case "3":
            header("Location: helper/h_index.html?帳號=$帳號"); // 助手頁面
            break;
        case "4":
            header("Location: admin/a_index.html?帳號=$帳號"); // 管理者頁面
            break;
        default:
            echo "<script>
                    alert('未定義的角色等級');
                    window.location.href = 'login.php';
                  </script>";
            exit;
    }
    exit; // 確保跳轉後停止執行其他代碼
} else {
    // 登入失敗處理
    echo "<script>
            alert('登入失敗，請檢查帳號或密碼。');
            window.location.href = 'index.html';
          </script>";
    exit;
}

// 關閉資料庫連接
mysqli_close($link);
?>
