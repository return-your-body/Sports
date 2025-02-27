<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

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


// 看診
require '../db.php';

// 確保使用者已登入
if (!isset($_SESSION["帳號"])) {
    echo "
    <script>alert('使用者未登入！'); window.location.href = 'login.php';</script>";
    exit;
}

$帳號 = $_SESSION["帳號"];

// 確保 `id` 存在
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "
    <script>alert('無法取得預約 ID！'); window.location.href = 'd_appointment-records.php';</script>";
    exit;
}
$appointment_id = intval($_GET['id']);

// 查詢該帳號的治療師 ID
$sql_doctor = "SELECT d.doctor_id, u.account, d.doctor AS name
    FROM user u
    JOIN doctor d ON u.user_id = d.user_id
    WHERE u.account = ?";
$stmt_doctor = mysqli_prepare($link, $sql_doctor);
mysqli_stmt_bind_param($stmt_doctor, "s", $帳號);
mysqli_stmt_execute($stmt_doctor);
$result_doctor = mysqli_stmt_get_result($stmt_doctor);
if ($doctor = mysqli_fetch_assoc($result_doctor)) {
    $doctor_id = $doctor['doctor_id'];
} else {
    echo "
    <script>alert('無法找到您的治療師資料！'); window.location.href = 'd_appointment-records.php';</script>";
    exit;
}

// 獲取完整的預約資訊，確保該預約是屬於此治療師
$sql_appointment = "SELECT
    a.appointment_id,
    p.name AS patient_name,
    CASE WHEN p.gender_id = 1 THEN '男' WHEN p.gender_id = 2 THEN '女' ELSE '無資料' END AS gender,
    IFNULL(DATE_FORMAT(p.birthday, '%Y-%m-%d'), '無資料') AS birthday,
    DATE_FORMAT(ds.date, '%Y-%m-%d') AS consultation_date,
    st.shifttime AS consultation_time,
    COALESCE(a.note, '無') AS note,
    a.created_at
    FROM appointment a
    LEFT JOIN people p ON a.people_id = p.people_id
    LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    LEFT JOIN shifttime st ON a.shifttime_id = st.shifttime_id
    WHERE a.appointment_id = ?
    AND ds.doctor_id = ?";

$stmt_appointment = mysqli_prepare($link, $sql_appointment);
mysqli_stmt_bind_param($stmt_appointment, "ii", $appointment_id, $doctor_id);
mysqli_stmt_execute($stmt_appointment);
$result_appointment = mysqli_stmt_get_result($stmt_appointment);

if (mysqli_num_rows($result_appointment) == 0) {
    echo "
    <script>alert('此預約不屬於您的患者！'); window.location.href = 'd_appointment-records.php';</script>";
    exit;
}
$appointment_data = mysqli_fetch_assoc($result_appointment);

// 獲取診療項目
$sql_items = "SELECT item_id, item, price FROM item";
$result_items = mysqli_query($link, $sql_items);

// 檢查是否已有診療紀錄
$sql_check_medicalrecord = "SELECT COUNT(*) FROM medicalrecord WHERE appointment_id = ?";
$stmt_check_medicalrecord = mysqli_prepare($link, $sql_check_medicalrecord);
mysqli_stmt_bind_param($stmt_check_medicalrecord, "i", $appointment_id);
mysqli_stmt_execute($stmt_check_medicalrecord);
mysqli_stmt_bind_result($stmt_check_medicalrecord, $record_exists);
mysqli_stmt_fetch($stmt_check_medicalrecord);
mysqli_stmt_close($stmt_check_medicalrecord);

if ($record_exists > 0) {
    echo "
    <script>alert('此預約已經有診療紀錄，無法重複新增！'); window.location.href = 'd_appointment-records.php';</script>";
    exit;
}

?>



<head>
    <!-- Site Title-->
    <title>治療師-看診</title>
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
    </style>
    <style>
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


        /* 看診 */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 50%;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #555;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
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

    <!--標題列-->
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
                                <!--Brand--><a class="brand-name" href="d_index.php"><img class="logo-default"
                                        src="images/logo-default-172x36.png" alt="" width="86" height="18"
                                        loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
                                        alt="" width="86" height="18" loading="lazy" /></a>
                            </div>
                        </div>
                        <div class="rd-navbar-nav-wrap">
                            <ul class="rd-navbar-nav">
                                <li class="rd-nav-item"><a class="rd-nav-link" href="d_index.php">首頁</a>
                                </li>

                                <li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="d_people.php">用戶資料</a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="d_appointment.php">預約</a>
                </li> -->
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
                                <li class="rd-nav-item active"><a class="rd-nav-link" href="#">紀錄</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="d_medical-record.php">看診紀錄</a>
                                        </li>
                                        <li class="rd-dropdown-item active"><a class="rd-dropdown-link"
                                                href="d_appointment-records.php">預約紀錄</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="d_change.php">變更密碼</a>
                                </li>
                                <!-- 登出按鈕 -->
                                <li class="rd-nav-item"><a class="rd-nav-link" href="javascript:void(0);"
                                        onclick="showLogoutBox()">登出</a>
                                </li>

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
                        <div class="rd-navbar-collapse-toggle" data-rd-navbar-toggle=".rd-navbar-collapse">
                            <span></span>
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
        <!--標題列-->

        <!--標題-->
        <div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
            <!-- Breadcrumbs-->
            <section class="breadcrumbs-custom breadcrumbs-custom-svg">
                <div class="container">
                    <!-- <p class="breadcrumbs-custom-subtitle">Ask us</p> -->
                    <p class="heading-1 breadcrumbs-custom-title">看診</p>
                    <ul class="breadcrumbs-custom-path">
                        <li><a href="d_index.php">首頁</a></li>
                        <li><a href="#">紀錄</a></li>
                        <li class="active">看診</li>
                    </ul>
                </div>
            </section>
        </div>
        <!--標題-->

        <!--看診-->
        <!-- **表單畫面** -->
        <div class="form-container">
            <h2>新增診療紀錄</h2>
            <form action="看診.php?id=<?php echo htmlspecialchars($_GET['id']); ?>" method="POST">
                <p>預約編號：<?php echo htmlspecialchars($appointment_data['appointment_id']); ?></p>
                <p>姓名：<?php echo htmlspecialchars($appointment_data['patient_name']); ?></p>
                <p>性別：<?php echo htmlspecialchars($appointment_data['gender']); ?></p>
                <p>生日：<?php echo htmlspecialchars($appointment_data['birthday']); ?></p>
                <p>看診日期：<?php echo htmlspecialchars($appointment_data['consultation_date']); ?></p>
                <p>看診時間：<?php echo htmlspecialchars($appointment_data['consultation_time']); ?></p>
                <p>備註：<?php echo htmlspecialchars($appointment_data['note']); ?></p>

                <label>診療項目：</label>
                <div class="checkbox-group">
                    <?php while ($row = mysqli_fetch_assoc($result_items)): ?>
                        <label>
                            <input type="checkbox" name="item_ids[]"
                                value="<?php echo htmlspecialchars($row['item_id']); ?>">
                            <?php echo htmlspecialchars($row['item']) . " (" . htmlspecialchars($row['price']) . "元)"; ?>
                        </label>
                    <?php endwhile; ?>
                </div>

                <label for="note_d">治療師備註：</label>
                <textarea name="note_d"
                    id="note_d"><?php echo isset($_POST['note_d']) ? htmlspecialchars($_POST['note_d']) : ''; ?></textarea>
                <br />
                <button type="submit">提交</button>
                <button type="button" onclick="window.location.href='d_appointment-records.php'">返回</button>
            </form>
        </div>

        <!--看診-->


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
                            <li><a href="d_change.php">變更密碼</a></li>
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

    <!-- Global Mailform Output-->
    <div class="snackbars" id="form-output-global"></div>
    <!-- Javascript-->
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>