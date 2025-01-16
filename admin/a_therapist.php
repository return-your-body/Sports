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

if (isset($_SESSION["帳號"])) {
	$帳號 = $_SESSION['帳號'];

	require '../db.php';

	// 查詢用戶詳細資料
	$sql = "SELECT account, grade_id FROM user WHERE account = ?";
	$stmt = mysqli_prepare($link, $sql);
	mysqli_stmt_bind_param($stmt, "s", $帳號);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	if ($result && mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
		$帳號名稱 = $row['account'];
		$等級 = $row['grade_id'];
	} else {
		echo "<script>
                alert('找不到對應的帳號資料，請重新登入。');
                window.location.href = '../index.html';
              </script>";
		exit();
	}
}

require '../db.php';

// 處理 AJAX 請求，返回按日期的總人數
if (isset($_GET['fetch'])) {
	$year = $_GET['year'] ?? date('Y');
	$month = $_GET['month'] ?? date('m');
	$doctor_id = $_GET['doctor_id'] ?? '';

	$sql = "SELECT DATE(d.date) as appointment_date, COUNT(*) as total
            FROM appointment a
            INNER JOIN doctorshift d ON a.doctorshift_id = d.doctorshift_id
            WHERE YEAR(d.date) = ? AND MONTH(d.date) = ?";
	$params = [$year, $month];

	if ($doctor_id !== 'all' && $doctor_id !== '') {
		$sql .= " AND d.doctor_id = ?";
		$params[] = $doctor_id;
	}
	$sql .= " GROUP BY DATE(d.date)";

	$stmt = mysqli_prepare($link, $sql);
	mysqli_stmt_bind_param($stmt, str_repeat("s", count($params)), ...$params);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	$data = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$data[$row['appointment_date']] = $row['total'];
	}
	echo json_encode($data);
	exit;
}

?>


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
		.highlight-red {
			color: red;
			font-weight: bold;
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
				<polyline class="line-cornered stroke-animation" points="0,0 100,0 100,100" stroke-width="10"
					fill="none">
				</polyline>
				<polyline class="line-cornered stroke-animation" points="0,0 0,100 100,100" stroke-width="10"
					fill="none">
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
					data-xxl-layout="rd-navbar-static" data-xxxl-layout="rd-navbar-static"
					data-lg-device-layout="rd-navbar-fixed" data-xl-device-layout="rd-navbar-static"
					data-xxl-device-layout="rd-navbar-static" data-xxxl-device-layout="rd-navbar-static"
					data-stick-up-offset="1px" data-sm-stick-up-offset="1px" data-md-stick-up-offset="1px"
					data-lg-stick-up-offset="1px" data-xl-stick-up-offset="1px" data-xxl-stick-up-offset="1px"
					data-xxx-lstick-up-offset="1px" data-stick-up="true">
					<div class="rd-navbar-inner">
						<!-- RD Navbar Panel-->
						<div class="rd-navbar-panel">
							<!-- RD Navbar Toggle-->
							<button class="rd-navbar-toggle"
								data-rd-navbar-toggle=".rd-navbar-nav-wrap"><span></span></button>
							<!-- RD Navbar Brand-->
							<div class="rd-navbar-brand">
								<!--Brand--><a class="brand-name" href="index.php"><img class="logo-default"
										src="images/logo-default-172x36.png" alt="" width="86" height="18"
										loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
										alt="" width="86" height="18" loading="lazy" /></a>
							</div>
						</div>
						<div class="rd-navbar-nav-wrap">
							<ul class="rd-navbar-nav">
								<li class="rd-nav-item"><a class="rd-nav-link" href="a_index.php">網頁編輯</a>
								</li>
								<li class="rd-nav-item active"><a class="rd-nav-link" href="">關於治療師</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_therapist.php">治療師時間表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_addds.php">新增治療師班表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">修改治療師班表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_leave.php">請假申請</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="a_comprehensive.php">綜合</a>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="">用戶管理</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_patient.php">用戶管理</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_addhd.php">新增治療師/助手</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_blacklist.php">黑名單</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="#">醫生管理</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_doctorlistadd.php">新增醫生資料</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_doctorlistmod.php">修改醫生資料</a>
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
						<div class="rd-navbar-collapse-toggle" data-rd-navbar-toggle=".rd-navbar-collapse"><span></span>
						</div>
						<!-- <div class="rd-navbar-aside-right rd-navbar-collapse">
							<div class="rd-navbar-social">
								<div class="rd-navbar-social-text">Follow us</div>
								<ul class="list-inline">
									<li><a class="icon novi-icon icon-default icon-custom-facebook"
											href="https://www.facebook.com/ReTurnYourBody/"></a></li>
									<li><a class="icon novi-icon icon-default icon-custom-instagram"
											href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"></a>
									</li>
								</ul>
							</div>
						</div> -->
						<?php
						echo "歡迎 ~ ";
						// 顯示姓名
						echo $帳號名稱;
						?>
					</div>
				</nav>
			</div>
		</header>
		<!-- Page Header-->
		<div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
			<!-- Breadcrumbs-->
			<section class="breadcrumbs-custom breadcrumbs-custom-svg">
				<div class="container">
					<p class="heading-1 breadcrumbs-custom-title">關於治療師</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="a_index.php">首頁</a></li>
						<li><a href="">關於治療師</a></li>
						<li class="active">治療師時間表</li>
					</ul>
				</div>
			</section>
		</div>
		<div>
			<label for="year">選擇年份：</label>
			<select id="year"></select>
			<label for="month">選擇月份：</label>
			<select id="month"></select>
			<!-- 選擇治療師 -->
			<label for="the">選擇治療師：</label>
			<select id="the" name="doctor_id">
				<!-- 新增「所有治療師」選項 -->
				<option value="all">所有治療師</option>
				<?php
				include '../db.php'; // 引入資料庫連線檔案
				
				// 查詢等級為「醫生」的資料
				$query = "
        SELECT d.doctor_id, d.doctor
        FROM doctor d
        INNER JOIN user u ON d.user_id = u.user_id
        INNER JOIN grade g ON u.grade_id = g.grade_id
        WHERE g.grade_id = 2
    ";
				$result = mysqli_query($link, $query);

				// 檢查查詢結果
				if (!$result) {
					echo "Error fetching doctor data: " . mysqli_error($link);
					exit;
				}

				// 輸出查詢結果到下拉選單
				while ($row = mysqli_fetch_assoc($result)) {
					echo "<option value='" . $row['doctor_id'] . "'>" . htmlspecialchars($row['doctor']) . "</option>";
				}
				?>
			</select>

			<a href="">數據</a>
			<!-- <a href="a_therapist2.php">預約用戶資料</a> -->

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
			const doctorSelect = document.getElementById('the');
			const calendarBody = document.getElementById('calendar');

			function initYearOptions() {
				for (let year = currentYear - 5; year <= currentYear + 5; year++) {
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

			async function fetchAppointments(year, month, doctorId) {
				const response = await fetch(`?fetch=1&year=${year}&month=${month}&doctor_id=${doctorId}`);
				return response.json();
			}

			async function generateCalendar(year, month) {
				const appointments = await fetchAppointments(year, month + 1, doctorSelect.value);

				calendarBody.innerHTML = '';
				const firstDay = new Date(year, month, 1).getDay();
				const lastDate = new Date(year, month + 1, 0).getDate();
				const today = new Date();

				let row = document.createElement('tr');

				// 空白單元格
				for (let i = 0; i < firstDay; i++) {
					const emptyCell = document.createElement('td');
					row.appendChild(emptyCell);
				}

				for (let date = 1; date <= lastDate; date++) {
					if (row.children.length === 7) {
						calendarBody.appendChild(row);
						row = document.createElement('tr');
					}

					const cellDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
					const isToday = new Date(cellDate).toDateString() === today.toDateString();
					const isFuture = new Date(cellDate) > today;

					const dateCell = document.createElement('td');
					const dateText = document.createElement('div');
					dateText.textContent = date;

					const bookingInfo = document.createElement('div');
					bookingInfo.classList.add('booking-info');

					// 保留你的判斷邏輯，並填入預約人數
					if (isToday) {
						bookingInfo.innerHTML = `<a href="a_therapist2.php?date=${cellDate}&type=today" class="highlight-red">目前總人數：${appointments[cellDate] || 0}</a>`;
					} else if (isFuture) {
						bookingInfo.innerHTML = `<a href="a_therapist2.php?date=${cellDate}&type=future">目前預約人數：${appointments[cellDate] || 0}</a>`;
					} else {
						bookingInfo.innerHTML = `<a href="a_therapist2.php?date=${cellDate}&type=past">總人數：${appointments[cellDate] || 0}</a>`;
					}



					dateCell.appendChild(dateText);
					dateCell.appendChild(bookingInfo);
					row.appendChild(dateCell);
				}

				while (row.children.length < 7) {
					row.appendChild(document.createElement('td'));
				}
				calendarBody.appendChild(row);
			}

			// 綁定事件
			yearSelect.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
			});

			monthSelect.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
			});

			doctorSelect.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
			});

			// 初始化
			initYearOptions();
			initMonthOptions();
			generateCalendar(currentYear, currentMonth);

		</script>


		<!-- Global Mailform Output-->
		<div class="snackbars" id="form-output-global"></div>
		<!-- Javascript-->
		<script src="js/core.min.js"></script>
		<script src="js/script.js"></script>
</body>

</html>