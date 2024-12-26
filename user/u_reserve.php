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

// 檢查 "帳號" 和 "姓名" 是否存在於 $_SESSION 中
if (isset($_SESSION["帳號"])) {
	// 獲取用戶帳號和姓名
	$帳號 = $_SESSION['帳號'];
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
								<li class="rd-nav-item"><a class="rd-nav-link" href="u_index.php">主頁</a>
								</li>

								<li class="rd-nav-item"><a class="rd-nav-link" href="">關於我們</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">醫生介紹</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">個案分享</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">日常小知識</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item active"><a class="rd-nav-link" href="#">預約</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_reserve.php">立即預約</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">查看預約資料</a>
											<!-- 修改預約 -->
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">查看預約時段</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="">歷史紀錄</a>
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
						<div class="rd-navbar-aside-right rd-navbar-collapse">
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
						</div>
					</div>
				</nav>
			</div>
		</header>

		<style>
			body {
				font-family: Arial, sans-serif;
				margin: 20px;
			}

			table {
				width: 60%;
				border-collapse: collapse;
			}

			td {
				padding: 8px;
			}

			label {
				margin-right: 10px;
			}

			input,
			select,
			textarea,
			button {
				width: 100%;
				box-sizing: border-box;
				padding: 5px;
			}

			button {
				background-color: #4CAF50;
				color: white;
				border: none;
				padding: 10px;
				cursor: pointer;
			}

			button:hover {
				background-color: #45a049;
			}
		</style>
		<?php
		require '../db.php';
		?>
		<!-- <h2>資訊填寫表格</h2> -->
		<form id="form" onsubmit="return validateForm()">
			<table border="1">
				<!-- 姓名輸入 -->
				<tr>
					<td><label for="name">姓名：</label></td>
					<!-- 輸入框，限制為必填 -->
					<td><input type="text" id="name" name="name" placeholder="請輸入姓名" required></td>
				</tr>
				<!-- 性別選擇 -->
				<tr>
					<td>性別：</td>
					<td>
						<!-- 單選按鈕，限制為必選 -->
						<label><input type="radio" name="gender" value="male" required>男</label>
						<label><input type="radio" name="gender" value="female" required>女</label>
					</td>
				</tr>
				<!-- 出生年月日輸入 -->
				<tr>
					<td><label for="birthDate">出生年月日：</label></td>
					<!-- 日期選擇器，限制只能選今天以前的日期 -->
					<td><input type="date" id="birthDate" name="birthDate" max="" required></td>
				</tr>
				<!-- 身分證字號輸入 -->
				<tr>
					<td><label for="idNumber">身分證字號：</label></td>
					<!-- 輸入框，限制為必填 -->
					<td><input type="text" id="idNumber" name="idNumber" placeholder="請輸入身分證字號" required></td>
				</tr>
				<!-- 電話號碼輸入 -->
				<tr>
					<td><label for="phone">電話：</label></td>
					<!-- 輸入框，限制為必填 -->
					<td><input type="tel" id="phone" name="phone" placeholder="請輸入電話號碼" required></td>
				</tr>
				<!-- 地址輸入 -->
				<tr>
					<td>地址：</td>
					<!-- 單一輸入框，方便填寫完整地址 -->
					<td><input type="text" id="address" name="address" placeholder="請填寫完整地址" required></td>
				</tr>
				<!-- 預約日期選擇 -->
				<tr>
					<td><label for="appointmentDate">預約日期：</label></td>
					<!-- 日期選擇器，限制為必填 -->
					<td><input type="date" id="appointmentDate" name="appointmentDate" required></td>
				</tr>
				<!-- 預約時間選擇 -->
				<tr>
					<td><label for="appointmentTime">預約時間：</label></td>
					<!-- 下拉選單，自動生成每 20 分鐘的時間區間 -->
					<td>
						<select id="appointmentTime" name="appointmentTime" required>
							<option value="">請選擇時間</option>
						</select>
					</td>
				</tr>
				<!-- 選擇治療師 -->
				<tr>
					<td>選擇治療師：</td>
					<!-- 下拉選單，選項暫時留空，限制為必選 -->
					<td>
						<select id="therapist" name="therapist" required>
							<option value="">請選擇治療師</option>
						</select>
					</td>
				</tr>
			</table>
			<!-- 提交按鈕 -->
			<button type="submit">完成</button>
		</form>

		<script>
			// 設定出生年月日限制為今天以前
			const birthDateInput = document.getElementById("birthDate");
			// 獲取當天日期，格式為 yyyy-mm-dd
			const today = new Date().toISOString().split("T")[0];
			// 設定日期選擇器的最大日期為今天
			birthDateInput.max = today;

			// 預約時間選項生成
			const appointmentTimeSelect = document.getElementById("appointmentTime");
			// 設定時間區間起始和結束小時數
			const startHour = 7; // 早上 7 點
			const endHour = 19; // 晚上 7 點，可根據需求調整
			const interval = 20; // 每 20 分鐘一個區間

			// 迴圈生成每 20 分鐘的時間選項
			for (let hour = startHour; hour < endHour; hour++) {
				for (let minute = 0; minute < 60; minute += interval) {
					// 格式化時間，確保是兩位數（例如 07:00）
					const time = `${hour.toString().padStart(2, "0")}:${minute.toString().padStart(2, "0")}`;
					// 創建選項元素
					const option = document.createElement("option");
					option.value = time; // 設定選項的值
					option.textContent = time; // 設定選項的顯示文字
					// 添加到下拉選單中
					appointmentTimeSelect.appendChild(option);
				}
			}

			// 表單驗證函數
			function validateForm() {
				// 獲取表單元素
				const form = document.getElementById("form");
				// 驗證表單是否完整填寫
				if (!form.checkValidity()) {
					alert("請完整填寫表單！");
					return false;
				}
				return true; // 驗證通過
			}
		</script>

		<!-- Global Mailform Output-->
		<div class="snackbars" id="form-output-global"></div>
		<!-- Javascript-->
		<script src="js/core.min.js"></script>
		<script src="js/script.js"></script>
</body>

</html>