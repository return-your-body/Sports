<?php
session_start();

if (!isset($_SESSION["登入狀態"])) {
	// 如果未登入或會話過期，跳轉至登入頁面
	header("Location: ../index.html");
	exit;
}

// 防止頁面被瀏覽器緩存
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

// 引入資料庫連接檔案
require '../db.php';

// 檢查是否有 "帳號" 在 Session 中
if (isset($_SESSION["帳號"])) {
	// 獲取用戶帳號
	$帳號 = $_SESSION['帳號'];

	// 查詢用戶詳細資料
	$sql = "SELECT account, grade_id FROM user WHERE account = ?";
	$stmt = mysqli_prepare($link, $sql);
	mysqli_stmt_bind_param($stmt, "s", $帳號);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	if ($result && mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
		$帳號名稱 = $row['account']; // 使用者帳號
		$等級 = $row['grade_id']; // 等級（例如 1: 醫生, 2: 護士, 等等）
	} else {
		// 如果查詢不到對應的資料
		echo "<script>
                alert('找不到對應的帳號資料，請重新登入。');
                window.location.href = '../index.html';
              </script>";
		exit();
	}
}

// 取得醫生資料
$query = "
    SELECT d.doctor_id, d.doctor
    FROM doctor d
    INNER JOIN user u ON d.user_id = u.user_id
    INNER JOIN grade g ON u.grade_id = g.grade_id
    WHERE g.grade = '醫生'
";
$result = mysqli_query($link, $query);

// 檢查查詢結果
if (!$result) {
	echo "Error fetching doctor data: " . mysqli_error($link);
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
					<p class="heading-1 breadcrumbs-custom-title">新增治療師班表</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="a_index.php">首頁</a></li>
						<li><a href="">關於治療師</a></li>
						<li class="active">新增治療師班表</li>
					</ul>
				</div>
			</section>
		</div>
		<div class="form-row">
			<!-- 年份選擇 -->
			<label for="year">選擇年份：</label>
			<select id="year"></select>

			<!-- 月份選擇 -->
			<label for="month">選擇月份：</label>
			<select id="month"></select>

			<!-- 治療師選擇 -->
			<label for="the">選擇治療師：</label>
			<select id="the" name="doctor">
				<option value="">所有</option>
				<?php
				while ($row = mysqli_fetch_assoc($result)) {
					echo "<option value='" . $row['doctor_id'] . "'>" . htmlspecialchars($row['doctor']) . "</option>";
				}
				?>
			</select>

			<!-- 工作日選擇 -->
			<label for="weekday-select">選擇工作日：</label>
			<div class="dropdown-container">
				<button id="dropdownButton" class="dropdown-button">選擇工作日</button>
				<div class="dropdown-options" id="dropdownOptions">
					<label><input type="checkbox" value="0"> 日</label>
					<label><input type="checkbox" value="1" checked> 一</label>
					<label><input type="checkbox" value="2" checked> 二</label>
					<label><input type="checkbox" value="3" checked> 三</label>
					<label><input type="checkbox" value="4" checked> 四</label>
					<label><input type="checkbox" value="5" checked> 五</label>
					<label><input type="checkbox" value="6"> 六</label>
				</div>
			</div>

			<!-- 送出按鈕 -->
			<button type="button" class="custom-green-button">送出</button>
		</div>


		<!-- 日曆表格，用於顯示每個月的天數及工作安排 -->
		<table class="table-custom table-color-header table-custom-bordered">
			<thead>
				<tr>
					<!-- 星期標題 -->
					<th>日</th>
					<th>一</th>
					<th>二</th>
					<th>三</th>
					<th>四</th>
					<th>五</th>
					<th>六</th>
				</tr>
			</thead>

			<tbody id="calendar"></tbody> <!-- 日曆內容將由 JavaScript 動態生成 -->
		</table>

		<style>
			.custom-green-button {
				display: inline-block;
				padding: 10px 20px;
				/* 調整按鈕內的間距 */
				font-size: 14px;
				/* 調整字體大小 */
				font-weight: bold;
				/* 讓文字加粗 */
				color: #ffffff;
				/* 文字顏色為白色 */
				background-color: #00a99d;
				/* 綠色背景色 */
				border: none;
				/* 移除邊框 */
				border-radius: 8px;
				/* 圓角按鈕 */
				cursor: pointer;
				/* 滑鼠移上時變成手指 */
				transition: all 0.3s ease;
				/* 添加過渡效果 */
				box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
				/* 按鈕陰影 */
			}

			.custom-green-button:hover {
				background-color: #008f84;
				/* 懸停時加深綠色 */
				box-shadow: 0px 6px 8px rgba(0, 0, 0, 0.2);
				/* 懸停時加強陰影效果 */
				transform: translateY(-2px);
				/* 懸停時向上移動一點 */
			}

			.custom-green-button:active {
				background-color: #00796c;
				/* 按下時的深綠色 */
				box-shadow: 0px 3px 4px rgba(0, 0, 0, 0.1);
				/* 按下時陰影變小 */
				transform: translateY(0);
				/* 回到原位 */
			}



			/* 多選下拉選單的容器樣式 */
			.dropdown-container {
				position: relative;
				display: inline-block;
			}

			/* 按鈕樣式，用於打開下拉選單 */
			.dropdown-button {
				padding: 8px;
				border: 1px solid #ccc;
				cursor: pointer;
				background-color: white;
			}

			/* 下拉選項的樣式 */
			.dropdown-options {
				display: none;
				/* 預設為隱藏 */
				position: absolute;
				z-index: 1;
				/* 保證選單在其他內容之上 */
				border: 1px solid #ccc;
				background-color: white;
				max-height: 150px;
				/* 最大高度，避免內容過長 */
				overflow-y: auto;
				/* 垂直滾動條 */
				box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
				/* 陰影效果 */
			}

			/* 每個選項的樣式 */
			.dropdown-options label {
				display: block;
				/* 每個選項單獨一行 */
				padding: 5px;
				cursor: pointer;
			}

			/* 滑鼠懸停在選項時的樣式 */
			.dropdown-options label:hover {
				background-color: #f1f1f1;
				/* 提示使用者可點選 */
			}
		</style>

		<script>
			document.querySelector('.custom-green-button').addEventListener('click', function () {
				var doctor_id = document.getElementById('the').value; // 取得選中的醫生
				if (!doctor_id) {
					alert('請選擇治療師');
					return;
				}

				var year = document.getElementById('year').value;
				var month = document.getElementById('month').value;
				month = (parseInt(month) + 1).toString().padStart(2, '0'); // 修正月份格式

				var shifts = [];
				document.querySelectorAll('#calendar td').forEach(function (cell) {
					var date = cell.querySelector('div')?.textContent?.trim();
					if (!date || isNaN(date)) return; // 跳過空白或非數字的單元格
					date = date.padStart(2, '0'); // 日期補零
					var fullDate = `${year}-${month}-${date}`; // 完整日期

					var startHour = cell.querySelector('.start-hour')?.value || '07';
					var startMinute = cell.querySelector('.start-minute')?.value || '00';
					var endHour = cell.querySelector('.end-hour')?.value || '16';
					var endMinute = cell.querySelector('.end-minute')?.value || '00';
					var offDuty = cell.querySelector('.off-duty-checkbox')?.checked;

					if (offDuty) return; // 跳過不上班

					var start_time = `${startHour}:${startMinute}`;
					var end_time = `${endHour}:${endMinute}`;
					shifts.push({ date: fullDate, start_time: start_time, end_time: end_time });
				});

				if (shifts.length === 0) {
					alert('無有效的班表資料');
					return;
				}

				var xhr = new XMLHttpRequest();
				xhr.open('POST', '新增班表.php', true);
				xhr.setRequestHeader('Content-Type', 'application/json');
				xhr.onreadystatechange = function () {
					if (xhr.readyState === 4 && xhr.status === 200) {
						var response = JSON.parse(xhr.responseText);
						if (response.status === 'success') {
							alert('班表新增成功！');
						} else {
							alert('班表新增失敗：' + response.message);
						}
					}
				};
				xhr.send(JSON.stringify({ doctor_id: doctor_id, shifts: shifts }));
			});


			// 以下為現有代碼的動態日曆與表單初始化邏輯
			const currentDate = new Date();
			const currentYear = currentDate.getFullYear();
			const currentMonth = currentDate.getMonth();

			const yearSelect = document.getElementById('year');
			const monthSelect = document.getElementById('month');
			const doctorSelect = document.getElementById('the');
			const calendarBody = document.getElementById('calendar');
			const dropdownButton = document.getElementById('dropdownButton');
			const dropdownOptions = document.getElementById('dropdownOptions');

			function initHourOptions(defaultHour = null) {
				const hours = [];
				for (let hour = 7; hour <= 24; hour++) {
					const selected = defaultHour === hour ? 'selected' : '';
					hours.push(`<option value="${hour}" ${selected}>${hour.toString().padStart(2, '0')}</option>`);
				}
				return hours.join('');
			}

			function initMinuteOptions(defaultMinute = null) {
				const minutes = [];
				for (let minute = 0; minute < 60; minute += 20) {
					const selected = defaultMinute === minute ? 'selected' : '';
					minutes.push(`<option value="${minute}" ${selected}>${minute.toString().padStart(2, '0')}</option>`);
				}
				return minutes.join('');
			}

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

			function getSelectedWeekdays() {
				const selectedOptions = Array.from(dropdownOptions.querySelectorAll('input:checked'));
				return selectedOptions.map(option => parseInt(option.value));
			}

			function handleOffDutyCheckbox(event, dateCell) {
				const checkbox = event.target;
				const startHourSelect = dateCell.querySelector('.start-hour');
				const startMinuteSelect = dateCell.querySelector('.start-minute');
				const endHourSelect = dateCell.querySelector('.end-hour');
				const endMinuteSelect = dateCell.querySelector('.end-minute');

				if (checkbox.checked) {
					startHourSelect.value = '0';
					startMinuteSelect.value = '0';
					endHourSelect.value = '0';
					endMinuteSelect.value = '0';
					startHourSelect.disabled = true;
					startMinuteSelect.disabled = true;
					endHourSelect.disabled = true;
					endMinuteSelect.disabled = true;
				} else {
					startHourSelect.disabled = false;
					startMinuteSelect.disabled = false;
					endHourSelect.disabled = false;
					endMinuteSelect.disabled = false;
					startHourSelect.value = '7';
					startMinuteSelect.value = '0';
					endHourSelect.value = '16';
					endMinuteSelect.value = '0';
				}
			}

			function generateCalendar(year, month) {
				calendarBody.innerHTML = '';
				const firstDay = new Date(year, month, 1).getDay();
				const lastDate = new Date(year, month + 1, 0).getDate();
				const selectedWeekdays = getSelectedWeekdays();

				let row = document.createElement('tr');

				for (let i = 0; i < firstDay; i++) {
					const emptyCell = document.createElement('td');
					row.appendChild(emptyCell);
				}

				for (let date = 1; date <= lastDate; date++) {
					if (row.children.length === 7) {
						calendarBody.appendChild(row);
						row = document.createElement('tr');
					}

					const weekday = (firstDay + date - 1) % 7;
					const isOffDuty = !selectedWeekdays.includes(weekday);

					const dateCell = document.createElement('td');
					dateCell.innerHTML = `
				<div>${date}</div>
				<div>
					<label>上班：</label><br>
					<select class="start-hour" ${isOffDuty ? 'disabled' : ''}>${initHourOptions(isOffDuty ? 0 : 7)}</select> :
					<select class="start-minute" ${isOffDuty ? 'disabled' : ''}>${initMinuteOptions(isOffDuty ? 0 : 0)}</select>
				</div>
				<div>
					<label>下班：</label><br>
					<select class="end-hour" ${isOffDuty ? 'disabled' : ''}>${initHourOptions(isOffDuty ? 0 : 16)}</select> :
					<select class="end-minute" ${isOffDuty ? 'disabled' : ''}>${initMinuteOptions(isOffDuty ? 0 : 0)}</select>
				</div>
				<div>
					<label>
						<input type="checkbox" class="off-duty-checkbox" ${isOffDuty ? 'checked' : ''}> 不上班
					</label>
				</div>
			`;

					const offDutyCheckbox = dateCell.querySelector('.off-duty-checkbox');
					offDutyCheckbox.addEventListener('change', (event) => handleOffDutyCheckbox(event, dateCell));

					row.appendChild(dateCell);
				}

				while (row.children.length < 7) {
					const emptyCell = document.createElement('td');
					row.appendChild(emptyCell);
				}
				calendarBody.appendChild(row);
			}

			dropdownButton.addEventListener('click', () => {
				dropdownOptions.style.display = dropdownOptions.style.display === 'block' ? 'none' : 'block';
			});

			document.addEventListener('click', (event) => {
				if (!dropdownButton.contains(event.target) && !dropdownOptions.contains(event.target)) {
					dropdownOptions.style.display = 'none';
				}
			});

			dropdownOptions.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
			});

			yearSelect.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
			});

			monthSelect.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
			});

			initYearOptions();
			initMonthOptions();
			generateCalendar(currentYear, currentMonth);
		</script>



		<!-- Global Mailform Output-->
		<div class="snackbars" id="form-output-global"></div>
		<!-- Javascript-->
		<script src="js/core.min.js"></script>
		<script src="js/script.js"></script>

	</div>
</body>

</html>