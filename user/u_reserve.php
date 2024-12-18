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
								<!--Brand--><a class="brand-name" href="u_index.php"><img class="logo-default"
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

								<li class="rd-nav-item"><a class="rd-nav-link" href="">個人資料</a>
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
		<h2>資訊填寫表格</h2>
		<form id="form" onsubmit="return validateForm()">
			<table border="1">
				<tr>
					<td><label for="name">姓名：</label></td>
					<td><input type="text" id="name" name="name" placeholder="請輸入姓名" required></td>
				</tr>
				<tr>
					<td>性別：</td>
					<td>
						<label><input type="radio" name="gender" value="male" required> 男</label>
						<label><input type="radio" name="gender" value="female" required> 女</label>
					</td>
				</tr>
				<tr>
					<td>出生年月日：</td>
					<td>
						<select id="year" name="year" required>
							<option value="">年</option>
							<script>
								for (let y = 1980; y <= 2024; y++) {
									document.write(`<option value="${y}">${y}</option>`);
								}
							</script>
						</select>
						<select id="month" name="month" required>
							<option value="">月</option>
							<script>
								for (let m = 1; m <= 12; m++) {
									document.write(`<option value="${m}">${m}</option>`);
								}
							</script>
						</select>
						<select id="day" name="day" required>
							<option value="">日</option>
							<script>
								for (let d = 1; d <= 31; d++) {
									document.write(`<option value="${d}">${d}</option>`);
								}
							</script>
						</select>
					</td>
				</tr>
				<tr>
					<td><label for="idNumber">身分證字號：</label></td>
					<td><input type="text" id="idNumber" name="idNumber" placeholder="請輸入身分證字號" required></td>
				</tr>
				<tr>
					<td><label for="phone">電話：</label></td>
					<td><input type="tel" id="phone" name="phone" placeholder="請輸入電話號碼" required></td>
				</tr>
				<tr>
					<td>地址：</td>
					<td>
						<select id="city" name="city" required>
							<option value="">選擇市</option>
							<option value="台北市">台北市</option>
							<option value="新北市">新北市</option>
							<option value="桃園市">桃園市</option>
							<option value="台中市">台中市</option>
						</select>
						<select id="district" name="district" required>
							<option value="">選擇區</option>
							<option value="中正區">中正區</option>
							<option value="大安區">大安區</option>
							<option value="信義區">信義區</option>
						</select>
						<input type="text" id="otherAddress" name="otherAddress" placeholder="請填寫詳細地址" required>
					</td>
				</tr>
				<tr>
					<td><label for="remark">備註：</label></td>
					<td><textarea id="remark" name="remark" rows="4" placeholder="請輸入備註（可選填）"></textarea></td>
				</tr>
				<tr>
					<td>選擇治療師：</td>
					<td>
						<select id="therapist" name="therapist" required>
							<option value="">請選擇治療師</option>
							<option value="張醫師">張醫師</option>
							<option value="李醫師">李醫師</option>
							<option value="王治療師">王治療師</option>
						</select>
					</td>
				</tr>
			</table>
			<button type="submit">完成</button>
		</form>

		<script>
			function validateForm() {
				const form = document.getElementById("form");
				const year = document.getElementById("year").value;
				const month = document.getElementById("month").value;
				const day = document.getElementById("day").value;
				const city = document.getElementById("city").value;
				const district = document.getElementById("district").value;

				// 額外檢查下拉選單
				if (year === "" || month === "" || day === "") {
					alert("請完整填寫出生年月日！");
					return false;
				}

				if (city === "" || district === "") {
					alert("請選擇完整的地址！");
					return false;
				}

				// 表單內其他 required 已自動驗證
				return true;
			}
		</script>
		<!-- Global Mailform Output-->
		<div class="snackbars" id="form-output-global"></div>
		<!-- Javascript-->
		<script src="js/core.min.js"></script>
		<script src="js/script.js"></script>
</body>

</html>