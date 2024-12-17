<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
<?php
session_start();

if (!isset($_SESSION["登入狀態"])) {
  header("Location: login.html");
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

    <!-- hi, we are brave-->
    <section class="section section-lg bg-default novi-bg novi-bg-img">
      <div class="container">
        <div
          class="row row-ten row-50 justify-content-md-center align-items-lg-center justify-content-xl-between flex-lg-row-reverse">
          <div class="col-md-8 col-lg-5 col-xl-4">
            <h3>Who we are</h3>
            <p class="heading-5">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
              incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostru.</p>
            <p class="text-spacing-sm">Exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute
              irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur
              sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit.</p><a
              class="button button-primary button-nina" href="#">Learn more</a>
          </div>
          <div class="col-md-8 col-lg-5"><img src="images/about-us-01-570x535.jpg" alt="" width="570" height="535" />
          </div>
        </div>
      </div>
    </section>

    <!-- Small Features-->
    <section class="section section-lg bg-gray-100 novi-bg novi-bg-img"
      data-preset='{"title":"Services 1","category":"services","reload":false,"id":"services-1"}'>
      <div class="container-wide">
        <div class="row row-50 justify-content-sm-center text-gray-light">
          <div class="col-sm-10 col-md-6 col-xl-3">
            <article class="box-minimal">
              <div class="box-minimal-header">
                <div class="box-minimal-icon box-minimal-icon-lg novi-icon mdi mdi-thumb-up-outline"></div>
                <h6 class="box-minimal-title">Individual approach</h6>
              </div>
              <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam
                rem aperiam, eaque ipsa quae ab illo.</p>
            </article>
          </div>
          <div class="col-sm-10 col-md-6 col-xl-3">
            <article class="box-minimal">
              <div class="box-minimal-header">
                <div class="box-minimal-icon box-minimal-icon-lg novi-icon mdi mdi-account-multiple"></div>
                <h6 class="box-minimal-title">Qualified instructors</h6>
              </div>
              <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni
                dolores eos qui ratione voluptatem.</p>
            </article>
          </div>
          <div class="col-sm-10 col-md-6 col-xl-3">
            <article class="box-minimal">
              <div class="box-minimal-header">
                <div class="box-minimal-icon box-minimal-icon-lg novi-icon mdi mdi-headset"></div>
                <h6 class="box-minimal-title">24/7 online support</h6>
              </div>
              <p>Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia
                non numquam eius modi tempora incidunt.</p>
            </article>
          </div>
          <div class="col-sm-10 col-md-6 col-xl-3">
            <article class="box-minimal">
              <div class="box-minimal-header">
                <div class="box-minimal-icon box-minimal-icon-lg novi-icon mdi mdi-credit-card"></div>
                <h6 class="box-minimal-title">Payment Methods</h6>
              </div>
              <p>Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut
                aliquid ex ea commodi consequatur.</p>
            </article>
          </div>
        </div>
      </div>
    </section>

    <!-- Our history-->
    <section class="section section-lg bg-default">
      <div class="container">
        <h3>Our history</h3>
        <div class="row row-ten row-50 justify-content-sm-center justify-content-xl-between">
          <div class="col-sm-9 col-md-12 col-xl-6">
            <p class="text-gray-light">At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis
              praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati
              cupiditate non provident.</p>
            <div class="time-line-vertical inset-md">
              <div class="time-line-vertical-element">
                <div class="unit unit-sm flex-column flex-md-row unit-spacing-xxl">
                  <div class="unit-left">
                    <div class="time-line-time">
                      <time class="wow fadeInLeft" data-wow-delay=".0s" datetime="2020">April 2010</time>
                    </div>
                  </div>
                  <div class="unit-body">
                    <div class="time-line-content wow fadeInRight" data-wow-delay=".1s">
                      <h5>We gathered a team of knowledgeable and experienced IT industry instructors.</h5>
                      <p>Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis
                        est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus.</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="time-line-vertical-element">
                <div class="unit flex-column flex-md-row unit-spacing-xxl">
                  <div class="unit-left">
                    <div class="time-line-time">
                      <time class="wow fadeInLeft" data-wow-delay=".1s" datetime="2020">September 2015</time>
                    </div>
                  </div>
                  <div class="unit-body">
                    <div class="time-line-content wow fadeInRight" data-wow-delay=".2s">
                      <h5>Extending the range of offered courses</h5>
                      <p>Omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut
                        officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et
                        molestiae</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="time-line-vertical-element">
                <div class="unit flex-column flex-md-row unit-spacing-xxl">
                  <div class="unit-left">
                    <div class="time-line-time">
                      <time class="wow fadeInLeft" data-wow-delay=".2s" datetime="2020">March 2021</time>
                    </div>
                  </div>
                  <div class="unit-body">
                    <div class="time-line-content wow fadeInRight" data-wow-delay=".3s">
                      <h5>Providing free & discounted online courses</h5>
                      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                        labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-9 col-md-12 col-xl-3">
            <div class="row row-30">
              <div class="col-md-6 col-xl-12 wow fadeInUp" data-wow-delay=".1s"><img src="images/about-us-2-420x280.jpg"
                  alt="" width="420" height="280" />
              </div>
              <div class="col-md-6 col-xl-12 wow fadeInUp" data-wow-delay=".2s"><img src="images/about-us-3-420x280.jpg"
                  alt="" width="420" height="280" />
              </div>
              <div class="col-md-6 col-xl-12 wow fadeInUp" data-wow-delay=".3s"><img src="images/about-us-4-420x280.jpg"
                  alt="" width="420" height="280" />
              </div>
              <div class="col-md-6 col-xl-12 wow fadeInUp" data-wow-delay=".3s"><img src="images/about-us-5-420x280.jpg"
                  alt="" width="420" height="280" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Popular courses-->
    <section class="section section-lg novi-bg novi-bg-img bg-gray-100">
      <div class="container">
        <h3 class="text-center">Popular courses</h3>
        <div class="row row-40 row-lg-50">
          <div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".1s">
            <div class="box-project box-width-3">
              <div class="box-project-media"><img class="box-project-img" src="images/home-04-360x345.jpg" alt=""
                  width="360" height="345" loading="lazy" />
              </div>
              <div class="box-project-subtitle small">Business</div>
              <h6 class="box-project-title"><a href="single-course.html">Development of your business skills</a></h6>
              <div class="box-project-meta">
                <div class="box-project-meta-item"><span class="box-project-meta-icon linearicons-clock3"></span><span
                    class="box-project-meta-text">23 hours</span></div>
                <div class="box-project-meta-item"><span
                    class="box-project-meta-icon linearicons-credit-card"></span><span
                    class="box-project-meta-text">$165</span></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".2s">
            <div class="box-project box-width-3">
              <div class="box-project-media"><img class="box-project-img" src="images/home-05-360x345.jpg" alt=""
                  width="360" height="345" loading="lazy" />
              </div>
              <div class="box-project-subtitle small">PROGRAMMING</div>
              <h6 class="box-project-title"><a href="single-course.html">Python programming</a></h6>
              <div class="box-project-meta">
                <div class="box-project-meta-item"><span class="box-project-meta-icon linearicons-clock3"></span><span
                    class="box-project-meta-text">46 hours</span></div>
                <div class="box-project-meta-item"><span
                    class="box-project-meta-icon linearicons-credit-card"></span><span
                    class="box-project-meta-text">$290</span></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".3s">
            <div class="box-project box-width-3">
              <div class="box-project-media"><img class="box-project-img" src="images/home-06-360x345.jpg" alt=""
                  width="360" height="345" loading="lazy" />
              </div>
              <div class="box-project-subtitle small">Programming</div>
              <h6 class="box-project-title"><a href="single-course.html">Data structures and algorithms</a></h6>
              <div class="box-project-meta">
                <div class="box-project-meta-item"><span class="box-project-meta-icon linearicons-clock3"></span><span
                    class="box-project-meta-text">18 hours</span></div>
                <div class="box-project-meta-item"><span
                    class="box-project-meta-icon linearicons-credit-card"></span><span
                    class="box-project-meta-text">$120</span></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".1s">
            <div class="box-project box-width-3">
              <div class="box-project-media"><img class="box-project-img" src="images/home-07-360x345.jpg" alt=""
                  width="360" height="345" loading="lazy" />
              </div>
              <div class="box-project-subtitle small">ARCHITECTURE, ART &amp; DESIGN</div>
              <h6 class="box-project-title"><a href="single-course.html">Global housing design</a></h6>
              <div class="box-project-meta">
                <div class="box-project-meta-item"><span class="box-project-meta-icon linearicons-clock3"></span><span
                    class="box-project-meta-text">34 hours</span></div>
                <div class="box-project-meta-item"><span
                    class="box-project-meta-icon linearicons-credit-card"></span><span
                    class="box-project-meta-text">$195</span></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".2s">
            <div class="box-project box-width-3">
              <div class="box-project-media"><img class="box-project-img" src="images/home-08-360x345.jpg" alt=""
                  width="360" height="345" loading="lazy" />
              </div>
              <div class="box-project-subtitle small">Business</div>
              <h6 class="box-project-title"><a href="single-course.html">IT fundamentals for business</a></h6>
              <div class="box-project-meta">
                <div class="box-project-meta-item"><span class="box-project-meta-icon linearicons-clock3"></span><span
                    class="box-project-meta-text">8 hours</span></div>
                <div class="box-project-meta-item"><span
                    class="box-project-meta-icon linearicons-credit-card"></span><span
                    class="box-project-meta-text">$75</span></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".3s">
            <div class="box-project box-width-3">
              <div class="box-project-media"><img class="box-project-img" src="images/home-09-360x345.jpg" alt=""
                  width="360" height="345" loading="lazy" />
              </div>
              <div class="box-project-subtitle small">Marketing</div>
              <h6 class="box-project-title"><a href="single-course.html">Online marketing: SEO and SMM</a></h6>
              <div class="box-project-meta">
                <div class="box-project-meta-item"><span class="box-project-meta-icon linearicons-clock3"></span><span
                    class="box-project-meta-text">43 hours</span></div>
                <div class="box-project-meta-item"><span
                    class="box-project-meta-icon linearicons-credit-card"></span><span
                    class="box-project-meta-text">$145</span></div>
              </div>
            </div>
          </div>
          <div class="col-12 text-center mt-2 mt-xl-4"><a class="button button-primary button-nina"
              href="courses.html">All courses</a></div>
        </div>
      </div>
    </section>

    <section class="section section-md bg-accent text-center text-md-start">
      <div class="container">
        <div class="row">
          <div class="col-xl-11">
            <div class="box-cta box-cta-inline">
              <div class="box-cta-inner">
                <h3 class="box-cta-title"><span class="box-cta-icon icon-custom-briefcase"></span><span>Free
                    courses</span></h3>
                <p>Free courses contain industry-relevant content and practical tasks and projects.</p>
              </div>
              <div class="box-cta-inner"><a class="button button-dark button-nina" href="contacts.html">Contact us</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

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