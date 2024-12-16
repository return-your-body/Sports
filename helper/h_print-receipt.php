<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
  <!-- Site Title-->
  <title>助手-列印收據</title>
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
                    <li class="rd-dropdown-item active"><a class="rd-dropdown-link" href="h_print-receipt.php">列印收據</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_print-appointment.php">列印預約單</a>
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
          <p class="heading-1 breadcrumbs-custom-title">列印收據</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="h_index.php">首頁</a></li>
            <li class="active">列印收據</li>
          </ul>
        </div>
      </section>
    </div>
    <!--標題-->


    <!--列印收據-->
    <section class="section section-lg bg-default text-center">
      <div class="container">
        <div class="row row-50 justify-content-lg-center">
          <div class="col-lg-10 col-xl-8">
            <!-- Bootstrap collapse-->
            <div class="accordion-custom-group accordion-custom-group-custom accordion-custom-group-corporate"
              id="accordion1" role="tablist" aria-multiselectable="false">
              <dl class="list-terms">

                <?php
                // 假設這些資料來自資料庫
                $people_name = "李XX";
                $people_idcard = "A12XXX";
                $appointment_date = "2024-12-20";
                $doctor_name = "治療師川";
                $items = [
                  ["item" => "A", "price" => 200],
                  ["item" => "B", "price" => 200],
                  ["item" => "C", "price" => 500],
                ];

                $total_cost = array_sum(array_column($items, "price"));
                ?>

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

                <!-- 收據顯示區域 -->
                <div id="print-area">
                  <h1>看診收據</h1>
                  <p>姓名：<?php echo $people_name; ?></p>
                  <p>病歷號：<?php echo $people_idcard; ?></p>
                  <p>就診日期：<?php echo $appointment_date; ?></p>
                  <p>治療師：<?php echo $doctor_name; ?></p>
                  <table style="border-collapse: collapse; width: 100%; text-align: center;">
                    <tr>
                      <th style="border: 1px solid black;">治療項目</th>
                      <th style="border: 1px solid black;">費用</th>
                    </tr>
                    <?php foreach ($items as $item): ?>
                      <tr>
                        <td style="border: 1px solid black;"><?php echo $item['item']; ?></td>
                        <td style="border: 1px solid black;"><?php echo $item['price']; ?></td>
                      </tr>
                    <?php endforeach; ?>
                    <tr>
                      <td style="border: 1px solid black;"><strong>總費用</strong></td>
                      <td style="border: 1px solid black;"><strong><?php echo $total_cost; ?></strong></td>
                    </tr>
                  </table>

                  <p>列印時間：<?php echo date('Y-m-d H:i:s'); ?></p>
                </div>

                <!-- 列印按鈕 -->
                <button onclick="window.print()">列印收據</button>

                <!-- <div class="accordion-custom-item accordion-custom-corporate">
                <h4 class="accordion-custom-heading" id="accordion1-accordion-head-aogsrrgj">
                  <button class="accordion-custom-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accordion1-accordion-body-iqrnvtmc"
                    aria-controls="accordion1-accordion-body-iqrnvtmc" aria-expanded="false">Can I track my order?<span
                      class="accordion-custom-arrow"></span>
                  </button>
                </h4>
                <div class="accordion-custom-collapse collapse" id="accordion1-accordion-body-iqrnvtmc"
                  aria-labelledby="accordion1-accordion-head-aogsrrgj" data-bs-parent="#accordion1">
                  <div class="accordion-custom-body">
                    <p>Yes, you can! After placing your order you will receive an order confirmation via email. Each
                      order starts production 24 hours after your order is placed. Within 72 hours of you placing your
                      order, you will receive an expected delivery date.</p>
                  </div>
                </div>
              </div> -->
              </dl>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!--列印收據-->

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
</body>

</html>