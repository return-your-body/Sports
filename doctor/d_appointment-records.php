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
} else {
  echo "<script>
            alert('會話過期或資料遺失，請重新登入。');
            window.location.href = '../index.html';
          </script>";
  exit();
}


?>



<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
  <!-- Site Title-->
  <title>治療師-預約紀錄</title>
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
    /* 表格外容器 */
    .table-container {
      width: 100%;
      overflow-x: auto;
    }

    /* 表格樣式 */
    .table-responsive {
      width: 100%;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
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

    /* 搜尋與筆數選擇在同一行靠右 */
    .search-limit-container {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: 15px;
      margin-bottom: 10px;
      flex-wrap: wrap;
    }

    /* 搜尋框 */
    .search-form {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .search-form input,
    .search-form select {
      padding: 6px 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      height: 38px;
      /* 讓下拉選單與搜尋框高度一致 */
      font-size: 14px;
    }

    .search-form button {
      padding: 6px 12px;
      border: none;
      background-color: #007bff;
      color: white;
      cursor: pointer;
      border-radius: 4px;
      transition: background 0.3s ease-in-out;
      height: 38px;
      /* 與搜尋框 & 下拉選單一致 */
    }

    .search-form button:hover {
      background-color: #0056b3;
    }

    /* 下拉選單 */
    .search-form select {
      height: 38px;
      /* 確保與輸入框相同 */
    }

    /* 筆數選擇 */
    .limit-selector {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .limit-selector select {
      height: 38px;
      /* 確保筆數選擇下拉選單與搜尋框一致 */
    }

    /* 分頁資訊與頁碼 */
    .pagination-wrapper {
      display: flex;
      justify-content: space-between;
      /* 左右對齊 */
      align-items: center;
      margin-top: 20px;
      flex-wrap: wrap;
    }

    /* 分頁資訊 (靠右對齊) */
    .pagination-info {
      font-size: 14px;
      color: #555;
      display: flex;
      justify-content: flex-end;
      /* 讓內容靠右 */
      width: 100%;
    }

    /* 頁碼按鈕 (置中) */
    .pagination-container {
      display: flex;
      justify-content: center;
      /* 讓頁碼置中 */
      flex-grow: 1;
      gap: 10px;
    }

    .pagination-container a,
    .pagination-container strong {
      padding: 5px 10px;
      text-decoration: none;
      border: 1px solid #ddd;
      border-radius: 4px;
      color: #007bff;
    }

    .pagination-container strong {
      font-weight: bold;
      background-color: #007bff;
      color: white;
    }

    /* 響應式處理 */
    @media (max-width: 768px) {
      .pagination-wrapper {
        flex-direction: column;
        align-items: center;
      }

      .pagination-info {
        justify-content: center;
        margin-bottom: 10px;
        width: auto;
      }

      .pagination-container {
        justify-content: center;
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

                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="d_appointment.php">預約</a></li> -->
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
                </li>
                <li class="rd-nav-item active"><a class="rd-nav-link" href="#">紀錄</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="d_medical-record.php">看診紀錄</a>
                    </li>
                    <li class="rd-dropdown-item active"><a class="rd-dropdown-link"
                        href="d_appointment-records.php">預約紀錄</a>
                    </li>
                  </ul>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="d_change.php">變更密碼</a>
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
            <li><a href="d_index.php">首頁</a></li>
            <li><a href="#">紀錄</a></li>
            <li class="active">預約紀錄</li>
          </ul>
        </div>
      </section>
    </div>
    <!--標題-->

    <!--預約紀錄-->
    <?php
    session_start();
    require '../db.php';

    // 確保使用者已登入
    if (!isset($_SESSION['帳號'])) {
      die('未登入或 Session 已失效，請重新登入。');
    }

    $帳號 = $_SESSION['帳號']; // 取得當前登入治療師的帳號
    
    // 接收搜尋與篩選條件
    $search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
    $filter_status = isset($_GET['status']) ? (int) $_GET['status'] : 0;
    $filter_date = isset($_GET['date']) ? $_GET['date'] : 'all';

    // 記住每頁顯示筆數
    $records_per_page = isset($_GET['limit']) ? max(3, (int) $_GET['limit']) : 10;
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $offset = ($page - 1) * $records_per_page;

    // 生成 SQL 條件
    $conditions = "u.account = ? AND p.name LIKE CONCAT('%', ?, '%')";
    $params = ['ss', $帳號, $search_name];

    if ($filter_status > 0) {
      $conditions .= " AND a.status_id = ?";
      $params[0] .= 'i';
      $params[] = $filter_status;
    }

    if ($filter_date === 'today') {
      $conditions .= " AND ds.date = CURDATE()";
    }

    // 計算總筆數
    $count_stmt = $link->prepare("
    SELECT COUNT(*) AS total 
    FROM appointment a
    LEFT JOIN people p ON a.people_id = p.people_id
    LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
    LEFT JOIN user u ON d.user_id = u.user_id
    WHERE $conditions
");

    $count_stmt->bind_param(...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total'] ?? 0;
    $total_pages = max(ceil($total_records / $records_per_page), 1);

    // 查詢符合條件的預約紀錄
    $params[] = $offset;
    $params[] = $records_per_page;
    $params[0] .= 'ii';

    $stmt = $link->prepare("
    SELECT 
        a.appointment_id AS id,
        COALESCE(p.name, '未預約') AS name,
        CASE 
            WHEN p.gender_id = 1 THEN '男' 
            WHEN p.gender_id = 2 THEN '女' 
            ELSE '無資料' 
        END AS gender,
        COALESCE(
            CONCAT(DATE_FORMAT(p.birthday, '%Y-%m-%d'), ' (', TIMESTAMPDIFF(YEAR, p.birthday, CURDATE()), '歲)'),
            '無資料'
        ) AS birthday,
        DATE_FORMAT(ds.date, '%Y-%m-%d') AS appointment_date,
        st.shifttime AS shifttime,
        COALESCE(a.note, '無') AS note,
        a.created_at AS created_at,
        COALESCE(s.status_name, '無狀態') AS status_name,
        a.status_id
    FROM appointment a
    LEFT JOIN people p ON a.people_id = p.people_id
    LEFT JOIN shifttime st ON a.shifttime_id = st.shifttime_id
    LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
    LEFT JOIN user u ON d.user_id = u.user_id
    LEFT JOIN status s ON a.status_id = s.status_id
    WHERE $conditions
    ORDER BY ds.date, st.shifttime
    LIMIT ?, ?
");

    $stmt->bind_param(...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    ?>


    <script>
      function updateLimit() {
        let limit = document.getElementById("limit").value;
        let url = new URL(window.location.href);
        url.searchParams.set("limit", limit);
        url.searchParams.set("page", 1); // 避免換筆數時還留在舊頁面
        window.location.href = url.href;
      }
    </script>

    <section class="section section-lg bg-default text-center">
      <div class="container">
        <!-- 搜尋與篩選 -->
        <div class="search-limit-container">
          <form method="GET" action="" class="search-form">
            <input type="text" name="search_name" id="search_name" placeholder="請輸入搜尋姓名"
              value="<?php echo htmlspecialchars($search_name); ?>">

            <!-- 狀態篩選 -->
            <select name="status" onchange="this.form.submit()">
              <option value="0" <?php echo ($filter_status === 0) ? 'selected' : ''; ?>>所有狀態</option>
              <option value="1" <?php echo ($filter_status === 1) ? 'selected' : ''; ?>>預約</option>
              <option value="3" <?php echo ($filter_status === 3) ? 'selected' : ''; ?>>報到</option>
              <option value="8" <?php echo ($filter_status === 8) ? 'selected' : ''; ?>>看診中</option>
            </select>

            <!-- 日期篩選 -->
            <select name="date" onchange="this.form.submit()">
              <option value="all" <?php echo ($filter_date === 'all') ? 'selected' : ''; ?>>所有日期</option>
              <option value="today" <?php echo ($filter_date === 'today') ? 'selected' : ''; ?>>當天日期</option>
            </select>

            <button type="submit">搜尋</button>
          </form>

          <!-- 每頁顯示筆數 -->
          <div class="limit-selector">
            <label for="limit">每頁顯示筆數:</label>
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



        <!-- 表格 -->
        <div class="table-responsive">
          <table class="responsive-table">
            <thead>
              <tr>
                <th>#</th>
                <th>姓名</th>
                <th>性別</th>
                <th>生日 (年齡)</th>
                <th>看診日期</th>
                <th>看診時間</th>
                <th>備註</th>
                <th>狀態</th>
                <th>建立時間</th>
                <th>選項</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['id']); ?></td>
                  <td><?php echo htmlspecialchars($row['name']); ?></td>
                  <td><?php echo htmlspecialchars($row['gender']); ?></td>
                  <td><?php echo htmlspecialchars($row['birthday']); ?></td>
                  <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                  <td><?php echo htmlspecialchars($row['shifttime']); ?></td>
                  <td><?php echo htmlspecialchars($row['note']); ?></td>
                  <td><?php echo htmlspecialchars($row['status_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                  <td>
                    <?php if ($row['status_id'] == 3): ?>
                      <a href="d_medical.php?id=<?php echo $row['id']; ?>" target="_blank">
                        <button type="button">新增看診資料</button>
                      </a>
                    <?php else: ?>
                      <span style="color: gray;">不可新增</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>

          <!-- 分頁資訊 -->
          <div class="pagination-wrapper">
            <div class="pagination-info">
              第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁（總共
              <strong><?php echo $total_records; ?></strong> 筆資料）
            </div>
            <div class="pagination-container">
              <?php if ($page > 1): ?>
                <a
                  href="?page=<?php echo $page - 1; ?>&limit=<?php echo $records_per_page; ?>&search_name=<?php echo urlencode($search_name); ?>">上一頁</a>
              <?php endif; ?>
              <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                  <strong><?php echo $i; ?></strong>
                <?php else: ?>
                  <a
                    href="?page=<?php echo $i; ?>&limit=<?php echo $records_per_page; ?>&search_name=<?php echo urlencode($search_name); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
              <?php endfor; ?>
              <?php if ($page < $total_pages): ?>
                <a
                  href="?page=<?php echo $page + 1; ?>&limit=<?php echo $records_per_page; ?>&search_name=<?php echo urlencode($search_name); ?>">下一頁</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
    </section>

    <!-- 預約紀錄 -->

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
              <li><a href="d_people.php">用戶資料</a></li>
              <!-- <li><a href="d_appointment.php">預約</a></li> -->
              <li><a href="d_numberpeople.php">當天人數及時段</a></li>
              <li><a href="d_doctorshift.php">班表時段</a></li>
              <li><a href="d_leave.php">請假申請</a></li>
              <li><a href="d_leave-query.php">請假資料查詢</a></li>
              <li><a href="d_medical-record.php">看診紀錄</a></li>
              <li><a href="d_appointment-records.php">預約紀錄</a></li>
              <li><a href="d_change.php">變更密碼</a></li>
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
  <!-- coded by Himic-->
</body>

</html>