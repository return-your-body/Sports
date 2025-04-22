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

    // 關閉資料庫連接
    mysqli_close($link);
} else {
    echo "<script>
            alert('會話過期或資料遺失，請重新登入。');
            window.location.href = '../index.html';
          </script>";
    exit();
}


//看診紀錄

?>



<head>
    <!-- Site Title-->
    <title>助手-看診紀錄</title>
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


        /* 看診紀錄 */
        /* 保持表格容器全寬 */
        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        /* 表格樣式 */
        .table-responsive {
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            white-space: nowrap;
            /* 防止內容換行 */
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        /* 響應式設計（平板） */
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }

            th,
            td {
                padding: 8px;
                font-size: 14px;
            }
        }

        /* 響應式設計（手機） */
        @media (max-width: 480px) {

            th,
            td {
                padding: 6px;
                font-size: 12px;
            }
        }

        /* 讓搜尋框與筆數選擇在同一行，並靠右對齊 */
        .search-limit-container {
            display: flex;
            justify-content: flex-end;
            /* 內容靠右對齊 */
            align-items: center;
            gap: 15px;
            /* 設定搜尋框與筆數選擇之間的間距 */
            margin-bottom: 10px;
            flex-wrap: wrap;
            /* 確保在小螢幕時換行 */
        }

        /* 搜尋框 */
        .search-form {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .search-form input {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-form button {
            padding: 6px 12px;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.3s ease-in-out;
        }

        .search-form button:hover {
            background-color: #0056b3;
        }

        /* 筆數選擇 */
        .limit-selector {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* 分頁資訊 + 頁碼容器 */
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        /* 分頁資訊 (靠右對齊) */
        .pagination-info {
            font-size: 14px;
            color: #555;
            text-align: right;
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }

        /* 頁碼按鈕 (置中) */
        .pagination-container {
            display: flex;
            justify-content: center;
            flex-grow: 1;
            gap: 10px;
            margin-top: 10px;
        }

        .pagination-container a,
        .pagination-container strong {
            padding: 5px 10px;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #007bff;
        }

        .pagination-container strong {
            font-weight: bold;
            background-color: #007bff;
            color: white;
        }

        /* 響應式設計：小螢幕置中 */
        @media (max-width: 768px) {
            .pagination-wrapper {
                flex-direction: column;
                align-items: center;
            }

            .pagination-info {
                justify-content: center;
                text-align: center;
                width: 100%;
            }

            .pagination-container {
                justify-content: center;
                width: 100%;
            }
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
                                <!--Brand--><a class="brand-name" href="h_index.php"><img class="logo-default"
                                        src="images/logo-default-172x36.png" alt="" width="86" height="18"
                                        loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
                                        alt="" width="86" height="18" loading="lazy" /></a>
                            </div>
                        </div>
                        <div class="rd-navbar-nav-wrap">
                            <ul class="rd-navbar-nav">
                                <li class="rd-nav-item"><a class="rd-nav-link" href="h_index.php">首頁</a>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="h_people.php">用戶資料</a>
                                        </li>
                                    </ul>
                                </li>
                                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="h_appointment.php">預約</a></li> -->
                                <li class="rd-nav-item"><a class="rd-nav-link" href="#">班表</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="h_doctorshift.php">治療師班表</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="h_numberpeople.php">當天人數及時段</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="h_assistantshift.php">每月班表</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="h_leave.php">請假申請</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="h_leave-query.php">請假資料查詢</a>
                                        </li>
                                    </ul>
                                </li>
                                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="#">列印</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="h_print-receipt.php">列印收據</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="h_print-appointment.php">列印預約單</a>
                                        </li>
                                    </ul>
                                </li> -->

                                <li class="rd-nav-item active"><a class="rd-nav-link" href="#">紀錄</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item active"><a class="rd-dropdown-link"
                                                href="h_medical-record.php">看診紀錄</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="h_appointment-records.php">預約紀錄</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="h_change.php">變更密碼</a>
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
                                            href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"></a>
                                    </li>
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
            <!-- Breadcrumbs-->
            <section class="breadcrumbs-custom breadcrumbs-custom-svg">
                <div class="container">
                    <!-- <p class="breadcrumbs-custom-subtitle">Ask us</p> -->
                    <p class="heading-1 breadcrumbs-custom-title">看診紀錄</p>
                    <ul class="breadcrumbs-custom-path">
                        <li><a href="h_index.php">首頁</a></li>
                        <li><a href="#">紀錄</a></li>
                        <li class="active">看診紀錄</li>
                    </ul>
                </div>
            </section>
        </div>
        <!--標題-->

        <?php
        require '../db.php';
        session_start();
        $帳號 = $_SESSION['帳號'];

        // 取得分頁設定
        $records_per_page = isset($_GET['limit']) ? max((int) $_GET['limit'], 1) : 10;
        $page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
        $offset = ($page - 1) * $records_per_page;

        // 取得搜尋與篩選條件
        $search_name = isset($_GET['search_name']) ? mysqli_real_escape_string($link, $_GET['search_name']) : '';
        $selected_doctor = isset($_GET['doctor']) ? mysqli_real_escape_string($link, $_GET['doctor']) : '全部';
        // 取得篩選條件
        $selected_date = isset($_GET['date']) ? mysqli_real_escape_string($link, $_GET['date']) : date('Y-m-d'); // 預設今天
        
        // 處理 date 篩選條件
        if (empty($selected_date) || $selected_date === '全部') {
            $selected_date = ''; // 設為空，代表不要篩選日期
        }

        // 建立 WHERE 條件
        $where_clauses = ["u.grade_id = 2"]; // 只顯示醫生
        
        if ($selected_doctor !== '全部') {
            $where_clauses[] = "d.doctor = ?";
            $params[] = $selected_doctor;
            $types .= "s";
        }

        if (!empty($search_name)) {
            $where_clauses[] = "p.name LIKE ?";
            $params[] = "%{$search_name}%";
            $types .= "s";
        }

        // ✅ **只有當 selected_date 有值時才篩選日期**
        if (!empty($selected_date)) {
            $where_clauses[] = "ds.date = ?";
            $params[] = $selected_date;
            $types .= "s";
        }

        $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

        // 計算總數（分頁用）
        $count_stmt = $link->prepare("
    SELECT COUNT(DISTINCT a.appointment_id) AS total
    FROM medicalrecord m
    LEFT JOIN appointment a ON m.appointment_id = a.appointment_id
    LEFT JOIN people p ON a.people_id = p.people_id
    LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
    LEFT JOIN user u ON d.user_id = u.user_id
    $where_sql
");
        if (!empty($params)) {
            $count_stmt->bind_param($types, ...$params);
        }
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_records = $count_result->fetch_assoc()['total'];
        $total_pages = ($total_records > 0) ? ceil($total_records / $records_per_page) : 1;

        // 查詢資料
        $query = "
        SELECT 
            a.appointment_id,
            p.name AS patient_name,
            CASE 
                WHEN p.gender_id = 1 THEN '男' 
                WHEN p.gender_id = 2 THEN '女' 
                ELSE '無資料' 
            END AS gender,
            CASE 
                WHEN p.birthday IS NOT NULL 
                    THEN CONCAT(p.birthday, ' (', TIMESTAMPDIFF(YEAR, p.birthday, CURDATE()), '歲)') 
                ELSE '無資料' 
            END AS birthday_with_age,
            d.doctor AS doctor_name,
            DATE_FORMAT(ds.date, '%Y-%m-%d') AS consultation_date,
            CASE DAYOFWEEK(ds.date) 
                WHEN 1 THEN '星期日' WHEN 2 THEN '星期一' WHEN 3 THEN '星期二' 
                WHEN 4 THEN '星期三' WHEN 5 THEN '星期四' WHEN 6 THEN '星期五' 
                WHEN 7 THEN '星期六' 
            END AS consultation_weekday,
            st.shifttime AS consultation_time,
            a.note AS user_note,
    
            -- ✅ 只顯示最新一筆醫生備註（這樣就不會顯示 789;789）
            (
                SELECT m1.note_d 
                FROM medicalrecord m1 
                WHERE m1.appointment_id = a.appointment_id 
                ORDER BY m1.created_at DESC 
                LIMIT 1
            ) AS doctor_notes
    
        FROM appointment a
        LEFT JOIN people p ON a.people_id = p.people_id
        LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
        LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
        LEFT JOIN user u ON d.user_id = u.user_id
        LEFT JOIN shifttime st ON a.shifttime_id = st.shifttime_id
        -- ✅ 外層 LEFT JOIN 不用連 medicalrecord，因為備註用子查詢處理了
        $where_sql
        GROUP BY a.appointment_id, p.name, p.gender_id, p.birthday, d.doctor, ds.date, st.shifttime, st.shifttime_id, a.note
        ORDER BY ds.date DESC, st.shifttime_id DESC
        LIMIT ?, ?
    ";
    

        $params[] = $offset;
        $params[] = $records_per_page;
        $types .= "ii";

        $data_stmt = $link->prepare($query);
        $data_stmt->bind_param($types, ...$params);
        $data_stmt->execute();
        $result = $data_stmt->get_result();
        ?>

        <!-- HTML 內容 -->
        <section class="section section-lg bg-default text-center">
            <div class="container">
                <div class="search-limit-container">
                    <form method="GET" action="">

                        <!-- 日期選擇 -->
                        <input type="date" name="date" id="dateInput"
                            value="<?php echo (!empty($selected_date) && $selected_date !== '全部') ? htmlspecialchars($selected_date) : ''; ?>"
                            onchange="this.form.submit()">
                        <!-- 選擇醫生 -->
                        <select name="doctor" onchange="this.form.submit()">
                            <option value="全部" <?php echo ($selected_doctor === '全部') ? 'selected' : ''; ?>>全部</option>
                            <?php
                            $doctor_query = $link->query("SELECT d.doctor FROM doctor d JOIN user u ON d.user_id = u.user_id WHERE u.grade_id = 2");
                            while ($row = $doctor_query->fetch_assoc()) {
                                $doctor_name = $row['doctor'];
                                $selected = ($selected_doctor === $doctor_name) ? 'selected' : '';
                                echo "<option value='$doctor_name' $selected>$doctor_name</option>";
                            }
                            ?>
                        </select>

                        <!-- 搜尋姓名 -->
                        <input type="text" name="search_name" placeholder="請輸入搜尋姓名"
                            value="<?php echo htmlspecialchars($search_name); ?>">

                        <!-- 搜尋按鈕 -->
                        <button type="submit">搜尋</button>
                    </form>

                    <script>
                        function clearDate() {
                            document.getElementById('dateInput').value = '';
                            document.forms[0].submit();  // 自動提交表單
                        }
                    </script>


                    <div class="limit-selector">
                        <select id="limit" name="limit" onchange="updateLimit()">
                            <?php
                            $limits = [3, 5, 10, 20, 50, 100];
                            foreach ($limits as $limit) {
                                $selected = ($limit == $records_per_page) ? "selected" : "";
                                echo "<option value='$limit' $selected>$limit 筆/頁</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <script>
                    function updateLimit() {
                        var limit = document.getElementById("limit").value;
                        var searchName = document.querySelector("input[name='search_name']").value;
                        var selectedDoctor = document.querySelector("select[name='doctor']").value;

                        window.location.href = "?limit=" + limit + "&page=1&search_name=" + encodeURIComponent(searchName) + "&doctor=" + encodeURIComponent(selectedDoctor);
                    }
                </script>

                <div class="table-container">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>姓名</th>
                                    <th>性別</th>
                                    <th>生日 (年齡)</th>
                                    <th>看診日期 (星期)</th>
                                    <th>看診時間</th>
                                    <th>治療師</th>
                                    <th>使用者備註</th>
                                    <th>醫生備註</th>
                                    <th>選項</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($row['birthday_with_age']); ?></td>
                                        <td><?php echo htmlspecialchars($row['consultation_date']) . " (" . htmlspecialchars($row['consultation_weekday']) . ")"; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['consultation_time']); ?></td>
                                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_note']); ?></td>
                                        <td><?php echo htmlspecialchars($row['doctor_notes']); ?></td>
                                        <td>
                                            <a href="h_print-receipt.php?id=<?php echo $row['appointment_id']; ?>"
                                                target="_blank">
                                                <button type="button">列印收據</button>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <!-- 分頁資訊 + 頁碼 -->
                        <div class="pagination-wrapper">
                            <!-- 分頁資訊 (靠右) -->
                            <div class="pagination-info">
                                第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁（總共
                                <strong><?php echo $total_records; ?></strong> 筆資料）
                            </div>

                            <!-- 頁碼按鈕 (置中) -->
                            <div class="pagination-container">
                                <?php if ($page > 1): ?>
                                    <a
                                        href="?page=<?php echo $page - 1; ?>&limit=<?php echo $records_per_page; ?>&search_name=<?php echo urlencode($search_name); ?>">上一頁</a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <strong><?php echo $i; ?></strong>
                                    <?php else: ?>
                                        <a
                                            href="?page=<?php echo $i; ?>&limit=<?php echo $records_per_page; ?>&search_name=<?php echo urlencode($search_name); ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a
                                        href="?page=<?php echo $page + 1; ?>&limit=<?php echo $records_per_page; ?>&search_name=<?php echo urlencode($search_name); ?>">下一頁</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
        </section>


        <!--看診紀錄-->

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
                            <li><a href="h_index.php">首頁</a></li>
                            <li><a href="h_people.php">用戶資料</a></li>
                            <!-- <li><a href="h_appointment.php">預約</a></li> -->
                            <li><a href="h_numberpeople.php">當天人數及時段</a></li>
                            <li><a href="h_doctorshift.php">治療師班表時段</a></li>
                            <li><a href="h_assistantshift.php">每月班表</a></li>
                            <li><a href="h_leave.php">請假申請</a></li>
                            <li><a href="h_leave-query.php">請假資料查詢</a></li>
                            <li><a href="h_medical-record.php">看診紀錄</a></li>
                            <li><a href="h_appointment-records.php">預約紀錄</a></li>
                            <li><a href="h_change.php">變更密碼</a></li>
                            <!-- <li><a href="h_print-receipt.php">列印收據</a></li>
              <li><a href="h_print-appointment.php">列印預約單</a></li> -->

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