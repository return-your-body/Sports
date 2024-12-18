<?php
session_start(); // 啟用 session

include "../db.php"; // 引入資料庫連線設定

// 檢查表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 接收表單資料並進行 SQL 防止注入
    $account = mysqli_real_escape_string($link, $_POST['account']); // 帳號
    $password = mysqli_real_escape_string($link, $_POST['pwd']);   // 密碼
    $name = mysqli_real_escape_string($link, $_POST['name']);      // 姓名
    $grade = $_POST['grade'];                                      // 等級

    // 設定 grade_id: 醫生 (2), 助手 (3)
    $grade_id = ($grade === 'doctor') ? 2 : (($grade === 'assistant') ? 3 : 0);

    // 確保 grade 正確
    if ($grade_id === 0) {
        echo "<script>alert('無效的等級選擇！'); window.history.back();</script>";
        exit;
    }

    // 防呆：檢查帳號是否已存在
    $check_account_query = "SELECT * FROM user WHERE account = '$account'";
    $result = mysqli_query($link, $check_account_query);

    if (mysqli_num_rows($result) > 0) {
        // 帳號已存在
        echo "<script>alert('帳號已存在，請使用其他帳號！'); window.history.back();</script>";
        exit;
    }

    // 新增帳號和密碼到 user 資料表
    $insert_user_query = "INSERT INTO user (account, password, grade_id) VALUES ('$account', '$password', $grade_id)";
    if (mysqli_query($link, $insert_user_query)) {
        // 取得剛新增的 user_id
        $user_id = mysqli_insert_id($link);

        // 無論等級，將姓名寫入 doctor 資料表
        $insert_doctor_query = "INSERT INTO doctor (user_id, doctor) VALUES ($user_id, '$name')";
        if (!mysqli_query($link, $insert_doctor_query)) {
            echo "<script>alert('姓名寫入失敗: " . mysqli_error($link) . "'); window.history.back();</script>";
            exit;
        }

        // 使用 ob_clean 清理輸出，確保 script 正常執行
        ob_clean();
        echo "<script>
                alert('新增成功！');
                window.location.href = 'a_addhd.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('寫入 user 資料表失敗: " . mysqli_error($link) . "'); window.history.back();</script>";
        exit;
    }
}

// 關閉資料庫連線
mysqli_close($link);
?>
