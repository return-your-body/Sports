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


//預約
include "../db.php"; // 引入資料庫連線

// 查詢姓名 (people)
$query_people = "SELECT people_id, name FROM people";
$result_people = mysqli_query($link, $query_people);
if (!$result_people) {
  die("查詢姓名失敗: " . mysqli_error($link));
}

// 查詢預約時間 (shifttime)
$query_shifttime = "SELECT shifttime_id, shifttime FROM shifttime";
$result_shifttime = mysqli_query($link, $query_shifttime);
if (!$result_shifttime) {
  die("查詢時間失敗: " . mysqli_error($link));
}

// 查詢治療師姓名 (doctor)
$query_doctor = "SELECT doctor.doctor_id, doctor.doctor 
     FROM doctor
     INNER JOIN user ON doctor.user_id = user.user_id
     WHERE user.grade_id = 2";
$result_doctor = mysqli_query($link, $query_doctor);
if (!$result_doctor) {
  die("查詢治療師失敗: " . mysqli_error($link));
}
?>

<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

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
    /* 禁用按鈕樣式 */
    button.disabled {
      background-color: #ccc;
      /* 灰色背景 */
      color: #666;
      /* 灰色文字 */
      border: 1px solid #999;
      /* 灰色邊框 */
      cursor: not-allowed;
      /* 禁用鼠標樣式 */
      pointer-events: none;
      /* 禁止所有事件 */
    }

    /* 排班資訊樣式 */
    .shift-info {
      margin: 5px 0;
      font-size: 14px;
      color: #555;
    }

    /* 預約按鈕樣式 */
    .shift-info button {
      background-color: #008CBA;
      color: white;
      border: none;
      padding: 5px 10px;
      font-size: 12px;
      cursor: pointer;
      border-radius: 3px;
    }

    .shift-info button:hover {
      background-color: #005f7f;
    }

    /* 無排班提示樣式 */
    .no-schedule {
      font-size: 18px;
      color: #aaa;
    }

    /* 當前日期高亮樣式 */
    .today {
      background-color: #ffeb3b;
    }

    /* 基本樣式調整 */
    .table-custom {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .table-custom th,
    .table-custom td {
      border: 1px solid #ddd;
      text-align: center;
      vertical-align: middle;
      padding: 10px;
      word-wrap: break-word;
      /* 避免文字超出格子 */
    }

    /* 手機設備響應式設計 */
    @media (max-width: 768px) {
      .table-custom {
        display: block;
        /* 將表格改為 block，允許水平滾動 */
        overflow-x: auto;
        /* 加入水平滾動 */
        white-space: nowrap;
        /* 禁止自動換行 */
      }

      .table-custom th,
      .table-custom td {
        font-size: 12px;
        /* 縮小文字大小 */
        padding: 5px;
        /* 減小內邊距 */
      }

      .shift-info button {
        font-size: 10px;
        /* 縮小按鈕文字 */
        padding: 5px;
        /* 減小按鈕大小 */
      }
    }

    /* 極小設備 (手機) */
    @media (max-width: 480px) {

      .table-custom th,
      .table-custom td {
        font-size: 10px;
        padding: 3px;
      }

      .shift-info {
        display: block;
        font-size: 10px;
      }

      .shift-info button {
        font-size: 9px;
        padding: 3px;
      }
    }




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

    /* 醫生班表 */
    .table-custom {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .table-custom th,
    .table-custom td {
      border: 1px solid #ddd;
      text-align: left;
      /* 文字靠左對齊 */
      padding: 8px;
      white-space: nowrap;
    }

    .table-custom th {
      background-color: #00a79d;
      color: white;
      text-align: center;
    }

    .reservation-info {
      color: red;
      margin-top: 5px;
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
                <li class="rd-nav-item active"><a class="rd-nav-link" href="h_appointment.php">預約</a>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="#">醫生班表</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_doctorshift.php">治療師班表</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_numberpeople.php">當天人數及時段</a>
                    </li>
                  </ul>
                </li>

                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="#">列印</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_print-receipt.php">列印收據</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_print-appointment.php">列印預約單</a>
                    </li>
                  </ul>
                </li> -->
                <li class="rd-nav-item"><a class="rd-nav-link" href="#">紀錄</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_medical-record.php">看診紀錄</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_appointment-records.php">預約紀錄</a>
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
    <!--標題列-->

    <!--標題-->
    <div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
      <!-- Breadcrumbs-->
      <section class="breadcrumbs-custom breadcrumbs-custom-svg">
        <div class="container">
          <!-- <p class="breadcrumbs-custom-subtitle">Our team</p> -->
          <p class="heading-1 breadcrumbs-custom-title">預約</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="h_index.php">首頁</a></li>
            <li class="active">預約</li>
          </ul>
        </div>
      </section>
    </div>
    <!--標題-->

    <!-- 預約-->
    <section class="section section-lg novi-bg novi-bg-img bg-default">
      <div class="container">
        <div class="row row-40 row-lg-50">
          <div class="form-container">
            <h3 style="text-align: center;">預約表單</h3>
         
            <form action="預約.php" method="post">

              <label for="people_id">姓名：</label>
              <select id="people_id" name="people_id" required>
                <option value="">請選擇姓名</option>
                <?php while ($row = mysqli_fetch_assoc($result_people)): ?>
                  <option value="<?= htmlspecialchars($row['people_id']); ?>">
                    <?= htmlspecialchars($row['name']); ?>
                  </option>
                <?php endwhile; ?>
              </select>

              <label for="date">預約日期：</label>
              <input type="date" id="date" name="date" required min="<?= date('Y-m-d'); ?>">

              <label for="time">預約時間：</label>
              <select id="time" name="time" required>
                <option value="">請選擇時間</option>
                <?php while ($row = mysqli_fetch_assoc($result_shifttime)): ?>
                  <option value="<?= htmlspecialchars($row['shifttime_id']); ?>">
                    <?= htmlspecialchars($row['shifttime']); ?>
                  </option>
                <?php endwhile; ?>
              </select>

              <label for="doctor">治療師姓名：</label>
              <select id="doctor" name="doctor" required>
                <option value="">請選擇治療師</option>
                <?php while ($row = mysqli_fetch_assoc($result_doctor)): ?>
                  <option value="<?= htmlspecialchars($row['doctor_id']); ?>">
                    <?= htmlspecialchars($row['doctor']); ?>
                  </option>
                <?php endwhile; ?>
              </select>

              <label for="note">備註：</label>
              <textarea id="note" name="note" rows="4" maxlength="200" placeholder="請輸入備註，最多200字"></textarea>

              <button type="submit">提交預約</button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- 預約-->

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
              <li><a href="h_appointment.php">預約</a></li>
              <li><a href="h_numberpeople.php">當天人數及時段</a></li>
              <li><a href="h_doctorshift.php">班表時段</a></li>
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