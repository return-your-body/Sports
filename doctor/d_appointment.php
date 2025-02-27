<?php
session_start();
require '../db.php';

// 確保用戶已登錄並傳入必要的參數
if (!isset($_SESSION['登入狀態']) || !isset($_GET['id']) || empty($_GET['id'])) {
	echo "<script>alert('請先登入！');window.location.href='../index.html';</script>";
	exit;
}

// 獲取 GET 參數
$people_id = intval($_GET['id']);
$_SESSION['people_id'] = $people_id;

// 查詢 People 表以獲取使用者資料
$query_people = "SELECT name FROM people WHERE people_id = ?";
$stmt_people = $link->prepare($query_people);
$stmt_people->bind_param("i", $people_id);
$stmt_people->execute();
$result_people = $stmt_people->get_result();

if ($result_people->num_rows > 0) {
	$data_people = $result_people->fetch_assoc();
	$_SESSION['user_name'] = $data_people['name'];
} else {
	echo "<script>alert('無法找到對應的使用者資料！');window.location.href='d_people.php';</script>";
	exit;
}

// 查詢當前登入治療師的資料
$user_account = $_SESSION['帳號'];
$query_doctor = "
    SELECT doctor_id, doctor AS 治療師姓名 
    FROM doctor 
    JOIN user ON doctor.user_id = user.user_id
    WHERE user.account = ? AND user.grade_id = 2";
$stmt_doctor = $link->prepare($query_doctor);
$stmt_doctor->bind_param("s", $user_account);
$stmt_doctor->execute();
$result_doctor = $stmt_doctor->get_result();

if ($result_doctor->num_rows > 0) {
	$data_doctor = $result_doctor->fetch_assoc();
	$_SESSION['doctor_id'] = $data_doctor['doctor_id'];
	$_SESSION['doctor_name'] = $data_doctor['治療師姓名'];
} else {
	echo "<script>alert('無法找到治療師資料！');window.location.href='d_people.php';</script>";
	exit;
}
?>


<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
	<!-- Site Title-->
	<title>治療師-預約</title>
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



		/* 預約 */
		body {
			font-family: Arial, sans-serif;
			background-color: #f9f9f9;
			color: #333;
		}

		.form-container {
			background-color: #ffffff;
			padding: 30px;
			border-radius: 10px;
			box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
		}

		.form-label {
			font-weight: bold;
			font-size: 14px;
			color: #555;
		}

		.form-control,
		.form-select {
			border: 1px solid #ddd;
			border-radius: 5px;
			padding: 10px;
			font-size: 14px;
			width: 100%;
			background-color: #f9f9f9;
			box-shadow: inset 0px 1px 3px rgba(0, 0, 0, 0.1);
		}

		.form-control:focus,
		.form-select:focus {
			border-color: #007bff;
			outline: none;
			box-shadow: 0px 0px 5px rgba(0, 123, 255, 0.5);
		}

		textarea.form-control {
			resize: none;
		}

		.btn-primary {
			background-color: #007bff;
			border: none;
			color: #fff;
			font-weight: bold;
			cursor: pointer;
			border-radius: 5px;
			transition: background-color 0.3s ease;
		}

		.btn-primary:hover {
			background-color: #0056b3;
		}

		.text-center {
			text-align: center;
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

	<!--標籤列-->
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
								<!--Brand--><a class="brand-name" href="d_index.html"><img class="logo-default"
										src="images/logo-default-172x36.png" alt="" width="86" height="18"
										loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
										alt="" width="86" height="18" loading="lazy" /></a>
							</div>
						</div>
						<div class="rd-navbar-nav-wrap">
							<ul class="rd-navbar-nav">
								<li class="rd-nav-item"><a class="rd-nav-link" href="d_index.php">首頁</a></li>

								<li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="d_people.php">用戶資料</a>
										</li>
									</ul>
								</li>

								<!-- <li class="rd-nav-item active"><a class="rd-nav-link" href="d_appointment.php">預約</a></li> -->

								<li class="rd-nav-item"><a class="rd-nav-link" href="#">班表</a>
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
										onclick="showLogoutBox()">登出</a></li>

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
						echo $_SESSION['doctor_name'];
						;
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
		<!--標籤列-->


		<!--標題-->
		<div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
			<!-- Breadcrumbs-->
			<section class="breadcrumbs-custom breadcrumbs-custom-svg">
				<div class="container">
					<!-- <p class="breadcrumbs-custom-subtitle">Our team</p> -->
					<p class="heading-1 breadcrumbs-custom-title">預約</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="d_index.php">首頁</a></li>
						<li class="active">預約</li>
					</ul>
				</div>
			</section>
		</div>
		<!--標題-->

		<!-- 預約 -->
		<section class="section section-lg novi-bg novi-bg-img bg-default">
			<div class="container">
				<div class="row justify-content-center">
					<div class="col-lg-6 col-md-8">
						<div class="form-container shadow-lg p-4 rounded bg-light">
							<h3 class="text-center mb-4">預約表單</h3>
							<form action="預約.php" method="post">
								<!-- 使用者姓名 (顯示姓名，實際提交 people_id) -->
								<div class="form-group mb-3">
									<label for="people_name">姓名：</label>
									<input type="text" id="people_name" class="form-control"
										value="<?= htmlspecialchars($_SESSION['user_name']); ?>" readonly required />
									<input type="hidden" name="people_id" value="<?= $_SESSION['people_id']; ?>">
								</div>

								<!-- 治療師姓名 (顯示姓名，實際提交 doctor_id) -->
								<div class="form-group mb-3">
									<label for="doctor_name">治療師姓名：</label>
									<input type="text" id="doctor_name" class="form-control"
										value="<?= htmlspecialchars($_SESSION['doctor_name']); ?>" readonly required />
									<input type="hidden" name="doctor_id" value="<?= $_SESSION['doctor_id']; ?>">
								</div>

								<!-- 預約日期 -->
								<div class="form-group mb-3">
									<label for="date">預約日期：</label>
									<input type="date" id="date" name="date" class="form-control"
										onchange="fetchAvailableTimes()" required min="<?= date('Y-m-d'); ?>"
										max="<?= date('Y-m-d', strtotime('+360 days')); ?>" />

								</div>

								<!-- 預約時間 -->
								<div class="form-group mb-3">
									<label for="time">預約時間：</label>
									<select id="time" name="time" class="form-select" required>
										<option value="">請選擇時間</option>
									</select>
								</div>

								<!-- 備註 -->
								<div class="form-group mb-4">
									<label for="note">備註：</label>
									<textarea id="note" name="note" class="form-control" rows="4" maxlength="200"
										placeholder="請輸入備註，最多200字"></textarea>
								</div>

								<!-- 提交按鈕 -->
								<div class="text-center">
									<button type="submit" class="btn btn-primary px-4 py-2">提交預約</button>
									<button type="button" onclick="location.href='d_people.php'"
										class="btn btn-secondary px-4 py-2">返回</button>
								</div>
							</form>

						</div>
					</div>
				</div>
			</div>
		</section>

		<script>
			function fetchAvailableTimes() {
				const date = document.getElementById("date").value;
				const doctorId = document.querySelector("input[name='doctor_id']").value;
				const timeSelect = document.getElementById("time");

				timeSelect.innerHTML = '<option value="">加載中...</option>';

				if (date && doctorId) {
					fetch("檢查預約.php", {
						method: "POST",
						headers: { "Content-Type": "application/x-www-form-urlencoded" },
						body: `date=${date}&doctor_id=${doctorId}`,
					})
						.then(response => response.json())
						.then(data => {
							timeSelect.innerHTML = '<option value="">請選擇時間</option>';
							if (data.success) {
								data.times.forEach(time => {
									const option = document.createElement("option");
									option.value = time.shifttime_id;
									option.textContent = time.shifttime;
									timeSelect.appendChild(option);
								});
							} else {
								timeSelect.innerHTML = '<option value="">無可用時段</option>';
							}
						})
						.catch(error => {
							console.error("錯誤：", error);
							timeSelect.innerHTML = '<option value="">無法加載時段</option>';
						});
				} else {
					timeSelect.innerHTML = '<option value="">請選擇日期</option>';
				}
			}

			document.addEventListener("DOMContentLoaded", function () {
				const dateInput = document.getElementById("date");
				if (dateInput) {
					dateInput.addEventListener("change", fetchAvailableTimes);
				}
			});
		</script>


		<!-- 預約-->



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