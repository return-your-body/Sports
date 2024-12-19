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
								
								<li class="rd-nav-item"><a class="rd-nav-link" href="">關於我們</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="">醫生介紹</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="">個案分享</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="">日常小知識</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_reserve.php">立即預約</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="">查看預約資料</a> <!-- 修改預約 -->
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="">查看預約時段</a>
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
											href="https://lin.ee/sUaUVMq"></a>
									</li>
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
		</body>
			<marquee>跑馬燈</marquee>
		</body>
		<!DOCTYPE html>
		<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>吳孟軒 - 成人物理治療師</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        h1, h2 {
            color: #007BFF;
        }
        p {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>吳孟軒</h1>
    <p>大重仁復健科診所 - 成人物理治療師</p>

    <h2>專長</h2>
    <p>徒手治療：筋肌膜疼痛、軟組織放鬆</p>
    <p>動作分析與訓練指導</p>
    <p>肌肉骨骼問題</p>
    <p>慢性下背痛</p>
    <p>環狀軟骨關節疼痛、落枕、五十肩等關節活動問題</p>
    <p>術後復健</p>

    <h2>學歷</h2>
    <p>長庚大學 物理治療學系 學士</p>
    <p>長庚大學 生物醫學學系 學士</p>

    <h2>經歷</h2>
    <p>大重仁復健科診所 成人物理治療師</p>
    <p>明台大學物理治療所 實習物理治療師</p>

    <h2>專業認證與進修課程</h2>
    <p>中華民國物理治療學會徒手治療專科學員</p>
    <p>McConnell Institute 肩部與膝部疼痛處理認證課程</p>
    <p>Anatomy Trains in Motion: Myofascial Body Map for Movement 認證</p>

    <h2>治療理念</h2>
    <p>透過專業物理治療技術，分析每位患者的需求，提供量身訂製的治療計畫。希望透過一對一的專業治療，幫助患者減輕疼痛、恢復日常功能，達到最佳生活品質。</p>
</body>
</html>

<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>吳孟軒 - 成人物理治療師</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
        }
        .header h1 {
            margin: 10px 0;
            font-size: 24px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            font-size: 20px;
            border-bottom: 2px solid #007BFF;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .list {
            list-style: none;
            padding: 0;
        }
        .list li {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
        }
        .list li::before {
            content: '\2022';
            color: #007BFF;
            font-weight: bold;
            display: inline-block;
            width: 20px;
            margin-right: 10px;
        }
        .footer {
            font-size: 14px;
            color: #555;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="" alt="吳孟軒">
            <h1>吳孟軒</h1>
            <p>大重仁復健科診所 - 成人物理治療師</p>
        </div>

        <div class="section">
            <h2>專長</h2>
            <ul class="list">
                <li>徒手治療：筋肌膜疼痛、軟組織放鬆</li>
                <li>動作分析與訓練指導</li>
                <li>肌肉骨骼問題</li>
                <li>慢性下背痛</li>
                <li>環狀軟骨關節疼痛、落枕、五十肩等關節活動問題</li>
                <li>術後復健</li>
            </ul>
        </div>

        <div class="section">
            <h2>學歷</h2>
            <ul class="list">
                <li>長庚大學 物理治療學系 學士</li>
                <li>長庚大學 生物醫學學系 學士</li>
            </ul>
        </div>

        <div class="section">
            <h2>經歷</h2>
            <ul class="list">
                <li>大重仁復健科診所 成人物理治療師</li>
                <li>明台大學物理治療所 實習物理治療師</li>
            </ul>
        </div>

        <div class="section">
            <h2>專業認證與進修課程</h2>
            <ul class="list">
                <li>中華民國物理治療學會徒手治療專科學員</li>
                <li>McConnell Institute 肩部與膝部疼痛處理認證課程</li>
                <li>Anatomy Trains in Motion: Myofascial Body Map for Movement 認證</li>
            </ul>
        </div>

        <div class="section">
            <h2>治療理念</h2>
            <p>透過專業物理治療技術，分析每位患者的需求，提供量身訂製的治療計畫。希望透過一對一的專業治療，幫助患者減輕疼痛、恢復日常功能，達到最佳生活品質。</p>
        </div>
    </div>
    <div class="footer">
        <p>&copy; 2024 吳孟軒 - 成人物理治療師</p>
    </div>
</body>
</html>

		</html>
		<!DOCTYPE html>
		<html lang="zh-Hant">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
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



		<!-- A few words about-->
		<section class="section section-lg bg-default novi-bg novi-bg-img">
			<div class="container">
				<div class="row row-30 align-items-center justify-content-xxl-between">
					<div class="col-md-6">
						<h3>A few words about</h3>
						<p class="big">Every member of Pract team believes strongly in the empowering power of
							knowledge. And we aim
							to share our knowledge with everyone willing to learn.</p>
						<div class="row row-30 row-offset-1">
							<div class="col-md-10 offset-xxl-2">
								<div class="box-small">
									<h5 class="box-small-title">Best industry leaders</h5>
									<div class="box-small-text">Pract involves the best industry leaders, who share
										their experience,
										knowledge and tips with our students.</div>
								</div>
								<div class="box-small">
									<h5 class="box-small-title">Best teachers</h5>
									<div class="box-small-text">Our staff consists of professional experienced teachers,
										who love what
										they do and use creative approach in teaching.</div>
								</div>
								<div class="box-small">
									<h5 class="box-small-title">National awards</h5>
									<div class="box-small-text">Pract takes part in many IT educational contests. We won
										the National IT
										Progress Award 3 times.</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-lg-5 col-xl-4 offset-xl-1 col-xxl-5 position-relative d-none d-md-block">
						<div class="parallax-scene-js parallax-scene parallax-scene-1" data-scalar-x="5"
							data-scalar-y="10">
							<div class="layer-01">
								<div class="layer" data-depth="0.25"><img src="images/home-03-515x600.jpg" alt=""
										width="515" height="600" loading="lazy" />
								</div>
							</div>
							<div class="layer-02">
								<div class="layer" data-depth=".55"><img src="images/home-02-340x445.jpg" alt=""
										width="340" height="445" loading="lazy" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Popular courses-->
		<section class="section section-lg novi-bg novi-bg-img bg-default">
			<div class="container">
				<h3 class="text-center">Popular courses</h3>
				<div class="row row-40 row-lg-50">
					<div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".1s">
						<div class="box-project box-width-3">
							<div class="box-project-media"><img class="box-project-img" src="images/home-04-360x345.jpg"
									alt="" width="360" height="345" loading="lazy" />
							</div>
							<div class="box-project-subtitle small">Business</div>
							<h6 class="box-project-title"><a href="single-course.html">Development of your business
									skills</a></h6>
							<div class="box-project-meta">
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-clock3"></span><span
										class="box-project-meta-text">23 hours</span></div>
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-credit-card"></span><span
										class="box-project-meta-text">$165</span></div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".2s">
						<div class="box-project box-width-3">
							<div class="box-project-media"><img class="box-project-img" src="images/home-05-360x345.jpg"
									alt="" width="360" height="345" loading="lazy" />
							</div>
							<div class="box-project-subtitle small">PROGRAMMING</div>
							<h6 class="box-project-title"><a href="single-course.html">Python programming</a></h6>
							<div class="box-project-meta">
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-clock3"></span><span
										class="box-project-meta-text">46 hours</span></div>
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-credit-card"></span><span
										class="box-project-meta-text">$290</span></div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".3s">
						<div class="box-project box-width-3">
							<div class="box-project-media"><img class="box-project-img" src="images/home-06-360x345.jpg"
									alt="" width="360" height="345" loading="lazy" />
							</div>
							<div class="box-project-subtitle small">Programming</div>
							<h6 class="box-project-title"><a href="single-course.html">Data structures and
									algorithms</a></h6>
							<div class="box-project-meta">
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-clock3"></span><span
										class="box-project-meta-text">18 hours</span></div>
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-credit-card"></span><span
										class="box-project-meta-text">$120</span></div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".1s">
						<div class="box-project box-width-3">
							<div class="box-project-media"><img class="box-project-img" src="images/home-07-360x345.jpg"
									alt="" width="360" height="345" loading="lazy" />
							</div>
							<div class="box-project-subtitle small">ARCHITECTURE, ART &amp; DESIGN</div>
							<h6 class="box-project-title"><a href="single-course.html">Global housing design</a></h6>
							<div class="box-project-meta">
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-clock3"></span><span
										class="box-project-meta-text">34 hours</span></div>
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-credit-card"></span><span
										class="box-project-meta-text">$195</span></div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".2s">
						<div class="box-project box-width-3">
							<div class="box-project-media"><img class="box-project-img" src="images/home-08-360x345.jpg"
									alt="" width="360" height="345" loading="lazy" />
							</div>
							<div class="box-project-subtitle small">Business</div>
							<h6 class="box-project-title"><a href="single-course.html">IT fundamentals for business</a>
							</h6>
							<div class="box-project-meta">
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-clock3"></span><span
										class="box-project-meta-text">8 hours</span></div>
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-credit-card"></span><span
										class="box-project-meta-text">$75</span></div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-4 wow fadeInUp" data-wow-delay=".3s">
						<div class="box-project box-width-3">
							<div class="box-project-media"><img class="box-project-img" src="images/home-09-360x345.jpg"
									alt="" width="360" height="345" loading="lazy" />
							</div>
							<div class="box-project-subtitle small">Marketing</div>
							<h6 class="box-project-title"><a href="single-course.html">Online marketing: SEO and SMM</a>
							</h6>
							<div class="box-project-meta">
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-clock3"></span><span
										class="box-project-meta-text">43 hours</span></div>
								<div class="box-project-meta-item"><span
										class="box-project-meta-icon linearicons-credit-card"></span><span
										class="box-project-meta-text">$145</span></div>
							</div>
						</div>
					</div>
					<div class="col-12 text-center mt-2 mt-xl-4"><a class="button button-primary button-nina"
							href="courses.html">All courses</a></div>
				</div>
			</div>
		</section>
		<!-- A few words about-->
		<section class="section section-lg bg-default novi-bg novi-bg-img">
			<div class="container">
				<div class="row row-30 justify-content-md-between align-items-center">
					<div class="col-md-5 position-relative d-none d-md-block">
						<div class="parallax-scene-js parallax-scene parallax-scene-2" data-scalar-x="5"
							data-scalar-y="10">
							<div class="layer-01">
								<div class="layer" data-depth="0.25"><img src="images/home-17-460x580.jpg" alt=""
										width="460" height="580" loading="lazy" />
								</div>
							</div>
							<div class="layer-02">
								<div class="layer" data-depth=".55"><img src="images/home-18-260x355.jpg" alt=""
										width="260" height="355" loading="lazy" />
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row row-30 row-xxl-60 text-center text-md-start">
							<div class="col-6">
								<div class="counter-wrap"><span class="icon novi-icon linearicons-users2"></span>
									<div class="counter-value heading-3"><span class="counter">3045</span><span
											class="counter-preffix">+</span>
									</div>
									<p class="heading-5">Satisfied students</p>
								</div>
							</div>
							<div class="col-6">
								<div class="counter-wrap"><span class="icon novi-icon linearicons-book"></span>
									<div class="counter-value heading-3"><span class="counter">7852</span><span
											class="counter-preffix">+</span>
									</div>
									<p class="heading-5">Available courses</p>
								</div>
							</div>
							<div class="col-6">
								<div class="counter-wrap"><span class="icon novi-icon linearicons-medal-empty"></span>
									<div class="counter-value heading-3"><span class="counter">9862</span><span
											class="counter-preffix">+</span>
									</div>
									<p class="heading-5">Graduates</p>
								</div>
							</div>
							<div class="col-6">
								<div class="counter-wrap"><span class="icon novi-icon linearicons-star"></span>
									<div class="counter-value heading-3"><span class="counter">145</span><span
											class="counter-preffix">+</span>
									</div>
									<p class="heading-5">Best teachers</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Web development-->
		<section class="section section-lg novi-bg novi-bg-img bg-default">
			<div class="container">
				<div class="row row-40 row-lg-50">
					<div class="col-sm-6 col-md-4">
						<div class="list-border">
							<h4 class="list-border-title">診療地點:新北市三重區重新路五段592號</h4>

		</section>
		<!-- Awesome & skilled instructors-->
		<section class="section section-lg novi-bg novi-bg-img bg-default">
			<div class="container">
				<h3 class="text-center">Awesome & skilled instructors</h3>
				<div class="row row-40 row-lg-50">
					<div class="col-sm-6 col-lg-3 wow fadeInLeft" data-wow-delay=".2s">
						<div class="team-default box-width-3">
							<div class="team-default-media"><img class="team-default-img"
									src="images/home-10-260x345.jpg" alt="" width="260" height="345" loading="lazy" />
							</div>
							<h6 class="team-default-title"><a href="single-teacher.html">Theresa Webb</a></h6>
							<div class="team-default-meta small">Web design instructor</div>
							<div class="team-default-social"><a class="team-default-icon icon-custom-facebook"
									href="#"></a><a class="team-default-icon icon-custom-linkedin" href="#"></a><a
									class="team-default-icon icon-custom-instagram" href="#"></a></div>
						</div>
					</div>
					<div class="col-sm-6 col-lg-3 wow fadeInLeft">
						<div class="team-default box-width-3">
							<div class="team-default-media"><img class="team-default-img"
									src="images/home-11-260x345.jpg" alt="" width="260" height="345" loading="lazy" />
							</div>
							<h6 class="team-default-title"><a href="single-teacher.html">Jenny Wilson</a></h6>
							<div class="team-default-meta small">Web development instructor</div>
							<div class="team-default-social"><a class="team-default-icon icon-custom-facebook"
									href="#"></a><a class="team-default-icon icon-custom-linkedin" href="#"></a><a
									class="team-default-icon icon-custom-instagram" href="#"></a></div>
						</div>
					</div>
					<div class="col-sm-6 col-lg-3 wow fadeInRight">
						<div class="team-default box-width-3">
							<div class="team-default-media"><img class="team-default-img"
									src="images/home-12-260x345.jpg" alt="" width="260" height="345" loading="lazy" />
							</div>
							<h6 class="team-default-title"><a href="single-teacher.html">Jacob Jones</a></h6>
							<div class="team-default-meta small">Cybersecurity &amp; IT instructor</div>
							<div class="team-default-social"><a class="team-default-icon icon-custom-facebook"
									href="#"></a><a class="team-default-icon icon-custom-linkedin" href="#"></a><a
									class="team-default-icon icon-custom-instagram" href="#"></a></div>
						</div>
					</div>
					<div class="col-sm-6 col-lg-3 wow fadeInRight" data-wow-delay=".2s">
						<div class="team-default box-width-3">
							<div class="team-default-media"><img class="team-default-img"
									src="images/home-13-260x345.jpg" alt="" width="260" height="345" loading="lazy" />
							</div>
							<h6 class="team-default-title"><a href="single-teacher.html">Bessie Cooper</a></h6>
							<div class="team-default-meta small">PM &amp; business instructor</div>
							<div class="team-default-social"><a class="team-default-icon icon-custom-facebook"
									href="#"></a><a class="team-default-icon icon-custom-linkedin" href="#"></a><a
									class="team-default-icon icon-custom-instagram" href="#"></a></div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Success stories from our students-->
		<section class="section section-lg novi-bg novi-bg-img bg-gray-100">
			<div class="container">
				<div class="row justify-content-center text-center">
					<div class="col-xl-10">
						<h3>Success stories from our students</h3>
						<p class="big">Feel free to learn more about our courses from our students and the trustworthy
							testimonials
							they have recently submitted.</p>
					</div>
				</div>
				<div class="owl-carousel owl-layout-6" data-items="1" data-lg-items="2" data-loop="true"
					data-margin="40" data-autoplay="true" data-owl='{"nav":true}'>
					<div class="quote-boxed-2">
						<div class="quote-boxed-2-media"><img class="quote-boxed-2-img" src="images/home-14-86x86.jpg"
								alt="" width="86" height="86" loading="lazy" />
						</div>
						<div class="quote-boxed-2-body">
							<h6 class="quote-boxed-2-title">Easy-to-understand courses</h6>
							<div class="quote-boxed-2-text">The material that instructors of Pract use is pretty
								straightforward yet
								quite detailed. It’s also very useful to both newbies and professionals. You can choose
								any course to
								get started.</div>
							<div class="quote-boxed-2-cite small">Leslie Alexander</div>
						</div>
					</div>
					<div class="quote-boxed-2">
						<div class="quote-boxed-2-media"><img class="quote-boxed-2-img" src="images/home-15-86x86.jpg"
								alt="" width="86" height="86" loading="lazy" />
						</div>
						<div class="quote-boxed-2-body">
							<h6 class="quote-boxed-2-title">A wide selection of courses</h6>
							<div class="quote-boxed-2-text">I prefer the range of courses offerd by this online school
								to any other
								website. The team of Pract regularly updates their courses list and provides them at a
								very affordable
								price.</div>
							<div class="quote-boxed-2-cite small">Jane Cooper</div>
						</div>
					</div>
					<div class="quote-boxed-2">
						<div class="quote-boxed-2-media"><img class="quote-boxed-2-img" src="images/home-16-86x86.jpg"
								alt="" width="86" height="86" loading="lazy" />
						</div>
						<div class="quote-boxed-2-body">
							<h6 class="quote-boxed-2-title">A great knowledge source</h6>
							<div class="quote-boxed-2-text">As a web developer, I’m always trying to learn more about
								new development
								tips and tricks. Your online courses provided me with this unique possibility. Thank
								you!</div>
							<div class="quote-boxed-2-cite small">James wilson</div>
						</div>
					</div>
					<div class="quote-boxed-2">
						<div class="quote-boxed-2-media"><img class="quote-boxed-2-img" src="images/home-19-86x86.jpg"
								alt="" width="86" height="86" loading="lazy" />
						</div>
						<div class="quote-boxed-2-body">
							<h6 class="quote-boxed-2-title">#1 knowledge solution</h6>
							<div class="quote-boxed-2-text">The online courses allow greater flexibility in planning
								your day and
								working around family. With Pract, you can also be sure of improving your professional
								level.</div>
							<div class="quote-boxed-2-cite small">Peter Adams</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<section class="section section-md bg-accent text-center text-md-start">
			<div class="container">
				<div class="row">
					<div class="col-xl-11">
						<div class="box-cta box-cta-inline">
							<div class="box-cta-inner">
								<h3 class="box-cta-title"><span
										class="box-cta-icon icon-custom-briefcase"></span><span>Free
										courses</span></h3>
								<p>Free courses contain industry-relevant content and practical tasks and projects.</p>
							</div>
							<div class="box-cta-inner"><a class="button button-dark button-nina"
									href="contacts.html">Contact us</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
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