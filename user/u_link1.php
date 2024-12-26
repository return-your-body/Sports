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
	$sql = "SELECT user.account, people.name 
            FROM user 
            JOIN people ON user.user_id = people.user_id 
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

								<li class="rd-nav-item active"><a class="rd-nav-link" href="#">關於我們</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item active"><a class="rd-dropdown-link"
												href="u_link.php">醫生介紹</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">個案分享</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">日常小知識</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_reserve.php">立即預約</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_reserve-record.php">查看預約資料</a>
											<!-- 修改預約 -->
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="u_reserve-time.php">查看預約時段</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="u_history.php">歷史紀錄</a>
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
						<!-- <div class="rd-navbar-aside-right rd-navbar-collapse">
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


		<!--標題-->
		<div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
			<!-- Breadcrumbs-->
			<section class="breadcrumbs-custom breadcrumbs-custom-svg">
				<div class="container">
					<!-- <p class="breadcrumbs-custom-subtitle">Our team</p> -->
					<p class="heading-1 breadcrumbs-custom-title">醫生介紹</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="u_index.php">首頁</a></li>
						<li class="active">醫生介紹</li>
					</ul>
				</div>
			</section>
		</div>
		<!--標題-->

		<!-- Ronald Chen-->
		<section class="section section-lg bg-default text-start novi-bg novi-bg-img">
			<div class="container">
				<div class="row row-70 row-ten row-fix justify-content-xl-between">
					<div class="col-xl-3">
						<div class="row row-70 row-fix align-items-md-center">
							<div class="col-md-6 col-xl-12"><img src="images/team-member-1-420x420.jpg" alt=""
									width="420" height="420" />
							</div>
							<div class="col-md-6 col-xl-12 text-md-start">
								<h6>Get in Touch</h6>
								<!-- RD Mailform-->
								<form class="rd-mailform" data-form-output="form-output-global" data-form-type="contact"
									method="post" action="bat/rd-mailform.php">
									<div class="row row-20 row-fix">
										<div class="col-md-12">
											<div class="form-wrap form-wrap-validation">
												<label class="form-label-outside" for="form-1-name">First name</label>
												<input class="form-input" id="form-1-name" type="text" name="name"
													data-constraints="@Required">
											</div>
										</div>
										<div class="col-md-12">
											<div class="form-wrap form-wrap-validation">
												<label class="form-label-outside" for="form-1-email">E-mail</label>
												<input class="form-input" id="form-1-email" type="email" name="email"
													data-constraints="@Email @Required">
											</div>
										</div>
										<div class="col-sm-12">
											<div class="form-wrap form-wrap-validation">
												<label class="form-label-outside" for="form-1-message">Message</label>
												<textarea class="form-input" id="form-1-message" name="message"
													data-constraints="@Required"></textarea>
											</div>
										</div>
										<div class="col-sm-12 offset-custom-4">
											<div class="form-button">
												<button class="button button-primary button-nina" type="submit">Send
													message</button>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
					<div class="col-xl-6">
						<div class="row row-120 row-fix">
							<div class="col-sm-12">
								<div class="heading-5">Web design instructor</div>
								<h3>Theresa Webb</h3>
								<div class="divider divider-default"></div>
								<p class="text-spacing-sm">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed
									do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
									veniam, quis nostrud exercitation.</p>
								<div class="row text-center row-lg row-30 justify-content-center">
									<div class="col-auto col-sm-4">
										<!--Circle progress bar-->
										<div class="progress-circle">
											<div class="progress-circle-block">
												<svg class="progress-circle-bar" x="0" y="0" width="140" height="140"
													viewbox="0 0 140 140">
													<circle class="progress-circle-bg" cx="70" cy="70" r="67"></circle>
													<circle class="progress-circle-fg clipped" cx="70" cy="70" r="67">
													</circle>
												</svg>
												<div class="progress-circle-counter radial">75</div>
											</div>
											<div class="heading-5 progress-circle-title">UI/UX Design</div>
										</div>
									</div>
									<div class="col-auto col-sm-4">
										<!--Circle progress bar-->
										<div class="progress-circle">
											<div class="progress-circle-block">
												<svg class="progress-circle-bar" x="0" y="0" width="140" height="140"
													viewbox="0 0 140 140">
													<circle class="progress-circle-bg" cx="70" cy="70" r="67"></circle>
													<circle class="progress-circle-fg clipped" cx="70" cy="70" r="67">
													</circle>
												</svg>
												<div class="progress-circle-counter radial">50</div>
											</div>
											<div class="heading-5 progress-circle-title">Research</div>
										</div>
									</div>
									<div class="col-auto col-sm-4">
										<!--Circle progress bar-->
										<div class="progress-circle">
											<div class="progress-circle-block">
												<svg class="progress-circle-bar" x="0" y="0" width="140" height="140"
													viewbox="0 0 140 140">
													<circle class="progress-circle-bg" cx="70" cy="70" r="67"></circle>
													<circle class="progress-circle-fg clipped" cx="70" cy="70" r="67">
													</circle>
												</svg>
												<div class="progress-circle-counter radial">25</div>
											</div>
											<div class="heading-5 progress-circle-title">Graphic Design</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-12">
								<div class="row row-70">
									<div class="col-md-6">
										<h6>Skills</h6>
										<p>Visual design, UX, communication, creativity, time management, teamwork<br
												class="d-none d-xxl-block"> strategy, communication, coaching, PR
										</p>
									</div>
									<div class="col-md-6">
										<h6>Awards</h6>
										<ul class="list list-xxs text-spacing-sm">
											<li>2021 - The Best Web Design Instructor</li>
											<li>2018 - UX Designer of the Year</li>
											<li>2017 - #1 Web Design Course Instructor</li>
										</ul>
									</div>
									<div class="col-md-6">
										<h6>Certificates</h6>
										<ul class="list list-xxs text-spacing-sm">
											<li>2021 - New Ways of Design</li>
											<li>2019 - UX Design for Mobile Platforms</li>
											<li>2017 - Advanced Graphic Design</li>
										</ul>
									</div>
									<div class="col-md-6">
										<h6>Contact information</h6>
										<ul class="list-xs">
											<li class="box-inline box-inline-gray"><span
													class="icon novi-icon icon-md-smaller icon-primary mdi mdi-map-marker"></span>
												<div><a href="#">2130 Fulton Street, San Diego, CA<br>94117-1080 USA</a>
												</div>
											</li>
											<li class="box-inline box-inline-gray"><span
													class="icon novi-icon icon-md-smaller icon-primary mdi mdi-phone"></span>
												<ul class="list-comma">
													<li><a href="tel:#">1-800-1234-567</a></li>
												</ul>
											</li>
											<li>
												<ul class="inline-list-xs">
													<li><a class="icon novi-icon icon-sm-bigger icon-gray-1 mdi mdi-facebook"
															href="#"></a></li>
													<li><a class="icon novi-icon icon-sm-bigger icon-gray-1 mdi mdi-twitter"
															href="#"></a></li>
													<li><a class="icon novi-icon icon-sm-bigger icon-gray-1 mdi mdi-instagram"
															href="#"></a></li>
													<li><a class="icon novi-icon icon-sm-bigger icon-gray-1 mdi mdi-google"
															href="#"></a></li>
													<li><a class="icon novi-icon icon-sm-bigger icon-gray-1 mdi mdi-linkedin"
															href="#"></a></li>
												</ul>
											</li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- Global Mailform Output-->
		<div class="snackbars" id="form-output-global"></div>
		<!-- Javascript-->
		<script src="js/core.min.js"></script>
		<script src="js/script.js"></script>
</body>

</html>