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

// 用戶資料

// 開啟 session 並引入資料庫連線
session_start();
require '../db.php'; // 資料庫連線

// 預設參數
$records_per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10; // 預設 10 筆
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search_idcard = isset($_GET['search_idcard']) ? trim($_GET['search_idcard']) : '';

// 計算總筆數
$sql_total = "SELECT COUNT(*) AS total FROM `people` WHERE `idcard` LIKE ?";
$stmt_total = $link->prepare($sql_total);
$like_search = "%$search_idcard%";
$stmt_total->bind_param("s", $like_search);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$row_total = $result_total->fetch_assoc();
$total_records = $row_total['total'];
$stmt_total->close();

// 計算總頁數與分頁偏移量
$total_pages = ($total_records > 0) ? ceil($total_records / $records_per_page) : 1;
$offset = ($current_page - 1) * $records_per_page;

// 查詢分頁資料，計算年齡
$sql = "
SELECT 
    people_id, 
    name, 
    COALESCE(
        CASE 
            WHEN gender_id = 1 THEN '男'
            WHEN gender_id = 2 THEN '女'
            ELSE '無資料'
        END, '未知') AS gender,
    COALESCE(
        CASE 
            WHEN birthday IS NOT NULL THEN CONCAT(DATE_FORMAT(birthday, '%Y-%m-%d'), ' (', FLOOR(DATEDIFF(CURDATE(), birthday) / 365.25), ' 歲)')
            ELSE '無資料'
        END, '無資料') AS birthday_with_age,
    COALESCE(NULLIF(idcard, ''), '無資料') AS idcard
FROM `people`
WHERE `idcard` LIKE ?
LIMIT ?, ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("sii", $like_search, $offset, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();
$people = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 關閉資料庫連線
$link->close();
?>


<head>
    <!-- Site Title-->
    <title>助手-預約</title>
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


        /* 用戶資料 */
        /* 表格樣式 */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            white-space: nowrap;
            /* 防止文字換行 */
        }

        th {
            background-color: #f2f2f2;
        }

        /* 表格容器樣式 */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* 按鈕樣式 */
        .popup-btn {
            background-color: #00A896;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .popup-btn:hover {
            background-color: #007f6e;
        }

        /* 下拉選單樣式 */
        .pagination-select {
            padding: 5px 10px;
            font-size: 14px;
            margin-left: 10px;
        }

        /* 手機模式樣式 */
        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 8px;
            }

            .table-responsive {
                overflow-x: scroll;
            }

            .popup-btn {
                padding: 5px 10px;
                font-size: 12px;
            }

            .pagination-select {
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
                                <li class="rd-nav-item active"><a class="rd-nav-link" href="#">預約</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item active"><a class="rd-dropdown-link"
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

                                <li class="rd-nav-item"><a class="rd-nav-link" href="#">紀錄</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
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
                    <p class="heading-1 breadcrumbs-custom-title">用戶資料</p>
                    <ul class="breadcrumbs-custom-path">
                        <li><a href="h_index.php">首頁</a></li>
                        <li><a href="#">紀錄</a></li>
                        <li class="active">用戶資料</li>
                    </ul>
                </div>
            </section>
        </div>
        <!--標題-->

        <!-- 用戶資料 -->
        <section class="section section-lg bg-default">
            <div class="container">
                <?php
                session_start();
                require '../db.php';

                // 取得搜尋條件和分頁設定
                $records_per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
                $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $search_idcard = isset($_GET['search_idcard']) ? $_GET['search_idcard'] : '';

                // 計算總筆數
                $total_query = "SELECT COUNT(*) AS total FROM people WHERE idcard LIKE ?";
                $stmt_total = $link->prepare($total_query);
                $like_search = "%$search_idcard%";
                $stmt_total->bind_param('s', $like_search);
                $stmt_total->execute();
                $total_result = $stmt_total->get_result()->fetch_assoc();
                $total_records = $total_result['total'];
                $total_pages = ceil($total_records / $records_per_page);
                $offset = ($current_page - 1) * $records_per_page;

                // 查詢使用者資料及黑名單狀態
                $sql = "SELECT people_id, name, gender_id, birthday, idcard, black,
                CASE WHEN birthday IS NOT NULL THEN CONCAT(DATE_FORMAT(birthday, '%Y-%m-%d'), ' (', FLOOR(DATEDIFF(CURDATE(), birthday)/365.25), ' 歲)')
                     ELSE '無資料' END AS birthday_with_age,
                CASE gender_id WHEN 1 THEN '男' WHEN 2 THEN '女' ELSE '無資料' END AS gender
                FROM people
                WHERE idcard LIKE ?
                LIMIT ?, ?";
                $stmt = $link->prepare($sql);
                $stmt->bind_param('sii', $like_search, $offset, $records_per_page);
                $stmt->execute();
                $result = $stmt->get_result();

                $people = $result->fetch_all(MYSQLI_ASSOC);
                ?>

                <!-- 搜尋表單 + 每頁筆數 -->
                <div class="search-container"
                    style="display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <form method="GET" style="display: flex; align-items: center; gap: 10px;">
                        <input type="text" name="search_idcard" placeholder="請輸入身分證號"
                            value="<?php echo htmlspecialchars($search_idcard); ?>">
                        <button type="submit" class="popup-btn">搜尋</button>
                    </form>

                    <select
                        onchange="location.href='?search_idcard=<?php echo urlencode($search_idcard); ?>&per_page='+this.value">
                        <?php foreach ([3, 5, 10, 20, 50, 100] as $num): ?>
                            <option value="<?php echo $num; ?>" <?php if ($records_per_page == $num)
                                   echo 'selected'; ?>>
                                <?php echo $num; ?> 筆/頁
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 資料表格 -->
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>姓名</th>
                                <th>性別</th>
                                <th>生日 (年齡)</th>
                                <th>身分證</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($people)): ?>
                                <?php foreach ($people as $person): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($person['people_id']); ?></td>
                                        <td><?php echo htmlspecialchars($person['name']); ?></td>
                                        <td><?php echo htmlspecialchars($person['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($person['birthday_with_age']); ?></td>
                                        <td><?php echo htmlspecialchars($person['idcard']); ?></td>
                                        <td>
                                            <?php if ($person['black'] >= 3): ?>
                                                <button class="popup-btn" disabled
                                                    style="background-color:gray; cursor:not-allowed;">
                                                    禁止預約
                                                </button>
                                            <?php else: ?>
                                                <a href="h_appointment.php?id=<?php echo urlencode($person['people_id']); ?>">
                                                    <button class="popup-btn">預約</button>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">目前無資料</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 分頁資訊 -->
                <div style="text-align: right; margin-top: 20px;">
                    <strong>
                        第 <?php echo $current_page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁（總共
                        <?php echo $total_records; ?> 筆資料）
                    </strong>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?search_idcard=<?php echo urlencode($search_idcard); ?>&page=<?php echo $i; ?>&per_page=<?php echo $records_per_page; ?>"
                            style="margin:0 5px;<?php echo $i == $current_page ? 'font-weight:bold;' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <?php
                $stmt_total->close();
                $stmt->close();
                $link->close();
                ?>
            </div>
        </section>


        <!-- 📝 JavaScript 讓 per_page 變更後即時更新頁面 -->
        <script>
            function changePerPage(perPage) {
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', perPage); // 設定 per_page 參數
                url.searchParams.set('page', 1); // 切換筆數時回到第一頁
                window.location.href = url.toString(); // 重新導向
            }
        </script>


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