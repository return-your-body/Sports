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

	// 關閉資料庫連接
	mysqli_close($link);
} else {
	echo "<script>
            alert('會話過期或資料遺失，請重新登入。');
            window.location.href = '../index.html';
          </script>";
	exit();
}


//預約

?>

<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
	<!-- Site Title-->
	<title>助手-預約</title>
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
		/* 禁用按鈕樣式 */
		button.disabled {
			background-color: #ccc;
			/* 灰色背景 */
			color: #666;
			/* 灰色文字 */
			border: 1px solid #999;
			/* 灰色邊框 */
			cursor: not-allowed;
			/* 禁用鼠標樣式 */
			pointer-events: none;
			/* 禁止所有事件 */
		}

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

		/* 基本樣式調整 */
		.table-custom {
			width: 100%;
			border-collapse: collapse;
			table-layout: fixed;
		}

		.table-custom th,
		.table-custom td {
			border: 1px solid #ddd;
			text-align: center;
			vertical-align: middle;
			padding: 10px;
			word-wrap: break-word;
			/* 避免文字超出格子 */
		}

		/* 手機設備響應式設計 */
		@media (max-width: 768px) {
			.table-custom {
				display: block;
				/* 將表格改為 block，允許水平滾動 */
				overflow-x: auto;
				/* 加入水平滾動 */
				white-space: nowrap;
				/* 禁止自動換行 */
			}

			.table-custom th,
			.table-custom td {
				font-size: 12px;
				/* 縮小文字大小 */
				padding: 5px;
				/* 減小內邊距 */
			}

			.shift-info button {
				font-size: 10px;
				/* 縮小按鈕文字 */
				padding: 5px;
				/* 減小按鈕大小 */
			}
		}

		/* 極小設備 (手機) */
		@media (max-width: 480px) {

			.table-custom th,
			.table-custom td {
				font-size: 10px;
				padding: 3px;
			}

			.shift-info {
				display: block;
				font-size: 10px;
			}

			.shift-info button {
				font-size: 9px;
				padding: 3px;
			}
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

		/* 治療師班表 */
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
								<!--Brand--><a class="brand-name" href="h_index.php"><img class="logo-default"
										src="images/logo-default-172x36.png" alt="" width="86" height="18"
										loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
										alt="" width="86" height="18" loading="lazy" /></a>
							</div>
						</div>
						<div class="rd-navbar-nav-wrap">
							<ul class="rd-navbar-nav">
								<li class="rd-nav-item"><a class="rd-nav-link" href="h_index.php">首頁</a>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_people.php">用戶資料</a>
										</li>
									</ul>
								</li>
								<!-- <li class="rd-nav-item active"><a class="rd-nav-link" href="h_appointment.php">預約</a>
				</li> -->
								<li class="rd-nav-item"><a class="rd-nav-link" href="#">治療師班表</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_doctorshift.php">治療師班表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_numberpeople.php">當天人數及時段</a>
										</li>
									</ul>
								</li>

								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="#">列印</a>
				  <ul class="rd-menu rd-navbar-dropdown">
					<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_print-receipt.php">列印收據</a>
					</li>
					<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="h_print-appointment.php">列印預約單</a>
					</li>
				  </ul>
				</li> -->
								<li class="rd-nav-item"><a class="rd-nav-link" href="#">紀錄</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_medical-record.php">看診紀錄</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_appointment-records.php">預約紀錄</a>
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
					<!-- <p class="breadcrumbs-custom-subtitle">Our team</p> -->
					<p class="heading-1 breadcrumbs-custom-title">預約</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="h_index.php">首頁</a></li>
						<li class="active">預約</li>
					</ul>
				</div>
			</section>
		</div>
		<!--標題-->

		<?php
		include "../db.php";
		if (isset($_GET['id'])) {
			$_SESSION['people_id'] = $_GET['id']; // 將 people_id 存入 Session
		}
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
		$query_leaves = "SELECT l.doctor_id, l.start_date, l.end_date FROM leaves l WHERE l.is_approved = 1";

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

				<div style="overflow-x: auto; max-width: 100%;">
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

			</div>
		</section>

		<script>
			const data = <?php echo json_encode(['schedule' => $schedule, 'leaves' => $leaves, 'appointments' => $appointments], JSON_UNESCAPED_UNICODE); ?>;
			const calendarData = data.schedule; // 排班數據
			const leaveData = data.leaves; // 請假數據
			const appointmentData = data.appointments; // 已預約時段
			const now = new Date(); // 目前時間

			console.log("Calendar Data:", calendarData);
			console.log("Leave Data:", leaveData);
			console.log("Appointment Data:", appointmentData);

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

					const currentDate = new Date(year, month, date);

					if (calendarData[fullDate]) {
						calendarData[fullDate].forEach(shift => {
							const goTime = shift.go_time;
							const offTime = shift.off_time;
							const shiftStart = new Date(`${fullDate}T${goTime}`);
							const shiftEnd = new Date(`${fullDate}T${offTime}`);

							// **修正條件：今天的話，允許當前時間之後的時段預約**
							if (currentDate > now || (currentDate.toDateString() === now.toDateString() && shiftEnd > now)) {
								const shiftDiv = document.createElement('div');
								shiftDiv.textContent = `${shift.doctor}: ${goTime} - ${offTime}`;

								// 檢查請假
								if (isDoctorOnLeave(shift.doctor_id, fullDate)) {
									shiftDiv.textContent = `${shift.doctor}: 請假`;
									shiftDiv.style.color = 'red';
								} else {
									const reserveButton = document.createElement('button');
									reserveButton.textContent = '查看';
									reserveButton.style.marginLeft = '10px';

									reserveButton.onclick = () => {
										openModal(shift.doctor, fullDate, goTime, offTime);
									};

									shiftDiv.appendChild(reserveButton);
								}
								shiftDiv.className = 'shift-info';
								cell.appendChild(shiftDiv);
							}
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
			 * 檢查該醫生是否請假
			 */
			function isDoctorOnLeave(doctorId, date) {
				return leaveData.some(leave =>
					leave.doctor_id === doctorId &&
					new Date(leave.start_date) <= new Date(`${date}T23:59:59`) &&
					new Date(leave.end_date) >= new Date(`${date}T00:00:00`)
				);
			}

			/**
			 * 初始化年份與月份選單
			 */
			function initSelectOptions() {
				const yearSelect = document.getElementById('year');
				const monthSelect = document.getElementById('month');

				for (let year = now.getFullYear(); year <= now.getFullYear() + 1; year++) {
					const option = document.createElement('option');
					option.value = year;
					option.textContent = year;
					if (year === now.getFullYear()) option.selected = true;
					yearSelect.appendChild(option);
				}

				for (let month = 1; month <= 12; month++) {
					const option = document.createElement('option');
					option.value = month;
					option.textContent = month;
					if (month === now.getMonth() + 1) option.selected = true;
					monthSelect.appendChild(option);
				}

				generateCalendar();
			}

			document.getElementById('year').addEventListener('change', generateCalendar);
			document.getElementById('month').addEventListener('change', generateCalendar);

			initSelectOptions();

		</script>

		<!-- 預約時間段的主彈窗 -->
		<div id="appointment-modal" class="modal">
			<div class="modal-content">
				<span class="close">&times;</span>
				<h3 id="modal-title"></h3>
				<div id="time-slots"></div>
			</div>
		</div>

		<style>
			/* 通用的彈窗背景 */
			.modal {
				display: none;
				/* 預設隱藏 */
				position: fixed;
				/* 固定位置 */
				z-index: 1000;
				/* 顯示在最上層 */
				left: 0;
				top: 0;
				width: 100%;
				/* 占滿螢幕寬度 */
				height: 100%;
				/* 占滿螢幕高度 */
				overflow: auto;
				/* 當內容過多時允許滾動 */
				background-color: rgba(0, 0, 0, 0.4);
				/* 半透明背景 */
			}

			/* 備註彈窗內容區域 */
			.note-modal-content {
				background-color: #fff;
				/* 背景顏色 */
				margin: 10% auto;
				/* 垂直置中，上下距離10%，水平居中 */
				padding: 20px;
				/* 內邊距 */
				border-radius: 10px;
				/* 圓角邊框 */
				width: 40%;
				/* 寬度調整為40% */
				box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
				/* 添加陰影效果 */
				animation: fadeIn 0.3s ease-in-out;
				/* 彈窗淡入動畫 */
			}

			/* 關閉按鈕 */
			.close-note {
				float: right;
				/* 靠右對齊 */
				font-size: 20px;
				/* 字體大小 */
				font-weight: bold;
				/* 加粗字體 */
				color: #aaa;
				/* 字體顏色 */
				cursor: pointer;
				/* 鼠標變為指針 */
				transition: color 0.3s;
				/* 顏色變化過渡 */
			}

			.close-note:hover {
				color: #000;
				/* 懸停時變為黑色 */
			}

			/* 標題樣式 */
			.note-modal-content h3 {
				font-size: 22px;
				/* 標題字體大小 */
				font-weight: bold;
				/* 加粗 */
				text-align: center;
				/* 置中對齊 */
				color: #333;
				/* 深灰色文字 */
				margin-bottom: 20px;
				/* 與內容間距 */
			}

			/* 備註輸入框 */
			.note-modal-content textarea {
				width: 100%;
				/* 全寬度 */
				height: 100px;
				/* 高度調整 */
				font-size: 16px;
				/* 字體大小 */
				padding: 10px;
				/* 內邊距 */
				border: 1px solid #ddd;
				/* 灰色邊框 */
				border-radius: 5px;
				/* 圓角 */
				box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
				/* 內陰影 */
				resize: none;
				/* 禁止調整大小 */
			}

			/* 提交按鈕 */
			.note-modal-content button {
				display: block;
				/* 占滿一行 */
				margin: 20px auto 0;
				/* 垂直置中 */
				padding: 10px 20px;
				/* 按鈕內邊距 */
				background-color: #008CBA;
				/* 按鈕背景顏色 */
				color: #fff;
				/* 按鈕文字顏色 */
				font-size: 16px;
				/* 字體大小 */
				font-weight: bold;
				/* 字體加粗 */
				border: none;
				/* 無邊框 */
				border-radius: 5px;
				/* 圓角邊框 */
				cursor: pointer;
				/* 鼠標變為指針 */
				transition: background-color 0.3s ease;
				/* 背景色過渡效果 */
			}

			.note-modal-content button:hover {
				background-color: #005f7f;
				/* 懸停時顏色變深 */
			}

			/* 彈窗淡入動畫 */
			@keyframes fadeIn {
				from {
					opacity: 0;
				}

				to {
					opacity: 1;
				}
			}
		</style>


		<!-- 備註輸入的獨立彈窗 -->
		<div id="note-modal" class="modal">
			<div class="modal-content note-modal-content">
				<span class="close-note">&times;</span>
				<h3>填寫備註</h3>
				<textarea id="note" rows="5" cols="40" placeholder="請輸入備註內容"></textarea>
				<button id="submit-note">送出</button>
			</div>
		</div>




		<style>
			/* 彈窗樣式調整 */
			.modal-content {
				background-color: white;
				margin: 10% auto;
				/* 距離頂部 10%，水平居中 */
				padding: 20px;
				border-radius: 10px;
				/* 圓角邊框 */
				width: 60%;
				/* 寬度調整為 60% */
				box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
				/* 添加陰影效果 */
			}

			/* 標題樣式調整 (治療師和日期) */
			#modal-title {
				font-size: 18px;
				/* 字體大小調整為 18px */
				font-weight: normal;
				/* 調整為普通字重 */
				color: #333;
				/* 深灰色文字 */
				text-align: center;
				/* 文字置中 */
				margin-bottom: 20px;
				/* 與時間段的距離 */
			}

			/* 時間段的容器，使用 grid 佈局 */
			#time-slots {
				display: grid;
				grid-template-columns: repeat(6, 1fr);
				/* 每行 6 列 */
				gap: 15px;
				/* 時間段之間的間距 */
				justify-items: center;
				/* 內容水平置中 */
				align-items: center;
				/* 垂直置中 */
				margin-top: 20px;
				/* 與標題的間距 */
			}

			/* 單個時間段卡片樣式 */
			#time-slots div {
				display: flex;
				flex-direction: column;
				/* 時間和按鈕上下排列 */
				align-items: center;
				/* 水平置中 */
				padding: 10px;
				/* 內邊距 */
				border: 1px solid #ddd;
				/* 邊框 */
				border-radius: 5px;
				/* 圓角 */
				background-color: #f9f9f9;
				/* 背景色 */
				width: 80px;
				/* 固定寬度 */
				box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
				/* 添加陰影 */
			}

			/* 調整時間字體樣式 */
			#time-slots span {
				font-size: 14px;
				/* 時間字體大小 */
				color: #333;
				/* 深灰色文字 */
			}

			/* 按鈕樣式調整 */
			.reserve-btn {
				background-color: #008CBA;
				/* 按鈕背景色 */
				color: white;
				/* 按鈕文字顏色 */
				border: none;
				/* 無邊框 */
				padding: 5px 10px;
				/* 按鈕內邊距 */
				font-size: 12px;
				/* 字體大小 */
				cursor: pointer;
				/* 鼠標變為指針 */
				border-radius: 3px;
				/* 圓角邊框 */
				margin-top: 5px;
				/* 與時間的間距 */
			}

			.reserve-btn:hover {
				background-color: #005f7f;
				/* 按鈕懸停時的背景色 */
			}

			.reserve-btn:active {
				background-color: #003f5c;
				/* 按鈕點擊時的背景色 */
			}

			/* 手機樣式：每行 3 個時間段 */
			@media (max-width: 768px) {
				.modal-content {
					width: 90%;
					/* 手機螢幕時彈窗寬度調整為 90% */
				}

				#time-slots {
					grid-template-columns: repeat(3, 1fr);
					/* 每行 3 列 */
					gap: 10px;
					/* 時間段之間的間距調小 */
				}

				#time-slots div {
					width: 70px;
					/* 手機版每個時間卡片寬度縮小 */
				}

				#modal-title {
					font-size: 16px;
					/* 手機版字體再稍微縮小 */
				}
			}
		</style>


		<script>
			// 取得兩個彈窗的相關元素
			const modal = document.getElementById("appointment-modal"); // 主彈窗
			const noteModal = document.getElementById("note-modal"); // 備註彈窗
			const modalTitle = document.getElementById("modal-title"); // 主彈窗標題
			const timeSlotsContainer = document.getElementById("time-slots"); // 時間段按鈕容器
			const noteTextarea = document.getElementById("note"); // 備註輸入框
			const submitNoteButton = document.getElementById("submit-note"); // 備註送出按鈕

			// 全局變數，用於記錄當前預約的資訊
			let currentReservation = {};

			/**
				* 打開彈窗，顯示時間段
				* @param {string} doctor - 治療師名稱
				* @param {string} date - 預約日期 (YYYY-MM-DD)
				* @param {string} startTime - 上班時間 (HH:mm 格式)
				* @param {string} endTime - 下班時間 (HH:mm 格式)
				*/
			function openModal(doctor, date, startTime, endTime) {
				modalTitle.textContent = `治療師：${doctor} 日期：${date}`;
				timeSlotsContainer.innerHTML = "";

				let current = new Date(`${date}T${startTime}`);
				const end = new Date(`${date}T${endTime}`);

				while (current < end) {
					const timeSlot = current.toTimeString().slice(0, 5);
					const slotElement = document.createElement("div");
					slotElement.innerHTML = `
			<span>${timeSlot}</span>
			<button class="reserve-btn" disabled>檢查中...</button>
		`;

					const button = slotElement.querySelector(".reserve-btn");
					// 檢查該時間段的可用性
					checkAvailability(doctor, date, timeSlot, button);

					timeSlotsContainer.appendChild(slotElement);

					current = new Date(current.getTime() + 20 * 60000);
				}

				modal.style.display = "block";
			}


			// 打開備註彈窗
			function openNoteModal(doctor, date, time) {
				currentReservation = { doctor, date, time }; // 記錄當前預約資訊
				noteModal.style.display = "block"; // 顯示備註彈窗
			}

			// 關閉主彈窗
			document.querySelector(".close").onclick = () => {
				modal.style.display = "none";
			};

			// 關閉備註彈窗
			document.querySelector(".close-note").onclick = () => {
				noteModal.style.display = "none";
			};

			// 當用戶點擊備註彈窗的送出按鈕時
			submitNoteButton.onclick = () => {
				const note = noteTextarea.value; // 獲取用戶輸入的備註
				reserve(currentReservation.doctor, currentReservation.date, currentReservation.time, note); // 呼叫預約函數
				noteModal.style.display = "none"; // 關閉備註彈窗
				noteTextarea.value = ""; // 清空備註輸入框
			};

			// 關閉彈窗功能：點擊彈窗外部關閉
			window.onclick = (event) => {
				if (event.target === modal) {
					modal.style.display = "none";
				} else if (event.target === noteModal) {
					noteModal.style.display = "none";
				}
			};

			/**
			 * 檢查某個時間段是否已被預約
			 * @param {string} doctor - 治療師名稱
			 * @param {string} date - 預約日期
			 * @param {string} timeSlot - 時間段 (格式: HH:mm)
			 * @param {HTMLElement} button - 預約按鈕
			 */
			function checkAvailability(doctor, date, timeSlot, button) {
				const xhr = new XMLHttpRequest();
				xhr.open("POST", "檢查預約.php", true);
				xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

				xhr.onreadystatechange = function () {
					if (xhr.readyState === 4 && xhr.status === 200) {
						const response = JSON.parse(xhr.responseText);

						if (response.available) {
							// 如果時段可用，啟用按鈕並設置為「預約」
							button.textContent = "預約";
							button.disabled = false;
							button.onclick = () => openNoteModal(doctor, date, timeSlot); // 點擊時開啟備註彈窗
						} else {
							// 如果時段已滿，設置為「額滿」並禁用按鈕
							button.textContent = "額滿";
							button.disabled = true;
							button.classList.add("disabled");
							button.style.cursor = "not-allowed";
						}
					}
				};

				const params = `doctor=${encodeURIComponent(doctor)}&date=${date}&time=${timeSlot}`;
				xhr.send(params);
			}




			// 關閉彈窗功能
			document.querySelector(".close").onclick = () => {
				modal.style.display = "none"; // 隱藏彈窗
			};

			// 當用戶點擊彈窗外部時關閉彈窗
			window.onclick = (event) => {
				if (event.target === modal) {
					modal.style.display = "none";
				}
			};


			/**
			* 預約功能，將治療師、日期、時間和備註資訊發送到伺服器進行處理
			* @param {string} doctor - 治療師名稱
			* @param {string} date - 預約日期 (格式: YYYY-MM-DD)
			* @param {string} time - 預約時間 (格式: HH:mm)
			*/
			function reserve(doctor, date, time) {
				// 獲取備註內容
				const note = document.getElementById("note").value;

				// 彈出確認提示，讓使用者確認是否要進行該預約
				if (confirm(`確定要預約？\n治療師：${doctor}\n日期：${date}\n時間：${time}\n備註：${note}`)) {
					// 創建一個新的 AJAX 請求對象
					const xhr = new XMLHttpRequest();

					// 設置 HTTP 請求方法為 POST，目標 URL 為 預約.php，並將其設置為非同步請求
					xhr.open("POST", "預約.php", true);

					// 設置請求標頭，告訴伺服器資料的傳遞格式為 URL 編碼形式
					xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

					// 定義請求完成後要執行的回調函數
					xhr.onreadystatechange = function () {
						// 檢查請求是否已完成 (readyState === 4) 並且伺服器已成功回應 (status === 200)
						if (xhr.readyState === 4 && xhr.status === 200) {
							const response = JSON.parse(xhr.responseText);

							// 根據伺服器回應處理
							if (response.success) {
								alert(response.message); // 顯示成功訊息
								modal.style.display = "none"; // 關閉彈窗
							} else {
								alert(response.message); // 顯示錯誤訊息
							}
						}
					};

					// 將要傳遞的參數組合成 URL 編碼字串
					const params = `doctor=${encodeURIComponent(doctor)}&date=${date}&time=${time}&note=${encodeURIComponent(note)}`;

					// 發送請求到伺服器，並將參數作為請求的主體內容
					xhr.send(params);
				}
			}



			// 修改生成日曆時的查看按鈕功能
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

					if (calendarData[fullDate]) {
						calendarData[fullDate].forEach(shift => {
							const adjustedShift = adjustShiftTime(shift.doctor_id, fullDate, shift.go_time, shift.off_time);

							if (adjustedShift) {
								const button = document.createElement('button');
								button.textContent = '查看';
								button.onclick = () => openModal(shift.doctor, fullDate, adjustedShift.go_time, adjustedShift.off_time);
								cell.appendChild(button);
							} else {
								const noSchedule = document.createElement('div');
								noSchedule.textContent = `${shift.doctor}: 請假`;
								noSchedule.style.color = 'red';
								cell.appendChild(noSchedule);
							}
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
		</script>




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
							<li><a href="h_index.php">首頁</a></li>
							<li><a href="h_people.php">用戶資料</a></li>
							<!-- <li><a href="h_appointment.php">預約</a></li> -->
							<li><a href="h_numberpeople.php">當天人數及時段</a></li>
							<li><a href="h_doctorshift.php">班表時段</a></li>
							<!-- <li><a href="h_print-receipt.php">列印收據</a></li>
			  <li><a href="h_print-appointment.php">列印預約單</a></li> -->

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