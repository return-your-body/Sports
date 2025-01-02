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
		<div>
			<!-- 年份選擇下拉選單 -->
			<label for="year">選擇年份：</label>
			<select id="year"></select>

			<!-- 月份選擇下拉選單 -->
			<label for="month">選擇月份：</label>
			<select id="month"></select>

			<!-- 治療師選擇下拉選單 -->
			<label for="the">選擇治療師：</label>
			<select id="the" name="doctor">
				<!-- 第一個選項為 "所有"，表示不限治療師 -->
				<option value="">所有</option>
				<?php
				// 從資料庫中動態生成治療師選項，避免 XSS 攻擊，使用 htmlspecialchars 轉譯特殊字符
				while ($row = mysqli_fetch_assoc($result)) {
					echo "<option value='" . $row['doctor_id'] . "'>" . htmlspecialchars($row['doctor']) . "</option>";
				}
				?>
			</select>

			<!-- 自定義的 Excel 樣式多選下拉選單 -->
			<label for="weekday-select">選擇工作日：</label>
			<div class="dropdown-container">
				<!-- 按鈕，用於打開或關閉多選選單 -->
				<button id="dropdownButton" class="dropdown-button">選擇工作日</button>

				<!-- 下拉選項，提供多選功能 -->
				<div class="dropdown-options" id="dropdownOptions">
					<label><input type="checkbox" value="0"> 日</label> <!-- 星期日 -->
					<label><input type="checkbox" value="1" checked> 一</label> <!-- 預設勾選星期一 -->
					<label><input type="checkbox" value="2" checked> 二</label> <!-- 預設勾選星期二 -->
					<label><input type="checkbox" value="3" checked> 三</label> <!-- 預設勾選星期三 -->
					<label><input type="checkbox" value="4" checked> 四</label> <!-- 預設勾選星期四 -->
					<label><input type="checkbox" value="5" checked> 五</label> <!-- 預設勾選星期五 -->
					<label><input type="checkbox" value="6"> 六</label> <!-- 星期六 -->
				</div>
			</div>
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
			// 初始化當前日期
			const currentDate = new Date();
			const currentYear = currentDate.getFullYear(); // 當前年份
			const currentMonth = currentDate.getMonth();  // 當前月份（0 為 1 月）

			// DOM 元素引用
			const yearSelect = document.getElementById('year');
			const monthSelect = document.getElementById('month');
			const doctorSelect = document.getElementById('the');
			const calendarBody = document.getElementById('calendar');
			const dropdownButton = document.getElementById('dropdownButton');
			const dropdownOptions = document.getElementById('dropdownOptions');

			// 初始化小時選單，提供小時選項（7 至 24 點）
			function initHourOptions(defaultHour = null) {
				const hours = [];
				for (let hour = 7; hour <= 24; hour++) {
					const selected = defaultHour === hour ? 'selected' : ''; // 如果是預設小時，則選中
					hours.push(`<option value="${hour}" ${selected}>${hour.toString().padStart(2, '0')}</option>`);
				}
				return hours.join('');
			}

			// 初始化分鐘選單，提供分鐘選項（每隔 20 分鐘）
			function initMinuteOptions(defaultMinute = null) {
				const minutes = [];
				for (let minute = 0; minute < 60; minute += 20) {
					const selected = defaultMinute === minute ? 'selected' : ''; // 如果是預設分鐘，則選中
					minutes.push(`<option value="${minute}" ${selected}>${minute.toString().padStart(2, '0')}</option>`);
				}
				return minutes.join('');
			}

			// 初始化年份選單，範圍為當前年份 ± 5 年
			function initYearOptions() {
				for (let year = currentYear - 5; year <= currentYear + 5; year++) {
					const option = document.createElement('option');
					option.value = year;
					option.textContent = year;
					if (year === currentYear) option.selected = true; // 預設選中當前年份
					yearSelect.appendChild(option);
				}
			}

			// 初始化月份選單（1 至 12 月）
			function initMonthOptions() {
				for (let month = 0; month < 12; month++) {
					const option = document.createElement('option');
					option.value = month;
					option.textContent = month + 1; // 顯示為 1 至 12
					if (month === currentMonth) option.selected = true; // 預設選中當前月份
					monthSelect.appendChild(option);
				}
			}

			// 獲取被選中的工作日（多選）
			function getSelectedWeekdays() {
				// 獲取多選選單中被勾選的選項
				const selectedOptions = Array.from(dropdownOptions.querySelectorAll('input:checked'));
				return selectedOptions.map(option => parseInt(option.value)); // 返回選中值的數字陣列
			}

			// 處理 "不上班" 勾選按鈕
			function handleOffDutyCheckbox(event, dateCell) {
				const checkbox = event.target;
				const startHourSelect = dateCell.querySelector('.start-hour');
				const startMinuteSelect = dateCell.querySelector('.start-minute');
				const endHourSelect = dateCell.querySelector('.end-hour');
				const endMinuteSelect = dateCell.querySelector('.end-minute');

				if (checkbox.checked) {
					// 當勾選 "不上班" 時，禁用所有時間選單並設為 00:00
					startHourSelect.value = '0';
					startMinuteSelect.value = '0';
					endHourSelect.value = '0';
					endMinuteSelect.value = '0';
					startHourSelect.disabled = true;
					startMinuteSelect.disabled = true;
					endHourSelect.disabled = true;
					endMinuteSelect.disabled = true;
				} else {
					// 取消勾選 "不上班"，啟用時間選單並恢復預設時間
					startHourSelect.disabled = false;
					startMinuteSelect.disabled = false;
					endHourSelect.disabled = false;
					endMinuteSelect.disabled = false;
					startHourSelect.value = '7'; // 預設上班時間
					startMinuteSelect.value = '0';
					endHourSelect.value = '16'; // 預設下班時間
					endMinuteSelect.value = '0';
				}
			}

			// 動態生成日曆
			function generateCalendar(year, month) {
				calendarBody.innerHTML = ''; // 清空日曆表格
				const firstDay = new Date(year, month, 1).getDay(); // 計算當月 1 號是星期幾
				const lastDate = new Date(year, month + 1, 0).getDate(); // 計算當月最後一天是幾號
				const selectedWeekdays = getSelectedWeekdays(); // 獲取用戶選中的工作日

				let row = document.createElement('tr'); // 建立一行

				// 填充空白單元格，讓第一天對應正確的星期
				for (let i = 0; i < firstDay; i++) {
					const emptyCell = document.createElement('td');
					row.appendChild(emptyCell);
				}

				// 填充日期單元格
				for (let date = 1; date <= lastDate; date++) {
					if (row.children.length === 7) {
						// 當行滿 7 格時，插入到表格並開始新行
						calendarBody.appendChild(row);
						row = document.createElement('tr');
					}

					const weekday = (firstDay + date - 1) % 7; // 計算日期對應的星期
					const isOffDuty = !selectedWeekdays.includes(weekday); // 判斷該天是否為 "不上班"

					const dateCell = document.createElement('td'); // 建立日期單元格
					dateCell.innerHTML = `
				<div>${date}</div> <!-- 顯示日期 -->
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

					// 綁定 "不上班" 勾選事件
					const offDutyCheckbox = dateCell.querySelector('.off-duty-checkbox');
					offDutyCheckbox.addEventListener('change', (event) => handleOffDutyCheckbox(event, dateCell));

					row.appendChild(dateCell); // 將日期單元格加入當前行
				}

				// 填充最後一行的剩餘空白單元格
				while (row.children.length < 7) {
					const emptyCell = document.createElement('td');
					row.appendChild(emptyCell);
				}
				calendarBody.appendChild(row); // 插入最後一行
			}

			// 點擊按鈕切換多選選單的顯示/隱藏
			dropdownButton.addEventListener('click', () => {
				dropdownOptions.style.display = dropdownOptions.style.display === 'block' ? 'none' : 'block';
			});

			// 點擊其他地方時隱藏下拉選單
			document.addEventListener('click', (event) => {
				if (!dropdownButton.contains(event.target) && !dropdownOptions.contains(event.target)) {
					dropdownOptions.style.display = 'none';
				}
			});

			// 當用戶改變選中的工作日時，重新生成日曆
			dropdownOptions.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
			});

			// 當年份改變時，重新生成日曆
			yearSelect.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
			});

			// 當月份改變時，重新生成日曆
			monthSelect.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
			});

			// 初始化年份、月份和日曆
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