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

// 治療師班表

$帳號 = $_SESSION['帳號']; // 取得當前登入的帳號

// SQL 查詢：根據登入帳號取得對應的治療師姓名
$query = "
SELECT d.doctor_id, d.doctor 
FROM doctor d 
INNER JOIN user u ON d.user_id = u.user_id 
WHERE u.account = ?
";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "s", $帳號);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
	$doctor_id = $row['doctor_id'];
	$doctor_name = htmlspecialchars($row['doctor']);
} else {
	$doctor_id = '';
	$doctor_name = '未知治療師';
	error_log("未找到治療師資料，帳號: $帳號");
}

// 查詢排班資料（關聯 shifttime 表）
$shift_query = "
SELECT ds.date, st1.shifttime AS start_time, st2.shifttime AS end_time
FROM doctorshift ds
INNER JOIN shifttime st1 ON ds.go = st1.shifttime_id
INNER JOIN shifttime st2 ON ds.off = st2.shifttime_id
WHERE ds.doctor_id = ?
";

// 查詢請假資料（只包含通過的請假資訊）
$leave_query = "
SELECT start_date, end_date, reason
FROM leaves
WHERE doctor_id = ? AND is_approved = 1
";

$shifts = []; // 用於存放排班資料
$leaves = []; // 用於存放請假資料

if (!empty($doctor_id)) {
	// 查詢排班資料
	$stmt_shift = mysqli_prepare($link, $shift_query);
	mysqli_stmt_bind_param($stmt_shift, "i", $doctor_id);
	mysqli_stmt_execute($stmt_shift);
	$shift_result = mysqli_stmt_get_result($stmt_shift);

	while ($shift_row = mysqli_fetch_assoc($shift_result)) {
		$shifts[$shift_row['date']] = [
			'start_time' => $shift_row['start_time'],
			'end_time' => $shift_row['end_time']
		];
	}

	// 查詢請假資料（只顯示審核通過的請假記錄）
	$stmt_leave = mysqli_prepare($link, $leave_query);
	mysqli_stmt_bind_param($stmt_leave, "i", $doctor_id);
	mysqli_stmt_execute($stmt_leave);
	$leave_result = mysqli_stmt_get_result($stmt_leave);

	while ($leave_row = mysqli_fetch_assoc($leave_result)) {
		$start_date = substr($leave_row['start_date'], 0, 10);
		$end_date = substr($leave_row['end_date'], 0, 10);
		$start_time = substr($leave_row['start_date'], 11, 5);
		$end_time = substr($leave_row['end_date'], 11, 5);
		$reason = $leave_row['reason'];

		$current_date = $start_date;
		while ($current_date <= $end_date) {
			$leaves[$current_date] = [
				'reason' => $reason,
				'start_time' => $start_time,
				'end_time' => $end_time
			];
			$current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
		}
	}
}

// 關閉查詢和資料庫連線
mysqli_stmt_close($stmt_shift);
mysqli_stmt_close($stmt_leave);
mysqli_close($link);
?>


<head>
	<!-- Site Title-->
	<title>治療師-班表時段</title>
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

		/* 治療師班表 */
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

	<!--標題列-->
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
								<!--Brand--><a class="brand-name" href="d_index.php"><img class="logo-default"
										src="images/logo-default-172x36.png" alt="" width="86" height="18"
										loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
										alt="" width="86" height="18" loading="lazy" /></a>
							</div>
						</div>
						<div class="rd-navbar-nav-wrap">
							<ul class="rd-navbar-nav">
								<li class="rd-nav-item"><a class="rd-nav-link" href="d_index.php">首頁</a>
								</li>

								<li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="d_people.php">用戶資料</a>
										</li>
									</ul>
								</li>

								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="d_appointment.php">預約</a>
				</li> -->
								<li class="rd-nav-item active"><a class="rd-nav-link" href="#">班表</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="d_doctorshift.php">每月班表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="d_numberpeople.php">當天人數及時段</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="d_leave.php">請假申請</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="d_leave-query.php">請假資料查詢</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="#">紀錄</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="d_medical-record.php">看診紀錄</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
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
						<div class="rd-navbar-collapse-toggle" data-rd-navbar-toggle=".rd-navbar-collapse"><span></span>
						</div>
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
						<a href="#" id="clock-btn">🕒 打卡</a>

						<!-- 打卡彈跳視窗 -->
						<div id="clock-modal" class="modal">
							<div class="modal-content">
								<span class="close">&times;</span>
								<h4>上下班打卡</h4>
								<p id="clock-status">目前狀態: 查詢中...</p>
								<button id="clock-in-btn">上班打卡</button>
								<button id="clock-out-btn" disabled>下班打卡</button>
							</div>
						</div>

						<style>
							.modal {
								display: none;
								position: fixed;
								z-index: 1000;
								left: 0;
								top: 0;
								width: 100%;
								height: 100%;
								background-color: rgba(0, 0, 0, 0.4);
							}

							.modal-content {
								background-color: white;
								margin: 15% auto;
								padding: 20px;
								width: 300px;
								border-radius: 10px;
								text-align: center;
							}

							.close {
								float: right;
								font-size: 24px;
								cursor: pointer;
							}
						</style>

						<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
						<script>
							$(document).ready(function () {
								let doctorId = 1; // 假設目前使用者的 doctor_id

								// 打開彈跳視窗
								$("#clock-btn").click(function () {
									$("#clock-modal").fadeIn();
									checkClockStatus();
								});

								$(".close").click(function () {
									$("#clock-modal").fadeOut();
								});

								function checkClockStatus() {
									$.post("檢查打卡狀態.php", { doctor_id: doctorId }, function (data) {
										let statusText = "尚未打卡";

										if (data.clock_in) {
											statusText = "已上班: " + data.clock_in;
											if (data.late) statusText += " <br>(遲到 " + data.late + ")";
											$("#clock-in-btn").prop("disabled", true);
											$("#clock-out-btn").prop("disabled", data.clock_out !== null);
										}

										if (data.clock_out) {
											statusText += "<br>已下班: " + data.clock_out;
											if (data.work_duration) statusText += "<br>總工時: " + data.work_duration;
										}

										$("#clock-status").html(statusText);
									}, "json").fail(function (xhr) {
										alert("發生錯誤：" + xhr.responseText);
									});
								}


								$("#clock-in-btn").click(function () {
									$.post("上班打卡.php", { doctor_id: doctorId }, function (data) {
										alert(data.message);
										checkClockStatus();
									}, "json").fail(function (xhr) {
										alert("發生錯誤：" + xhr.responseText);
									});
								});

								$("#clock-out-btn").click(function () {
									$.post("下班打卡.php", { doctor_id: doctorId }, function (data) {
										alert(data.message);
										checkClockStatus();
									}, "json").fail(function (xhr) {
										alert("發生錯誤：" + xhr.responseText);
									});
								});
							});

						</script>

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
					<!-- <p class="breadcrumbs-custom-subtitle">What We Offer</p> -->
					<p class="heading-1 breadcrumbs-custom-title">班表時段</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="d_index.php">首頁</a></li>
						<li><a href="#">班表</a></li>
						<li class="active">治療師班表時段</li>
					</ul>
				</div>
			</section>
		</div>

		<!-- 治療師班表 -->
		<section class="section section-lg bg-default novi-bg novi-bg-img">

			<!-- 選擇年份與月份 -->
			<div style="font-size: 18px; font-weight: bold; color: #333; margin-top: 10px; text-align: center;">
				治療師姓名：<?php echo $doctor_name; ?> <br /> <!-- 顯示治療師姓名 -->
				<label for="year">選擇年份：</label> <!-- 年份下拉選單標籤 -->
				<select id="year"></select> <!-- 年份下拉選單 -->
				<label for="month">選擇月份：</label> <!-- 月份下拉選單標籤 -->
				<select id="month"></select> <!-- 月份下拉選單 -->
			</div>

			<!-- 滾動容器 -->
			<div style="overflow-x: auto; white-space: nowrap; margin-top: 20px;">
				<!-- 班表表格 -->
				<table class="table-custom table-color-header table-custom-bordered" style="min-width: 600px;">
					<thead>
						<tr>
							<th>日</th> <!-- 星期日 -->
							<th>一</th> <!-- 星期一 -->
							<th>二</th> <!-- 星期二 -->
							<th>三</th> <!-- 星期三 -->
							<th>四</th> <!-- 星期四 -->
							<th>五</th> <!-- 星期五 -->
							<th>六</th> <!-- 星期六 -->
						</tr>
					</thead>
					<tbody id="calendar"></tbody> <!-- 日曆內容將動態生成 -->
				</table>
			</div>

			<!-- JavaScript 日曆邏輯 -->
			<script>
				// 初始化班表與請假資料，從後端 PHP 傳遞資料
				const shifts = <?php echo json_encode($shifts); ?>; // 班表資料
				const leaves = <?php echo json_encode($leaves); ?>; // 請假資料

				const calendarBody = document.getElementById('calendar'); // 日曆內容容器

				// 生成日曆函數
				function generateCalendar(year, month) {
					calendarBody.innerHTML = ''; // 清空日曆內容

					const firstDay = new Date(year, month, 1).getDay(); // 當月第一天是星期幾
					const lastDate = new Date(year, month + 1, 0).getDate(); // 當月最後一天是幾號
					let row = document.createElement('tr'); // 建立一行

					// 填充月初的空白單元格
					for (let i = 0; i < firstDay; i++) {
						const emptyCell = document.createElement('td');
						row.appendChild(emptyCell); // 插入空白單元格
					}

					// 填充日期
					for (let date = 1; date <= lastDate; date++) {
						const fullDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`; // 格式化日期為 YYYY-MM-DD
						const cell = document.createElement('td'); // 建立日期單元格
						cell.textContent = date; // 設定單元格文字為日期

						// 判斷日期是否有班表或請假
						if (leaves[fullDate] && shifts[fullDate]) {
							// 同時有班表和請假
							const { start_time: shiftStart, end_time: shiftEnd } = shifts[fullDate]; // 取得班表時間
							const { start_time: leaveStart, end_time: leaveEnd } = leaves[fullDate]; // 取得請假時間

							if (shiftStart < leaveStart) {
								// 請假前的上班時間
								const workBeforeLeave = document.createElement('div');
								workBeforeLeave.textContent = `上班: ${shiftStart}~${leaveStart}`;
								workBeforeLeave.style.color = 'green'; // 設定文字顏色為綠色
								cell.appendChild(workBeforeLeave); // 插入上班時間
							}

							// 請假時間
							const leaveInfo = document.createElement('div');
							leaveInfo.textContent = `請假: ${leaveStart}~${leaveEnd}`;
							leaveInfo.style.color = 'blue'; // 設定文字顏色為藍色
							cell.appendChild(leaveInfo); // 插入請假時間

							if (leaveEnd < shiftEnd) {
								// 請假後的上班時間
								const workAfterLeave = document.createElement('div');
								workAfterLeave.textContent = `上班: ${leaveEnd}~${shiftEnd}`;
								workAfterLeave.style.color = 'green'; // 設定文字顏色為綠色
								cell.appendChild(workAfterLeave); // 插入上班時間
							}
						} else if (shifts[fullDate]) {
							// 只有班表
							const { start_time, end_time } = shifts[fullDate]; // 取得班表時間
							const shiftInfo = document.createElement('div');
							shiftInfo.textContent = `上班: ${start_time}~${end_time}`;
							shiftInfo.style.color = 'green'; // 設定文字顏色為綠色
							cell.appendChild(shiftInfo); // 插入班表
						} else if (leaves[fullDate]) {
							// 只有請假
							const { start_time, end_time } = leaves[fullDate]; // 取得請假時間
							const leaveInfo = document.createElement('div');
							leaveInfo.textContent = `請假: ${start_time}~${end_time}`;
							leaveInfo.style.color = 'blue'; // 設定文字顏色為藍色
							cell.appendChild(leaveInfo); // 插入請假
						} else {
							// 無班表也無請假，顯示休假
							const noShiftInfo = document.createElement('div');
							noShiftInfo.textContent = `休假`; // 顯示「休假」
							noShiftInfo.style.color = 'red'; // 設定文字顏色為紅色
							cell.appendChild(noShiftInfo); // 插入休假
						}

						row.appendChild(cell); // 插入單元格到行中

						if (row.children.length === 7) {
							// 當行滿 7 個單元格時，插入到日曆表格中
							calendarBody.appendChild(row);
							row = document.createElement('tr'); // 建立新行
						}
					}

					// 填充月末的空白單元格
					while (row.children.length < 7) {
						const emptyCell = document.createElement('td');
						row.appendChild(emptyCell); // 插入空白單元格
					}
					calendarBody.appendChild(row); // 插入最後一行
				}

				// 初始化年份與月份的選擇功能
				function initYearMonth() {
					const currentDate = new Date(); // 取得當前日期
					const currentYear = currentDate.getFullYear(); // 當前年份
					const currentMonth = currentDate.getMonth(); // 當前月份

					const yearSelect = document.getElementById('year'); // 年份下拉選單
					const monthSelect = document.getElementById('month'); // 月份下拉選單

					// 填充年份選單
					for (let i = currentYear - 5; i <= currentYear + 5; i++) {
						const option = document.createElement('option');
						option.value = i; // 設定選項值為年份
						option.textContent = i; // 設定選項文字為年份
						if (i === currentYear) option.selected = true; // 預設當前年份為選中
						yearSelect.appendChild(option); // 插入年份選項
					}

					// 填充月份選單
					for (let i = 0; i < 12; i++) {
						const option = document.createElement('option');
						option.value = i; // 設定選項值為月份
						option.textContent = i + 1; // 設定選項文字為月份（1~12）
						if (i === currentMonth) option.selected = true; // 預設當前月份為選中
						monthSelect.appendChild(option); // 插入月份選項
					}

					// 當年份或月份改變時，重新生成日曆
					yearSelect.addEventListener('change', () => {
						generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
					});
					monthSelect.addEventListener('change', () => {
						generateCalendar(parseInt(yearSelect.value), parseInt(monthSelect.value));
					});

					generateCalendar(currentYear, currentMonth); // 預設生成當前年月的日曆
				}

				initYearMonth(); // 初始化年份與月份選擇
			</script>
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
</body>

</html>