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
$帳號 = $_SESSION['帳號'];  // 取得當前登入的帳號

require '../db.php'; // 引入資料庫連接檔案

// 分頁設定
$records_per_page = 10; // 每頁顯示的記錄數
$page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1; // 當前頁碼，預設為第1頁
$offset = ($page - 1) * $records_per_page; // 計算記錄的起始位置

// 搜尋條件
$search_name = isset($_GET['search_name']) ? mysqli_real_escape_string($link, $_GET['search_name']) : '';

// 計算總記錄數
$count_sql = "
SELECT COUNT(*) AS total
FROM medicalrecord m
LEFT JOIN appointment a ON m.appointment_id = a.appointment_id
LEFT JOIN people p ON a.people_id = p.people_id
LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
LEFT JOIN item i ON m.item_id = i.item_id
WHERE 1=1
" . ($search_name ? "AND p.name LIKE '%$search_name%'" : "");

$count_result = mysqli_query($link, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ($total_records > 0) ? ceil($total_records / $records_per_page) : 1;

// 查詢資料
$sql = "
SELECT 
m.medicalrecord_id,
a.appointment_id,
p.name AS patient_name,
CASE 
WHEN p.gender_id = 1 THEN '男'
WHEN p.gender_id = 2 THEN '女'
ELSE '未設定'
END AS gender,
CONCAT(p.birthday, ' (', TIMESTAMPDIFF(YEAR, p.birthday, CURDATE()), '歲)') AS birthday_with_age,
d.doctor AS doctor_name,
i.item AS treatment_item,
i.price AS treatment_price,
DATE_FORMAT(ds.date, '%Y-%m-%d') AS consultation_date,
DAYNAME(ds.date) AS consultation_weekday,
m.created_at
FROM medicalrecord m
LEFT JOIN appointment a ON m.appointment_id = a.appointment_id
LEFT JOIN people p ON a.people_id = p.people_id
LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
LEFT JOIN item i ON m.item_id = i.item_id
WHERE 1=1
" . ($search_name ? "AND p.name LIKE '%$search_name%'" : "") . "
LIMIT $offset, $records_per_page";

$result = mysqli_query($link, $sql);


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


        /* 看診紀錄 */

        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
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
            /* 禁止換行 */
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        @media (max-width: 768px) {

            th,
            td {
                padding: 8px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {

            th,
            td {
                padding: 6px;
                font-size: 12px;
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
                                <li class="rd-nav-item"><a class="rd-nav-link" href="#">醫生班表</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="h_doctorshift.php">治療師班表</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="h_numberpeople.php">當天人數及時段</a>
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
                                <li class="rd-nav-item"><a class="rd-nav-link" href="h_patient-needs.php">病患需求</a>
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

        <!--看診紀錄-->
        <section class="section section-lg bg-default text-center">
            <div class="container">
                <div class="row row-50 justify-content-lg-center">
                    <div class="col-lg-10 col-xl-8">
                        <!-- 搜尋框 -->
                        <div class="search-container">
                            <form method="GET" action="">
                                <input type="hidden" name="is_search" value="1">
                                <input type="text" name="search_name" id="search_name" placeholder="請輸入搜尋姓名"
                                    value="<?php echo isset($_GET['search_name']) ? htmlspecialchars($_GET['search_name']) : ''; ?>">
                                <button type="submit">搜尋</button>
                            </form>
                        </div>

                        <!-- 表格容器 -->
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>姓名</th>
                                        <th>性別</th>
                                        <th>生日 (年齡)</th>
                                        <th>看診日期 (星期)</th>
                                        <th>醫生</th>
                                        <th>治療項目</th>
                                        <th>治療費用</th>
                                        <th>建立時間</th>
                                        <th>選項</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['medicalrecord_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                                <td><?php echo htmlspecialchars($row['birthday_with_age']); ?></td>
                                                <td>
                                                    <?php
                                                    $consultation_date = htmlspecialchars($row['consultation_date']);
                                                    $timestamp = strtotime($consultation_date);
                                                    $weekdays = ['日', '一', '二', '三', '四', '五', '六'];
                                                    $weekday = $weekdays[date('w', $timestamp)];
                                                    echo $consultation_date . ' (星期' . $weekday . ')';
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['treatment_item']); ?></td>
                                                <td><?php echo htmlspecialchars($row['treatment_price']); ?></td>
                                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                                <td>
                                                    <a href="h_print-receipt.php?id=<?php echo $row['medicalrecord_id']; ?>"
                                                        target="_blank">
                                                        <button type="button">列印收據</button>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10">目前無資料</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- 分頁 -->
                        <div style="text-align: right; margin-top: 10px; margin-bottom: 10px;">
                            <span>
                                第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁（總共
                                <?php echo $total_records; ?> 筆資料）
                            </span>
                        </div>

                        <div style="text-align: center; margin-top: 20px;">
                            <?php if ($page > 1): ?>
                                <a
                                    href="?page=<?php echo $page - 1; ?>&search_name=<?php echo urlencode($search_name); ?>">上一頁</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <strong><?php echo $i; ?></strong>
                                <?php else: ?>
                                    <a
                                        href="?page=<?php echo $i; ?>&search_name=<?php echo urlencode($search_name); ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a
                                    href="?page=<?php echo $page + 1; ?>&search_name=<?php echo urlencode($search_name); ?>">下一頁</a>
                            <?php endif; ?>
                        </div>

                        <?php
                        mysqli_free_result($result);
                        mysqli_close($link);
                        ?>
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
                            <li><a href="h_doctorshift.php">班表時段</a></li>
                            <li><a href="h_medical-record.php">看診紀錄</a></li>
                            <li><a href="h_appointment-records.php">預約紀錄</a></li>
                            <!-- <li><a href="h_print-receipt.php">列印收據</a></li>
                            <li><a href="h_print-appointment.php">列印預約單</a></li> -->
                            <li><a href="h_patient-needs.php">患者需求</a></li>
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