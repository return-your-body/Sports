<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
<?php
session_start();

// 檢查使用者是否登入
if (!isset($_SESSION["登入狀態"])) {
	header("Location: ../index.html");
	exit;
}

// 防止瀏覽器快取
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

// 確保 "帳號" 存在於 Session
if (!isset($_SESSION["帳號"])) {
	echo "<script>
            alert('會話過期或資料遺失，請重新登入。');
            window.location.href = '../index.html';
          </script>";
	exit;
}

$帳號 = $_SESSION['帳號']; // 取得當前登入帳號

// 連接資料庫
require '../db.php';

// 查詢使用者的帳號資訊，**確保 grade_id = 3（助手）**
$sql = "
    SELECT u.account, u.grade_id, d.doctor_id, d.doctor AS name
    FROM user u
    LEFT JOIN doctor d ON u.user_id = d.user_id
    WHERE u.account = ? AND u.grade_id = 3
";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "s", $帳號);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

// 若找不到符合條件的帳號（非助手或帳號不存在）
if (!$row) {
	echo "<script>
            alert('未找到對應的助手帳號，請確認帳號是否正確。');
            window.location.href = '../index.html';
          </script>";
	exit;
}

// 設定使用者資訊
$doctor_name = htmlspecialchars($row['name']); // 抓取助手姓名
$doctor_id = $row['doctor_id']; // 抓取 doctor_id

// 如果 doctor_id 仍然是空的，記錄錯誤（方便除錯）
if (empty($doctor_id)) {
	error_log("【錯誤】帳號 {$帳號} 找不到對應的 doctor_id");
	echo "<script>
            alert('帳號未綁定助手身份，請聯繫管理員。');
            window.location.href = '../index.html';
          </script>";
	exit;
}

// 關閉資料庫連接
mysqli_stmt_close($stmt);
mysqli_close($link);
?>


<head>
	<!-- Site Title-->
	<title>助手-請假申請</title>
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

		/* 請假單 */

		.form-container {
			width: 40%;
			margin: auto;
			padding: 20px;
			border: 1px solid #ccc;
			border-radius: 8px;
			box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
		}

		label,
		input,
		select,
		textarea,
		button {
			display: block;
			width: 100%;
			margin-bottom: 10px;
		}

		button {
			background-color: #007bff;
			color: white;
			padding: 10px;
			border: none;
			cursor: pointer;
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
								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="h_appointment.php">預約</a>
				</li> -->
								<li class="rd-nav-item active"><a class="rd-nav-link" href="#">班表</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_doctorshift.php">治療師班表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_numberpeople.php">當天人數及時段</a>
										</li>
										<li class="rd-dropdown-item "><a class="rd-dropdown-link"
												href="h_assistantshift.php">每月班表</a>
										</li>
										<li class="rd-dropdown-item active"><a class="rd-dropdown-link"
												href="h_leave.php">請假申請</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_leave-query.php">請假資料查詢</a>
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
								<li class="rd-nav-item"><a class="rd-nav-link" href="h_change.php">變更密碼</a>
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
						echo $row['name'];
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
					<p class="heading-1 breadcrumbs-custom-title">治療師請假</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="d_index.php">首頁</a></li>
						<li><a href="#">班表</a></li>
						<li class="active">請假</li>
					</ul>
				</div>
			</section>
		</div>

		<!-- 請假單 -->

		<!-- Flatpickr 樣式 -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
		<!-- Flatpickr JS -->
		<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

		<div class="form-container">
			<h3 style="text-align: center;">請假單</h3>
			<form id="leave-form">
				<label for="doctor_name">姓名：</label>
				<input type="text" id="doctor_name" name="doctor_name" value="<?php echo $doctor_name; ?>" readonly>
				<input type="hidden" id="doctor_id" name="doctor_id" value="<?php echo $doctor_id; ?>">

				<label for="leave-type">請假類別：</label>
				<select id="leave-type" name="leave_type" required>
					<option value="" disabled selected>請選擇請假類別</option>
					<option value="年假">年假</option>
					<option value="病假">病假</option>
					<option value="事假">事假</option>
					<option value="公假">公假</option>
					<option value="其他">其他</option>
				</select>
				<input type="text" id="leave-type-other" name="leave_type_other" placeholder="若選擇其他，請填寫原因"
					style="display: none;">

				<label for="date">請假日期：</label>
				<input type="text" id="date" name="date" required>

				<label for="start-time">請假開始時間：</label>
				<input type="text" id="start-time" name="start_time" required>

				<label for="end-time">請假結束時間：</label>
				<input type="text" id="end-time" name="end_time" required>

				<label for="reason">請假原因：</label>
				<textarea id="reason" name="reason" rows="4" required></textarea>

				<button type="submit">提交</button>
			</form>
		</div>

		<script>
			document.addEventListener('DOMContentLoaded', function () {
				const leaveForm = document.getElementById('leave-form');
				const leaveType = document.getElementById('leave-type');
				const leaveTypeOther = document.getElementById('leave-type-other');
				const doctorId = document.getElementById('doctor_id').value;

				const datePicker = flatpickr("#date", { dateFormat: "Y-m-d", minDate: "today" });
				const startFlatpickr = flatpickr("#start-time", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true });
				const endFlatpickr = flatpickr("#end-time", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true });

				leaveType.addEventListener("change", function () {
					leaveTypeOther.style.display = (this.value === "其他") ? "block" : "none";
				});

				checkTodayAvailability();

				function checkTodayAvailability() {
					const today = new Date().toISOString().slice(0, 10);
					fetch(`檢查可請假時間.php?date=${today}&doctor=${doctorId}`)
						.then(response => response.json())
						.then(data => {
							if (!data.success) {
								datePicker.set('minDate', new Date().fp_incr(1));
							}
						})
						.catch(error => console.error("檢查當日是否可請假錯誤:", error));
				}

				datePicker.config.onChange.push(fetchAvailableLeaveTimes);

				function fetchAvailableLeaveTimes() {
					const date = document.getElementById('date').value;

					fetch(`檢查可請假時間.php?date=${date}&doctor=${doctorId}`)
						.then(response => response.json())
						.then(data => {
							if (data.success && data.times.length > 0) {
								const availableTimes = data.times.map(t => t.shifttime);
								startFlatpickr.set("enable", availableTimes);
								endFlatpickr.set("enable", availableTimes);
							}
							// else {
							//   startFlatpickr.clear();
							//   endFlatpickr.clear();
							//   alert(data.message || "當天無可請假時段");
							// }
						})
						.catch(error => console.error("請假時間加載錯誤:", error));
				}

				leaveForm.addEventListener('submit', function (event) {
					event.preventDefault();
					const formData = new FormData(this);

					fetch("請假.php", {
						method: "POST",
						body: formData
					})
						.then(response => response.json())
						.then(data => {
							alert(data.message);
							if (data.success) {
								location.reload();
							}
						})
						.catch(error => console.error("請假提交錯誤:", error));
				});
			});

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
							<li><a href="h_doctorshift.php">治療師班表時段</a></li>
							<li><a href="h_assistantshift.php">每月班表</a></li>
							<li><a href="h_leave.php">請假申請</a></li>
							<li><a href="h_leave-query.php">請假資料查詢</a></li>
							<li><a href="h_medical-record.php">看診紀錄</a></li>
							<li><a href="h_appointment-records.php">預約紀錄</a></li>
							<li><a href="h_change.php">變更密碼</a></li>
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