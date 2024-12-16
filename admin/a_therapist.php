<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
  <!-- Site Title-->
  <title>健康醫療網站</title>
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
  <!-- Page-->
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
                <!--Brand--><a class="brand-name" href="index.php"><img class="logo-default"
                    src="images/logo-default-172x36.png" alt="" width="86" height="18" loading="lazy" /><img
                    class="logo-inverse" src="images/logo-inverse-172x36.png" alt="" width="86" height="18"
                    loading="lazy" /></a>
              </div>
            </div>
            <div class="rd-navbar-nav-wrap">
              <ul class="rd-navbar-nav">
                <li class="rd-nav-item"><a class="rd-nav-link" href="a_index.php">網頁編輯</a>
                </li>
                <li class="rd-nav-item active"><a class="rd-nav-link" href="a_therapist.php">治療師時間表</a>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="teachers.php">新增診療項目</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="single-teacher.php">Single
                        teacher</a>
                    </li>
                  </ul>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="courses.php">病患資料</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="single-course.php">黑名單</a>
                    </li>
                  </ul>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="#">Pages</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="faqs.php">登出</a>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
            <div class="rd-navbar-collapse-toggle" data-rd-navbar-toggle=".rd-navbar-collapse"><span></span></div>
            <div class="rd-navbar-aside-right rd-navbar-collapse">
              <div class="rd-navbar-social">
                <div class="rd-navbar-social-text">Follow us</div>
                <ul class="list-inline">
                  <li><a class="icon novi-icon icon-default icon-custom-facebook"
                      href="https://www.facebook.com/ReTurnYourBody/"></a></li>
                  <li><a class="icon novi-icon icon-default icon-custom-instagram"
                      href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"></a></li>
                </ul>
              </div>
            </div>
          </div>
        </nav>
      </div>
    </header>
    <!-- Page Header-->
    <div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
      <!-- Breadcrumbs-->
      <section class="breadcrumbs-custom breadcrumbs-custom-svg">
        <div class="container">
          <p class="heading-1 breadcrumbs-custom-title">治療師時間表</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="index.php">首頁</a></li>
            <li class="active">治療師時間表</li>
          </ul>
        </div>
      </section>

    </div>

    <style>
      body {
        font-family: Arial, sans-serif;
        text-align: center;
        margin: 20px;
      }

      .calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
        margin-top: 20px;
      }

      .calendar div {
        border: 1px solid #ccc;
        padding: 10px;
        background: #f9f9f9;
        cursor: pointer;
      }

      .calendar .header {
        font-weight: bold;
        background: #ddd;
      }

      .calendar .empty {
        background: transparent;
        cursor: default;
      }

      /* 日期樣式：改為柔和文字色系 */
      .calendar .date-link {
        display: block;
        padding: 10px;
        color: #00796B;
        /* 第二張圖片的綠色系 */
        text-decoration: none;
        /* 移除底線 */
        font-size: 14px;
        font-weight: bold;
      }

      .calendar .date-link:hover {
        text-decoration: underline;
        /* 滑鼠懸停時加底線 */
        color: #004D40;
        /* 深綠色，增加互動感 */
      }

      select {
        padding: 5px;
        margin: 5px;
      }
    </style>
    <div>
      <label for="year">選擇年份：</label>
      <select id="year"></select>
      <label for="month">選擇月份：</label>
      <select id="month"></select>
    </div>
    <table class="table-custom table-color-header table-custom-bordered">
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

    <script>
      const currentDate = new Date();
      const currentYear = currentDate.getFullYear();
      const currentMonth = currentDate.getMonth();

      const yearSelect = document.getElementById('year');
      const monthSelect = document.getElementById('month');
      const calendarBody = document.getElementById('calendar');

      function initYearOptions() {
        const startYear = currentYear - 5;
        const endYear = currentYear + 5;
        for (let year = startYear; year <= endYear; year++) {
          const option = document.createElement('option');
          option.value = year;
          option.textContent = year;
          if (year === currentYear) option.selected = true;
          yearSelect.appendChild(option);
        }
      }

      function initMonthOptions() {
        for (let month = 0; month < 12; month++) {
          const option = document.createElement('option');
          option.value = month;
          option.textContent = month + 1;
          if (month === currentMonth) option.selected = true;
          monthSelect.appendChild(option);
        }
      }

      function generateCalendar(year, month) {
        calendarBody.innerHTML = ''; // 清空表格內容
        const firstDay = new Date(year, month, 1).getDay(); // 該月第一天是星期幾
        const lastDate = new Date(year, month + 1, 0).getDate(); // 該月最後一天是幾號
        let row = document.createElement('tr');

        // 空白單元格
        for (let i = 0; i < firstDay; i++) {
          const emptyCell = document.createElement('td');
          row.appendChild(emptyCell);
        }

        // 填入日期
        for (let date = 1; date <= lastDate; date++) {
          if (row.children.length === 7) {
            calendarBody.appendChild(row);
            row = document.createElement('tr');
          }

          const dateCell = document.createElement('td');
          const dateLink = document.createElement('a');
          dateLink.href = "#";
          dateLink.textContent = date;
          dateLink.addEventListener('click', (e) => {
            e.preventDefault();
            alert(`您選擇的日期是：${year}-${month + 1}-${date}`);
          });

          dateCell.appendChild(dateLink);
          row.appendChild(dateCell);
        }

        // 填補最後一行的空白單元格
        while (row.children.length < 7) {
          const emptyCell = document.createElement('td');
          row.appendChild(emptyCell);
        }
        calendarBody.appendChild(row);
      }

      // 初始化
      initYearOptions();
      initMonthOptions();
      generateCalendar(currentYear, currentMonth);

      yearSelect.addEventListener('change', () => {
        generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
      });
      monthSelect.addEventListener('change', () => {
        generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
      });
    </script>

    <!-- Global Mailform Output-->
    <div class="snackbars" id="form-output-global"></div>
    <!-- Javascript-->
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>