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



<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

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
				<polyline class="line-cornered stroke-animation" points="0,0 100,0 100,100" stroke-width="10"
					fill="none"></polyline>
				<polyline class="line-cornered stroke-animation" points="0,0 0,100 100,100" stroke-width="10"
					fill="none"></polyline>
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
								<!--Brand--><a class="brand-name" href="u_index.html"><img class="logo-default"
										src="images/logo-default-172x36.png" alt="" width="86" height="18"
										loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
										alt="" width="86" height="18" loading="lazy" /></a>
							</div>
						</div>
						<div class="rd-navbar-nav-wrap">
							<ul class="rd-navbar-nav">
								<li class="rd-nav-item"><a class="rd-nav-link" href="u_index.php">首頁</a>
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
								<li class="rd-nav-item active"><a class="rd-nav-link" href="#">預約</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item active"><a class="rd-dropdown-link"
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
											href="https://lin.ee/sUaUVMq"></a></li>
									<li><a class="icon novi-icon icon-default icon-custom-instagram"
											href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"></a>
									</li>
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
					<!-- <p class="breadcrumbs-custom-subtitle">Our team</p> -->
					<p class="heading-1 breadcrumbs-custom-title">立即預約</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="u_index.php">首頁</a></li>
						<li><a href="#">預約</a></li>
						<li class="active">立即預約</li>
					</ul>
				</div>
			</section>
		</div>
		<!--標題-->

		<?php
		include "../db.php";

		// 查詢排班數據
		$query = "
SELECT 
    d.doctor_id, 
    d.doctor, 
    ds.date, 
    st1.shifttime AS go_time, 
    st2.shifttime AS off_time
FROM 
    doctorshift ds
JOIN 
    doctor d ON ds.doctor_id = d.doctor_id
JOIN 
    shifttime st1 ON ds.go = st1.shifttime_id
JOIN 
    shifttime st2 ON ds.off = st2.shifttime_id
ORDER BY ds.date, d.doctor_id";

		$result = mysqli_query($link, $query);

		$schedule = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$date = $row['date'];
			if (!isset($schedule[$date])) {
				$schedule[$date] = [];
			}
			$schedule[$date][] = [
				'doctor' => $row['doctor'],
				'go_time' => $row['go_time'],
				'off_time' => $row['off_time'],
				'doctor_id' => $row['doctor_id'],
			];
		}

		// 查詢請假數據
		$query_leaves = "
SELECT 
    l.doctor_id, 
    l.start_date, 
    l.end_date
FROM 
    leaves l";

		$result_leaves = mysqli_query($link, $query_leaves);

		$leaves = [];
		while ($row = mysqli_fetch_assoc($result_leaves)) {
			$leaves[] = [
				'doctor_id' => $row['doctor_id'],
				'start_date' => $row['start_date'],
				'end_date' => $row['end_date'],
			];
		}

		// 輸出數據給前端
		header('Content-Type: application/json');
		// echo json_encode(['schedule' => $schedule, 'leaves' => $leaves], JSON_UNESCAPED_UNICODE);
		?>


		<section class="section section-lg bg-default">
			<div style="font-size: 18px; font-weight: bold; color: #333; margin-top: 10px; text-align: center;">
				<label for="year">選擇年份：</label>
				<select id="year"></select>
				<label for="month">選擇月份：</label>
				<select id="month"></select>

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
				const data = <?php echo json_encode(['schedule' => $schedule, 'leaves' => $leaves], JSON_UNESCAPED_UNICODE); ?>;
				const calendarData = data.schedule; // 排班數據
				const leaveData = data.leaves; // 請假數據
				const today = new Date(); // 今天日期
				const tomorrow = new Date(today); // 明天日期
				tomorrow.setDate(today.getDate() + 1);

				console.log("Calendar Data:", calendarData);
				console.log("Leave Data:", leaveData);

				/**
				 * 調整排班時間，處理部分請假情況
				 */
				function adjustShiftTime(doctorId, date, startTime, endTime) {
					const shiftStart = new Date(`${date}T${startTime}`);
					const shiftEnd = new Date(`${date}T${endTime}`);

					for (const leave of leaveData) {
						const leaveStart = new Date(leave.start_date);
						const leaveEnd = new Date(leave.end_date);

						if (leave.doctor_id === doctorId) {
							if (leaveStart <= shiftStart && leaveEnd >= shiftEnd) {
								return null; // 整天請假
							} else if (leaveStart <= shiftStart && leaveEnd > shiftStart && leaveEnd < shiftEnd) {
								return { go_time: leaveEnd.toTimeString().slice(0, 5), off_time: endTime }; // 調整上班時間
							} else if (leaveStart > shiftStart && leaveStart < shiftEnd && leaveEnd >= shiftEnd) {
								return { go_time: startTime, off_time: leaveStart.toTimeString().slice(0, 5) }; // 調整下班時間
							}
						}
					}
					return { go_time: startTime, off_time: endTime }; // 無請假
				}

				/**
				 * 生成日曆表格
				 */
				function generateCalendar() {
					const year = parseInt(document.getElementById('year').value);
					const month = parseInt(document.getElementById('month').value) - 1;

					const calendarBody = document.getElementById('calendar');
					calendarBody.innerHTML = '';

					const firstDay = new Date(year, month, 1).getDay();
					const lastDate = new Date(year, month + 1, 0).getDate();

					let row = document.createElement('tr');
					for (let i = 0; i < firstDay; i++) {
						row.appendChild(document.createElement('td'));
					}

					for (let date = 1; date <= lastDate; date++) {
						const fullDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
						const cell = document.createElement('td');
						cell.innerHTML = `<strong>${date}</strong>`;

						const currentDate = new Date(year, month, date); // 當前渲染的日期

						if (calendarData[fullDate]) {
							calendarData[fullDate].forEach(shift => {
								const adjustedShift = adjustShiftTime(shift.doctor_id, fullDate, shift.go_time, shift.off_time);

								const shiftDiv = document.createElement('div');
								if (adjustedShift) {
									shiftDiv.textContent = `${shift.doctor}: ${adjustedShift.go_time} - ${adjustedShift.off_time}`;

									// 只為明天及以後的日期添加預約按鈕
									if (currentDate >= tomorrow) {
										const reserveButton = document.createElement('button');
										reserveButton.textContent = '預約';
										reserveButton.style.marginLeft = '10px';
										reserveButton.onclick = () => alert(`預約成功！\n日期：${fullDate}\n醫生：${shift.doctor}`);
										shiftDiv.appendChild(reserveButton);
									}
								} else {
									shiftDiv.textContent = `${shift.doctor}: 請假`;
									shiftDiv.style.color = 'red';
								}

								shiftDiv.className = 'shift-info';
								cell.appendChild(shiftDiv);
							});
						} else {
							const noSchedule = document.createElement('div');
							noSchedule.textContent = '無排班';
							noSchedule.className = 'no-schedule';
							cell.appendChild(noSchedule);
						}

						row.appendChild(cell);

						if (row.children.length === 7) {
							calendarBody.appendChild(row);
							row = document.createElement('tr');
						}
					}

					while (row.children.length < 7) {
						row.appendChild(document.createElement('td'));
					}
					calendarBody.appendChild(row);
				}

				/**
				 * 初始化年份與月份選單
				 */
				function initSelectOptions() {
					const yearSelect = document.getElementById('year');
					const monthSelect = document.getElementById('month');

					for (let year = 2020; year <= 2030; year++) {
						const option = document.createElement('option');
						option.value = year;
						option.textContent = year;
						if (year === today.getFullYear()) option.selected = true;
						yearSelect.appendChild(option);
					}

					for (let month = 1; month <= 12; month++) {
						const option = document.createElement('option');
						option.value = month;
						option.textContent = month;
						if (month === today.getMonth() + 1) option.selected = true;
						monthSelect.appendChild(option);
					}

					generateCalendar();
				}

				document.getElementById('year').addEventListener('change', generateCalendar);
				document.getElementById('month').addEventListener('change', generateCalendar);

				initSelectOptions();
			</script>




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

						<div class="col-md-4">
							<h4>聯絡我們</h4>
							<br />
							<ul>
								<li>📍 <strong>診療地點:</strong>大重仁骨科復健科診所</li><br />
								<li>📍 <strong>地址:</strong>
									<a href="https://maps.app.goo.gl/u3TojSMqjGmdx5Pt5" class="custom-link"
										target="_blank" rel="noopener noreferrer">
										241 新北市三重區重新路五段 592 號
									</a>
								</li>
								<br />
								<li>📍 <strong>電話:</strong>(02) 2995-8283</li>
							</ul>

							<!-- <form class="rd-mailform rd-form-boxed" data-form-output="form-output-global"
							data-form-type="subscribe" method="post" action="bat/rd-mailform.php">
							<div class="form-wrap">
								<input class="form-input" type="email" name="email" data-constraints="@Email @Required"
									id="footer-mail">
								<label class="form-label" for="footer-mail">請輸入您的電子郵件</label>
							</div>
							<button class="form-button linearicons-paper-plane"></button>
						</form> -->
						</div>
					</div>
				</div>
			</footer>

			<!-- Global Mailform Output-->
			<div class="snackbars" id="form-output-global"></div>
			<!-- Javascript-->
			<script src="js/core.min.js"></script>
			<script src="js/script.js"></script>
</body>

</html>