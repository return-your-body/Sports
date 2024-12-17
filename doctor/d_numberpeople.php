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
  <title>醫生-當天人數時段</title>
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
                <li class="rd-nav-item active"><a class="rd-nav-link" href="d_numberpeople.php">當天人數及時段</a>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="d_appointment.php">預約</a>
                  <!-- <ul class="rd-menu rd-navbar-dropdown">
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="single-teacher.html"></a>
                      </li>
                    </ul> -->
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="d_doctorshift.php">班表時段</a>
                  <!-- <ul class="rd-menu rd-navbar-dropdown">
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="single-course.html">123</a>
                      </li>
                    </ul> -->
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="#">紀錄</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="d_medical-record.php">看診紀錄</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="d_appointment-records.php">預約紀錄</a>
                    </li>
                    <!-- <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="404-page.html">404 page</a>
                      </li>
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="503-page.html">503 page</a>
                      </li>
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="buttons.html">Buttons</a>
                      </li>
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="forms.html">Forms</a>
                      </li>
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="grid-system.html">Grid system</a>
                      </li>
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="tables.html">Tables</a>
                      </li>
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="typography.html">Typography</a>
                      </li> -->
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
          <!-- <p class="breadcrumbs-custom-subtitle">Who We Are</p> -->
          <p class="heading-1 breadcrumbs-custom-title">當天看診人數</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="d_index.php">首頁</a></li>
            <li class="active">當天時段人數</li>
          </ul>
        </div>
      </section>
    </div>
    <!--標題-->

    <section class="section section-lg bg-default novi-bg novi-bg-img">
      <?php
      session_start();
      require '../db.php';

      // 確保用戶已登入
      if (!isset($_SESSION['帳號'])) {
        echo "<script>
            alert('未登入或會話已過期，請重新登入！');
            window.location.href = '../index.html';
          </script>";
        exit;
      }

      $帳號 = $_SESSION['帳號'];

      // 取得登入醫生資訊
      $query_doctor = "SELECT doctor_id, doctor 
                 FROM doctor 
                 WHERE user_id = (SELECT user_id FROM user WHERE account = '$帳號')";
      $result_doctor = mysqli_query($link, $query_doctor);

      if ($row = mysqli_fetch_assoc($result_doctor)) {
        $doctor_id = $row['doctor_id'];
        $doctor_name = htmlspecialchars($row['doctor']);
      } else {
        die("找不到醫生資訊");
      }

      // 設定下拉選單的預設值
      $current_year = date('Y');
      $current_month = date('m');
      $current_day = date('d');

      // 接收 GET 參數
      $selected_year = isset($_GET['year']) ? $_GET['year'] : $current_year;
      $selected_month = isset($_GET['month']) ? $_GET['month'] : $current_month;
      $selected_day = isset($_GET['day']) ? $_GET['day'] : $current_day;

      $selected_date = "$selected_year-$selected_month-$selected_day";

      // 查詢當天所有預約的資料
      $query_appointments = "
    SELECT st.shifttime, p.name, a.appointment_id
    FROM doctorshift ds
    JOIN shifttime st ON ds.shifttime_id = st.shifttime_id
    LEFT JOIN appointment a ON ds.doctorshift_id = a.doctorshift_id
    LEFT JOIN people p ON a.people_id = p.people_id
    WHERE ds.date = '$selected_date' AND ds.doctor_id = '$doctor_id'
";
      $result_appointments = mysqli_query($link, $query_appointments);
      ?>

      <!-- 顯示醫生姓名 -->
      <h3 style="text-align: center;">當天時段</h3>
      <div style="font-size: 18px; font-weight: bold; color: #333; margin-bottom: 10px;">
        治療師姓名：<?php echo $doctor_name; ?>
      </div>

      <!-- 日期選擇下拉選單 -->
      <form method="GET" action="">
        <label for="year">選擇年份：</label>
        <select id="year" name="year">
          <?php
          for ($year = $current_year - 5; $year <= $current_year + 5; $year++) {
            $selected = ($year == $selected_year) ? 'selected' : '';
            echo "<option value='$year' $selected>$year</option>";
          }
          ?>
        </select>

        <label for="month">選擇月份：</label>
        <select id="month" name="month">
          <?php
          for ($month = 1; $month <= 12; $month++) {
            $month_padded = str_pad($month, 2, '0', STR_PAD_LEFT);
            $selected = ($month_padded == $selected_month) ? 'selected' : '';
            echo "<option value='$month_padded' $selected>$month</option>";
          }
          ?>
        </select>

        <label for="day">選擇日期：</label>
        <select id="day" name="day">
          <?php
          for ($day = 1; $day <= 31; $day++) {
            $day_padded = str_pad($day, 2, '0', STR_PAD_LEFT);
            $selected = ($day_padded == $selected_day) ? 'selected' : '';
            echo "<option value='$day_padded' $selected>$day</option>";
          }
          ?>
        </select>

        <button type="submit" style="padding: 5px 10px; background-color: #28a745; color: white; border: none;">
          查詢
        </button>
      </form>

      <!-- 顯示預約時段和姓名 -->
      <table border="1" style="width: 100%; text-align: center; border-collapse: collapse; margin-top: 20px;">
        <tr style="background-color: #f2f2f2;">
          <th>時段</th>
          <th>姓名</th>
        </tr>
        <?php
        if (mysqli_num_rows($result_appointments) > 0) {
          while ($row = mysqli_fetch_assoc($result_appointments)) {
            $shifttime = htmlspecialchars($row['shifttime']);
            $name = isset($row['name']) ? htmlspecialchars($row['name']) : '未預約';
            $appointment_id = $row['appointment_id'];

            echo "<tr>
                    <td>{$shifttime}</td>
                    <td>";
            if ($appointment_id) {
              echo "<a href='appointment_details.php?id={$appointment_id}'>{$name}</a>";
            } else {
              echo $name;
            }
            echo "</td></tr>";
          }
        } else {
          echo "<tr><td colspan='2'>當天無時段資料</td></tr>";
        }
        ?>
      </table>

      <?php mysqli_close($link); ?>

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