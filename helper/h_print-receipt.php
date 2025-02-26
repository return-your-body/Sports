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


?>



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

    /* 列印收據 */

    /* 通用樣式 */
    #print-area {
      width: 350px;
      margin: 0 auto;
      border: 2px solid black;
      padding: 20px;
      text-align: left;
      box-sizing: border-box;
      page-break-inside: avoid;
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

    #print-area table {
      border-collapse: collapse;
      width: 100%;
      text-align: center;
      margin-top: 20px;
    }

    #print-area table th,
    #print-area table td {
      border: 1px solid black;
      padding: 10px;
    }

    .button-container {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 20px;
    }

    button {
      padding: 10px 20px;
      font-size: 16px;
      cursor: pointer;
      border: 1px solid #ccc;
      border-radius: 5px;
      background-color: #f5f5f5;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #e0e0e0;
    }

    @media print {

      html,
      body {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow: hidden;
      }

      body * {
        visibility: hidden;
      }

      #print-area,
      #print-area * {
        visibility: visible;
      }

      button {
        display: none;
      }
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
                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="h_appointment.php">預約</a></li> -->
                <li class="rd-nav-item"><a class="rd-nav-link" href="#">班表</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_doctorshift.php">治療師班表</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_numberpeople.php">當天人數及時段</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_assistantshift.php">每月班表</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_leave.php">請假申請</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_leave-query.php">請假資料查詢</a>
                    </li>
                  </ul>
                </li>
                <!-- <li class="rd-nav-item active"><a class="rd-nav-link" href="#">列印</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item active"><a class="rd-dropdown-link" href="h_print-receipt.php">列印收據</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_print-appointment.php">列印預約單</a>
                    </li>
                  </ul>
                </li> -->
                <li class="rd-nav-item active"><a class="rd-nav-link" href="#">紀錄</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_medical-record.php">看診紀錄</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_appointment-records.php">預約紀錄</a>
                    </li>
                  </ul>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="h_change.php">變更密碼</a>
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
      <!-- Breadcrumbs-->
      <section class="breadcrumbs-custom breadcrumbs-custom-svg">
        <div class="container">
          <!-- <p class="breadcrumbs-custom-subtitle">Get in Touch with Us</p> -->
          <p class="heading-1 breadcrumbs-custom-title">列印收據</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="h_index.php">首頁</a></li>
            <li><a href="#">列印</a></li>
            <li class="active">列印收據</li>
          </ul>
        </div>
      </section>
    </div>
    <!--標題-->


    <!--列印收據-->
    <?php
    require '../db.php'; // 引入資料庫連接檔案
    
    // 確認 GET 請求是否攜帶有效的 ID
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
      $appointment_id = intval($_GET['id']);

      // ✅ **查詢基本資訊 (包含 `身分證號碼` & `使用者備註`)**
      $query = "
    SELECT 
        p.idcard AS people_idcard,  -- 新增顯示身分證
        p.name AS people_name,
        CASE 
            WHEN p.gender_id = 1 THEN '男'
            WHEN p.gender_id = 2 THEN '女'
            ELSE '無資料'
        END AS gender,
        CASE 
            WHEN p.birthday IS NOT NULL THEN CONCAT(p.birthday, ' (', TIMESTAMPDIFF(YEAR, p.birthday, CURDATE()), '歲)')
            ELSE '無資料'
        END AS birthday_with_age,
        d.doctor AS doctor_name,
        ds.date AS appointment_date,
        CASE DAYOFWEEK(ds.date)
            WHEN 1 THEN '星期日'
            WHEN 2 THEN '星期一'
            WHEN 3 THEN '星期二'
            WHEN 4 THEN '星期三'
            WHEN 5 THEN '星期四'
            WHEN 6 THEN '星期五'
            WHEN 7 THEN '星期六'
        END AS consultation_weekday,
        st.shifttime AS appointment_time,
        COALESCE(a.note, '無') AS user_note  -- 預約表的使用者備註
    FROM appointment a
    LEFT JOIN people p ON a.people_id = p.people_id
    LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
    LEFT JOIN shifttime st ON a.shifttime_id = st.shifttime_id
    WHERE a.appointment_id = $appointment_id";

      $result = mysqli_query($link, $query);

      if (!$result) {
        die("❌ SQL 錯誤：" . mysqli_error($link));
      }

      if (mysqli_num_rows($result) > 0) {
        $record = mysqli_fetch_assoc($result);

        // 提取基本資料
        $people_idcard = $record['people_idcard']; // 身分證號碼
        $people_name = $record['people_name'];
        $gender = $record['gender'];
        $birthday_with_age = $record['birthday_with_age'];
        $appointment_date = $record['appointment_date'];
        $consultation_weekday = $record['consultation_weekday'];
        $appointment_time = $record['appointment_time'];
        $doctor_name = $record['doctor_name'];
        $user_note = $record['user_note']; // 使用者備註
    
        // ✅ **查詢該次 `appointment` 的所有項目與價錢**
        $item_query = "
        SELECT i.item AS treatment_item, i.price AS treatment_price
        FROM medicalrecord m
        LEFT JOIN item i ON m.item_id = i.item_id
        WHERE m.appointment_id = $appointment_id";

        $item_result = mysqli_query($link, $item_query);

        if (!$item_result) {
          die("❌ SQL 錯誤：" . mysqli_error($link));
        }

        // ✅ **查詢 醫生備註**
        $doctor_note_query = "
        SELECT GROUP_CONCAT(m.note_d ORDER BY m.created_at SEPARATOR '; ') AS doctor_note
        FROM medicalrecord m
        WHERE m.appointment_id = $appointment_id";

        $doctor_note_result = mysqli_query($link, $doctor_note_query);
        $doctor_note_data = mysqli_fetch_assoc($doctor_note_result);
        $doctor_note = $doctor_note_data['doctor_note'] ?? '無'; // 沒有醫生備註則顯示「無」
      } else {
        die("❌ 未找到對應的病歷資料");
      }
    } else {
      die("❌ 無效的 ID");
    }
    ?>

    <section class="section section-lg bg-default text-center">
      <div class="container">
        <div class="row row-50 justify-content-lg-center">
          <div class="col-lg-10 col-xl-8">
            <div class="accordion-custom-group accordion-custom-group-custom accordion-custom-group-corporate"
              id="accordion1" role="tablist" aria-multiselectable="false">
              <dl class="list-terms">
                <!-- ✅ **看診收據顯示區域** -->
                <div id="print-area">
                  <h1> 看診收據</h1>
                  <p><strong>姓名：</strong><?php echo htmlspecialchars($people_name); ?></p>
                  <p><strong>性別：</strong><?php echo htmlspecialchars($gender); ?></p>
                  <p><strong>生日 (年齡)：</strong><?php echo htmlspecialchars($birthday_with_age); ?></p>
                  <p><strong>身分證號：</strong><?php echo htmlspecialchars($people_idcard); ?></p> <!-- ✅ 顯示身分證 -->
                  <p>
                    <strong>看診日期：</strong><?php echo htmlspecialchars($appointment_date) . " (" . htmlspecialchars($consultation_weekday) . ")"; ?>
                  </p>
                  <p><strong>看診時間：</strong><?php echo htmlspecialchars($appointment_time); ?></p>
                  <p><strong>治療師：</strong><?php echo htmlspecialchars($doctor_name); ?></p>
                  <p><strong>使用者備註：</strong> <?php echo htmlspecialchars($user_note); ?></p>
                  <p><strong>醫生備註：</strong> <?php echo htmlspecialchars($doctor_note); ?></p>

                  <!-- ✅ **顯示治療項目與費用** -->
                  <table>
                    <tr>
                      <th>治療項目</th>
                      <th>費用</th>
                    </tr>
                    <?php
                    $total_price = 0;
                    while ($item_row = mysqli_fetch_assoc($item_result)) {
                      echo "<tr>";
                      echo "<td>" . htmlspecialchars($item_row['treatment_item']) . "</td>";
                      echo "<td>" . htmlspecialchars($item_row['treatment_price']) . "</td>";
                      echo "</tr>";
                      $total_price += $item_row['treatment_price'];
                    }
                    ?>
                    <tr>
                      <td><strong>總費用</strong></td>
                      <td><strong><?php echo number_format($total_price); ?></strong></td>
                    </tr>
                  </table>

                  <p>列印時間：<?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
                <div class="button-container">
                  <button onclick="window.print()">🖨 列印收據</button>
                  <button onclick="location.href='h_medical-record.php'">⬅️ 返回</button>
                </div>
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
              <li><a href="h_doctorshift.php">治療師班表時段</a></li>
              <li><a href="h_assistantshift.php">每月班表</a></li>
              <li><a href="h_leave.php">請假申請</a></li>
              <li><a href="h_leave-query.php">請假資料查詢</a></li>
              <li><a href="h_medical-record.php">看診紀錄</a></li>
              <li><a href="h_appointment-records.php">預約紀錄</a></li>
              <li><a href="h_change.php">變更密碼</a></li>
              <!-- <li><a href="h_print-receipt.php">列印收據</a></li>
              <li><a href="h_print-appointment.php">列印預約單</a></li> -->

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