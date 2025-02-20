<?php
session_start();

if (!isset($_SESSION["登入狀態"])) {
    // 如果未登入或會話過期，跳轉至登入頁面
    header("Location: ../index.html");
    exit;
}

// 防止頁面被瀏覽器緩存
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

// 引入資料庫連接檔案
require '../db.php';

// 檢查是否有 "帳號" 在 Session 中
if (isset($_SESSION["帳號"])) {
    // 獲取用戶帳號
    $帳號 = $_SESSION['帳號'];

    // 查詢用戶詳細資料
    $sql = "SELECT account, grade_id FROM user WHERE account = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $帳號);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $帳號名稱 = $row['account']; // 使用者帳號
        $等級 = $row['grade_id']; // 等級（例如 1: 治療師, 2: 護士, 等等）
    } else {
        // 如果查詢不到對應的資料
        echo "<script>
                alert('找不到對應的帳號資料，請重新登入。');
                window.location.href = '../index.html';
              </script>";
        exit();
    }
}
// 查詢尚未審核的請假申請數量
$pendingCountResult = $link->query(
    "SELECT COUNT(*) as pending_count 
     FROM leaves 
     WHERE is_approved IS NULL"
);
$pendingCount = $pendingCountResult->fetch_assoc()['pending_count'];

?>

<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <!-- Site Title-->
    <title>運動筋膜放鬆-治療師班表</title>
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
        /* 禁用按鈕樣式 */
        button.disabled {
            background-color: #ccc;
            /* 灰色背景 */
            color: #666;
            /* 灰色文字 */
            border: 1px solid #999;
            /* 灰色邊框 */
            cursor: not-allowed;
            /* 禁用鼠標樣式 */
            pointer-events: none;
            /* 禁止所有事件 */
        }

        /* 排班資訊樣式 */
        .shift-info {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }

        /* 預約按鈕樣式 */
        .shift-info button {
            background-color: #008CBA;
            color: white;
            border: none;
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
            border-radius: 3px;
        }

        .shift-info button:hover {
            background-color: #005f7f;
        }

        /* 無排班提示樣式 */
        .no-schedule {
            font-size: 18px;
            color: #aaa;
        }

        /* 當前日期高亮樣式 */
        .today {
            background-color: #ffeb3b;
        }

        /* 基本樣式調整 */
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table-custom th,
        .table-custom td {
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
            padding: 10px;
            word-wrap: break-word;
            /* 避免文字超出格子 */
        }

        /* 手機設備響應式設計 */
        @media (max-width: 768px) {
            .table-custom {
                display: block;
                /* 將表格改為 block，允許水平滾動 */
                overflow-x: auto;
                /* 加入水平滾動 */
                white-space: nowrap;
                /* 禁止自動換行 */
            }

            .table-custom th,
            .table-custom td {
                font-size: 12px;
                /* 縮小文字大小 */
                padding: 5px;
                /* 減小內邊距 */
            }

            .shift-info button {
                font-size: 10px;
                /* 縮小按鈕文字 */
                padding: 5px;
                /* 減小按鈕大小 */
            }
        }

        /* 極小設備 (手機) */
        @media (max-width: 480px) {

            .table-custom th,
            .table-custom td {
                font-size: 10px;
                padding: 3px;
            }

            .shift-info {
                display: block;
                font-size: 10px;
            }

            .shift-info button {
                font-size: 9px;
                padding: 3px;
            }
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

        /* 治療師班表 */
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table-custom th,
        .table-custom td {
            border: 1px solid #ddd;
            text-align: left;
            /* 文字靠左對齊 */
            padding: 8px;
            white-space: nowrap;
        }

        .table-custom th {
            background-color: #00a79d;
            color: white;
            text-align: center;
        }

        .reservation-info {
            color: red;
            margin-top: 5px;
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
                                <!--Brand--><a class="brand-name" href="index.php"><img class="logo-default"
                                        src="images/logo-default-172x36.png" alt="" width="86" height="18"
                                        loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
                                        alt="" width="86" height="18" loading="lazy" /></a>
                            </div>
                        </div>
                        <div class="rd-navbar-nav-wrap">
                            <ul class="rd-navbar-nav">
                                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="a_index.php">網頁編輯</a> -->
                                </li>
                                <li class="rd-nav-item active"><a class="rd-nav-link" href="">關於治療師</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="a_therapist.php">總人數時段表</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="a_addds.php">治療師班表</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="a_treatment.php">新增治療項目</a>
                                        </li>
                                        <li class="rd-dropdown-item">
                                            <a class="rd-dropdown-link" href="a_leave.php">
                                                請假申請
                                                <?php if ($pendingCount > 0): ?>
                                                    <span style="
                        background-color: red;
                        color: white;
                        font-size: 12px;
                        border-radius: 50%;
                        padding: 2px 6px;
                        margin-left: 5px;
                        display: inline-block;
                    ">
                                                        <?php echo $pendingCount; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="a_comprehensive.php">綜合</a>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="">用戶管理</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="a_patient.php">用戶管理</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="a_addhd.php">新增治療師/助手</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="a_blacklist.php">黑名單</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="a_doctorlistadd.php">新增治療師資料</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="a_doctorlistmod.php">修改治療師資料</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="a_igadd.php">新增哀居貼文</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="a_change.php">變更密碼</a>
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
                                            href="https://www.facebook.com/ReTurnYourBody/"></a></li>
                                    <li><a class="icon novi-icon icon-default icon-custom-instagram"
                                            href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"></a>
                                    </li>
                                </ul>
                            </div>
                        </div> -->
                        <?php
                        echo "歡迎 ~ ";
                        // 顯示姓名
                        echo $帳號名稱;
                        ?>
                    </div>
                </nav>
            </div>
        </header>
        <!-- Page Header-->
        <div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
            <!-- Breadcrumbs-->
            <section class="breadcrumbs-custom breadcrumbs-custom-svg">
                <div class="container">
                    <p class="heading-1 breadcrumbs-custom-title">治療師班表</p>
                    <ul class="breadcrumbs-custom-path">
                        <li><a href="a_index.php">首頁</a></li>
                        <li><a href="">關於治療師</a></li>
                        <li class="active">治療師班表</li>
                    </ul>
                </div>
            </section>
        </div>

        <?php
        include "../db.php";

        // 查詢排班數據
        $query = "
SELECT 
    ds.doctorshift_id,
    d.doctor_id, 
    d.doctor, 
    ds.date, 
    st1.shifttime AS go_time, 
    st2.shifttime AS off_time
FROM 
    doctorshift ds
JOIN 
    doctor d ON ds.doctor_id = d.doctor_id
JOIN 
    shifttime st1 ON ds.go = st1.shifttime_id
JOIN 
    shifttime st2 ON ds.off = st2.shifttime_id
ORDER BY ds.date, d.doctor_id";

        $result = mysqli_query($link, $query);
        $schedule = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $date = $row['date'];
            if (!isset($schedule[$date])) {
                $schedule[$date] = [];
            }
            $schedule[$date][] = [
                'doctor' => $row['doctor'],
                'go_time' => $row['go_time'],
                'off_time' => $row['off_time'],
                'doctor_id' => $row['doctor_id'],
                'shift_id' => $row['doctorshift_id']
            ];
        }

        // 查詢請假數據
        $query_leaves = "SELECT l.doctor_id, l.start_date, l.end_date FROM leaves l WHERE l.is_approved = 1";
        $result_leaves = mysqli_query($link, $query_leaves);
        $leaves = [];

        while ($row = mysqli_fetch_assoc($result_leaves)) {
            $leaves[] = [
                'doctor_id' => $row['doctor_id'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
            ];
        }

        // **查詢整點的 shifttime (00:00 ~ 23:00)**
        $query_times = "SELECT shifttime_id, shifttime FROM shifttime WHERE shifttime LIKE '%:00'";
        $result_times = mysqli_query($link, $query_times);
        $shifttimes = [];

        while ($row = mysqli_fetch_assoc($result_times)) {
            $shifttimes[] = $row;
        }
        // 輸出數據給前端
        header('Content-Type: application/json');
        // echo json_encode(['schedule' => $schedule, 'leaves' => $leaves, 'shifttimes' => $shifttimes], JSON_UNESCAPED_UNICODE);
        ?>

        <section class="section section-lg bg-default">
            <div style="font-size: 18px; font-weight: bold; color: #333; margin-top: 10px; text-align: center;">
                <label for="year">選擇年份：</label>
                <select id="year"></select>
                <label for="month">選擇月份：</label>
                <select id="month"></select>
                <a href="a_addds2.php">新增班表</a>
                <div style="overflow-x: auto; max-width: 100%;">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>日</th>
                                <th>一</th>
                                <th>二</th>
                                <th>三</th>
                                <th>四</th>
                                <th>五</th>
                                <th>六</th>
                            </tr>
                        </thead>
                        <tbody id="calendar"></tbody>
                    </table>
                </div>

            </div>

            <div id="editShiftModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span> <!-- 關閉按鈕 -->
                    <h4 id="editShiftTitle"></h4> <!-- 顯示標題與日期 -->

                    <form id="editShiftForm">
                        <input type="hidden" id="editDoctorId"> <!-- 醫生 ID -->
                        <input type="hidden" id="editDate"> <!-- 日期 -->

                        <div class="shift-time-container">
                            <label for="editGoTime">上班時間：</label>
                            <select id="editGoTime"></select>
                        </div>

                        <div class="shift-time-container">
                            <label for="editOffTime">下班時間：</label>
                            <select id="editOffTime"></select>
                        </div>

                        <!-- 儲存按鈕 -->
                        <button type="button" onclick="saveShift()">儲存</button>
                        <!-- 🔥 新增「刪除」按鈕 -->
                        <button type="button" onclick="deleteShift()"
                            style="background-color: red; color: white; margin-top: 10px;">
                            刪除
                        </button>
                    </form>
                </div>
            </div>


            <!-- Modal 樣式 -->
            <style>
                /* Modal 樣式 */
                .modal {
                    display: none;
                    /* 預設隱藏 */
                    position: fixed;
                    z-index: 10;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.5);
                    justify-content: center;
                    align-items: center;
                }


                /* Modal 內容 */
                .modal-content {
                    background-color: white;
                    padding: 20px;
                    border-radius: 10px;
                    width: 400px;
                    text-align: center;
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
                }

                /* 關閉按鈕 */
                .close {
                    float: right;
                    font-size: 28px;
                    cursor: pointer;
                }

                /* 按鈕樣式 */
                .edit-btn {
                    background-color: #007bff;
                    color: white;
                    border: none;
                    padding: 5px 10px;
                    margin-top: 5px;
                    border-radius: 5px;
                    cursor: pointer;
                }

                .edit-btn:hover {
                    background-color: #0056b3;
                }

                /* 讓日期置中並換行 */
                .shift-date {
                    display: block;
                    font-size: 18px;
                    font-weight: bold;
                    margin-top: 5px;
                }

                .shift-time-container {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 10px;
                }

                .shift-time-container label {
                    font-size: 16px;
                    font-weight: bold;
                    margin-right: 10px;
                }

                .shift-time-container select {
                    padding: 5px;
                    font-size: 16px;
                    width: 100px;
                    text-align: center;
                }
            </style>



            <script>
                const data = <?php echo json_encode(['schedule' => $schedule, 'leaves' => $leaves], JSON_UNESCAPED_UNICODE); ?>;
                const calendarData = data.schedule; // 排班數據
                const leaveData = data.leaves; // 請假數據
                const today = new Date(); // 今天日期
                const tomorrow = new Date(today); // 明天日期
                tomorrow.setDate(today.getDate() + 1);

                console.log("Calendar Data:", calendarData);
                console.log("Leave Data:", leaveData);

                function populateShiftTimeOptions() {
                    const goSelect = document.getElementById('editGoTime');
                    const offSelect = document.getElementById('editOffTime');

                    goSelect.innerHTML = '';
                    offSelect.innerHTML = '';

                    // 只加入整點的選項
                    window.shifttimes.forEach(time => {
                        if (time.shifttime.endsWith(":00")) {  // 確保是整點
                            let option1 = new Option(time.shifttime, time.shifttime_id);
                            let option2 = new Option(time.shifttime, time.shifttime_id);
                            goSelect.appendChild(option1);
                            offSelect.appendChild(option2);
                        }
                    });
                }


                document.addEventListener('DOMContentLoaded', function () {
                    fetch('a_addds.php')
                        .then(response => response.json())
                        .then(data => {
                            window.scheduleData = data.schedule; // 排班數據
                            window.shifttimes = data.shifttimes; // 整點時間數據
                        });
                });

                function editShift(shiftId, date, doctorId, goTime, offTime) {
                    document.getElementById('shiftId').value = shiftId;
                    document.getElementById('shiftDate').value = date;
                    document.getElementById('shiftDoctorId').value = doctorId;

                    const goTimeSelect = document.getElementById('shiftGo');
                    const offTimeSelect = document.getElementById('shiftOff');

                    goTimeSelect.innerHTML = '';
                    offTimeSelect.innerHTML = '';

                    window.shifttimes.forEach(time => {
                        let optionGo = new Option(time.shifttime, time.shifttime_id);
                        let optionOff = new Option(time.shifttime, time.shifttime_id);

                        if (time.shifttime === goTime) optionGo.selected = true;
                        if (time.shifttime === offTime) optionOff.selected = true;

                        goTimeSelect.add(optionGo);
                        offTimeSelect.add(optionOff);
                    });

                    document.getElementById('editModal').style.display = 'block';
                }

                function closeModal() {
                    document.getElementById('editModal').style.display = 'none';
                }

                function saveShift() {
                    const shiftId = document.getElementById('shiftId').value;
                    const goTime = document.getElementById('shiftGo').value;
                    const offTime = document.getElementById('shiftOff').value;

                    fetch('編輯治療師班表.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `shiftId=${shiftId}&goTime=${goTime}&offTime=${offTime}`
                    })
                        .then(response => response.text())
                        .then(result => {
                            alert(result);
                            location.reload();
                        });
                }

                // **刪除班表**
                function deleteShift() {
                    const doctorId = document.getElementById('editDoctorId').value;
                    const date = document.getElementById('editDate').value;

                    if (!confirm("確定要刪除這個班表嗎？此操作無法恢復！")) {
                        return; // 如果使用者取消，則不執行刪除
                    }

                    fetch('刪除治療師班表.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ doctor_id: doctorId, date: date })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('班表已刪除');
                                closeModal(); // 關閉彈窗
                                location.reload(); // 重新載入頁面
                            } else {
                                alert('刪除失敗，請稍後再試');
                            }
                        })
                        .catch(error => {
                            console.error('刪除錯誤:', error);
                            alert('刪除過程中發生錯誤，請檢查伺服器日誌');
                        });
                }


                /**
                 * 調整排班時間，處理部分請假情況
                 */
                function adjustShiftTime(doctorId, date, startTime, endTime) {
                    const shiftStart = new Date(`${date}T${startTime}`);
                    const shiftEnd = new Date(`${date}T${endTime}`);

                    for (const leave of leaveData) {
                        const leaveStart = new Date(leave.start_date);
                        const leaveEnd = new Date(leave.end_date);

                        if (leave.doctor_id === doctorId) {
                            if (leaveStart <= shiftStart && leaveEnd >= shiftEnd) {
                                return null; // 整天請假
                            } else if (leaveStart <= shiftStart && leaveEnd > shiftStart && leaveEnd < shiftEnd) {
                                return { go_time: leaveEnd.toTimeString().slice(0, 5), off_time: endTime }; // 調整上班時間
                            } else if (leaveStart > shiftStart && leaveStart < shiftEnd && leaveEnd >= shiftEnd) {
                                return { go_time: startTime, off_time: leaveStart.toTimeString().slice(0, 5) }; // 調整下班時間
                            }
                        }
                    }
                    return { go_time: startTime, off_time: endTime }; // 無請假
                }

                /**
                    * 生成日曆表格
                    */
                function generateCalendar() {
                    const year = parseInt(document.getElementById('year').value);
                    const month = parseInt(document.getElementById('month').value) - 1;

                    const calendarBody = document.getElementById('calendar');
                    calendarBody.innerHTML = '';

                    const firstDay = new Date(year, month, 1).getDay();
                    const lastDate = new Date(year, month + 1, 0).getDate();

                    let row = document.createElement('tr');
                    for (let i = 0; i < firstDay; i++) {
                        row.appendChild(document.createElement('td'));
                    }

                    for (let date = 1; date <= lastDate; date++) {
                        const fullDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                        const cell = document.createElement('td');
                        cell.innerHTML = `<strong>${date}</strong>`;

                        if (calendarData[fullDate]) {
                            calendarData[fullDate].forEach(shift => {
                                const shiftDiv = document.createElement('div');
                                shiftDiv.innerHTML = `
                    ${shift.doctor}: ${shift.go_time} - ${shift.off_time} 
                    <button class="edit-btn" onclick="openEditModal(${shift.doctor_id}, '${shift.doctor}', '${fullDate}', '${shift.go_time}', '${shift.off_time}')">編輯</button>
                `;

                                shiftDiv.className = 'shift-info';
                                cell.appendChild(shiftDiv);
                            });
                        } else {
                            const noSchedule = document.createElement('div');
                            noSchedule.textContent = '無排班';
                            noSchedule.className = 'no-schedule';
                            cell.appendChild(noSchedule);
                        }

                        row.appendChild(cell);
                        if (row.children.length === 7) {
                            calendarBody.appendChild(row);
                            row = document.createElement('tr');
                        }
                    }

                    while (row.children.length < 7) {
                        row.appendChild(document.createElement('td'));
                    }
                    calendarBody.appendChild(row);
                }

                document.addEventListener("DOMContentLoaded", function () {
                    generateTimeOptions();
                });

                function generateTimeOptions() {
                    const goTimeSelect = document.getElementById("editGoTime");
                    const offTimeSelect = document.getElementById("editOffTime");

                    goTimeSelect.innerHTML = "";
                    offTimeSelect.innerHTML = "";

                    for (let hour = 7; hour <= 24; hour++) {
                        let timeStr = (hour < 10 ? "0" + hour : hour) + ":00"; // 格式化為 "07:00", "08:00", ..., "24:00"
                        let optionGo = new Option(timeStr, timeStr);
                        let optionOff = new Option(timeStr, timeStr);

                        goTimeSelect.appendChild(optionGo);
                        offTimeSelect.appendChild(optionOff);
                    }
                }

                // **開啟編輯視窗**
                function openEditModal(doctorId, doctorName, date, goTime, offTime) {
                    document.getElementById("editShiftTitle").innerHTML = `
        <strong>編輯 ${doctorName} 的班表</strong><br>
        <span class="shift-date">${formatDate(date)}</span>
    `;

                    document.getElementById("editDoctorId").value = doctorId;
                    document.getElementById("editDate").value = date;

                    const goTimeSelect = document.getElementById("editGoTime");
                    const offTimeSelect = document.getElementById("editOffTime");

                    // 設定選擇的時間
                    goTimeSelect.value = goTime;
                    offTimeSelect.value = offTime;

                    document.getElementById("editShiftModal").style.display = "flex";
                }


                // **格式化日期**
                function formatDate(dateString) {
                    const date = new Date(dateString);
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}年${month}月${day}日`;
                }

                // **關閉視窗**
                function closeModal() {
                    document.getElementById('editShiftModal').style.display = 'none';
                }

                // **儲存修改**
                function saveShift() {
                    const doctorId = document.getElementById('editDoctorId').value;
                    const date = document.getElementById('editDate').value;
                    const goTime = document.getElementById('editGoTime').value;
                    const offTime = document.getElementById('editOffTime').value;

                    fetch('編輯治療師班表.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ doctor_id: doctorId, date: date, go_time: goTime, off_time: offTime })
                    }).then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('班表已更新');
                                closeModal();
                                location.reload();
                            } else {
                                alert('更新失敗');
                            }
                        });
                }


                /**
                 * 初始化年份與月份選單
                 */
                function initSelectOptions() {
                    const yearSelect = document.getElementById('year');
                    const monthSelect = document.getElementById('month');

                    for (let year = 2020; year <= 2030; year++) {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = year;
                        if (year === today.getFullYear()) option.selected = true;
                        yearSelect.appendChild(option);
                    }

                    for (let month = 1; month <= 12; month++) {
                        const option = document.createElement('option');
                        option.value = month;
                        option.textContent = month;
                        if (month === today.getMonth() + 1) option.selected = true;
                        monthSelect.appendChild(option);
                    }

                    generateCalendar();
                }

                document.getElementById('year').addEventListener('change', generateCalendar);
                document.getElementById('month').addEventListener('change', generateCalendar);

                initSelectOptions();
            </script>



        </section>
        <!-- Global Mailform Output-->
        <div class="snackbars" id="form-output-global"></div>
        <!-- Javascript-->
        <script src="js/core.min.js"></script>
        <script src="js/script.js"></script>

    </div>
</body>

</html>