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

//班表 

?>

<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
  <!-- Site Title-->
  <title>助手-治療師班表時段</title>
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
    /* 電腦端樣式（保留原始設計） */
    .table-container {
      overflow-x: hidden;
      margin-top: 10px;
    }

    /* 手機端樣式（針對寬度小於768px的裝置） */
    @media screen and (max-width: 768px) {
      .table-container {
        overflow-x: auto;
        /* 啟用橫向滾動 */
      }

      .table-custom {
        width: 1200px;
        /* 設定寬度大於手機螢幕，讓用戶可以滑動 */
        min-width: 768px;
        /* 最小寬度，防止文字壓縮 */
      }

      .table-custom th,
      .table-custom td {
        white-space: nowrap;
        /* 禁止文字換行 */
        overflow: hidden;
        /* 隱藏超出部分 */
        text-overflow: ellipsis;
        /* 顯示省略號 */
      }
    }


    /* 表頭樣式 */
    .table-custom th {
      background-color: #00a79d;
      color: white;
      font-weight: bold;
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
                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="h_appointment.php">預約</a>
                </li> -->
                <li class="rd-nav-item active"><a class="rd-nav-link" href="#">醫生班表</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item active"><a class="rd-dropdown-link" href="h_doctorshift.php">治療師班表</a>
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


    <!--標題-->
    <div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
      <section class="breadcrumbs-custom breadcrumbs-custom-svg">
        <div class="container">
          <p class="heading-1 breadcrumbs-custom-title">治療師班表時段</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="h_index.php">首頁</a></li>
            <li><a href="#">醫生班表</a></li>
            <li class="active">治療師班表</li>
          </ul>
        </div>
      </section>
    </div>
    <!--標題-->

    <!--治療師班表-->
    <section class="section section-lg bg-default">
      <h3 style="text-align: center;">治療師班表</h3>
      <?php
      require '../db.php';

      // 查詢所有醫生的資料供下拉選單使用
      $doctor_list_query = "
        SELECT d.doctor_id, d.doctor
        FROM doctor d
        INNER JOIN user u ON d.user_id = u.user_id
        WHERE u.grade_id = 2
    ";
      $doctor_list_result = mysqli_query($link, $doctor_list_query);
      $doctor_list = [];
      while ($row = mysqli_fetch_assoc($doctor_list_result)) {
        $doctor_list[] = $row;
      }

      // 取得 GET 參數
      $doctor_id = isset($_GET['doctor_id']) ? (int) $_GET['doctor_id'] : 0;
      $year = isset($_GET['year']) ? (int) $_GET['year'] : date('Y');
      $month = isset($_GET['month']) ? (int) $_GET['month'] : date('m');

      // 初始化排班與請假資料
      $work_schedule = [];
      if ($doctor_id > 0) {
        // 查詢排班資料
        $query = "
            SELECT ds.date, st1.shifttime AS start_time, st2.shifttime AS end_time
            FROM doctorshift ds
            INNER JOIN shifttime st1 ON ds.go = st1.shifttime_id
            INNER JOIN shifttime st2 ON ds.off = st2.shifttime_id
            WHERE ds.doctor_id = ? AND YEAR(ds.date) = ? AND MONTH(ds.date) = ?
        ";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "iii", $doctor_id, $year, $month);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
          $work_schedule[$row['date']] = ['start_time' => $row['start_time'], 'end_time' => $row['end_time']];
        }
        mysqli_stmt_close($stmt);

        // 查詢請假資料
        $leave_query = "
            SELECT start_date, end_date
            FROM leaves
            WHERE doctor_id = ? AND (YEAR(start_date) = ? OR YEAR(end_date) = ?)
        ";
        $stmt_leave = mysqli_prepare($link, $leave_query);
        mysqli_stmt_bind_param($stmt_leave, "iii", $doctor_id, $year, $year);
        mysqli_stmt_execute($stmt_leave);
        $leave_result = mysqli_stmt_get_result($stmt_leave);

        while ($row = mysqli_fetch_assoc($leave_result)) {
          $leave_date = substr($row['start_date'], 0, 10);
          if (isset($work_schedule[$leave_date])) {
            // 修改排班時間，排除請假的時段
            if ($row['start_date'] > $leave_date . ' ' . $work_schedule[$leave_date]['start_time']) {
              $work_schedule[$leave_date]['end_time'] = substr($row['start_date'], 11, 5);
            } else {
              unset($work_schedule[$leave_date]); // 完全被請假覆蓋的排班
            }
          }
        }
        mysqli_stmt_close($stmt_leave);
      }

      mysqli_close($link);
      ?>

      <div style="font-size: 18px; font-weight: bold; color: #333; margin-top: 10px; text-align: center;">
        <!-- 醫生選單 -->
        <label for="doctor">選擇治療師：</label>
        <select id="doctor">
          <option value="0" selected>-- 請選擇 --</option>
          <?php foreach ($doctor_list as $doctor): ?>
            <option value="<?php echo $doctor['doctor_id']; ?>" <?php if ($doctor_id == $doctor['doctor_id'])
                 echo 'selected'; ?>>
              <?php echo htmlspecialchars($doctor['doctor']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <!-- 年份與月份選單 -->
        <label for="year">選擇年份：</label>
        <select id="year"></select>
        <label for="month">選擇月份：</label>
        <select id="month"></select>

        <!-- 搜尋按鈕 -->
        <button id="searchButton" onclick="validateAndFetch()">搜尋</button>

        <!-- 日曆表格 -->
        <div class="table-container">
          <table class="table-custom">
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
        </div>
        <script>
          const yearSelect = document.getElementById('year');
          const monthSelect = document.getElementById('month');
          const doctorSelect = document.getElementById('doctor');
          const calendarBody = document.getElementById('calendar');

          const workSchedule = <?php echo json_encode($work_schedule); ?>;

          // 初始化年份和月份選單
          function initSelectOptions() {
            const currentYear = new Date().getFullYear();
            yearSelect.innerHTML = '';
            monthSelect.innerHTML = '';

            for (let year = currentYear - 5; year <= currentYear + 5; year++) {
              yearSelect.innerHTML += `<option value="${year}" ${year == <?php echo $year; ?> ? 'selected' : ''}>${year}</option>`;
            }

            for (let month = 1; month <= 12; month++) {
              monthSelect.innerHTML += `<option value="${month}" ${month == <?php echo $month; ?> ? 'selected' : ''}>${month}</option>`;
            }
          }

          // 驗證選單並執行搜尋
          function validateAndFetch() {
            const doctor = doctorSelect.value;
            const year = yearSelect.value;
            const month = monthSelect.value;

            if (doctor === "0") {
              alert("請選擇治療師！");
              return;
            }
            if (!year || !month) {
              alert("請選擇年份和月份！");
              return;
            }

            window.location.href = `?doctor_id=${doctor}&year=${year}&month=${month}`;
          }

          // 生成日曆
          function generateCalendar() {
            const year = yearSelect.value;
            const month = monthSelect.value - 1;
            calendarBody.innerHTML = '';

            const firstDay = new Date(year, month, 1).getDay();
            const lastDate = new Date(year, month + 1, 0).getDate();

            let row = document.createElement('tr');
            for (let i = 0; i < firstDay; i++) {
              const emptyCell = document.createElement('td');
              row.appendChild(emptyCell);
            }

            for (let date = 1; date <= lastDate; date++) {
              const fullDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
              const cell = document.createElement('td');
              cell.textContent = date;

              if (workSchedule[fullDate]) {
                const workInfo = document.createElement('div');
                workInfo.textContent = `${workSchedule[fullDate].start_time} - ${workSchedule[fullDate].end_time}`;
                workInfo.style.color = 'gray'; // 時間設為灰色字
                workInfo.style.fontSize = '18px'; // 字體大小稍微調整以區分
                cell.appendChild(workInfo);
              }

              row.appendChild(cell);
              if (row.children.length === 7) {
                calendarBody.appendChild(row);
                row = document.createElement('tr');
              }
            }

            while (row.children.length < 7) {
              const emptyCell = document.createElement('td');
              row.appendChild(emptyCell);
            }
            calendarBody.appendChild(row);
          }

          // 初始化選單和日曆
          initSelectOptions();
          generateCalendar();
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
              <li><a href="h_index.php">首頁</a></li>
              <li><a href="h_people.php">用戶資料</a></li>
              <!-- <li><a href="h_appointment.php">預約</a></li> -->
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