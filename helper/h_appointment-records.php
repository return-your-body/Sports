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


//預約紀錄
$帳號 = $_SESSION['帳號'];// 取得當前登入的帳號

require '../db.php';   // 引入資料庫連接檔案

// 接收搜尋參數
$search_name = isset($_GET['search_name']) ? mysqli_real_escape_string($link, trim($_GET['search_name'])) : '';

// 分頁設定
$records_per_page = 10;
$page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
$offset = ($page - 1) * $records_per_page;

// 計算總記錄數
$count_sql = "
SELECT COUNT(*) AS total
FROM appointment a
LEFT JOIN people p ON a.people_id = p.people_id
LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
LEFT JOIN user u ON d.user_id = u.user_id
WHERE u.account = '$帳號'
AND p.name LIKE '%$search_name%'
";
$count_result = mysqli_query($link, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ($total_records > 0) ? ceil($total_records / $records_per_page) : 1;

// 查詢資料
$sql = "
SELECT 
a.appointment_id AS id,
COALESCE(p.name, '未預約') AS name,
CASE WHEN p.gender_id = 1 THEN '男' WHEN p.gender_id = 2 THEN '女' ELSE '未設定' END AS gender,
CONCAT(COALESCE(p.birthday, 'N/A'), 
' (', 
CASE 
 WHEN p.birthday IS NOT NULL THEN TIMESTAMPDIFF(YEAR, p.birthday, CURDATE())
 ELSE 'N/A' 
END, '歲)') AS birthday,
ds.date AS appointment_date,
COALESCE(st.shifttime, 'N/A') AS shifttime,
COALESCE(a.note, '') AS note,
a.created_at
FROM appointment a
LEFT JOIN people p ON a.people_id = p.people_id
LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
LEFT JOIN shifttime st ON ds.shifttime_id = st.shifttime_id
LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
LEFT JOIN user u ON d.user_id = u.user_id
WHERE u.account = '$帳號'
AND p.name LIKE '%$search_name%'
ORDER BY ds.date, st.shifttime
LIMIT $offset, $records_per_page
";
$result = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
  <!-- Site Title-->
  <title>醫生-預約紀錄</title>
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

    /*預約紀錄 */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th,
    td {
      padding: 8px;
      text-align: center;
      border: 1px solid #ddd;
    }

    th {
      background-color: #f2f2f2;
    }

    .search-container {
      text-align: right;
      margin-bottom: 10px;
    }

    input,
    button {
      padding: 5px;
      margin-right: 5px;
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
        <polyline class="line-cornered stroke-animation" points="0,0 100,0 100,100" stroke-width="10" fill="none">
        </polyline>
        <polyline class="line-cornered stroke-animation" points="0,0 0,100 100,100" stroke-width="10" fill="none">
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
          data-xxl-layout="rd-navbar-static" data-xxxl-layout="rd-navbar-static" data-lg-device-layout="rd-navbar-fixed"
          data-xl-device-layout="rd-navbar-static" data-xxl-device-layout="rd-navbar-static"
          data-xxxl-device-layout="rd-navbar-static" data-stick-up-offset="1px" data-sm-stick-up-offset="1px"
          data-md-stick-up-offset="1px" data-lg-stick-up-offset="1px" data-xl-stick-up-offset="1px"
          data-xxl-stick-up-offset="1px" data-xxx-lstick-up-offset="1px" data-stick-up="true">
          <div class="rd-navbar-inner">
            <!-- RD Navbar Panel-->
            <div class="rd-navbar-panel">
              <!-- RD Navbar Toggle-->
              <button class="rd-navbar-toggle" data-rd-navbar-toggle=".rd-navbar-nav-wrap"><span></span></button>
              <!-- RD Navbar Brand-->
              <div class="rd-navbar-brand">
                <!--Brand--><a class="brand-name" href="h_index.php"><img class="logo-default"
                    src="images/logo-default-172x36.png" alt="" width="86" height="18" loading="lazy" /><img
                    class="logo-inverse" src="images/logo-inverse-172x36.png" alt="" width="86" height="18"
                    loading="lazy" /></a>
              </div>
            </div>
            <div class="rd-navbar-nav-wrap">
              <ul class="rd-navbar-nav">
                <li class="rd-nav-item"><a class="rd-nav-link" href="h_index.php">首頁</a>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="h_appointment.php">預約</a></li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="#">醫生班表</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_doctorshift.php">治療師班表</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_numberpeople.php">當天人數及時段</a>
                    </li>
                  </ul>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="#">列印</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_print-receipt.php">列印收據</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_print-appointment.php">列印預約單</a>
                    </li>
                  </ul>
                </li>

                <li class="rd-nav-item active"><a class="rd-nav-link" href="#">紀錄</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_medical-record.php">看診紀錄</a>
                    </li>
                    <li class="rd-dropdown-item active"><a class="rd-dropdown-link"
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
            <div class="rd-navbar-collapse-toggle" data-rd-navbar-toggle=".rd-navbar-collapse"><span></span></div>
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
          </div>
        </nav>
      </div>
    </header>

    <!--標題-->
    <div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
      <!-- Breadcrumbs-->
      <section class="breadcrumbs-custom breadcrumbs-custom-svg">
        <div class="container">
          <!-- <p class="breadcrumbs-custom-subtitle">Terms of Use</p> -->
          <p class="heading-1 breadcrumbs-custom-title">預約紀錄</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="h_index.php">首頁</a></li>
            <li><a href="#">紀錄</a></li>
            <li class="active">預約紀錄</li>
          </ul>
        </div>
      </section>
    </div>
    <!--標題-->

    <!--預約紀錄-->
    <section class="section section-lg bg-default novi-bg novi-bg-img">
      <div class="container">
        <div class="row row-50 justify-content-lg-center">
          <div class="col-lg-10 col-xl-8">
            <!-- Bootstrap collapse-->
            <div class="accordion-custom-group accordion-custom-group-custom accordion-custom-group-corporate"
              id="accordion1" role="tablist" aria-multiselectable="false">
              <!-- <h3 style='text-align: center;'>預約紀錄</h3> -->

              <!-- 搜尋框 -->
              <div class="search-container">
                <form method="GET" action="">
                  <input type="text" name="search_name" placeholder="請輸入搜尋姓名"
                    value="<?php echo htmlspecialchars($search_name); ?>">
                  <button type="submit">搜尋</button>
                </form>
              </div>

              <!-- 資料表格 -->
              <table>
                <tr>
                  <th>編號</th>
                  <th>姓名</th>
                  <th>性別</th>
                  <th>生日(年齡)</th>
                  <th>預約日期</th>
                  <th>預約時間</th>
                  <th>備註</th>
                  <th>建立時間</th>
                </tr>
                <?php if (mysqli_num_rows($result) > 0): ?>
                  <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                      <td><?php echo $row['id']; ?></td>
                      <td><?php echo htmlspecialchars($row['name']); ?></td>
                      <td><?php echo $row['gender']; ?></td>
                      <td><?php echo $row['birthday']; ?></td>
                      <td><?php echo $row['appointment_date']; ?></td>
                      <td><?php echo $row['shifttime']; ?></td>
                      <td><?php echo htmlspecialchars($row['note']); ?></td>
                      <td><?php echo $row['created_at']; ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8">目前無資料</td>
                  </tr>
                <?php endif; ?>
              </table>

              <!-- 分頁 -->
              <div style="text-align: right; margin-top: 10px; margin-bottom: 10px;">
                <span>第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁（共 <?php echo $total_records; ?>
                  筆資料）</span>
              </div>

              <div style="text-align: center; margin-top: 20px;">
                <?php if ($page > 1): ?>
                  <a href="?page=<?php echo $page - 1; ?>&search_name=<?php echo urlencode($search_name); ?>">上一頁</a>
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
                  <a href="?page=<?php echo $page + 1; ?>&search_name=<?php echo urlencode($search_name); ?>">下一頁</a>
                <?php endif; ?>
              </div>

              <?php
              mysqli_close($link);
              ?>

            </div>
          </div>
        </div>
      </div>
    </section>
    <!--預約紀錄-->

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
              <li><a href="d_appointment.php">預約</a></li>
              <li><a href="d_numberpeople.php">當天人數及時段</a></li>
              <li><a href="d_doctorshift.php">班表時段</a></li>
              <li><a href="d_medical-record.php">看診紀錄</a></li>
              <li> <a href="d_appointment-records.php">預約紀錄</a></li>
              </a></li>
            </ul>
          </div>
          <!-- <div class="col-md-5">
          <h4>聯絡我們</h4>
          <p>Subscribe to our newsletter today to get weekly news, tips, and special offers from our team on the
            courses we offer.</p>
          <form class="rd-mailform rd-form-boxed" data-form-output="form-output-global" data-form-type="subscribe"
            method="post" action="bat/rd-mailform.php">
            <div class="form-wrap">
              <input class="form-input" type="email" name="email" data-constraints="@Email @Required" id="footer-mail">
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
  <!-- coded by Himic-->
</body>

</html>