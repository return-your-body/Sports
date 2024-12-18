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

// 檢查 "帳號" 和 "姓名" 是否存在於 $_SESSION 中
if (isset($_SESSION["帳號"])) {
  // 獲取用戶帳號和姓名
  $帳號 = $_SESSION['帳號'];
} else {
  echo "<script>
            alert('會話過期或資料遺失，請重新登入。');
            window.location.href = '../index.html';
          </script>";
  exit();
}
?>

<head>
  <!-- Site Title-->
  <title>醫生-班表時段</title>
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
                <!--Brand--><a class="brand-name" href="d_index.php"><img class="logo-default"
                    src="images/logo-default-172x36.png" alt="" width="86" height="18" loading="lazy" /><img
                    class="logo-inverse" src="images/logo-inverse-172x36.png" alt="" width="86" height="18"
                    loading="lazy" /></a>
              </div>
            </div>
            <div class="rd-navbar-nav-wrap">
              <ul class="rd-navbar-nav">
                <li class="rd-nav-item"><a class="rd-nav-link" href="d_index.php">首頁</a>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="d_numberpeople.php">當天人數及時段</a>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="d_appointment.php">預約</a>
                </li>
                <li class="rd-nav-item active"><a class="rd-nav-link" href="d_doctorshift.php">班表時段</a>

                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="#">紀錄</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="d_medical-record.php">看診紀錄</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="d_appointment-records.php">預約紀錄</a>
                    </li>
                  </ul>
                </li>
                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="d_body-knowledge.php">身體小知識</a>
                </li> -->
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
            <div class="rd-navbar-aside-right rd-navbar-collapse">
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
            </div>
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
          <!-- <p class="breadcrumbs-custom-subtitle">What We Offer</p> -->
          <p class="heading-1 breadcrumbs-custom-title">班表時段</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="d_index.php">首頁</a></li>
            <li class="active">班表時段</li>
          </ul>
        </div>
      </section>
    </div>

    <!--治療師班表-->
    <section class="section section-lg bg-default novi-bg novi-bg-img">

      <?php
      session_start(); // 啟用 Session
      
      // 確保用戶已登入
      if (!isset($_SESSION['帳號'])) {
        echo "<script>
        alert('未登入或會話已過期，請重新登入！');
        window.location.href = '../index.html';
    </script>";
        exit;
      }

      // 取得當前登入的帳號
      $帳號 = $_SESSION['帳號'];

      // 引入資料庫連接檔案
      require '../db.php';

      // SQL 查詢：根據登入帳號取得對應的醫生姓名
      $query = "
    SELECT d.doctor_id, d.doctor 
    FROM doctor d 
    INNER JOIN user u ON d.user_id = u.user_id 
    WHERE u.account = ?
";

      $stmt = mysqli_prepare($link, $query);
      mysqli_stmt_bind_param($stmt, "s", $帳號);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);

      if ($row = mysqli_fetch_assoc($result)) {
        $doctor_id = $row['doctor_id'];
        $doctor_name = htmlspecialchars($row['doctor']);
      } else {
        $doctor_id = '';
        $doctor_name = '未知醫生';
        error_log("未找到醫生資料，帳號: $帳號");
      }

      // 查詢排班與預約資料
      $reservation_query = "
    SELECT ds.date, COUNT(a.appointment_id) AS people_count
    FROM doctorshift ds
    LEFT JOIN appointment a ON ds.doctorshift_id = a.doctorshift_id
    WHERE ds.doctor_id = ?
    GROUP BY ds.date
";

      $reservations = [];
      if (!empty($doctor_id)) {
        $stmt = mysqli_prepare($link, $reservation_query);
        mysqli_stmt_bind_param($stmt, "i", $doctor_id);
        mysqli_stmt_execute($stmt);
        $res_result = mysqli_stmt_get_result($stmt);

        while ($res_row = mysqli_fetch_assoc($res_result)) {
          $reservations[$res_row['date']] = $res_row['people_count'];
        }
      }

      mysqli_stmt_close($stmt);
      mysqli_close($link);
      ?>

       <h3 style="text-align: center;">治療師班表</h3>
    
      <div style="font-size: 18px; font-weight: bold; color: #333; margin-top: 10px; text-align: center;">
        治療師姓名：<?php echo $doctor_name; ?> <br/>  <!-- 顯示醫生姓名 -->
        <label for="year">選擇年份：</label>
        <select id="year"></select>
        <label for="month">選擇月份：</label>
        <select id="month"></select>
      </div>

      <table class="table-custom table-color-header table-custom-bordered">
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

      <script>
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth();

        const yearSelect = document.getElementById('year');
        const monthSelect = document.getElementById('month');
        const calendarBody = document.getElementById('calendar');

        // 從 PHP 獲取預約資料
        const reservations = <?php echo json_encode($reservations); ?>;

        function initYearOptions() {
          const startYear = currentYear - 5;
          const endYear = currentYear + 5;
          for (let year = startYear; year <= endYear; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            if (year === currentYear) option.selected = true;
            yearSelect.appendChild(option);
          }
        }

        function initMonthOptions() {
          for (let month = 0; month < 12; month++) {
            const option = document.createElement('option');
            option.value = month;
            option.textContent = month + 1;
            if (month === currentMonth) option.selected = true;
            monthSelect.appendChild(option);
          }
        }

        function generateCalendar(year, month) {
          calendarBody.innerHTML = ''; // 清空日曆內容
          const firstDay = new Date(year, month, 1).getDay();
          const lastDate = new Date(year, month + 1, 0).getDate();
          let row = document.createElement('tr');

          // 補足第一行空白單元格
          for (let i = 0; i < firstDay; i++) {
            row.appendChild(document.createElement('td'));
          }

          // 填入日期和預約人數
          for (let date = 1; date <= lastDate; date++) {
            const fullDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
            const cell = document.createElement('td');
            cell.textContent = date;

            if (reservations[fullDate]) {
              const reservationInfo = document.createElement('div');
              reservationInfo.textContent = `預約: ${reservations[fullDate]} 人`;
              reservationInfo.style.color = 'red';
              cell.appendChild(reservationInfo);
            }

            row.appendChild(cell);

            if (row.children.length === 7) {
              calendarBody.appendChild(row);
              row = document.createElement('tr');
            }
          }

          // 補足最後一行空白單元格
          while (row.children.length < 7) {
            row.appendChild(document.createElement('td'));
          }
          calendarBody.appendChild(row);
        }

        // 初始化日曆
        initYearOptions();
        initMonthOptions();
        generateCalendar(currentYear, currentMonth);

        yearSelect.addEventListener('change', () => {
          generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
        });
        monthSelect.addEventListener('change', () => {
          generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
        });
      </script>


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
              <li><a href="d_appointment.php">預約</a></li>
              <li><a href="d_numberpeople.php">當天人數及時段</a></li>
              <li><a href="d_doctorshift.php">班表時段</a></li>
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

  <!-- Global Mailform Output-->
  <div class="snackbars" id="form-output-global"></div>
  <!-- Javascript-->
  <script src="js/core.min.js"></script>
  <script src="js/script.js"></script>
</body>

</html>