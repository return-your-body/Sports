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
  // 接收搜尋參數
  $search_name = isset($_GET['search_name']) ? mysqli_real_escape_string($link, trim($_GET['search_name'])) : '';
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


} else {
  echo "<script>
            alert('會話過期或資料遺失，請重新登入。');
            window.location.href = '../index.html';
          </script>";
  exit();
}

// 看診紀錄


?>


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


    /* 看診紀錄 */
    table {
      width: auto;
      /* 讓表格寬度根據內容自動調整 */
      border-collapse: collapse;
      margin-top: 20px;
    }

    .search-container {
      text-align: right;
      margin-bottom: 10px;
    }

    th,
    td {
      padding: 8px;
      text-align: center;
      border: 1px solid #ddd;
      white-space: nowrap;
      /* 防止文字換行，保持欄位寬度符合文字 */
    }

    th {
      background-color: #f2f2f2;
    }

    table th,
    table td {
      text-align: center;
      vertical-align: middle;
      /* 垂直置中 */
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

                <li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="d_people.php">用戶資料</a>
                    </li>
                  </ul>
                </li>

                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="d_appointment.php">預約</a>
                </li> -->
                <li class="rd-nav-item"><a class="rd-nav-link" href="#">班表</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="d_doctorshift.php">每月班表</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="d_numberpeople.php">當天人數及時段</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="d_leave.php">請假申請</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="d_leave-query.php">請假資料查詢</a>
                    </li>
                  </ul>
                </li>
                <li class="rd-nav-item active"><a class="rd-nav-link" href="#">紀錄</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item active"><a class="rd-dropdown-link" href="d_medical-record.php">看診紀錄</a>
                    </li>
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="d_appointment-records.php">預約紀錄</a>
                    </li>
                  </ul>
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
    <section class="section section-lg bg-default text-center" style="padding: 20px 0;">
      <div
        style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh;">
        <!-- 搜尋框 -->
        <div class="search-container" style="margin-bottom: 10px; width: 80%; text-align: right;">
          <form method="GET" action="">
            <!-- 隱藏的狀態標誌，用於檢測是否按下搜尋按鈕 -->
            <input type="hidden" name="is_search" value="1">
            <input type="text" name="search_name" id="search_name" placeholder="請輸入搜尋姓名"
              value="<?php echo isset($_GET['search_name']) ? htmlspecialchars($_GET['search_name']) : ''; ?>"
              style="padding: 5px; width: 200px;">
            <button type="submit" style="padding: 5px 10px; margin-left: 10px;">搜尋</button>
          </form>
        </div>

        <?php
        require '../db.php'; // 引入資料庫連接檔案
        
        // 檢查是否由搜尋按鈕提交
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['is_search'])) {
          $search_name = trim($_GET['search_name']);

          if ($search_name === '') {
            // 如果搜尋姓名為空，顯示彈跳訊息
            echo "<script>alert('請輸入搜尋姓名！');</script>";
          } else {
            // 執行資料庫查詢，檢查是否有該資料
            $sql = "
            SELECT COUNT(*) AS total
            FROM appointment a
            LEFT JOIN people p ON a.people_id = p.people_id
            WHERE p.name LIKE '%$search_name%'
            ";
            $result = mysqli_query($link, $sql);
            $data = mysqli_fetch_assoc($result);

            if ($data['total'] == 0) {
              // 查無資料，顯示彈跳訊息
              echo "<script>alert('查無此人！');</script>";
            }
          }
        }

        // 接收搜尋參數
        $search_name = isset($_GET['search_name']) ? mysqli_real_escape_string($link, trim($_GET['search_name'])) : '';

        $page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
        $records_per_page = 10; // 每頁顯示筆數
        $offset = ($page - 1) * $records_per_page;

        // 查詢條件
        $where_clause = "1=1";
        if ($search_name !== '') {
          $where_clause .= " AND p.name LIKE '%" . mysqli_real_escape_string($link, $search_name) . "%'";
        }

        // 查詢總筆數
        $count_sql = "
    SELECT COUNT(*) AS total
    FROM medicalrecord m
    LEFT JOIN appointment a ON m.appointment_id = a.appointment_id
    LEFT JOIN people p ON a.people_id = p.people_id
    LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    LEFT JOIN item i ON m.item_id = i.item_id
    WHERE $where_clause
    ";
        $count_result = mysqli_query($link, $count_sql);
        $total_records = mysqli_fetch_assoc($count_result)['total'];
        $total_pages = max(ceil($total_records / $records_per_page), 1);

        // 查詢資料
        $sql = "
    SELECT 
        m.medicalrecord_id,
        p.name AS patient_name,
        p.gender_id,
        p.birthday,
        ds.date,
        i.item,
        i.price,
        m.created_at
    FROM medicalrecord m
    LEFT JOIN appointment a ON m.appointment_id = a.appointment_id
    LEFT JOIN people p ON a.people_id = p.people_id
    LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    LEFT JOIN item i ON m.item_id = i.item_id
    WHERE $where_clause
    ORDER BY ds.date
    LIMIT $offset, $records_per_page
    ";
        $result = mysqli_query($link, $sql);

        // 顯示表格
        if (mysqli_num_rows($result) > 0) {
          echo "<table style='border-collapse: collapse; width: 80%; text-align: center; margin: 0 auto;'>";
          echo "<thead>
          <tr>
              <th>編號</th>
              <th>姓名</th>
              <th>性別</th>
              <th>生日</th>
              <th>看診日期</th>
              <th>治療項目</th>
              <th>治療費用</th>
              <th>建立時間</th>
          </tr>
      </thead>";
          echo "<tbody>";
          while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
              <td>{$row['medicalrecord_id']}</td>
              <td>{$row['patient_name']}</td>
              <td>" . ($row['gender_id'] == 1 ? '男' : '女') . "</td>
              <td>{$row['birthday']}</td>
              <td>{$row['date']}</td>
              <td>{$row['item']}</td>
              <td>{$row['price']}</td>
              <td>{$row['created_at']}</td>
          </tr>";
          }
          echo "</tbody>";
          echo "</table>";
        } else {
          echo "<p>目前無資料，請輸入條件進行搜尋。</p>";
        }

        // 顯示頁碼及總頁數資訊
        echo "<div style='text-align: right; width: 80%; margin-top: 5px; font-size: 14px;'>";
        echo "第 $page 頁 / 共 $total_pages 頁（共 $total_records 筆資料）";
        echo "</div>";

        // 顯示頁碼
        echo "<div style='text-align: center; margin-top: 10px;'>";
        if ($page > 1) {
          echo "<a href='?page=" . ($page - 1) . "&search_name=" . urlencode($search_name) . "'>上一頁</a> ";
        }
        for ($i = 1; $i <= $total_pages; $i++) {
          if ($i == $page) {
            echo "<strong>$i</strong> ";
          } else {
            echo "<a href='?page=$i&search_name=" . urlencode($search_name) . "'>$i</a> ";
          }
        }
        if ($page < $total_pages) {
          echo "<a href='?page=" . ($page + 1) . "&search_name=" . urlencode($search_name) . "'>下一頁</a>";
        }
        echo "</div>";

        mysqli_close($link);
        ?>
      </div>
    </section>

    <!--看診紀錄-->

 
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
              <li><a href="d_leave.php">請假申請</a></li>
              <li><a href="d_leave-query.php"></a>請假資料查詢</li>
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