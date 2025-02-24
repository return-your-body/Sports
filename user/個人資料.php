<?php
session_start(); // 啟用 Session

// 從 Session 中取得用戶帳號
$帳號 = $_SESSION["帳號"];

// 引入資料庫連接配置檔
include "../db.php";

// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 從表單中獲取提交的資料
    $name = trim($_POST['username']);        // 用戶姓名
    $gender = trim($_POST['gender']);        // 性別（男/女）
    $birthday = trim($_POST['userdate']);    // 出生日期
    $idcard = trim($_POST['useridcard']);    // 身分證字號（可選）
    $phone = trim($_POST['userphone']);      // 聯絡電話
    $address = trim($_POST['address']);      // 地址（可選）

    // 將性別轉換為數字（1: 男, 2: 女, 3:其他）
    $gender_id = ($gender === "男") ? 1 : (($gender === "女") ? 2 : 3);

    // 驗證性別是否正確
    if (!in_array($gender_id, [1, 2, 3])) {
        echo "<script>
                alert('性別選擇無效，請重新選擇！');
                window.history.back();
              </script>";
        exit;
    }

    // 驗證必填欄位是否已填寫
    if (empty($name) || empty($birthday) || empty($phone)) {
        echo "<script>
                alert('請填寫所有必填欄位！');
                window.history.back();
              </script>";
        exit;
    }

    // 查詢該帳號對應的 user_id
    $sql = "SELECT user_id FROM user WHERE account = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $帳號);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        // 如果查詢結果存在，獲取對應的 user_id
        $user = mysqli_fetch_assoc($result);
        $user_id = $user['user_id'];

        // 更新資料到 people 表 (已移除電子郵件欄位)
        $updateSql = "UPDATE people SET 
                        name = ?, 
                        gender_id = ?, 
                        birthday = ?, 
                        idcard = ?, 
                        phone = ?, 
                        address = ?
                      WHERE user_id = ?";
        
        // 準備 SQL 語句
        $updateStmt = mysqli_prepare($link, $updateSql);
        
        // 綁定參數
        mysqli_stmt_bind_param(
            $updateStmt,
            "sissssi", // s:字串, i:整數
            $name,      // 用戶姓名
            $gender_id, // 性別 ID
            $birthday,  // 出生日期
            $idcard,    // 身分證字號
            $phone,     // 聯絡電話
            $address,   // 地址
            $user_id    // 關聯的 user_id
        );

        // 執行更新語句並檢查是否成功
        if (mysqli_stmt_execute($updateStmt)) {
            // 如果更新成功，顯示成功訊息並跳轉回 u_profile.php
            echo "<script>
                    alert('個人資料更新成功！');
                    window.location.href = 'u_profile.php';
                  </script>";
        } else {
            // 如果更新失敗，顯示錯誤訊息並返回上一頁
            echo "<script>
                    alert('資料更新失敗，請稍後再試！');
                    window.history.back();
                  </script>";
        }
    } else {
        // 如果帳號找不到對應的 user_id，提示重新登入
        echo "<script>
                alert('找不到對應的帳號資料，請重新登入。');
                window.location.href = '../index.html';
              </script>";
    }
}

// 關閉資料庫連接
mysqli_close($link);
?>
