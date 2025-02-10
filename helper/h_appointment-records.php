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

//預約紀錄
require '../db.php';

// 接收搜尋條件
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';

// 取得筆數選擇 (預設 10)
$records_per_page = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 10;

// 取得當前頁數
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// 總筆數計算
$count_stmt = $link->prepare("
    SELECT COUNT(*) AS total 
    FROM appointment a
    LEFT JOIN people p ON a.people_id = p.people_id
    LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
    LEFT JOIN user u ON d.user_id = u.user_id
    LEFT JOIN status s ON a.status_id = s.status_id
    WHERE p.name LIKE CONCAT('%', ?, '%')
");
if (!$count_stmt) {
  die('SQL 錯誤: ' . $link->error);
}
$count_stmt->bind_param('s', $search_name);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_records / $records_per_page);

// 查詢分頁資料
$stmt = $link->prepare("
    SELECT 
        a.appointment_id AS id,
        COALESCE(p.name, '未預約') AS name,
        CASE 
            WHEN p.gender_id = 1 THEN '男' 
            WHEN p.gender_id = 2 THEN '女' 
            ELSE '無資料' 
        END AS gender,
        IFNULL(CONCAT(DATE_FORMAT(p.birthday, '%Y-%m-%d'), ' (', TIMESTAMPDIFF(YEAR, p.birthday, CURDATE()), '歲)'), '無資料') AS birthday_with_age,
        DATE_FORMAT(ds.date, '%Y-%m-%d') AS appointment_date,
        st.shifttime AS shifttime,
        d.doctor AS doctor_name,
        COALESCE(a.note, '無') AS note,
        COALESCE(s.status_name, '未設定') AS status_name,  
        a.created_at
    FROM appointment a
    LEFT JOIN people p ON a.people_id = p.people_id
    LEFT JOIN shifttime st ON a.shifttime_id = st.shifttime_id
    LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
    LEFT JOIN user u ON d.user_id = u.user_id
    LEFT JOIN status s ON a.status_id = s.status_id  
    WHERE p.name LIKE CONCAT('%', ?, '%')
    ORDER BY ds.date, st.shifttime
    LIMIT ?, ?
");
if (!$stmt) {
  die('SQL 錯誤: ' . $link->error);
}
$stmt->bind_param('sii', $search_name, $offset, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();
?>



<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
  <!-- Site Title-->
  <title>助手-預約紀錄</title>
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

    /*預約紀錄 */
    .table-responsive {
      overflow-x: auto;
      margin-top: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th,
    td {
      padding: 10px;
      text-align: center;
      border: 1px solid #ddd;
      white-space: nowrap;
      /* 禁止換行 */
    }

    th {
      background-color: #f2f2f2;
      font-weight: bold;
    }

    @media (max-width: 768px) {

      th,
      td {
        padding: 8px;
        font-size: 14px;
      }
    }

    @media (max-width: 480px) {

      th,
      td {
        padding: 6px;
        font-size: 12px;
      }
    }

    /* 讓搜尋框與筆數選擇在同一行，並靠右對齊 */
    .search-limit-container {
      display: flex;
      justify-content: flex-end;
      /* 內容靠右對齊 */
      align-items: center;
      gap: 15px;
      /* 設定搜尋框與筆數選擇之間的間距 */
      margin-bottom: 10px;
      flex-wrap: wrap;
      /* 確保在小螢幕時換行 */
    }

    /* 搜尋框 */
    .search-form {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .search-form input {
      padding: 6px 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .search-form button {
      padding: 6px 12px;
      border: none;
      background-color: #007bff;
      color: white;
      cursor: pointer;
      border-radius: 4px;
      transition: background 0.3s ease-in-out;
    }

    .search-form button:hover {
      background-color: #0056b3;
    }

    /* 筆數選擇 */
    .limit-selector {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .limit-selector label {
      font-size: 14px;
    }

    .limit-selector select {
      padding: 6px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    /* 狀態 */

    /* 彈跳視窗 */
    /* 彈跳視窗 */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      width: 400px;
      background: white;
      box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.3);
      border-radius: 10px;
    }

    .modal-content {
      padding: 20px;
      text-align: center;
    }

    .close {
      position: absolute;
      right: 15px;
      top: 10px;
      cursor: pointer;
      font-size: 20px;
    }

    #appointment-date,
    #appointment-time {
      display: block;
      width: 100%;
      padding: 5px;
      margin-top: 10px;
    }

    #confirm-modify {
      background: #007bff;
      color: white;
      border: none;
      padding: 10px;
      cursor: pointer;
      width: 100%;
      margin-top: 10px;
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
                <li class="rd-nav-item active"><a class="rd-nav-link" href="#">紀錄</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_medical-record.php">看診紀錄</a>
                    </li>
                    <li class="rd-dropdown-item active"><a class="rd-dropdown-link"
                        href="h_appointment-records.php">預約紀錄</a>
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
          <!-- <p class="breadcrumbs-custom-subtitle">Terms of Use</p> -->
          <p class="heading-1 breadcrumbs-custom-title">預約紀錄</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="h_index.php">首頁</a></li>
            <li><a href="#">紀錄</a></li>
            <li class="active">預約紀錄</li>
          </ul>
        </div>
      </section>
    </div>
    <!--標題-->

    <!--預約紀錄-->
    <section class="section section-lg bg-default novi-bg novi-bg-img">
      <div class="container">
        <div class="row row-50 justify-content-lg-center">
          <div class="col-lg-12">
            <div id="accordion1" role="tablist" aria-multiselectable="false">
              <!-- 搜尋與筆數選擇 -->
              <div class="search-limit-container">
                <!-- 搜尋框 -->
                <form method="GET" action="" class="search-form">
                  <input type="hidden" name="is_search" value="1">
                  <input type="text" name="search_name" id="search_name" placeholder="請輸入搜尋姓名"
                    value="<?php echo htmlspecialchars($search_name); ?>">
                  <button type="submit">搜尋</button>
                </form>

                <!-- 每頁筆數選擇 -->
                <div class="limit-selector">
                  <!-- <label for="limit">每頁顯示筆數:</label> -->
                  <select id="limit" name="limit" onchange="updateLimit()">
                    <?php
                    $limits = [3, 5, 10, 20, 50, 100];
                    foreach ($limits as $limit) {
                      $selected = ($limit == $records_per_page) ? "selected" : "";
                      echo "<option value='$limit' $selected>$limit 筆/頁</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>


              <!-- 表格容器 -->
              <div class="table-responsive">
                <table>
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>姓名</th>
                      <th>性別</th>
                      <th>生日 (年齡)</th>
                      <th>看診日期 (星期)</th>
                      <th>看診時間</th>
                      <th>治療師</th>
                      <th>備註</th>
                      <th>狀態</th>
                      <th>選項</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($result->num_rows > 0): ?>
                      <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($row['id']); ?></td>
                          <td><?php echo htmlspecialchars($row['name']); ?></td>
                          <td><?php echo htmlspecialchars($row['gender']); ?></td>
                          <td><?php echo htmlspecialchars($row['birthday_with_age']); ?></td>
                          <td>
                            <?php
                            $appointment_date = htmlspecialchars($row['appointment_date']);
                            $weekday = ['日', '一', '二', '三', '四', '五', '六'][date('w', strtotime($appointment_date))];
                            echo "$appointment_date (星期$weekday)";
                            ?>
                          </td>
                          <td><?php echo htmlspecialchars($row['shifttime']); ?></td>
                          <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                          <td><?php echo htmlspecialchars($row['note']); ?></td>
                          <td>
    <select class="status-dropdown" data-id="<?php echo $row['id']; ?>"
        <?php echo ($row['status_name'] == '已看診') ? 'disabled' : ''; ?>>
        <option value="預約" <?php echo ($row['status_name'] == '預約') ? 'selected' : ''; ?>>預約</option>
        <option value="修改" <?php echo ($row['status_name'] == '修改') ? 'selected' : ''; ?>>修改</option>
        <option value="報到" <?php echo ($row['status_name'] == '報到') ? 'selected' : ''; ?>>報到</option>
        <option value="請假" <?php echo ($row['status_name'] == '請假') ? 'selected' : ''; ?>>請假</option>
        <option value="爽約" <?php echo ($row['status_name'] == '爽約') ? 'selected' : ''; ?>>爽約</option>
        <option value="已看診" <?php echo ($row['status_name'] == '已看診') ? 'selected' : ''; ?>>已看診</option>
    </select>
</td>

                          <td>
                            <a href="h_print-appointment.php?id=<?php echo $row['id']; ?>" target="_blank">
                              <button type="button">列印預約單</button>
                            </a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="10">目前無資料</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <!-- 狀態清單 -->
              <!-- 修改 -->
              <!-- 狀態下拉選單 -->


              <!-- 修改時間的 Modal -->


              <!-- 修改時間的 Modal -->
              <div id="modal" class="modal">
                <div class="modal-content">
                  <span id="modal-close" class="close">&times;</span>
                  <h2>修改預約</h2>
                  <label for="appointment-date">預約日期：</label>
                  <input type="date" id="appointment-date">

                  <label for="appointment-time">預約時間：</label>
                  <select id="appointment-time">
                    <option value="">請選擇時間</option>
                  </select>

                  <button id="confirm-modify">確認修改</button>
                </div>
              </div>



              <script>
                document.getElementById("appointment-date").addEventListener("change", function () {
                  const selectedDate = this.value;
                  console.log("📅 選擇日期:", selectedDate);

                  if (!selectedDate) {
                    return;
                  }

                  fetch(`獲取時間.php?date=${selectedDate}`)
                    .then(response => response.json())
                    .then(data => {
                      console.log("✅ 取得可用時間:", data);

                      let timeSelect = document.getElementById("appointment-time");
                      timeSelect.innerHTML = "<option value=''>請選擇時間</option>";

                      if (data.error) {
                        console.warn("⚠️ 無可用時段:", data.error);
                        return;
                      }

                      data.forEach(timeSlot => {
                        let option = document.createElement("option");
                        option.value = timeSlot.id;
                        option.textContent = timeSlot.time;
                        timeSelect.appendChild(option);
                      });
                    })
                    .catch(error => console.error("❌ 錯誤:", error));
                });

              </script>


              <!-- 分頁顯示 -->
              <script>
                function updateLimit() {
                  var limit = document.getElementById("limit").value;
                  window.location.href = "?limit=" + limit + "&search_name=<?php echo urlencode($search_name); ?>";
                }
              </script>

              <div style="text-align: right; margin: 20px 0;">
                <strong>第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁（總共 <?php echo $total_records; ?>
                  筆資料）</strong>
              </div>

              <div style="text-align: center; margin: 20px 0;">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                  <a href="?search_name=<?php echo urlencode($search_name); ?>&page=<?php echo $i; ?>"
                    style="margin: 0 5px; <?php echo $i == $page ? 'font-weight: bold;' : ''; ?>">
                    <?php echo $i; ?>
                  </a>
                <?php endfor; ?>
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
            </ul>
          </div>
          <!-- <div class="col-md-5">
          <h4>聯絡我們</h4>
          <p>Subscribe to our newsletter today to get weekly news, tips, and special offers from our team on the
            courses we offer.</p>
          <form class="rd-mailform rd-form-boxed" data-form-output="form-output-global" data-form-type="subscribe"
            method="post" action="bat/rd-mailform.php">
            <div class="form-wrap">
              <input class="form-input" type="email" name="email" data-constraints="@Email @Required" id="footer-mail">
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
  <!-- coded by Himic-->
</body>

</html>