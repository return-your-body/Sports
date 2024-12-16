<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
  <!-- Site Title-->
  <title>醫生-看診紀錄</title>
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
                  <!-- <ul class="rd-menu rd-navbar-dropdown">
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="single-teacher.php"></a>
                      </li>
                    </ul> -->
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="d_doctorshift.php">班表時段</a>
                  <!-- <ul class="rd-menu rd-navbar-dropdown">
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="single-course.php">123</a>
                      </li>
                    </ul> -->
                </li>
                <li class="rd-nav-item active"><a class="rd-nav-link" href="#">紀錄</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item active"><a class="rd-dropdown-link" href="d_medical-record.php">看診紀錄</a>
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
                <li class="rd-nav-item"><a class="rd-nav-link" href="d_body-knowledge.php">身體小知識</a>
                </li>
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
          <!-- <p class="breadcrumbs-custom-subtitle">Ask us</p> -->
          <p class="heading-1 breadcrumbs-custom-title">看診紀錄</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="d_index.php">首頁</a></li>
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
            <!-- Bootstrap collapse-->
            <div class="accordion-custom-group accordion-custom-group-custom accordion-custom-group-corporate"
              id="accordion1" role="tablist" aria-multiselectable="false">

              <?php
              session_start(); // 啟用 Session
              
              include "db.php"; // 引入資料庫連線
              
              // 分頁設定
              $records_per_page = 10; // 每頁顯示的記錄數量
              $page = isset($_GET['page']) ? (int) $_GET['page'] : 1; // 當前頁數，預設為第 1 頁
              if ($page < 1)
                $page = 1;

              // 計算總記錄數和總頁數
              $count_sql = "SELECT COUNT(*) AS total FROM consultation";
              $count_result = mysqli_query($link, $count_sql);
              $count_row = mysqli_fetch_assoc($count_result);
              $total_records = $count_row['total']; // 總記錄數
              $total_pages = ceil($total_records / $records_per_page); // 總頁數
              
              // 計算當前頁的起始記錄
              $offset = ($page - 1) * $records_per_page;

              // 查詢當前頁的看診紀錄（關聯 people 和 doctors 表）
              $sql = "SELECT 
            c.consultation_id AS id, 
            p.name AS patient_name, 
            p.gender AS patient_gender, 
            p.phone AS patient_phone, 
            d.name AS doctor_name, 
            d.specialty AS doctor_specialty, 
            c.date AS consultation_date, 
            c.time AS consultation_time, 
            c.diagnosis, 
            c.created_at 
        FROM 
            consultation c
        JOIN 
            people p ON c.people_id = p.people_id
        JOIN 
            doctors d ON c.doctor_id = d.doctor_id
        ORDER BY 
            c.date DESC, c.time DESC
        LIMIT $offset, $records_per_page";

              $result = mysqli_query($link, $sql);

              // 顯示表格
              echo "<h1 style='text-align: center;'>看診紀錄</h1>";
              // if (mysqli_num_rows($result) > 0) {
              echo "<table border='1' style='border-collapse: collapse; width: 100%; text-align: center;'>
            <tr style='background-color: #f2f2f2;'>
                <th style='border: 1px solid black; padding: 8px;'>編號</th>
                <th style='border: 1px solid black; padding: 8px;'>病人姓名</th>
                <th style='border: 1px solid black; padding: 8px;'>性別</th>
                <th style='border: 1px solid black; padding: 8px;'>聯絡電話</th>
                <th style='border: 1px solid black; padding: 8px;'>醫生姓名</th>
                <th style='border: 1px solid black; padding: 8px;'>專科</th>
                <th style='border: 1px solid black; padding: 8px;'>看診日期</th>
                <th style='border: 1px solid black; padding: 8px;'>看診時間</th>
                <th style='border: 1px solid black; padding: 8px;'>診斷結果</th>
                <th style='border: 1px solid black; padding: 8px;'>建立時間</th>
            </tr>";
              while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                <td style='border: 1px solid black; padding: 8px;'>{$row['id']}</td>
                <td style='border: 1px solid black; padding: 8px;'>{$row['patient_name']}</td>
                <td style='border: 1px solid black; padding: 8px;'>{$row['patient_gender']}</td>
                <td style='border: 1px solid black; padding: 8px;'>{$row['patient_phone']}</td>
                <td style='border: 1px solid black; padding: 8px;'>{$row['doctor_name']}</td>
                <td style='border: 1px solid black; padding: 8px;'>{$row['doctor_specialty']}</td>
                <td style='border: 1px solid black; padding: 8px;'>{$row['consultation_date']}</td>
                <td style='border: 1px solid black; padding: 8px;'>{$row['consultation_time']}</td>
                <td style='border: 1px solid black; padding: 8px;'>{$row['diagnosis']}</td>
                <td style='border: 1px solid black; padding: 8px;'>{$row['created_at']}</td>
            </tr>";
              }
              echo "</table>";

              // 顯示頁碼和總頁數
              echo "<div style='margin-top: 20px; text-align: center;'>";
              echo "<p>第 $page 頁，共 $total_pages 頁</p>";

              // 顯示頁碼連結
              if ($page > 1) {
                echo "<a href='?page=" . ($page - 1) . "' style='margin-right: 10px;'>上一頁</a>";
              }
              for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                  echo "<strong>$i</strong> ";
                } else {
                  echo "<a href='?page=$i'>$i</a> ";
                }
              }
              if ($page < $total_pages) {
                echo "<a href='?page=" . ($page + 1) . "' style='margin-left: 10px;'>下一頁</a>";
              }
              echo "</div>";
              // } else {
              //   echo "<p style='text-align: center;'>目前沒有看診紀錄。</p>";
              // }
              
              // 關閉資料庫連線
              mysqli_close($link);
              ?>

              <!-- <div class="accordion-custom-item accordion-custom-corporate">
                <h4 class="accordion-custom-heading" id="accordion1-accordion-head-klskjoon">
                  <button class="accordion-custom-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accordion1-accordion-body-gmraurpr"
                    aria-controls="accordion1-accordion-body-gmraurpr" aria-expanded="true">How can I change something
                    in my order?<span class="accordion-custom-arrow"></span>
                  </button>
                </h4>
                <div class="accordion-custom-collapse collapse show" id="accordion1-accordion-body-gmraurpr"
                  aria-labelledby="accordion1-accordion-head-klskjoon" data-bs-parent="#accordion1">
                  <div class="accordion-custom-body">
                    <p>If you need to change something in your order, please contact us immediately. We usually process
                      orders within 30 minutes, and once we have processed your order, we will be unable to make any
                      changes.</p>
                  </div>
                </div>
              </div> -->
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
          <div class="col-md-4">
            <h4>關於我們</h4>
            <p class="me-xl-5">Pract is a learning platform for education and skills training. We provide you
              professional knowledge using innovative approach.</p>
          </div>
          <div class="col-md-3">
            <h4>快速連結</h4>
            <ul class="list-marked">
              <li><a href="d_index.php">首頁</a></li>
              <li><a href="d_appointment.php">預約</a></li>
              <li><a href="d_numberpeople.php">當天人數及時段</a></li>
              <li><a href="d_doctorshift.php">班表時段</a></li>
              <li><a href="d_medical-record.php">看診紀錄</a></li>
              <li><a href="d_appointment-records.php">預約紀錄</a></li>
              <li><a href="d_body-knowledge.php">身體小知識</a></li>
            </ul>
          </div>
          <div class="col-md-5">
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
          </div>
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