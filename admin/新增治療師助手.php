<?php
session_start(); // 啟用 session

include "../db.php"; // 引入資料庫連線設定

// 檢查表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 接收表單資料並進行 SQL 防止注入
    $account = mysqli_real_escape_string($link, $_POST['account']); // 帳號
    $password = mysqli_real_escape_string($link, $_POST['pwd']);   // 密碼
    $name = mysqli_real_escape_string($link, $_POST['name']);      // 姓名
    $grade = $_POST['grade'];                                      // 等級 doctor / assistant

    // 設定 grade_id: 治療師 (2), 助手 (3)
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
        echo "<script>alert('帳號已存在，請使用其他帳號！'); window.history.back();</script>";
        exit;
    }

    // 新增帳號到 user 資料表
    $insert_user_query = "INSERT INTO user (account, password, grade_id) VALUES ('$account', '$password', $grade_id)";
    if (mysqli_query($link, $insert_user_query)) {
        $user_id = mysqli_insert_id($link);

        // 新增到 doctor 表
        $insert_doctor_query = "INSERT INTO doctor (user_id, doctor) VALUES ($user_id, '$name')";
        if (mysqli_query($link, $insert_doctor_query)) {
            $doctor_id = mysqli_insert_id($link);

            // 如果是治療師，才新增 doctorprofile
            if ($grade === 'doctor') {
                $insert_profile_query = "INSERT INTO doctorprofile (doctor_id) VALUES ($doctor_id)";
                if (!mysqli_query($link, $insert_profile_query)) {
                    echo "<script>alert('新增 doctorprofile 失敗: " . mysqli_error($link) . "'); window.history.back();</script>";
                    exit;
                }
            }

            // 成功跳轉
            ob_clean();
            echo "<script>
                    alert('新增成功！');
                    window.location.href = 'a_addhd.php';
                  </script>";
            exit;
        } else {
            echo "<script>alert('新增 doctor 資料失敗: " . mysqli_error($link) . "'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('新增 user 資料失敗: " . mysqli_error($link) . "'); window.history.back();</script>";
        exit;
    }
}

// 關閉資料庫連線
mysqli_close($link);
?>
