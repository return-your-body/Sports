<?php
session_start();
include "db.php";

// 接收並驗證表單資料
$name = trim($_POST['name']);
$gender = trim($_POST['gender']);
$phone = trim($_POST['phone']);
$date = trim($_POST['date']);
$time = trim($_POST['time']);
$note = trim($_POST['note']);

// 防呆驗證
// 防呆驗證
if (empty($name) || empty($gender) || empty($phone) || empty($appointment_date) || empty($appointment_time)) {
    echo "<script>
            alert('所有欄位都必須填寫！');
            window.location.href = 'h_appointment.php';
          </script>";
    exit;
}

if (!preg_match("/^[a-zA-Z0-9\u4e00-\u9fa5]+$/u", $name)) {
    die("姓名格式錯誤，請重新輸入。");
}

$gender = ['male', 'female', 'other'];
if (!in_array($gender, $gender)) {
    die("性別選擇錯誤，請重新選擇。");
}

if (!preg_match("/^09\d{2}(?!\d\1{7})\d{6}$/", $phone)) {
    die("電話格式錯誤，請重新輸入。");
}
if (!strtotime($date)) {
    die("日期格式錯誤，請重新選擇。");
}
if (!preg_match("/^(?:[01]\d|2[0-3]):[0-5]\d$/", $time)) {
    die("時間格式錯誤，請重新選擇。");
}
if (strlen($note) > 200) {
    die("備註內容過長，請重新輸入。");
}


// 搜尋是否已有相同的姓名、性別和電話
$search_sql = "SELECT people_id FROM people 
               WHERE name = '$name' 
               AND gender_id = (SELECT gender_id FROM gender WHERE gender = '$gender') 
               AND phone = '$phone'";

$result = mysqli_query($link, $search_sql);

if ($result && mysqli_num_rows($result) > 0) {
    // 若找到符合條件的使用者，取出 people_id
    $row = mysqli_fetch_assoc($result);
    $people_id = $row['people_id'];
} 
// else {
//     // 若未找到，插入新的使用者資料到 people 表
//     $insert_people_sql = "INSERT INTO people (name, gender_id, phone) 
//                           VALUES ('$name', (SELECT gender_id FROM gender WHERE gender = '$gender'), '$phone')";
//     if (mysqli_query($link, $insert_people_sql)) {
//         $people_id = mysqli_insert_id($link); // 取得插入後的 people_id
//     } else {
//         echo "<script>
//                 alert('新增使用者資料失敗，請稍後再試！');
//                 window.location.href = 'appointment.php';
//               </script>" . mysqli_error($link);
//         exit;
//     }
// }

// 插入預約資料到 appointment 表
$appointment_sql = "INSERT INTO appointment (people_id, appointment_date, appointment_time, note) 
                    VALUES ('$people_id', '$appointment_date', '$appointment_time', '$note')";

if (mysqli_query($link, $appointment_sql)) {
    echo "<script>
            alert('預約成功！請依預約時間前來！');
            window.location.href = 'h_appointment.php';
          </script>";
} else {
    echo "<script>
            alert('預約失敗，請稍後再試！');
            window.location.href = 'h_appointment.php';
          </script>" . mysqli_error($link);
}

?>