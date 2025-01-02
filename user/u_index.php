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
	$sql = "SELECT user.account, people.name 
            FROM user 
            JOIN people ON user.user_id = people.user_id 
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

// 查詢該醫生的班表和預約數量
$reservations = [];
$leaves = []; // 用於存放請假資料
if ($doctor_id > 0) {
	// 查詢排班與預約資料
	$reservation_query = "
    SELECT ds.date, COUNT(a.appointment_id) AS people_count
    FROM doctorshift ds
    LEFT JOIN appointment a ON ds.doctorshift_id = a.doctorshift_id
    WHERE ds.doctor_id = ? AND YEAR(ds.date) = ? AND MONTH(ds.date) = ?
    GROUP BY ds.date
    ";
	$stmt = mysqli_prepare($link, $reservation_query);
	mysqli_stmt_bind_param($stmt, "iii", $doctor_id, $year, $month);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	while ($row = mysqli_fetch_assoc($result)) {
		$reservations[$row['date']] = $row['people_count'];
	}
	mysqli_stmt_close($stmt);

	// 查詢請假資料
	$leave_query = "
    SELECT start_date, end_date, reason
    FROM leaves
    WHERE doctor_id = ? AND (YEAR(start_date) = ? OR YEAR(end_date) = ?)
    ";
	$stmt_leave = mysqli_prepare($link, $leave_query);
	mysqli_stmt_bind_param($stmt_leave, "iii", $doctor_id, $year, $year);
	mysqli_stmt_execute($stmt_leave);
	$leave_result = mysqli_stmt_get_result($stmt_leave);

	while ($row = mysqli_fetch_assoc($leave_result)) {
		$start_date = substr($row['start_date'], 0, 10);
		$end_date = substr($row['end_date'], 0, 10);
		$reason = $row['reason'];

		$current_date = $start_date;
		while ($current_date <= $end_date) {
			$leaves[$current_date] = $reason;
			$current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
		}
	}
	mysqli_stmt_close($stmt_leave);
}

mysqli_close($link);
?>



<head>
	<!-- Site Title-->
	<title>運動筋膜放鬆</title>
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
								<!--Brand--><a class="brand-name" href="u_index.php"><img class="logo-default"
										src="images/logo-default-172x36.png" alt="" width="86" height="18"
										loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
										alt="" width="86" height="18" loading="lazy" /></a>
							</div>
						</div>
						<div class="rd-navbar-nav-wrap">
							<ul class="rd-navbar-nav">
								<li class="rd-nav-item active"><a class="rd-nav-link" href="u_index.php">首頁</a>
								</li>

								<li class="rd-nav-item"><a class="rd-nav-link" href="#">關於我們</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_link.php">醫生介紹</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_caseshare.php">個案分享</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_body-knowledge.php">日常小知識</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_reserve.php">立即預約</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_reserve-record.php">查看預約資料</a>
											<!-- 修改預約 -->
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_reserve-time.php">查看預約時段</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="u_history.php">歷史紀錄</a>
								</li>
								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="">歷史紀錄</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="single-teacher.html">個人資料</a>
										</li>
									</ul>
								</li> -->

								<li class="rd-nav-item"><a class="rd-nav-link" href="u_profile.php">個人資料</a>
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
								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="contacts.html">Contacts</a>
								  </li> -->
							</ul>
						</div>
						<div class="rd-navbar-collapse-toggle" data-rd-navbar-toggle=".rd-navbar-collapse"><span></span>
						</div>
						<!-- <div class="rd-navbar-aside-right rd-navbar-collapse">
							<div class="rd-navbar-social">
								<div class="rd-navbar-social-text">聯絡方式</div>
								<ul class="list-inline">
									<li><a class="icon novi-icon icon-default icon-custom-facebook"
											href="https://www.facebook.com/ReTurnYourBody/"></a></li>
									<li><a class="icon novi-icon icon-default icon-custom-linkedin"
											href="https://lin.ee/sUaUVMq"></a>
									</li>
									<li><a class="icon novi-icon icon-default icon-custom-instagram"
											href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"></a>
									</li>
								</ul>
							</div>
						</div>-->
						<?php
						echo "歡迎 ~ ";
						// 顯示姓名
						echo $姓名;
						?>
					</div>
				</nav>
			</div>
		</header>

		<!-- <marquee>跑馬燈</marquee> -->

		<!--Welcome back user-->
		<section class="section section-xl bg-image bg-image-9 text-center novi-bg novi-bg-img">
			<div class="container">
				<!-- <h1>Welcome back user-<?php echo $姓名; ?></h1> -->
				<h1 class="fw-light text-uppercase">運動筋膜放鬆</h1>
				<p class="big">
					''運動筋膜放鬆在做什麼？''<br />
					✨運動筋膜放鬆是透過治療師的牽引將您的身體姿勢擺到適當的位置✨<br />
					✨還原回到它自己該在的位置而達到放鬆✨</p>
				<div class="group-md button-group">
					<a class="button button-dark button-nina" href="u_reserve.php">立即預約</a>
					<a class="button button-dark button-nina" href="u_link.php">醫生介紹</a>
				</div>
			</div>
		</section>
		<!--Welcome back doctor-->



		<!--班表-->
		<section class="section section-lg bg-default">
			<h3 style="text-align: center;">治療師班表</h3>

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
				const currentYear = new Date().getFullYear();
				const currentMonth = new Date().getMonth();
				const yearSelect = document.getElementById('year');
				const monthSelect = document.getElementById('month');
				const doctorSelect = document.getElementById('doctor');
				const calendarBody = document.getElementById('calendar');

				const reservations = <?php echo json_encode($reservations); ?>;
				const leaves = <?php echo json_encode($leaves); ?>;

				// 初始化選單
				function initSelectOptions() {
					yearSelect.innerHTML = '';
					monthSelect.innerHTML = '';

					for (let year = currentYear - 5; year <= currentYear + 5; year++) {
						yearSelect.innerHTML += `<option value="${year}" ${year == <?php echo $year; ?> ? 'selected' : ''}>${year}</option>`;
					}

					for (let month = 1; month <= 12; month++) {
						monthSelect.innerHTML += `<option value="${month}" ${month == <?php echo $month; ?> ? 'selected' : ''}>${month}</option>`;
					}
				}

				// 驗證所有選單並執行搜尋
				function validateAndFetch() {
					const doctor = doctorSelect.value;
					const year = yearSelect.value;
					const month = monthSelect.value;

					if (doctor === "0") {
						alert("請選擇治療師！");
						return;
					}
					if (!year) {
						alert("請選擇年份！");
						return;
					}
					if (!month) {
						alert("請選擇月份！");
						return;
					}

					fetchSchedule(doctor, year, month);
				}

				// 搜尋並跳轉到指定參數的網址
				function fetchSchedule(doctorId, year, month) {
					window.location.href = `?doctor_id=${doctorId}&year=${year}&month=${month}`;
				}

				// 生成日曆
				function generateCalendar() {
					const year = yearSelect.value;
					const month = monthSelect.value - 1;
					calendarBody.innerHTML = '';

					const firstDay = new Date(year, month, 1).getDay();
					const lastDate = new Date(year, month + 1, 0).getDate();

					let row = document.createElement('tr');
					for (let i = 0; i < firstDay; i++) row.appendChild(document.createElement('td'));

					for (let date = 1; date <= lastDate; date++) {
						const fullDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
						const cell = document.createElement('td');
						cell.textContent = date;

						if (leaves[fullDate]) {
							const leaveInfo = document.createElement('div');
							leaveInfo.textContent = '休';
							leaveInfo.style.color = 'blue';
							leaveInfo.style.cursor = 'pointer';

							leaveInfo.addEventListener('click', () => {
								alert(`日期: ${fullDate}\n原因: ${leaves[fullDate]}`);
							});

							cell.appendChild(leaveInfo);
						} else if (reservations[fullDate]) {
							const info = document.createElement('div');
							info.textContent = `預約: ${reservations[fullDate]} 人`;
							info.className = 'reservation-info';
							cell.appendChild(info);
						}

						row.appendChild(cell);
						if (row.children.length === 7) {
							calendarBody.appendChild(row);
							row = document.createElement('tr');
						}
					}
					while (row.children.length < 7) row.appendChild(document.createElement('td'));
					calendarBody.appendChild(row);
				}

				// 初始化選單與日曆
				initSelectOptions();
				generateCalendar();
			</script>

		</section>
		<!--班表-->

		<footer class="section novi-bg novi-bg-img footer-simple">
			<div class="container">
				<div class="row row-40">
					<div class="col-md-4">
						<h4>關於我們</h4>
						<ul class="list-inline" style="font-size: 40px; display: inline-block;color: #333333; ">
							<li><a class="icon novi-icon icon-default icon-custom-facebook"
									href="https://www.facebook.com/ReTurnYourBody/" target="_blank"></a></li>
							<li><a class="icon novi-icon icon-default icon-custom-linkedin"
									href="https://lin.ee/sUaUVMq" target="_blank"></a></li>
							<li><a class="icon novi-icon icon-default icon-custom-instagram"
									href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"
									target="_blank"></a></li>
						</ul>

					</div>
					<div class="col-md-3">
						<h4>快速連結</h4>
						<ul class="list-marked">
							<li><a href="u_index.php">首頁</a></li>
							<li><a href="u_link.php.php">醫生介紹</a></li>
							<li><a href="u_caseshare.php">個案分享</a></li>
							<li><a href="u_body-knowledge.php">日常小知識</a></li>
							<li><a href="u_reserve.php">預約</a></li>
							<li><a href="u_reserve-record.php">查看預約資料</a></li>
							<li><a href="u_reserve-time.php">查看預約時段</a></li>
							<li><a href="u_history.php">歷史紀錄</a></li>
							<li> <a href="u_profile.php">個人資料</a></li>
							</a></li>
						</ul>
					</div>
				</div>
			</div>
		</footer>

		
	</div>
	<!-- Global Mailform Output-->
	<div class="snackbars" id="form-output-global"></div>
	<!-- Javascript-->
	<script src="js/core.min.js"></script>
	<script src="js/script.js"></script>
</body>

</html>