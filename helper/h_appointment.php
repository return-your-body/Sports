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

    /* 預約 */
    body {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      color: #333;
    }

    .form-container {
      background-color: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }

    .form-label {
      font-weight: bold;
      font-size: 14px;
      color: #555;
    }

    .form-control,
    .form-select {
      border: 1px solid #ddd;
      border-radius: 5px;
      padding: 10px;
      font-size: 14px;
      width: 100%;
      background-color: #f9f9f9;
      box-shadow: inset 0px 1px 3px rgba(0, 0, 0, 0.1);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0px 0px 5px rgba(0, 123, 255, 0.5);
    }

    textarea.form-control {
      resize: none;
    }

    .btn-primary {
      background-color: #007bff;
      border: none;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
      border-radius: 5px;
      transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    .text-center {
      text-align: center;
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
                <li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_people.php">用戶資料</a>
                    </li>
                  </ul>
                </li>
                <!-- <li class="rd-nav-item active"><a class="rd-nav-link" href="h_appointment.php">預約</a>
                </li> -->
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

    <!-- 預約 -->
    <?php
    require '../db.php';
    session_start();

    // 確認登入角色
    $user_role = $_SESSION['role'] ?? null; // 確保有設定角色
    $doctors = []; // 初始化醫生陣列
    
    if ($user_role === 'helper') {
      // 查詢醫生資料
      $query = "
        SELECT d.doctor_id, d.doctor 
        FROM doctor d
        INNER JOIN user u ON d.user_id = u.user_id
        WHERE u.grade_id = 2
    ";
      $result = mysqli_query($link, $query);
      if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
          $doctors[] = $row;
        }
      }
    }

    // 如果沒有醫生資料
    if (empty($doctors)) {
      $doctors[] = ['doctor_id' => '', 'doctor' => '目前無醫生可供選擇'];
    }

    // 確認患者資料
    $people_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $query_people = "SELECT name FROM people WHERE people_id = ?";
    $stmt_people = $link->prepare($query_people);
    $stmt_people->bind_param("i", $people_id);
    $stmt_people->execute();
    $result_people = $stmt_people->get_result();
    $person = $result_people->fetch_assoc();

    // 關閉資料庫連線
    $stmt_people->close();
    $link->close();
    ?>


    <section class="section section-lg novi-bg novi-bg-img bg-default">
      <div class="container">
        <div class="form-container shadow-lg p-4 rounded bg-light">
          <h3 class="text-center mb-4">預約表單</h3>
          <form action="\u9810\u7d04.php" method="post">
            <!-- 使用者姓名 -->
            <div class="form-group mb-3">
              <label for="people_name" class="form-label">姓名：</label>
              <input type="text" id="people_name" name="people_name" class="form-control"
                value="<?= htmlspecialchars($person['name'] ?? '未知患者'); ?>" readonly required />
            </div>

            <!-- 醫生姓名 -->
            <div class="form-group mb-3">
              <select id="doctor_id" name="doctor_id" class="form-select" required>
                <option value="">請選擇醫生</option>
                <?php foreach ($doctors as $doctor): ?>
                  <option value="<?= htmlspecialchars($doctor['doctor_id']); ?>">
                    <?= htmlspecialchars($doctor['doctor']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- 預約日期 -->
            <div class="form-group mb-3">
              <label for="date" class="form-label">預約日期：</label>
              <input type="date" id="date" name="date" class="form-control" required min="<?= date('Y-m-d'); ?>"
                max="<?= date('Y-m-d', strtotime('+30 days')); ?>" />
            </div>

            <!-- 預約時間 -->
            <div class="form-group mb-3">
              <label for="time" class="form-label">預約時間：</label>
              <select id="time" name="time" class="form-select" required>
                <option value="">請選擇時間</option>
              </select>
            </div>

            <!-- 備註 -->
            <div class="form-group mb-4">
              <label for="note" class="form-label">備註：</label>
              <textarea id="note" name="note" class="form-control" rows="4" maxlength="200"
                placeholder="請輸入備註，最多200字"></textarea>
            </div>

            <!-- 提交按鈕 -->
            <div class="text-center">
              <button type="submit" class="btn btn-primary px-4 py-2">提交預約</button>
              <button type="button" onclick="location.href='h_people.php'"
                class="btn btn-secondary px-4 py-2">返回</button>
            </div>
          </form>
        </div>
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
              <li><a href="h_index.php">首頁</a></li>
              <li><a href="h_people.php">用戶資料</a></li>
              <!-- <li><a href="h_appointment.php">預約</a></li> -->
              <li><a href="h_numberpeople.php">當天人數及時段</a></li>
              <li><a href="h_doctorshift.php">班表時段</a></li>
              <li><a href="h_medical-record.php">看診紀錄</a></li>
              <li><a href="h_appointment-records.php">預約紀錄</a></li>
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