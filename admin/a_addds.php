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
// 查詢尚未審核的請假申請數量
$pendingCountResult = $link->query(
	"SELECT COUNT(*) as pending_count 
     FROM leaves 
     WHERE is_approved IS NULL"
);
$pendingCount = $pendingCountResult->fetch_assoc()['pending_count'];

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
		.rd-dropdown-link span {
			background-color: red;
			color: white;
			font-size: 12px;
			border-radius: 50%;
			padding: 2px 6px;
			margin-left: 5px;
			display: inline-block;
		}

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
			width: 200px;
			/* 調整容器寬度，方便設計 */
		}

		/* 按鈕樣式，用於打開下拉選單 */
		.dropdown-button {
			padding: 10px;
			width: 100%;
			/* 讓按鈕適應容器寬度 */
			border: 1px solid #ccc;
			border-radius: 4px;
			/* 圓角設計 */
			cursor: pointer;
			background-color: #fff;
			color: #333;
			text-align: left;
			/* 文字靠左對齊 */
			box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
			/* 添加陰影效果 */
			transition: background-color 0.3s ease, box-shadow 0.3s ease;
			/* 動態效果 */
		}

		/* 懸停時按鈕樣式 */
		.dropdown-button:hover {
			background-color: #f9f9f9;
			box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
			/* 提高陰影效果 */
		}

		/* 確保下拉選單顯示在其他元素上方 */
		.dropdown-options {
			display: none;
			/* 預設隱藏 */
			position: absolute;
			z-index: 1000;
			/* 確保下拉選單在最上層 */
			border: 1px solid #ccc;
			background-color: white;
			max-height: 200px;
			/* 防止內容過長 */
			overflow-y: auto;
			/* 添加垂直滾動條 */
			width: 100%;
			/* 確保與按鈕對齊 */
			box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
		}

		/* 修正按鈕樣式 */
		.dropdown-button {
			position: relative;
			cursor: pointer;
			border: 1px solid #ccc;
			background-color: #fff;
			padding: 8px 12px;
			text-align: left;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
			transition: all 0.2s ease;
		}

		/* 選項樣式 */
		.dropdown-options label {
			display: block;
			padding: 10px;
			font-size: 14px;
			cursor: pointer;
			transition: background-color 0.2s ease;
		}

		.dropdown-options label:hover {
			background-color: #f1f1f1;
		}

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
												href="a_therapist.php">總人數時段表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_addds.php">治療師班表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_treatment.php">新增治療項目</a>
										</li>
										<li class="rd-dropdown-item">
											<a class="rd-dropdown-link" href="a_leave.php">
												請假申請
												<?php if ($pendingCount > 0): ?>
													<span style="
						background-color: red;
						color: white;
						font-size: 12px;
						border-radius: 50%;
						padding: 2px 6px;
						margin-left: 5px;
						display: inline-block;
					">
														<?php echo $pendingCount; ?>
													</span>
												<?php endif; ?>
											</a>
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
					<p class="heading-1 breadcrumbs-custom-title">治療師班表</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="a_index.php">首頁</a></li>
						<li><a href="">關於治療師</a></li>
						<li class="active">治療師班表</li>
					</ul>
				</div>
			</section>
		</div>
		<!-- 表單 -->
		<form id="scheduleForm" method="POST" action="新增班表.php">
			<!-- 選擇治療師 -->
			<label for="the">選擇治療師：</label>
			<select id="the" name="doctor_id">
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


			<!-- 選擇年份 -->
			<label for="year">選擇年份：</label>
			<select id="year"></select>

			<!-- 選擇月份 -->
			<label for="month">選擇月份：</label>
			<select id="month"></select>

			<!-- 選擇範圍 -->
			<label for="range">選擇範圍：</label>
			<select id="range">
				<option value="1-week">一周</option>
				<option value="2-weeks">兩周</option>
				<option value="1-month" selected>一個月</option>
			</select>

			<!-- 工作日選單 -->
			<label for="weekday-select">選擇工作日：</label>
			<div class="dropdown-container">
				<button type="button" id="dropdownButton" class="dropdown-button">選擇工作日</button>
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

			<!-- 日曆表格 -->
			<div style="overflow-x: auto; white-space: nowrap;"><table class="table-custom table-color-header table-custom-bordered">
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
			</table></div>

			<!-- 隱藏的表單數據 -->
			<div id="calendarData"></div>

			<!-- 送出按鈕 -->
			<button type="button" id="submitSchedule" class="custom-green-button">送出</button>
		</form>

		<script>
			const currentDate = new Date();
			const currentYear = currentDate.getFullYear();
			const currentMonth = currentDate.getMonth();

			const yearSelect = document.getElementById('year');
			const monthSelect = document.getElementById('month');
			const rangeSelect = document.getElementById('range');
			const calendarBody = document.getElementById('calendar');
			// 選擇按鈕和選單
			const dropdownButton = document.getElementById('dropdownButton');
			const dropdownOptions = document.getElementById('dropdownOptions');

			// 點擊按鈕顯示/隱藏選單
			dropdownButton.addEventListener('click', (e) => {
				e.stopPropagation(); // 防止事件冒泡影響其他元素
				dropdownOptions.style.display = dropdownOptions.style.display === 'block' ? 'none' : 'block';
			});

			// 點擊選項時，不關閉下拉選單
			dropdownOptions.addEventListener('click', (e) => {
				e.stopPropagation(); // 防止選擇時關閉下拉選單
			});

			// 點擊其他地方時關閉選單
			document.addEventListener('click', () => {
				dropdownOptions.style.display = 'none';
			});


			// 初始化年份選單
			function initYearOptions() {
				for (let year = currentYear - 5; year <= currentYear + 5; year++) {
					const option = document.createElement('option');
					option.value = year;
					option.textContent = year;
					if (year === currentYear) option.selected = true;
					yearSelect.appendChild(option);
				}
			}

			// 初始化月份選單
			function initMonthOptions() {
				for (let month = 0; month < 12; month++) {
					const option = document.createElement('option');
					option.value = month;
					option.textContent = month + 1;
					if (month === currentMonth) option.selected = true;
					monthSelect.appendChild(option);
				}
			}

			// 獲取選中的工作日
			function getSelectedWeekdays() {
				const selectedOptions = Array.from(dropdownOptions.querySelectorAll('input:checked'));
				return selectedOptions.map(option => parseInt(option.value));
			}

			// 確保「選擇工作日」按鈕正確顯示選項
			dropdownButton.addEventListener('click', () => {
				dropdownOptions.classList.toggle('visible'); // 切換選單的顯示/隱藏
			});

			// 動態更新選中的工作日，並刷新日曆
			dropdownOptions.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value), rangeSelect.value);
			});
			/**
 * 根據選擇的年份、月份和範圍生成對應的日曆。
 * @param {number} year - 選擇的年份。
 * @param {number} month - 選擇的月份 (0 表示 1 月，11 表示 12 月)。
 * @param {string} range - 日曆顯示範圍，可選值為 "1-week" (一週)、"2-weeks" (兩週)、"1-month" (整個月)。
 */
			function generateCalendar(year, month, range) {
				// 清空日曆的內容
				calendarBody.innerHTML = '';

				// 定義當前月份的第一天和最後一天
				const firstDayOfMonth = new Date(year, month, 1); // 月份的第一天
				const lastDayOfMonth = new Date(year, month + 1, 0); // 月份的最後一天
				let startDate, endDate;

				// 根據範圍選擇，計算日曆的開始日期和結束日期
				if (range === '1-week') {
					// 一週範圍：從當前日期所在週的週日到週六
					const today = new Date(); // 獲取當前日期
					const currentDay = today.getDay(); // 獲取當前日期是星期幾 (0 表示週日，6 表示週六)
					startDate = new Date(year, month, today.getDate() - currentDay); // 本週的週日
					endDate = new Date(startDate);
					endDate.setDate(startDate.getDate() + 6); // 本週的週六

					// 如果範圍超出了當前月份，調整到當月範圍內
					if (startDate < firstDayOfMonth) startDate = firstDayOfMonth;
					if (endDate > lastDayOfMonth) endDate = lastDayOfMonth;

				} else if (range === '2-weeks') {
					// 兩週範圍：從當前日期所在週的週日開始，顯示兩週
					const today = new Date();
					const currentDay = today.getDay(); // 獲取當前日期是星期幾
					startDate = new Date(year, month, today.getDate() - currentDay); // 本週的週日
					endDate = new Date(startDate);
					endDate.setDate(startDate.getDate() + 13); // 兩週的週六

					// 如果範圍超出了當前月份，調整到當月範圍內
					if (startDate < firstDayOfMonth) startDate = firstDayOfMonth;
					if (endDate > lastDayOfMonth) endDate = lastDayOfMonth;

				} else if (range === '1-month') {
					// 整個月範圍：從該月的第一天到最後一天
					startDate = firstDayOfMonth;
					endDate = lastDayOfMonth;

				} else {
					console.error('未知的範圍選項:', range);
					return;
				}

				// 以起始日期開始生成日曆
				let currentDate = new Date(startDate);
				let row = document.createElement('tr'); // 創建表格的行

				// 在日曆開頭補齊空白單元格，讓第一天對齊對應的星期
				for (let i = 0; i < startDate.getDay(); i++) {
					row.appendChild(document.createElement('td')); // 添加空白單元格
				}

				// 循環生成日曆的每一天
				while (currentDate <= endDate) {
					// 如果行已滿 7 個單元格（即一週），添加到日曆中，並創建新行
					if (row.children.length === 7) {
						calendarBody.appendChild(row);
						row = document.createElement('tr');
					}

					const cell = document.createElement('td'); // 創建單元格
					const dayOfMonth = currentDate.getDate(); // 獲取日期 (1-31)
					const dayOfWeek = currentDate.getDay(); // 獲取星期幾 (0 表示週日)
					const isOffDuty = !getSelectedWeekdays().includes(dayOfWeek); // 是否為非工作日

					// 單元格的 HTML 結構
					cell.innerHTML = `
			<div>${dayOfMonth}</div>
			<div>
				<label>上班：</label>
				<select class="start-hour"></select>
			</div>
			<div>
				<label>下班：</label>
				<select class="end-hour"></select>
			</div>
			<div>
				<label><input type="checkbox" class="off-duty-checkbox" ${isOffDuty ? 'checked' : ''}> 不上班</label>
			</div>
		`;

					const startHourSelect = cell.querySelector('.start-hour'); // 上班時間下拉選單
					const endHourSelect = cell.querySelector('.end-hour'); // 下班時間下拉選單
					const offDutyCheckbox = cell.querySelector('.off-duty-checkbox'); // 「不上班」的勾選框

					// 動態生成時間選項
					generateHourOptions(startHourSelect, "07:00");
					generateHourOptions(endHourSelect, "16:00");

					// 如果是非工作日，禁用時間選擇並勾選「不上班」
					if (isOffDuty) {
						startHourSelect.disabled = true;
						endHourSelect.disabled = true;
						offDutyCheckbox.checked = true;
					}

					// 綁定「不上班」的勾選框切換事件
					offDutyCheckbox.addEventListener('change', function () {
						if (this.checked) {
							startHourSelect.disabled = true;
							endHourSelect.disabled = true;
						} else {
							startHourSelect.disabled = false;
							endHourSelect.disabled = false;
						}
					});

					row.appendChild(cell); // 將單元格添加到當前行

					// 跳到下一天
					currentDate.setDate(currentDate.getDate() + 1);
				}

				// 在日曆末尾補齊空白單元格，讓最後一行對齊
				while (row.children.length < 7) {
					row.appendChild(document.createElement('td'));
				}

				calendarBody.appendChild(row); // 添加最後一行到日曆
			}

			/**
			 * 動態生成整點時間選項。
			 * @param {HTMLElement} selectElement - 下拉選單的 HTML 元素。
			 * @param {string} defaultTime - 預設選中的時間，例如 "07:00"。
			 */
			function generateHourOptions(selectElement, defaultTime) {
				selectElement.innerHTML = ''; // 清空下拉選單的內容
				for (let hour = 0; hour < 24; hour++) {
					const option = document.createElement('option');
					option.value = `${hour.toString().padStart(2, '0')}:00`; // 格式為 HH:MM
					option.textContent = `${hour.toString().padStart(2, '0')}:00`;
					if (`${hour.toString().padStart(2, '0')}:00` === defaultTime) {
						option.selected = true; // 設置預設選中
					}
					selectElement.appendChild(option);
				}
			}



			/**
			 * 獲取選中的工作日。
			 * @returns {number[]} 返回選中的工作日索引數組，例如 [1, 2, 3, 4, 5] 表示週一到週五。
			 */
			function getSelectedWeekdays() {
				const selectedOptions = Array.from(dropdownOptions.querySelectorAll('input:checked'));
				return selectedOptions.map(option => parseInt(option.value));
			}

			// 初始化年份和月份選擇器
			function initYearMonthSelectors() {
				const yearSelect = document.getElementById('year');
				const monthSelect = document.getElementById('month');

				const currentYear = new Date().getFullYear();
				const currentMonth = new Date().getMonth();

				// 初始化年份選項
				for (let year = currentYear - 5; year <= currentYear + 5; year++) {
					const option = document.createElement('option');
					option.value = year;
					option.textContent = year;
					if (year === currentYear) option.selected = true;
					yearSelect.appendChild(option);
				}

				// 初始化月份選項
				for (let month = 0; month < 12; month++) {
					const option = document.createElement('option');
					option.value = month;
					option.textContent = month + 1;
					if (month === currentMonth) option.selected = true;
					monthSelect.appendChild(option);
				}
			}

			// 初始化範圍選擇事件
			document.getElementById('range').addEventListener('change', () => {
				const year = parseInt(document.getElementById('year').value);
				const month = parseInt(document.getElementById('month').value);
				const range = document.getElementById('range').value;
				generateCalendar(year, month, range); // 根據選擇生成日曆
			});

			// 初始化年份和月份選擇器，並默認顯示當前周
			initYearMonthSelectors();
			generateCalendar(new Date().getFullYear(), new Date().getMonth(), '1-week');


			// 動態更新選中的工作日，並刷新日曆
			dropdownOptions.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value), rangeSelect.value);
			});



			document.getElementById('submitSchedule').addEventListener('click', function () {
				const doctorId = document.getElementById('the').value;
				if (!doctorId) {
					alert('請選擇治療師！');
					return;
				}

				const calendarData = document.getElementById('calendarData');
				calendarData.innerHTML = ''; // 清空舊數據

				// 遍歷日曆中的每一天，生成隱藏輸入
				document.querySelectorAll('#calendar td').forEach((cell) => {
					const dateDiv = cell.querySelector('div');
					if (!dateDiv) return; // 跳過空白單元格

					const date = `${yearSelect.value}-${(parseInt(monthSelect.value) + 1).toString().padStart(2, '0')}-${dateDiv.textContent.padStart(2, '0')}`;
					const startHour = cell.querySelector('.start-hour').value;
					const endHour = cell.querySelector('.end-hour').value;
					const offDuty = cell.querySelector('.off-duty-checkbox').checked ? 1 : 0;

					if (offDuty) return; // 不上班時，跳過該日期

					calendarData.innerHTML += `
		<input type="hidden" name="dates[]" value="${date}">
		<input type="hidden" name="go_times[]" value="${startHour}">
		<input type="hidden" name="off_times[]" value="${endHour}">
		`;
				});

				document.getElementById('scheduleForm').submit(); // 提交表單
			});


			// 初始化
			initYearOptions();
			initMonthOptions();
			generateCalendar(currentYear, currentMonth, '1-month');
			rangeSelect.addEventListener('change', () => {
				generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value), rangeSelect.value);
			});
		</script>

		<!-- Global Mailform Output-->
		<div class="snackbars" id="form-output-global"></div>
		<!-- Javascript-->
		<script src="js/core.min.js"></script>
		<script src="js/script.js"></script>

	</div>
</body>

</html>