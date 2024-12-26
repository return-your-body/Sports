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
								<!--Brand--><a class="brand-name" href="u_index.php"><img class="logo-default"
										src="images/logo-default-172x36.png" alt="" width="86" height="18"
										loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
										alt="" width="86" height="18" loading="lazy" /></a>
							</div>
						</div>
						<div class="rd-navbar-nav-wrap">
							<ul class="rd-navbar-nav">
								<li class="rd-nav-item active"><a class="rd-nav-link" href="u_index.php">主頁</a>
								</li>

								<li class="rd-nav-item"><a class="rd-nav-link" href="#">關於我們</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_link.php">醫生介紹</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="u_caseshare.php">個案分享</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="u_body-knowledge.php">日常小知識</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_reserve.php">立即預約</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="u_reserve-record.php">查看預約資料</a>
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
											href="https://lin.ee/sUaUVMq"></a>
									</li>
									<li><a class="icon novi-icon icon-default icon-custom-instagram"
											href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"></a>
									</li>
								</ul>
							</div>
						</div>-->
						<?php 
						echo"歡迎 ~ ";
						// 顯示姓名
						echo $姓名;
						?>
					</div>
				</nav>
			</div>
		</header>

		<!-- <marquee>跑馬燈</marquee> -->

		<!--Welcome back user-->
		<section class="section section-xl bg-image bg-image-9 text-center novi-bg novi-bg-img">
			<div class="container">
				<!-- <h1>Welcome back user-<?php echo $姓名;?></h1> -->
				<h1 class="fw-light text-uppercase">運動筋膜放鬆</h1>
				<p class="big">
					''運動筋膜放鬆在做什麼？''<br />
					✨運動筋膜放鬆是透過治療師的牽引將您的身體姿勢擺到適當的位置✨<br />
					✨還原回到它自己該在的位置而達到放鬆✨</p>
				<div class="group-md button-group">
					<a class="button button-dark button-nina" href="u_reserve.php">立即預約</a>
					<a class="button button-dark button-nina" href="u_link.php">醫生介紹</a>
				</div>
			</div>
		</section>
		<!--Welcome back doctor-->


		<!--醫生簡介-->
		<section class="section section-lg bg-default novi-bg novi-bg-img">
			<div class="container">
				<div class="row row-30 align-items-center justify-content-xxl-between">
					<div class="col-md-6">
						<h2 class="box-small-title">醫生簡介</h2>
						<p class="big">
							🏋🏻吳孟軒</br>
							🏋🏻長庚大學 物理治療學系-學士、生物醫學系-學士</br>
							🏋🏻現任 大重仁復健科診所--成人物理治療師</p>
						<div class="row row-30 row-offset-1">
							<div class="col-md-10 offset-xxl-2">
								<div class="box-small">
									<h4 class="box-small-title">專長</h4>
									<div class="box-small-text">
										<p class="big">
											💪🏻徒手治撩：筋肌膜疼縮、軟組織放鬆</br>
											💪🏻動作分析與控制訓練</br>
											💪🏻肌肉骨骼疼痛</br>
											💪🏻慢性下背痛</br>
											💪🏻肩頸功能障礙：頸因性頭痛、落枕、五十肩、旋轉肌群拉傷</br>
											💪🏻術後復健</p>
									</div>
								</div>
								<div class="box-small">
									<h4 class="box-small-title">專業認證與進修課程</h4>
									<div class="box-small-text">
										<p class="big">
											🏅 中華民國高考合格物理治療師</br>
											🏅 McConnell Institute 實證下肢生物力學</br>
											🏅 McConnell Institute 起始位置與肌肉骨骼問題之關係</br>
											🏅 台灣陽明學苑骨科肌肉系統全方位處置策略</br>
											🏅 解剖列車 Anatomy Trains in Motion: Myofascial</br>
											🏅 Body Map for Movement</br>
											🏅 結構治療
										</p>
									</div>
								</div>
								<div class="box-small">
									<h4 class="box-small-title">治療理念</h4>
									<div class="box-small-text">
										<p class="big">
											治療是一個控制疼痛的過程,如何控制好疼痛不再出現是重要的課題。</br>
											透過評估尋找真正造成問題的來源,進而改變原因達到控制疼痛。</br>
											在一對一治療中使得身體使用模式回歸到中軸,讓身體活動更輕鬆、更能享受生活。
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- 圖片-->
					<!-- <div class="col-md-6 col-lg-5 col-xl-4 offset-xl-1 col-xxl-5 position-relative d-none d-md-block">
			<div class="parallax-scene-js parallax-scene parallax-scene-1" data-scalar-x="5" data-scalar-y="10">
			  <div class="layer-01">
				<div class="layer" data-depth="0.25"><img src="images/home-03-515x600.jpg" alt="" width="515"
					height="600" loading="lazy" />
				</div>
			  </div>
			  <div class="layer-02">
				<div class="layer" data-depth=".55"><img src="images/home-02-340x445.jpg" alt="" width="340"
					height="445" loading="lazy" />
				</div>
			  </div>
			</div>
		  </div> -->
				</div>
			</div>
		</section>
		<!--醫生簡介-->


		<!--班表-->
		<title>班表</title>
		<style>
			body {
				font-family: Arial, sans-serif;
				margin: 20px;
				background-color: #f9f9f9;
			}

			table {
				width: 100%;
				border-collapse: collapse;
				margin: 20px 0;
				background-color: #fff;
			}

			th,
			td {
				border: 1px solid #ccc;
				text-align: center;
				padding: 10px;
			}

			th {
				background-color: #4CAF50;
				color: white;
			}

			tr:nth-child(even) {
				background-color: #f2f2f2;
			}

			caption {
				font-size: 1.5em;
				margin: 10px;
				font-weight: bold;
			}
		</style>

		<h1>班表</h1>
		<table>
			<caption>每週班表</caption>
			<thead>
				<tr>
					<th>時段/星期</th>
					<th>星期一</th>
					<th>星期二</th>
					<th>星期三</th>
					<th>星期四</th>
					<th>星期五</th>
					<th>星期六</th>
					<th>星期日</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>早班 (8:00 - 12:00)</td>
					<td>王小明</td>
					<td>李大華</td>
					<td>張小美</td>
					<td>王小明</td>
					<td>李大華</td>
					<td>休息</td>
					<td>休息</td>
				</tr>
				<tr>
					<td>午班 (12:00 - 16:00)</td>
					<td>李大華</td>
					<td>張小美</td>
					<td>王小明</td>
					<td>張小美</td>
					<td>王小明</td>
					<td>休息</td>
					<td>休息</td>
				</tr>
				<tr>
					<td>晚班 (16:00 - 20:00)</td>
					<td>張小美</td>
					<td>王小明</td>
					<td>李大華</td>
					<td>李大華</td>
					<td>張小美</td>
					<td>休息</td>
					<td>休息</td>
				</tr>
			</tbody>
		</table>

		<!--班表-->


		<!-- Page Footer-->
		<footer class="section novi-bg novi-bg-img footer-simple">
			<div class="container">
				<div class="row row-40">
					<div class="col-md-4">
						<h4>About us</h4>
						<p class="me-xl-5">Pract is a learning platform for education and skills training. We provide
							you
							professional knowledge using innovative approach.</p>
					</div>
					<div class="col-md-3">
						<h4>Quick links</h4>
						<ul class="list-marked">
							<li><a href="index.html">Home</a></li>
							<li><a href="courses.html">Courses</a></li>
							<li><a href="about-us.html">About us</a></li>
							<li><a href="#">Blog</a></li>
							<li><a href="contacts.html">Contacts</a></li>
							<li><a href="#">Become a teacher</a></li>
						</ul>
					</div>
					<div class="col-md-5">
						<h4>Newsletter</h4>
						<p>Subscribe to our newsletter today to get weekly news, tips, and special offers from our team
							on the
							courses we offer.</p>
						<form class="rd-mailform rd-form-boxed" data-form-output="form-output-global"
							data-form-type="subscribe" method="post" action="bat/rd-mailform.php">
							<div class="form-wrap">
								<input class="form-input" type="email" name="email" data-constraints="@Email @Required"
									id="footer-mail">
								<label class="form-label" for="footer-mail">Enter your e-mail</label>
							</div>
							<button class="form-button linearicons-paper-plane"></button>
						</form>
					</div>
				</div>
				<p class="rights"><span>&copy;&nbsp;</span><span
						class="copyright-year"></span><span>&nbsp;</span><span>Pract</span><span>.&nbsp;All Rights
						Reserved.&nbsp;</span><a href="privacy-policy.html">Privacy Policy</a> <a target="_blank"
						href="https://www.mobanwang.com/" title="网站模板">网站模板</a></p>
			</div>
		</footer>
	</div>
	<!-- Global Mailform Output-->
	<div class="snackbars" id="form-output-global"></div>
	<!-- Javascript-->
	<script src="js/core.min.js"></script>
	<script src="js/script.js"></script>
</body>

</html>