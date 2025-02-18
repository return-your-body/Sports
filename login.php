<?php
session_start();
include "db.php"; // 連接資料庫

// 取得使用者輸入的帳號和密碼
$帳號 = trim($_POST["account"]);
$密碼 = trim($_POST["password"]);

// **🔹 檢查是否輸入帳號和密碼**
if ($帳號 == "" || $密碼 == "") {
    echo "<script>
            alert('請輸入帳號和密碼');
            window.location.href = 'login.php';
          </script>";
    exit;
}

// **🔹 防止 SQL 注入**
$帳號 = mysqli_real_escape_string($link, $帳號);
$密碼 = mysqli_real_escape_string($link, $密碼);

// **🔹 查詢 `user` 資料表，取得用戶資訊**
$SQL指令 = "
    SELECT u.*, g.grade 
    FROM `user` u
    JOIN `grade` g ON u.grade_id = g.grade_id
    WHERE u.account = '$帳號' AND u.password = '$密碼';
";
$ret = mysqli_query($link, $SQL指令) or die(mysqli_error($link));

if ($row = mysqli_fetch_assoc($ret)) {
    // **🔹 設置 Session**
    $_SESSION["帳號"] = $row["account"];
    $_SESSION["user_id"] = $row["user_id"];
    $_SESSION["等級"] = $row["grade"];
    $_SESSION['登入狀態'] = true;
    echo "<script>sessionStorage.setItem('登入狀態', 'true');</script>";

    $user_id = $row["user_id"];

    // **🔹 檢查 `logindata` 是否已經有該 `user_id` 的紀錄**
    $checkLoginQuery = "SELECT logindata FROM logindata WHERE user_id = ?";
    $stmt = mysqli_prepare($link, $checkLoginQuery);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($loginData = mysqli_fetch_assoc($result)) {
        // **🔹 如果 `logindata = 0`，且 `grade_id = 1`（一般使用者），則跳轉到 `u_profile.php`**
        if ($loginData['logindata'] == 0 && $row["grade_id"] == 1) {
            // **更新 `logindata` 為 1，然後跳轉**
            $updateLoginQuery = "UPDATE logindata SET logindata = 1 WHERE user_id = ?";
            $stmt = mysqli_prepare($link, $updateLoginQuery);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);

            header("Location: user/u_profile.php?帳號=$帳號");
            exit;
        }

        // **🔹 如果 `logindata > 0`，則累加一次 (`+1`)**
        $updateLoginQuery = "UPDATE logindata SET logindata = logindata + 1 WHERE user_id = ?";
        $stmt = mysqli_prepare($link, $updateLoginQuery);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
    } else {
        // **🔹 第一次登入，插入新紀錄 (`logindata = 1`)**
        $insertLoginQuery = "INSERT INTO logindata (user_id, logindata) VALUES (?, 1)";
        $stmt = mysqli_prepare($link, $insertLoginQuery);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        // **🔹 如果是一般使用者 (grade_id = 1)，則強制跳轉 `u_profile.php`**
        if ($row["grade_id"] == 1) {
            header("Location: user/u_profile.php?帳號=$帳號");
            exit;
        }
    }

    // **🔹 根據等級跳轉到不同的頁面**
    switch ($row["grade_id"]) {
        case "1":
            header("Location: user/u_index.php?帳號=$帳號"); // 一般使用者
            break;
        case "2":
            header("Location: doctor/d_index.php?帳號=$帳號"); // 治療師
            break;
        case "3":
            header("Location: helper/h_index.php?帳號=$帳號"); // 助手
            break;
        case "4":
            header("Location: admin/a_patient.php?帳號=$帳號"); // 管理員
            break;
        default:
            echo "<script>
                    alert('未定義的角色等級');
                    window.location.href = 'login.php';
                  </script>";
            exit;
    }
} else {
    // **🔹 登入失敗處理**
    echo "<script>
            alert('登入失敗，請檢查帳號或密碼。');
            window.location.href = 'index.html';
          </script>";
    exit;
}
?>
