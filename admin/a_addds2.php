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
	<title>運動筋膜放鬆-治療師班表</title>
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
								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="a_index.php">網頁編輯</a> -->
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
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_doctorlistadd.php">新增治療師資料</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_doctorlistmod.php">修改治療師資料</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="a_igadd.php">新增哀居貼文</a>
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
				
				// 查詢等級為「治療師」的資料
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
			<a href="a_addds.php">查看班表</a>
			<!-- 日曆表格 -->
			<div style="overflow-x: auto; white-space: nowrap;">
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
			</div>

			<!-- 隱藏的表單數據 -->
			<div id="calendarData"></div>

			<!-- 送出按鈕 -->
			<button type="button" id="submitSchedule" class="custom-green-button">送出</button>
		</form>

		<?php
		include '../db.php'; // 連線資料庫
		
		// 取得已填寫的班表資料
		$existingShifts = [];
		$query = "SELECT doctor_id, date FROM doctorshift";
		$result = mysqli_query($link, $query);

		while ($row = mysqli_fetch_assoc($result)) {
			$existingShifts[$row['doctor_id']][$row['date']] = true;
		}

		?>

		<script>
			document.addEventListener("DOMContentLoaded", function () {
				const yearSelect = document.getElementById("year");
				const monthSelect = document.getElementById("month");
				const rangeSelect = document.getElementById("range");
				const calendarBody = document.getElementById("calendar");
				const doctorSelect = document.getElementById("the");
				const dropdownButton = document.getElementById("dropdownButton");
				const dropdownOptions = document.getElementById("dropdownOptions");

				const currentYear = new Date().getFullYear();
				const currentMonth = new Date().getMonth() + 1;

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

						const dateText = dateDiv.textContent.trim(); // 獲取日期數字
						const additionalText = cell.innerText.trim(); // 獲取單元格內的所有文字
						const date = `${yearSelect.value}-${(parseInt(monthSelect.value)).toString().padStart(2, '0')}-${dateText.padStart(2, '0')}`;

						// 檢查是否有「已填寫」的文字
						if (additionalText.includes('已填寫')) return; // 跳過已填寫的日期

						const startHourElem = cell.querySelector('.start-hour');
						const endHourElem = cell.querySelector('.end-hour');
						const offDutyElem = cell.querySelector('.off-duty-checkbox');

						if (!startHourElem || !endHourElem || !offDutyElem) return; // 確保元素存在

						const startHour = startHourElem.value;
						const endHour = endHourElem.value;
						const offDuty = offDutyElem.checked ? 1 : 0;

						if (offDuty) return; // 不上班時，跳過該日期

						calendarData.innerHTML += `
			<input type="hidden" name="dates[]" value="${date}">
			<input type="hidden" name="go_times[]" value="${startHour}">
			<input type="hidden" name="off_times[]" value="${endHour}">
		`;
					});

					document.getElementById('scheduleForm').submit(); // 提交表單
				});


				// 初始化年份選單
				function initYearOptions() {
					yearSelect.innerHTML = "";
					for (let year = currentYear - 5; year <= currentYear + 5; year++) {
						const option = document.createElement("option");
						option.value = year;
						option.textContent = year;
						if (year === currentYear) option.selected = true;
						yearSelect.appendChild(option);
					}
				}

				// 初始化月份選單
				function initMonthOptions() {
					monthSelect.innerHTML = "";
					for (let month = 1; month <= 12; month++) {
						const option = document.createElement("option");
						option.value = month;
						option.textContent = month;
						if (month === currentMonth) option.selected = true;
						monthSelect.appendChild(option);
					}
				}

				// 「選擇工作日」按鈕事件
				dropdownButton.addEventListener("click", function (e) {
					e.stopPropagation();
					dropdownOptions.style.display = dropdownOptions.style.display === "block" ? "none" : "block";
				});

				document.addEventListener("click", function () {
					dropdownOptions.style.display = "none";
				});

				dropdownOptions.addEventListener("click", function (e) {
					e.stopPropagation();
				});

				function getSelectedWeekdays() {
					return Array.from(dropdownOptions.querySelectorAll("input:checked"))
						.map(option => parseInt(option.value));
				}

				// 取得已填寫的班表
				const existingShifts = <?php echo json_encode($existingShifts); ?>;

				function generateCalendar(year, month) {
					calendarBody.innerHTML = "";
					const jsMonth = month - 1;
					const firstDayOfMonth = new Date(year, jsMonth, 1);
					const lastDayOfMonth = new Date(year, jsMonth + 1, 0);
					let currentDate = new Date(firstDayOfMonth);
					let row = document.createElement("tr");

					for (let i = 0; i < firstDayOfMonth.getDay(); i++) {
						row.appendChild(document.createElement("td"));
					}

					const selectedDoctorId = doctorSelect.value;

					while (currentDate <= lastDayOfMonth) {
						if (row.children.length === 7) {
							calendarBody.appendChild(row);
							row = document.createElement("tr");
						}

						const cell = document.createElement("td");
						const dateStr = `${year}-${(month).toString().padStart(2, '0')}-${currentDate.getDate().toString().padStart(2, '0')}`;
						const dayOfWeek = currentDate.getDay();
						const isOffDuty = !getSelectedWeekdays().includes(dayOfWeek);

						if (existingShifts[selectedDoctorId] && existingShifts[selectedDoctorId][dateStr]) {
							cell.innerHTML = `<div>${currentDate.getDate()}</div><div>已填寫</div>`;
							cell.style.backgroundColor = "#e0e0e0";
						} else {
							cell.innerHTML = `
					<div>${currentDate.getDate()}</div>
					<div><label>上班：</label><select class="start-hour"></select></div>
					<div><label>下班：</label><select class="end-hour"></select></div>
					<div><label><input type="checkbox" class="off-duty-checkbox" ${isOffDuty ? "checked" : ""}> 不上班</label></div>
				`;

							const startHourSelect = cell.querySelector(".start-hour");
							const endHourSelect = cell.querySelector(".end-hour");
							const offDutyCheckbox = cell.querySelector(".off-duty-checkbox");

							generateHourOptions(startHourSelect, "07:00");
							generateHourOptions(endHourSelect, "16:00");

							if (isOffDuty) {
								startHourSelect.disabled = true;
								endHourSelect.disabled = true;
							}

							offDutyCheckbox.addEventListener("change", function () {
								startHourSelect.disabled = this.checked;
								endHourSelect.disabled = this.checked;
							});
						}

						row.appendChild(cell);
						currentDate.setDate(currentDate.getDate() + 1);
					}

					while (row.children.length < 7) {
						row.appendChild(document.createElement("td"));
					}

					calendarBody.appendChild(row);
				}

				function generateHourOptions(selectElement, defaultTime) {
					selectElement.innerHTML = "";
					for (let hour = 7; hour < 24; hour++) {
						const timeString = `${hour.toString().padStart(2, "0")}:00`;
						const option = document.createElement("option");
						option.value = timeString;
						option.textContent = timeString;
						if (timeString === defaultTime) option.selected = true;
						selectElement.appendChild(option);
					}
				}

				function updateCalendar() {
					const selectedYear = parseInt(yearSelect.value);
					const selectedMonth = parseInt(monthSelect.value);
					generateCalendar(selectedYear, selectedMonth);
				}



				yearSelect.addEventListener("change", updateCalendar);
				monthSelect.addEventListener("change", updateCalendar);
				doctorSelect.addEventListener("change", updateCalendar);
				rangeSelect.addEventListener("change", updateCalendar);
				dropdownOptions.addEventListener("change", updateCalendar);

				initYearOptions();
				initMonthOptions();
				updateCalendar();
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