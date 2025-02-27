<?php
session_start();

if (!isset($_SESSION["登入狀態"])) {
    header("Location: ../index.html");
    exit;
}

// 防止頁面被瀏覽器緩存
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

// 檢查 "帳號" 是否存在於 $_SESSION 中
if (isset($_SESSION["帳號"])) {
    // 獲取用戶帳號
    $帳號 = $_SESSION['帳號'];

    // 資料庫連接
    require '../db.php';
    // 接收搜尋參數
    $search_name = isset($_GET['search_name']) ? mysqli_real_escape_string($link, trim($_GET['search_name'])) : '';
    // 查詢該帳號的詳細資料
    $sql = "SELECT user.account, doctor.doctor AS name 
            FROM user 
            JOIN doctor ON user.user_id = doctor.user_id 
            WHERE user.account = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $帳號);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // 抓取對應姓名
        $row = mysqli_fetch_assoc($result);
        $姓名 = $row['name'];
        $帳號名稱 = $row['account'];

        // 顯示帳號和姓名
        // echo "歡迎您！<br>";
        // echo "帳號名稱：" . htmlspecialchars($帳號名稱) . "<br>";
        // echo "姓名：" . htmlspecialchars($姓名);
        // echo "<script>
        //   alert('歡迎您！\\n帳號名稱：{$帳號名稱}\\n姓名：{$姓名}');
        // </script>";
    } else {
        // 如果資料不存在，提示用戶重新登入
        echo "<script>
                alert('找不到對應的帳號資料，請重新登入。');
                window.location.href = '../index.html';
              </script>";
        exit();
    }


} else {
    echo "<script>
            alert('會話過期或資料遺失，請重新登入。');
            window.location.href = '../index.html';
          </script>";
    exit();
}

// 歷史紀錄
require '../db.php'; // 引入資料庫連線

// 檢查是否有 `id` 參數，確保有正確的 people_id
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<p>缺少必要的參數。</p>";
    exit;
}

$people_id = intval($_GET['id']); // 取得 URL 參數中的 people_id

// 查詢使用者基本資訊
$query_user = "
SELECT p.name, g.gender, p.birthday 
FROM people p
LEFT JOIN gender g ON p.gender_id = g.gender_id
WHERE p.people_id = ?";
$stmt_user = mysqli_prepare($link, $query_user);
mysqli_stmt_bind_param($stmt_user, "i", $people_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);

if ($row_user = mysqli_fetch_assoc($result_user)) {
    $user_name = $row_user['name'];
    $user_gender = $row_user['gender'] ?? '未知';
    $user_birthday = $row_user['birthday'] ?? '未知';
} else {
    echo "<p>找不到使用者資料。</p>";
    exit;
}

// 查詢該使用者的歷史預約紀錄
$query_history = "
SELECT 
a.appointment_id,
ds.date AS appointment_date,
st.shifttime AS shifttime,
COALESCE(a.note, '無備註') AS note
FROM appointment a
LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
LEFT JOIN shifttime st ON a.shifttime_id = st.shifttime_id
WHERE a.people_id = ?
ORDER BY ds.date DESC, st.shifttime ASC
LIMIT 50";
$stmt_history = mysqli_prepare($link, $query_history);
mysqli_stmt_bind_param($stmt_history, "i", $people_id);
mysqli_stmt_execute($stmt_history);
$result_history = mysqli_stmt_get_result($stmt_history);
?>


<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <!-- Site Title-->
    <title>運動筋膜放鬆</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css2?family=Exo:wght@300;400;500;600;700&amp;display=swap">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* 彈窗樣式 */
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            text-align: center;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .btn {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .close-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .close-btn:hover {
            background-color: #0056b3;
        }



        .ie-panel {
            display: none;
            background: #212121;
            padding: 10px 0;
            box-shadow: 3px 3px 5px 0 rgba(0, 0, 0, .3);
            clear: both;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        html.ie-10 .ie-panel,
        html.lt-ie-10 .ie-panel {
            display: block;
        }

        /* 登出確認視窗 - 初始隱藏 */
        .logout-box {
            display: none;
            /* 預設隱藏 */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            /* 半透明背景 */
            justify-content: center;
            /* 水平置中 */
            align-items: center;
            /* 垂直置中 */
            z-index: 1000;
            /* 保證在最上層 */
        }

        /* 彈出視窗內容 */
        .logout-dialog {
            background: #fff;
            padding: 30px 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 280px;
            font-family: Arial, sans-serif;
        }

        /* 彈出視窗內文字 */
        .logout-dialog p {
            margin-bottom: 20px;
            font-size: 18px;
            color: #333;
        }

        /* 按鈕樣式 */
        .logout-dialog button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            background-color: #333;
            color: #fff;
            transition: background 0.3s ease;
        }

        .logout-dialog button:hover {
            background-color: #555;
        }

        .button-shadow {
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
        }

        /* 歷史資料(預約+看診紀錄) */

        /* 基本樣式 */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            white-space: nowrap;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        p {
            text-align: center;
            margin-top: 20px;
            color: #555;
        }

        /* 彈跳視窗樣式 */
        #popup {
            display: none;
            position: fixed;
            top: 50%;
            /* 設為 50% 確保視窗在正中央 */
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            /* 確保彈窗在最上層 */
            width: 50%;
            /* 設定適當的寬度 */
            max-width: 600px;
            /* 最大寬度 */
            text-align: center;
        }

        /* 遮罩背景 */
        #popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            /* 半透明黑色背景 */
            z-index: 999;
            /* 遮罩層應低於 popup，但高於其他元素 */
        }


        /* 按鈕樣式 */
        button {
            padding: 10px 15px;
            font-size: 14px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* 響應式設計 */
        @media (max-width: 768px) {
            table {
                width: 100%;
                font-size: 14px;
            }

            th,
            td {
                padding: 8px;
                font-size: 12px;
                white-space: normal;
            }
        }

        @media (max-width: 480px) {
            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 6px;
            }

            button {
                font-size: 12px;
                padding: 8px 10px;
            }
        }



        /* 聯絡我們 */
        .custom-link {
            color: rgb(246, 247, 248);
            /* 設定超連結顏色 */
            text-decoration: none;
            /* 移除超連結的下劃線 */
        }

        .custom-link:hover {
            color: #0056b3;
            /* 滑鼠懸停時的顏色，例如深藍色 */
            text-decoration: underline;
            /* 懸停時增加下劃線效果 */
        }
    </style>
</head>

<body>
    <div class="ie-panel"><a href="http://windows.microsoft.com/en-US/internet-explorer/"><img
                src="images/ie8-panel/warning_bar_0000_us.jpg" height="42" width="820"
                alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today."></a>
    </div>
    <!-- Page preloader-->
    <div class="page-loader">
        <div class="preloader-inner">
            <svg class="preloader-svg" width="200" height="200" viewbox="0 0 100 100">
                <polyline class="line-cornered stroke-still" points="0,0 100,0 100,100" stroke-width="10" fill="none">
                </polyline>
                <polyline class="line-cornered stroke-still" points="0,0 0,100 100,100" stroke-width="10" fill="none">
                </polyline>
                <polyline class="line-cornered stroke-animation" points="0,0 100,0 100,100" stroke-width="10"
                    fill="none">
                </polyline>
                <polyline class="line-cornered stroke-animation" points="0,0 0,100 100,100" stroke-width="10"
                    fill="none">
                </polyline>
            </svg>
        </div>
    </div>
    <!-- Page-->
    <div class="page">
        <header class="section page-header">
            <!-- RD Navbar-->
            <div class="rd-navbar-wrap rd-navbar-centered">
                <nav class="rd-navbar" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed"
                    data-md-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-fixed" data-xl-layout="rd-navbar-static"
                    data-xxl-layout="rd-navbar-static" data-xxxl-layout="rd-navbar-static"
                    data-lg-device-layout="rd-navbar-fixed" data-xl-device-layout="rd-navbar-static"
                    data-xxl-device-layout="rd-navbar-static" data-xxxl-device-layout="rd-navbar-static"
                    data-stick-up-offset="1px" data-sm-stick-up-offset="1px" data-md-stick-up-offset="1px"
                    data-lg-stick-up-offset="1px" data-xl-stick-up-offset="1px" data-xxl-stick-up-offset="1px"
                    data-xxx-lstick-up-offset="1px" data-stick-up="true">
                    <div class="rd-navbar-inner">
                        <!-- RD Navbar Panel-->
                        <div class="rd-navbar-panel">
                            <!-- RD Navbar Toggle-->
                            <button class="rd-navbar-toggle"
                                data-rd-navbar-toggle=".rd-navbar-nav-wrap"><span></span></button>
                            <!-- RD Navbar Brand-->
                            <div class="rd-navbar-brand">
                                <!--Brand--><a class="brand-name" href="d_index.html"><img class="logo-default"
                                        src="images/logo-default-172x36.png" alt="" width="86" height="18"
                                        loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
                                        alt="" width="86" height="18" loading="lazy" /></a>
                            </div>
                        </div>
                        <div class="rd-navbar-nav-wrap">
                            <ul class="rd-navbar-nav">
                                <li class="rd-nav-item"><a class="rd-nav-link" href="d_index.php">首頁</a></li>

                                <li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item active"><a class="rd-dropdown-link"
                                                href="d_people.php">用戶資料</a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- <li class="rd-nav-item active"><a class="rd-nav-link" href="d_appointment.php">預約</a></li> -->

                                <li class="rd-nav-item"><a class="rd-nav-link" href="#">班表</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="d_doctorshift.php">每月班表</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="d_numberpeople.php">當天人數及時段</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="d_leave.php">請假申請</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="d_leave-query.php">請假資料查詢</a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="rd-nav-item"><a class="rd-nav-link" href="#">紀錄</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="d_medical-record.php">看診紀錄</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="d_appointment-records.php">預約紀錄</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="d_change.php">變更密碼</a>
                                </li>
                                <!-- 登出按鈕 -->
                                <li class="rd-nav-item"><a class="rd-nav-link" href="javascript:void(0);"
                                        onclick="showLogoutBox()">登出</a></li>

                                <!-- 自訂登出確認視窗 -->
                                <div id="logoutBox" class="logout-box">
                                    <div class="logout-dialog">
                                        <p>你確定要登出嗎？</p>
                                        <button onclick="confirmLogout()">確定</button>
                                        <button class="button-shadow" onclick="hideLogoutBox()">取消</button>
                                    </div>
                                </div>

                                <script>
                                    // 顯示登出確認視窗
                                    function showLogoutBox() {
                                        document.getElementById('logoutBox').style.display = 'flex';
                                    }

                                    // 確認登出邏輯
                                    function confirmLogout() {
                                        // 清除登入狀態
                                        sessionStorage.removeItem('登入狀態');
                                        // 跳轉至登出處理頁面
                                        window.location.href = '../logout.php';
                                    }

                                    // 隱藏登出確認視窗
                                    function hideLogoutBox() {
                                        document.getElementById('logoutBox').style.display = 'none';
                                    }
                                </script>

                            </ul>
                        </div>
                        <div class="rd-navbar-collapse-toggle" data-rd-navbar-toggle=".rd-navbar-collapse"><span></span>
                        </div>
                        <!-- <div class="rd-navbar-aside-right rd-navbar-collapse">
              <div class="rd-navbar-social">
                <div class="rd-navbar-social-text">Follow us</div>
                <ul class="list-inline">
                  <li><a class="icon novi-icon icon-default icon-custom-facebook"
                      href="https://www.facebook.com/ReTurnYourBody/"></a>
                  </li>
                  <li><a class="icon novi-icon icon-default icon-custom-linkedin"
                      href="https://line.me/R/ti/p/@888tnmck?oat_content=url&ts=10041434"></a>
                  </li>
                  <li><a class="icon novi-icon icon-default icon-custom-instagram"
                      href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"></a></li>
                </ul>
              </div>
            </div> -->
                        <?php
                        echo "歡迎 ~ ";
                        // 顯示姓名
                        echo $姓名;
                        ?>
                        <a href="#" id="clock-btn">🕒 打卡</a>

                        <!-- 打卡彈跳視窗 -->
                        <div id="clock-modal" class="modal">
                            <div class="modal-content">
                                <span class="close">&times;</span>
                                <h4>上下班打卡</h4>
                                <p id="clock-status">目前狀態: 查詢中...</p>
                                <button id="clock-in-btn">上班打卡</button>
                                <button id="clock-out-btn" disabled>下班打卡</button>
                            </div>
                        </div>

                        <style>
                            .modal {
                                display: none;
                                position: fixed;
                                z-index: 1000;
                                left: 0;
                                top: 0;
                                width: 100%;
                                height: 100%;
                                background-color: rgba(0, 0, 0, 0.4);
                            }

                            .modal-content {
                                background-color: white;
                                margin: 15% auto;
                                padding: 20px;
                                width: 300px;
                                border-radius: 10px;
                                text-align: center;
                            }

                            .close {
                                float: right;
                                font-size: 24px;
                                cursor: pointer;
                            }
                        </style>

                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                        <script>
                            $(document).ready(function () {
                                let doctorId = 1; // 假設目前使用者的 doctor_id

                                // 打開彈跳視窗
                                $("#clock-btn").click(function () {
                                    $("#clock-modal").fadeIn();
                                    checkClockStatus();
                                });

                                $(".close").click(function () {
                                    $("#clock-modal").fadeOut();
                                });

                                function checkClockStatus() {
                                    $.post("檢查打卡狀態.php", { doctor_id: doctorId }, function (data) {
                                        let statusText = "尚未打卡";

                                        if (data.clock_in) {
                                            statusText = "已上班: " + data.clock_in;
                                            if (data.late) statusText += " <br>(遲到 " + data.late + ")";
                                            $("#clock-in-btn").prop("disabled", true);
                                            $("#clock-out-btn").prop("disabled", data.clock_out !== null);
                                        }

                                        if (data.clock_out) {
                                            statusText += "<br>已下班: " + data.clock_out;
                                            if (data.work_duration) statusText += "<br>總工時: " + data.work_duration;
                                        }

                                        $("#clock-status").html(statusText);
                                    }, "json").fail(function (xhr) {
                                        alert("發生錯誤：" + xhr.responseText);
                                    });
                                }


                                $("#clock-in-btn").click(function () {
                                    $.post("上班打卡.php", { doctor_id: doctorId }, function (data) {
                                        alert(data.message);
                                        checkClockStatus();
                                    }, "json").fail(function (xhr) {
                                        alert("發生錯誤：" + xhr.responseText);
                                    });
                                });

                                $("#clock-out-btn").click(function () {
                                    $.post("下班打卡.php", { doctor_id: doctorId }, function (data) {
                                        alert(data.message);
                                        checkClockStatus();
                                    }, "json").fail(function (xhr) {
                                        alert("發生錯誤：" + xhr.responseText);
                                    });
                                });
                            });

                        </script>

                    </div>
                </nav>
            </div>
        </header>

        <!--標題-->
        <div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">

            <section class="breadcrumbs-custom breadcrumbs-custom-svg">
                <div class="container">

                    <p class="heading-1 breadcrumbs-custom-title">歷史紀錄</p>
                    <ul class="breadcrumbs-custom-path">
                        <li><a href="d_index.php">首頁</a></li>
                        <li><a href="#">用戶資料</a></li>
                        <li class="active">歷史紀錄</li>
                    </ul>
                </div>
            </section>
        </div>

        <!-- 歷史紀錄 -->
        <?php
        require '../db.php'; // 引入資料庫連線
        
        // 檢查是否有 `id` 參數，確保有正確的 people_id
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            echo "<p>缺少必要的參數。</p>";
            exit;
        }

        $people_id = intval($_GET['id']); // 取得 URL 參數中的 people_id
        
        // 查詢使用者基本資訊
        $query_user = "
SELECT p.name, g.gender, p.birthday, p.idcard 
FROM people p
LEFT JOIN gender g ON p.gender_id = g.gender_id
WHERE p.people_id = ?";
        $stmt_user = mysqli_prepare($link, $query_user);
        mysqli_stmt_bind_param($stmt_user, "i", $people_id);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);

        if ($row_user = mysqli_fetch_assoc($result_user)) {
            $user_name = $row_user['name'];
            $user_gender = $row_user['gender'] ?? '無資料';
            $user_birthday = $row_user['birthday'] ?? '無資料';
            $user_idcard = $row_user['idcard'] ?? '無資料';
        } else {
            echo "<p>找不到使用者資料。</p>";
            exit;
        }


        // 查詢該使用者的歷史預約紀錄及詳細資料
        $query_history = "
SELECT 
    a.appointment_id,
    ds.date AS appointment_date,
    st.shifttime AS shifttime,
    COALESCE(a.note, '無備註') AS note,
    d.doctor AS doctor_name,
    GROUP_CONCAT(DISTINCT t.item ORDER BY t.item SEPARATOR ', ') AS treatment_items,
    SUM(t.price) AS total_price,
    COALESCE(m.note_d, '無備註') AS doctor_note,
    a.created_at
FROM appointment a
LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
LEFT JOIN shifttime st ON a.shifttime_id = st.shifttime_id
LEFT JOIN medicalrecord m ON a.appointment_id = m.appointment_id
LEFT JOIN item t ON m.item_id = t.item_id
WHERE a.people_id = ?
GROUP BY a.appointment_id
ORDER BY ds.date DESC, st.shifttime ASC";

        $stmt_history = mysqli_prepare($link, $query_history);
        mysqli_stmt_bind_param($stmt_history, "i", $people_id);
        mysqli_stmt_execute($stmt_history);
        $result_history = mysqli_stmt_get_result($stmt_history);
        ?>

        <!-- 歷史紀錄 -->
        <section class="section section-lg bg-default novi-bg novi-bg-img">
            <div class="container">
                <!-- <h3 style="text-align: center; color: #333; font-weight: bold;">歷史預約看診紀錄</h3> -->

                <!-- 使用者資訊卡片 -->
                <div style="max-width: 400px; margin: 20px auto; padding: 20px; 
            background: #ffffff; border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); text-align: center;">

                    <h3 style="margin-bottom: 15px; color: #007BFF; font-weight: bold;">使用者資訊</h3>

                    <div style="border-bottom: 1px solid #ddd; padding: 10px;">
                        <strong style="color: #333;">姓名：</strong>
                        <span style="color: #555;"><?php echo htmlspecialchars($user_name); ?></span>
                    </div>

                    <div style="border-bottom: 1px solid #ddd; padding: 10px;">
                        <strong style="color: #333;">性別：</strong>
                        <span style="color: #555;"><?php echo htmlspecialchars($user_gender); ?></span>
                    </div>

                    <div style="border-bottom: 1px solid #ddd; padding: 10px;">
                        <strong style="color: #333;">生日：</strong>
                        <span style="color: #555;"><?php echo htmlspecialchars($user_birthday); ?></span>
                    </div>

                    <div style="padding: 10px;">
                        <strong style="color: #333;">身分證：</strong>
                        <span style="color: #555;"><?php echo htmlspecialchars($user_idcard); ?></span>
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="d_people.php"
                            style="display: inline-block; padding: 10px 20px; font-size: 16px; color: white; background-color: #007BFF; border-radius: 5px; text-decoration: none;">返回前一頁</a>
                    </div>

                </div>

                <?php if (mysqli_num_rows($result_history) === 0): ?>
                    <p style="text-align: center; font-size: 18px;">目前沒有歷史資料。</p>
                <?php else: ?>
                    <?php while ($appointment = mysqli_fetch_assoc($result_history)): ?>
                        <div style="padding: 20px; margin-bottom: 20px; background: #f8f9fa; border-radius: 10px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 16px;">
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 10px;">預約日期</td>
                                    <td style="border: 1px solid #ddd; padding: 10px;">
                                        <?php echo htmlspecialchars($appointment['appointment_date']); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 10px;">預約時段</td>
                                    <td style="border: 1px solid #ddd; padding: 10px;">
                                        <?php echo htmlspecialchars($appointment['shifttime']); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 10px;">備註</td>
                                    <td style="border: 1px solid #ddd; padding: 10px;">
                                        <?php echo htmlspecialchars($appointment['note']); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 10px;">治療師姓名</td>
                                    <td style="border: 1px solid #ddd; padding: 10px;">
                                        <?php echo htmlspecialchars($appointment['doctor_name'] ?? '無資料'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 10px;">治療項目</td>
                                    <td style="border: 1px solid #ddd; padding: 10px;">
                                        <?php echo htmlspecialchars($appointment['treatment_items'] ?? '未看診'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 10px;">治療費用</td>
                                    <td style="border: 1px solid #ddd; padding: 10px;">
                                        <?php echo htmlspecialchars(number_format($appointment['total_price'] ?? 0)); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 10px;">治療師備註</td>
                                    <td style="border: 1px solid #ddd; padding: 10px;">
                                        <?php echo htmlspecialchars($appointment['doctor_note'] ?? '無資料'); ?>
                                    </td>
                                </tr>
                                <!-- <tr>
                                    <td style="border: 1px solid #ddd; padding: 10px;">建立時間</td>
                                    <td style="border: 1px solid #ddd; padding: 10px;">
                                        <?php echo htmlspecialchars($appointment['created_at'] ?? '無資料'); ?>
                                    </td>
                                </tr> -->
                            </table>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

            </div>
        </section>


        <!--頁尾-->
        <footer class="section novi-bg novi-bg-img footer-simple">
            <div class="container">
                <div class="row row-40">
                    <!-- <div class="col-md-4">
            <h4>關於我們</h4>
            <p class="me-xl-5">Pract is a learning platform for education and skills training. We provide you
              professional knowledge using innovative approach.</p>
          </div> -->
                    <div class="col-md-3">
                        <h4>快速連結</h4>
                        <ul class="list-marked">
                            <li><a href="d_index.php">首頁</a></li>
                            <li><a href="d_people.php">用戶資料</a></li>
                            <!-- <li><a href="d_appointment.php">預約</a></li> -->
                            <li><a href="d_numberpeople.php">當天人數及時段</a></li>
                            <li><a href="d_doctorshift.php">班表時段</a></li>
                            <li><a href="d_leave.php">請假申請</a></li>
                            <li><a href="d_leave-query.php">請假資料查詢</a></li>
                            <li><a href="d_medical-record.php">看診紀錄</a></li>
                            <li><a href="d_appointment-records.php">預約紀錄</a></li>
                            <!-- <li><a href="d_body-knowledge.php">身體小知識</a></li> -->
                        </ul>
                    </div>
                    <!-- <div class="col-md-5">
            <h4>聯絡我們</h4>
            <p>Subscribe to our newsletter today to get weekly news, tips, and special offers from our team on the
              courses we offer.</p>
            <form class="rd-mailform rd-form-boxed" data-form-output="form-output-global" data-form-type="subscribe"
              method="post" action="bat/rd-mailform.php">
              <div class="form-wrap">
                <input class="form-input" type="email" name="email" data-constraints="@Email @Required"
                  id="footer-mail">
                <label class="form-label" for="footer-mail">Enter your e-mail</label>
              </div>
              <button class="form-button linearicons-paper-plane"></button>
            </form>
          </div> -->
                </div>
                <!-- <p class="rights"><span>&copy;&nbsp;</span><span
            class="copyright-year"></span><span>&nbsp;</span><span>Pract</span><span>.&nbsp;All Rights
            Reserved.&nbsp;</span><a href="privacy-policy.html">Privacy Policy</a> <a target="_blank"
            href="https://www.mobanwang.com/" title="网站模板">网站模板</a></p> -->
            </div>
        </footer>
    </div>
    <!--頁尾-->
    </div>
    <!-- Global Mailform Output-->
    <div class="snackbars" id="form-output-global"></div>
    <!-- Javascript-->
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>