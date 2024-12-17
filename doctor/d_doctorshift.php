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
                  <!-- <ul class="rd-menu rd-navbar-dropdown">
                      <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="single-teacher.html"></a>
                      </li>
                    </ul> -->
                </li>
                <li class="rd-nav-item active"><a class="rd-nav-link" href="d_doctorshift.php">班表時段</a>
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
    <!-- a few words about us-->
    <section class="section section-lg text-center text-md-start bg-default">
      <div class="container">
        <div class="row row-50 justify-content-md-center justify-content-xl-end justify-content-xxl-end">
          <div class="col-md-9 col-lg-8 col-xl-6 col-xxl-5">
            <div class="box-range-content">
              <h3>A Variety of Courses</h3>
              <p class="text-spacing-sm">Nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit
                in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><a
                class="button button-primary button-nina" href="about-us.html">learn more</a>
            </div>
          </div>
          <div class="col-md-9 col-lg-8 col-xl-6 col-xxl-6 jp-video-init">
            <div class="build-video">
              <div class="build-video-inner"><img class="image-wrap" src="images/block-video-870x500.png" alt=""
                  width="870" height="500" />
              </div>
              <div class="build-video-element">
                <div class="jp-video jp-video-single">
                  <div class="jp-type-playlist">
                    <!-- Hidden playlist for script-->
                    <ul class="jp-player-list">
                      <li class="jp-player-list-item" data-jp-m4v="video/video-bg.mp4" data-jp-title="local video"
                        data-jp-poster="video/video-bg.jpg"></li>
                    </ul>
                    <!-- container in which our video will be played-->
                    <div class="jp-jplayer"></div>
                    <!-- main containers for our controls-->
                    <div class="jp-gui">
                      <div class="jp-interface">
                        <div class="jp-controls-holder">
                          <!-- play and pause buttons--><a class="jp-play" href="javascript:;" tabindex="1">play</a><a
                            class="jp-pause" href="javascript:;" tabindex="1">pause</a><span
                            class="separator sep-1"></span>
                          <!-- progress bar-->
                          <div class="jp-progress">
                            <div class="jp-seek-bar">
                              <div class="jp-play-bar"><span></span></div>
                            </div>
                          </div>
                          <div class="jp-time-wrapper">
                            <!-- time notifications-->
                            <div class="jp-current-time"></div><span class="time-sep">/</span>
                            <div class="jp-duration"></div>
                          </div><span class="separator sep-2"></span>
                          <!-- mute / unmute toggle--><a class="jp-mute" href="javascript:;" tabindex="1"
                            title="mute">mute</a><a class="jp-unmute" href="javascript:;" tabindex="1"
                            title="unmute">unmute</a>
                          <!-- volume bar-->
                          <div class="jp-volume-bar">
                            <div class="jp-volume-bar-value"><span class="handle"></span></div>
                          </div><span class="separator sep-2"></span>
                          <!-- full screen toggle--><a class="jp-full-screen" href="javascript:;" tabindex="1"
                            title="full screen">full screen</a><a class="jp-restore-screen" href="javascript:;"
                            tabindex="1" title="restore screen">restore screen</a>
                        </div>
                        <!-- end jp-controls-holder-->
                      </div>
                      <!-- end jp-interface-->
                    </div>
                    <!-- end jp-gui-->
                    <!-- unsupported message-->
                    <div class="jp-playlist">
                      <ul>
                        <li></li>
                      </ul>
                    </div>
                    <div class="jp-no-solution"><span>Update Required</span>Here's a message which will appear if the
                      video isn't supported. A Flash alternative can be used here if you fancy it.</div>
                    <!-- end jp_container_1-->
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-lg text-center bg-gray-100">
      <div class="container-wide">
        <h3>Our advantages</h3>
        <div class="row row-50 justify-content-sm-center text-start">
          <div class="col-sm-10 col-md-6 col-xl-3">
            <article class="box-minimal box-minimal-border">
              <div class="box-minimal-icon novi-icon mdi mdi-thumb-up-outline"></div>
              <p class="big box-minimal-title">Individual Approach</p>
              <hr>
              <div class="box-minimal-text">Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit,
                sed quia consequuntur magni dolores eos qui ratione.</div>
            </article>
          </div>
          <div class="col-sm-10 col-md-6 col-xl-3">
            <article class="box-minimal box-minimal-border">
              <div class="box-minimal-icon novi-icon mdi mdi-account-multiple"></div>
              <p class="big box-minimal-title">Qualified Employees</p>
              <hr>
              <div class="box-minimal-text">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium
                doloremque laudantium, totam rem aperiam, eaque ipsa.</div>
            </article>
          </div>
          <div class="col-sm-10 col-md-6 col-xl-3">
            <article class="box-minimal box-minimal-border">
              <div class="box-minimal-icon novi-icon mdi mdi-headset"></div>
              <p class="big box-minimal-title">24/7 Online Support</p>
              <hr>
              <div class="box-minimal-text">Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet,
                consectetur, adipisci velit, sed quia non numquam eius modi.</div>
            </article>
          </div>
          <div class="col-sm-10 col-md-6 col-xl-3">
            <article class="box-minimal box-minimal-border">
              <div class="box-minimal-icon novi-icon mdi mdi-credit-card"></div>
              <p class="big box-minimal-title">Various Payment Methods</p>
              <hr>
              <div class="box-minimal-text">Dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis
                nostrum exercitationem ullam corporis suscipit labori.</div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-wrap section-wrap-equal">
      <div class="section-wrap-inner">
        <div class="container container-bigger">
          <div
            class="row row-fix row-ten justify-content-md-center justify-content-lg-start justify-content-xl-between">
            <div class="col-md-8 col-lg-4">
              <div class="section-lg">
                <h3>Development of your business skills</h3>
                <div class="divider divider-default"></div>
                <p>Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae
                  consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur fugiat quo.</p><a
                  class="button button-primary button-nina" href="single-course.html">Learn more</a>
              </div>
            </div>
          </div>
        </div>
        <div class="section-wrap-aside section-wrap-image"><img src="images/services-1-960x660.jpg" alt="" width="960"
            height="660" />
        </div>
      </div>
    </section>
    <section class="section section-wrap section-wrap-equal section-lg-reverse">
      <div class="section-wrap-inner">
        <div class="container container-bigger">
          <div class="row row-fix row-ten justify-content-md-center justify-content-lg-start justify-content-lg-end">
            <div class="col-md-8 col-lg-4">
              <div class="section-lg">
                <h3>Python programming</h3>
                <div class="divider divider-default"></div>
                <p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum
                  deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati.</p><a
                  class="button button-primary button-nina" href="single-course.html">Learn more</a>
              </div>
            </div>
          </div>
        </div>
        <div class="section-wrap-aside section-wrap-image"><img src="images/services-2-960x660.jpg" alt="" width="960"
            height="660" />
        </div>
      </div>
    </section>
    <section class="section section-wrap section-wrap-equal">
      <div class="section-wrap-inner">
        <div class="container container-bigger">
          <div
            class="row row-fix row-ten justify-content-md-center justify-content-lg-start justify-content-xl-between">
            <div class="col-md-8 col-lg-4">
              <div class="section-lg">
                <h3>Data structures and algorithms</h3>
                <div class="divider divider-default"></div>
                <p>Non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum
                  fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero.</p><a
                  class="button button-primary button-nina" href="single-course.html">Learn more</a>
              </div>
            </div>
          </div>
        </div>
        <div class="section-wrap-aside section-wrap-image"><img src="images/services-3-960x660.jpg" alt="" width="960"
            height="660" />
        </div>
      </div>
    </section>
    <section class="section section-wrap section-wrap-equal section-lg-reverse">
      <div class="section-wrap-inner">
        <div class="container container-bigger">
          <div class="row row-ten justify-content-md-center justify-content-lg-start justify-content-lg-end">
            <div class="col-md-8 col-lg-4">
              <div class="section-lg">
                <h3>Global housing design</h3>
                <div class="divider divider-default"></div>
                <p>Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et
                  voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic.</p><a
                  class="button button-primary button-nina" href="single-course.html">Learn more</a>
              </div>
            </div>
          </div>
        </div>
        <div class="section-wrap-aside section-wrap-image"><img src="images/services-4-960x660.jpg" alt="" width="960"
            height="660" />
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