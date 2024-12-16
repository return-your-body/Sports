<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
  <!-- Site Title-->
  <title>助手-列印預約單</title>
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
                <li class="rd-nav-item"><a class="rd-nav-link" href="h_numberpeople.php">當天人數及時段</a>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="h_appointment.php">預約</a>
                  <!-- <ul class="rd-menu rd-navbar-dropdown">
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="single-teacher.html">Single teacher</a>
                      </li>
                    </ul> -->
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="h_doctorshift.php">醫生班表</a>
                  <!-- <ul class="rd-menu rd-navbar-dropdown">
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="single-course.html">Single course</a>
                      </li>
                    </ul> -->
                </li>
                <li class="rd-nav-item active"><a class="rd-nav-link" href="#">列印</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_print-receipt.php">列印收據</a>
                    </li>
                    <li class="rd-dropdown-item active"><a class="rd-dropdown-link"
                        href="h_print-appointment.php">列印預約單</a>
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
                <li class="rd-nav-item"><a class="rd-nav-link" href="h_patient-needs.php">病患需求</a>
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


    <!--標題-->
    <div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
      <!-- Breadcrumbs-->
      <section class="breadcrumbs-custom breadcrumbs-custom-svg">
        <div class="container">
          <!-- <p class="breadcrumbs-custom-subtitle">Get in Touch with Us</p> -->
          <p class="heading-1 breadcrumbs-custom-title">列印預約單</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="h_index.php">首頁</a></li>
            <li class="active">列印預約單</li>
          </ul>
        </div>
      </section>
    </div>
    <!--標題-->


    <!--列印預約單-->
    <section class="section section-lg bg-default novi-bg novi-bg-img">
      <div class="container">
        <dl class="list-terms">
          <title>預約單</title>
          <style>
            /* 通用樣式 */
            /* body {
              margin: 0;
              padding: 0;
              font-family: Arial, sans-serif;
              text-align: center;
            } */

            #print-area {
              width: 350px;
              margin: 50px auto;
              /* 區塊垂直居中 */
              border: 2px solid black;
              padding: 20px;
              text-align: left;
              box-sizing: border-box;
            }

            #print-area h1 {
              text-align: center;
              margin: 0 0 20px;
              font-size: 24px;
            }

            #print-area p {
              margin: 10px 0;
              line-height: 1.6;
              font-size: 16px;
            }

            .doctor-sign {
              margin-top: 20px;
              font-weight: bold;
            }

            /* 列印樣式 */
            @media print {
              body * {
                visibility: hidden;
              }

              #print-area,
              #print-area * {
                visibility: visible;
              }

              #print-area {
                margin: 0 auto;
                page-break-inside: avoid;
              }

              html,
              body {
                height: 100%;
              }

              button {
                display: none;
                /* 隱藏按鈕 */
              }
            }

            /* 按鈕樣式 */
            button {
              margin: 20px auto 0;
              /* 距離表格的間距 */
              display: block;
              /* 讓按鈕居中 */
              padding: 10px 20px;
              font-size: 16px;
              cursor: pointer;
            }
          </style>

          <?php
          session_start();
          include "../db.php"; // 引入資料庫連線
          
          // 確認 appointment_id 是否存在
          if (!isset($_GET['appointment_id']) || empty($_GET['appointment_id'])) {
            die("<p>無效的預約單 ID，請返回重試。</p>");
          }

          // 取得預約單 ID
          $appointment_id = mysqli_real_escape_string($link, $_GET['appointment_id']);

          // 查詢關聯資料
          $sql = "SELECT 
            p.name AS people_name, 
            CASE 
                WHEN p.gender_id = 1 THEN '男'
                WHEN p.gender_id = 2 THEN '女'
                ELSE '其他'
            END AS people_gender,
            p.birthday AS people_birthday,
            a.appointment_date AS appointment_date,
            st.shifttime AS appointment_time,
            d.doctor AS doctor_name
        FROM 
            appointment a
        LEFT JOIN 
            people p ON a.people_id = p.people_id
        LEFT JOIN 
            shifttime st ON a.shifttime_id = st.shifttime_id
        LEFT JOIN 
            doctor d ON a.doctor_id = d.doctor_id
        WHERE 
            a.appointment_id = '$appointment_id'";

          $result = mysqli_query($link, $sql);

          if (!$result) {
            die("<p>SQL 執行失敗：" . mysqli_error($link) . "</p>");
          }

          if (mysqli_num_rows($result) == 0) {
            die("<p>找不到對應的預約資料，請返回重試。</p>");
          }

          // 提取資料
          $row = mysqli_fetch_assoc($result);
          $people_name = $row['people_name'];
          $people_gender = $row['people_gender'];
          $people_birthday = $row['people_birthday'];
          $appointment_date = $row['appointment_date'];
          $appointment_time = $row['appointment_time'];
          $doctor_name = $row['doctor_name'];

          mysqli_close($link); // 關閉資料庫連接
          ?>
          <!DOCTYPE html>
          <html lang="zh-Hant">

          <head>
            <meta charset="UTF-8">
            <title>預約單</title>
          </head>

          <body>
            <!-- 預約單內容 -->
            <h1>預約單</h1>
            <p>姓名：<?php echo htmlspecialchars($people_name); ?></p>
            <p>性別：<?php echo htmlspecialchars($people_gender); ?></p>
            <p>生日：<?php echo htmlspecialchars($people_birthday); ?></p>
            <p>預約日期：<?php echo htmlspecialchars($appointment_date); ?></p>
            <p>預約時間：<?php echo htmlspecialchars($appointment_time); ?></p>
            <p>治療師：<?php echo htmlspecialchars($doctor_name); ?></p>
            <p>列印時間：<?php echo date('Y-m-d H:i:s'); ?></p>
          </body>

          </html>


          <!-- 預約單內容 -->
          <div id="print-area">
            <h1 style="text-align: center;">預約單</h1>
            <p>姓名：<?php echo htmlspecialchars($people_name); ?></p>
            <p>性別：<?php echo htmlspecialchars($people_gender); ?></p>
            <p>生日：<?php echo htmlspecialchars($people_birthday); ?></p>
            <p>預約日期：<?php echo htmlspecialchars($appointment_date); ?></p>
            <p>預約時間：<?php echo htmlspecialchars($appointment_time); ?></p>
            <p>治療師：<?php echo htmlspecialchars($doctor_name); ?></p>
            <p>列印時間：<?php echo date('Y-m-d H:i:s'); ?></p>
          </div>

          <!-- 列印按鈕 -->
          <button onclick="window.print()">列印預約單</button>

          <!-- <dt class="heading-6">General information</dt>
          <dd>
            Welcome to our Privacy Policy page! When you use our web site services, you trust us with your
            information. This Privacy Policy is meant to help you understand what data we collect, why we
            collect it, and what we do with it. When you share information with us, we can make our services
            even better for you. For instance, we can show you more relevant search results and ads, help
            you connect with people or to make sharing with others quicker and easier. As you use our
            services, we want you to be clear how we’re using information and the ways in which you can
            protect your privacy. This is important; we hope you will take time to read it carefully.
            Remember, you can find controls to manage your information and protect your privacy and
            security. We’ve tried
            to keep it as simple as possible.
          </dd> -->
        </dl>
      </div>
    </section>
    <!--列印預約單-->

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
              <li><a href="h_index.php">首頁</a></li>
              <li><a href="h_appointment.php">預約</a></li>
              <li><a href="h_numberpeople.php">當天人數及時段</a></li>
              <li><a href="h_doctorshift.php">班表時段</a></li>
              <li><a href="h_print-receipt.php">列印收據</a></li>
              <li><a href="h_print-appointment.php">列印預約單</a></li>
              <li><a href="h_patient-needs.php">患者需求</a></li>
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
  <!-- coded by Himic-->
</body>

</html>