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



//班表

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


		/* 治療師班表 */
		/* 電腦端樣式（保留原始設計） */
		.table-container {
			overflow-x: hidden;
			margin-top: 10px;
		}

		/* 手機端樣式（針對寬度小於768px的裝置） */
		@media screen and (max-width: 768px) {
			.table-container {
				overflow-x: auto;
				/* 啟用橫向滾動 */
			}

			.table-custom {
				width: 1200px;
				/* 設定寬度大於手機螢幕，讓用戶可以滑動 */
				min-width: 768px;
				/* 最小寬度，防止文字壓縮 */
			}

			.table-custom th,
			.table-custom td {
				white-space: nowrap;
				/* 禁止文字換行 */
				overflow: hidden;
				/* 隱藏超出部分 */
				text-overflow: ellipsis;
				/* 顯示省略號 */
			}
		}


		/* 表頭樣式 */
		.table-custom th {
			background-color: #00a79d;
			color: white;
			font-weight: bold;
			text-align: center;
		}




		/* 聯絡我們 */
		.custom-link {
			color: rgb(246, 247, 248);
			/* 設定超連結顏色 */
			text-decoration: none;
			/* 移除超連結的下劃線 */
		}

		.custom-link:hover {
			color: #0056b3;
			/* 滑鼠懸停時的顏色，例如深藍色 */
			text-decoration: underline;
			/* 懸停時增加下劃線效果 */
		}

		/* 天氣API */

		.weather-container {
			max-width: 600px;
			margin: 0 auto;
			background: white;
			padding: 20px;
			border-radius: 10px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		}

		.weather-container h1 {
			text-align: center;
			color: #333;
		}

		.weather-container p {
			margin: 10px 0;
			color: #555;
		}

		.advice {
			margin-top: 20px;
			padding: 15px;
			background: #f9f9f9;
			border-left: 5px solid #007bff;
			border-radius: 5px;
		}

		.advice h2 {
			margin: 0;
			font-size: 18px;
			color: #007bff;
		}

		.advice ul {
			list-style: none;
			padding: 0;
			margin: 10px 0;
		}

		.advice ul li {
			margin: 5px 0;
		}

		.error {
			color: red;
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
								<li class="rd-nav-item active"><a class="rd-nav-link" href="u_index.php">首頁</a>
								</li>

								<li class="rd-nav-item"><a class="rd-nav-link" href="#">關於我們</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_link.php">治療師介紹</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_caseshare.php">個案分享</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_body-knowledge.php">日常小知識</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_good+1.php">好評再+1</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="u_reserve.php">預約</a></li>
								<!-- <li class="rd-nav-item"><a class="rd-nav-link active" href="#">預約</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_reserve.php">立即預約</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_reserve-record.php">查看預約資料</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="u_reserve-time.php">查看預約時段</a>
										</li>
									</ul>
								</li> -->
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
								<li class="rd-nav-item"><a class="rd-nav-link" href="u_change.php">變更密碼</a>
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
						echo "歡迎 ~ ";
						// 顯示姓名
						echo $姓名;
						?>

						<a href="#" id="clock-btn">目前叫號</a>

						<style>
							table {
								width: 60%;
								border-collapse: collapse;
								margin: 20px auto;
							}

							th,
							td {
								border: 1px solid black;
								padding: 10px;
								text-align: center;
							}

							th {
								background-color: #f4f4f4;
							}

							/* 讓按鈕置中 */
							.center-button {
								text-align: center;
								margin-top: 20px;
							}

							button {
								padding: 10px 20px;
								font-size: 16px;
								cursor: pointer;
							}

							/* 彈跳視窗樣式 */
							.modal {
								display: none;
								position: fixed;
								z-index: 1;
								left: 0;
								top: 0;
								width: 100%;
								height: 100%;
								background-color: rgba(0, 0, 0, 0.5);
							}

							.modal-content {
								background-color: white;
								margin: 10% auto;
								padding: 20px;
								border: 1px solid #888;
								width: 50%;
								text-align: center;
							}

							.close-btn {
								color: black;
								float: right;
								font-size: 24px;
								font-weight: bold;
								cursor: pointer;
							}
						</style>

						<!-- 叫號狀態彈跳視窗 -->
						<div id="callModal" class="modal">
							<div class="modal-content">
								<span class="close-btn">&times;</span>
								<h2>目前叫號狀態</h2>
								<table>
									<tr>
										<th>病人姓名</th>
										<th>治療師</th>
										<th>時段</th>
									</tr>
									<tr id="callRow">
										<td id="patient_name">載入中...</td>
										<td id="therapist">載入中...</td>
										<td id="shifttime">載入中...</td>
									</tr>
								</table>

								<!-- 讓返回按鈕置中 -->
								<!-- <div class="center-button">
									<a href="h_numberpeople.php">
										<button>返回</button>
									</a>
								</div> -->
							</div>
						</div>

						<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
						<script>
							$(document).ready(function () {
								function fetchCallStatus() {
									$.ajax({
										url: "取得正在叫號的病人.php",
										type: "GET",
										dataType: "json",
										success: function (data) {
											if (data.length > 0) {
												$("#patient_name").text(data[0].patient_name || "無資料");
												$("#therapist").text(data[0].therapist || "無資料");
												$("#shifttime").text(data[0].shifttime || "無資料");
											} else {
												$("#patient_name").text("目前無叫號中");
												$("#therapist").text("-");
												$("#shifttime").text("-");
											}
										},
										error: function () {
											$("#patient_name").text("載入失敗");
											$("#therapist").text("-");
											$("#shifttime").text("-");
										}
									});
								}

								// 每 5 秒自動更新叫號狀態
								fetchCallStatus();
								setInterval(fetchCallStatus, 5000);

								// 打開彈跳視窗
								$("#clock-btn").click(function () {
									$("#callModal").fadeIn();
								});

								// 關閉彈跳視窗
								$(".close-btn").click(function () {
									$("#callModal").fadeOut();
								});

								// 點擊視窗外部也能關閉
								$(window).click(function (event) {
									if (event.target.id === "callModal") {
										$("#callModal").fadeOut();
									}
								});
							});
						</script>



					</div>
				</nav>
			</div>
		</header>



		<!--Welcome back user-->
		<section class="section section-xl bg-image bg-image-9 text-center novi-bg novi-bg-img">
			<div class="container">
				<!-- <h1>Welcome back user-<?php echo $姓名; ?></h1> -->
				<h1 class="fw-light text-uppercase">運動筋膜放鬆</h1>
				<p class="big">
					''運動筋膜放鬆在做什麼？''<br />
					✨運動筋膜放鬆是透過治療師的牽引將您的身體姿勢擺到適當的位置✨<br />
					✨還原回到它自己該在的位置而達到放鬆✨</p>
				<div class="group-md button-group">
					<a class="button button-dark button-nina" href="u_reserve.php">立即預約</a>
					<a class="button button-dark button-nina" href="u_link.php">治療師介紹</a>
				</div>
			</div>
		</section>
		<!--Welcome back doctor-->
		<div>
			<marquee>
				您好！目前任職於三重的「大重仁復健科診所」。由於治療師的上班時間每月不同，歡迎來電或留言詢問預約時段。感謝您的配合！
			</marquee>
		</div>

		<br />

		<!-- 天氣API -->
		<div class="weather-container">
			<?php
			$apiKey = "857208d7d30e1cacd0cfeebeb29f0f60";
			$city = "Taipei";
			$apiUrl = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$apiKey}&units=metric&lang=zh_tw";

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $apiUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			curl_close($ch);

			$weatherData = json_decode($response, true);

			if (isset($weatherData['cod']) && $weatherData['cod'] == 200) {
				$cityName = $weatherData['name'];
				$temperature = $weatherData['main']['temp'];
				$weatherDescription = $weatherData['weather'][0]['description'];
				$humidity = $weatherData['main']['humidity'];
				$windSpeed = $weatherData['wind']['speed'];
				?>

				<h3 style="text-align: center;">今日天氣</h3><!--<?php echo $cityName; ?> -->
				<p>溫度：<strong><?php echo $temperature; ?>°C</strong></p>
				<p>天氣狀況：<strong><?php echo $weatherDescription; ?></strong></p>
				<p>濕度：<strong><?php echo $humidity; ?>%</strong></p>
				<p>風速：<strong><?php echo $windSpeed; ?> m/s</strong></p>

				<div class="advice">
					<h2>筋膜按摩建議</h2>
					<ul>
						<?php
						// 按溫度條件
						if ($temperature < 15) {
							echo "- 天氣寒冷，建議先使用暖身墊加熱肩頸和下背部，再進行深層筋膜按摩。<br>";
							echo "- 可重點放鬆膝關節周圍的筋膜，預防僵硬。<br>";
							echo "- 按摩後穿著保暖衣物，避免按摩後肌肉受寒。<br>";
						} elseif ($temperature >= 15 && $temperature < 25) {
							echo "- 涼爽天氣，適合進行全身筋膜放鬆，搭配深層呼吸放鬆效果更佳。<br>";
							echo "- 建議針對腿部和小腿，進行滾筒按摩，改善長時間靜止的僵硬感。<br>";
							echo "- 可加入簡單的伸展運動，例如貓式伸展配合按摩。<br>";
						} elseif ($temperature >= 25) {
							echo "- 溫暖天氣，適合使用筋膜槍進行局部放鬆，例如肩膀和手臂。<br>";
							echo "- 建議搭配步行或簡單運動後按摩效果更佳。<br>";
							echo "- 注意補充水分，提升筋膜按摩的代謝效果。<br>";
						}

						// 按濕度條件
						if ($humidity > 70) {
							echo "- 濕度較高，容易感到疲勞，建議加強背部和肩頸的筋膜按摩。<br>";
							echo "- 可搭配精油進行放鬆按摩，有助於緩解濕重的不適感。<br>";
						} elseif ($humidity < 50) {
							echo "- 濕度偏低，建議加強補水，搭配滾筒按摩改善筋膜的彈性。<br>";
						}

						// 按天氣描述
						if (strpos($weatherDescription, "雨") !== false) {
							echo "- 下雨天建議在室內進行全身放鬆按摩，重點針對下背部和膝關節。<br>";
							echo "- 可使用熱敷配合滾筒，減少潮濕對肌肉的影響。<br>";
						} elseif (strpos($weatherDescription, "晴") !== false) {
							echo "- 晴天適合搭配戶外運動後進行筋膜放鬆，重點舒緩腿部肌群。<br>";
							echo "- 使用筋膜槍針對運動後的僵硬部位，例如小腿和大腿外側。<br>";
						} elseif (strpos($weatherDescription, "雲") !== false) {
							echo "- 多雲天建議針對肩頸進行適度的深層按摩，放鬆緊繃感。<br>";
						}

						// 按風速條件
						if ($windSpeed > 10) {
							echo "- 大風天氣可能增加肩頸緊繃感，建議重點按摩肩膀和上背部。<br>";
							echo "- 可使用滾筒進行輕壓，配合熱敷效果更佳。<br>";
						}
			} else {
				// 如果獲取數據失敗，顯示錯誤信息
				echo "無法獲取天氣資訊，請檢查 API 設定或城市名稱。";
			}
			?>
				</ul>
			</div>
		</div>



		<br />
		<style>
			/* 讓整個內容區塊在頁面中水平置中 */
			.wrapper {
				display: flex;
				justify-content: center;
				/* 水平置中 */
				align-items: center;
				flex-direction: column;
				/* 讓內容垂直排列 */
				width: 100%;
			}

			/* 貼文容器：使用 Grid 讓內容整齊排列 */
			.post-container {
				display: grid;
				grid-template-columns: repeat(3, 1fr);
				/* 預設桌機版：一行三個 */
				gap: 20px;
				/* 貼文之間的間距 */
				max-width: 1200px;
				/* 限制最大寬度 */
				width: 100%;
				/* 讓它隨畫面縮放 */
				padding: 20px;
				justify-content: center;
				/* 這樣 Grid 內的元素也會對齊 */
			}

			/* 貼文區塊樣式 */
			.post-item {
				text-align: center;
				border: 1px solid #ddd;
				padding: 15px;
				border-radius: 8px;
				background-color: #fff;
				/* 確保貼文有背景顏色 */
			}

			/* 圖片樣式（加上連結） */
			.post-item a img {
				width: 100%;
				height: 200px;
				object-fit: cover;
				border-radius: 8px;
				transition: transform 0.2s ease-in-out;
			}

			.post-item a img:hover {
				transform: scale(1.05);
				/* 滑鼠移上去圖片放大 */
			}

			/* 貼文標題 */
			.post-title {
				font-size: 16px;
				font-weight: bold;
				margin-top: 10px;
			}

			/* 貼文標題連結 */
			.post-title a {
				text-decoration: none;
				color: #007bff;
			}

			.post-title a:hover {
				text-decoration: underline;
			}

			/* 內文過長時省略 */
			.post-desc {
				font-size: 14px;
				color: #555;
				margin-top: 5px;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
			}

			/* 分頁按鈕 */
			.pagination {
				text-align: center;
				margin-top: 20px;
				width: 100%;
				display: flex;
				justify-content: center;
				/* 讓分頁按鈕置中 */
			}

			/* 分頁按鈕樣式 */
			.pagination a {
				text-decoration: none;
				padding: 8px 12px;
				margin: 5px;
				border: 1px solid #007bff;
				color: #007bff;
				border-radius: 5px;
			}

			.pagination a:hover {
				background-color: #007bff;
				color: #fff;
			}

			/* 當前頁面按鈕樣式 */
			.pagination .current-page {
				padding: 8px 12px;
				margin: 5px;
				border: 1px solid #007bff;
				background-color: #007bff;
				color: white;
				border-radius: 5px;
			}

			/* 🔹 媒體查詢 (Media Queries) - 調整貼文數量 🔹 */

			/* 平板模式 (最大 1024px)：一行顯示 2 個 */
			@media (max-width: 1024px) {
				.post-container {
					grid-template-columns: repeat(2, 1fr);
				}
			}

			/* 手機模式 (最大 768px)：一行顯示 1 個 */
			@media (max-width: 768px) {
				.post-container {
					grid-template-columns: repeat(1, 1fr);
				}
			}
		</style>

		<?php
		require '../db.php'; // 連接資料庫
		
		// 定義要顯示的 igpost_class_id 及對應標題
		$categories = [
			1 => "個案分享",
			2 => "日常小知識",
			3 => "好評再+1"
		];

		$posts_per_class = 6; // 每個類別顯示 6 筆資料
		
		?>

		<section class="section section-lg bg-default text-center">
			<div class="container">
				<div class="row justify-content-sm-center">
					<div class="col-md-10 col-xl-8">
						<div class="wrapper">

							<?php foreach ($categories as $class_id => $title): ?>
								<h2 class="category-title"><?php echo $title; ?></h2> <!-- 顯示大標題 -->

								<div class="post-container">
									<?php
									// 取得該分類的最新 6 筆貼文
									$query = "SELECT image_data, title, description, embed_code 
                                      FROM instagram_posts 
                                      WHERE igpost_class_id = ? 
                                      ORDER BY created_at DESC 
                                      LIMIT ?";
									$stmt = mysqli_prepare($link, $query);
									mysqli_stmt_bind_param($stmt, "ii", $class_id, $posts_per_class);
									mysqli_stmt_execute($stmt);
									$result = mysqli_stmt_get_result($stmt);

									while ($row = mysqli_fetch_assoc($result)): ?>
										<div class="post-item">
											<a href="<?php echo htmlspecialchars($row['embed_code']); ?>" target="_blank">
												<img src="data:image/jpeg;base64,<?php echo base64_encode($row['image_data']); ?>"
													alt="Instagram Image">
											</a>
											<div class="post-title">
												<a href="<?php echo htmlspecialchars($row['embed_code']); ?>" target="_blank">
													<?php echo htmlspecialchars($row['title']); ?>
												</a>
											</div>
											<div class="post-desc">
												<?php echo mb_strimwidth(htmlspecialchars($row['description']), 0, 50, "..."); ?>
											</div>
										</div>
									<?php endwhile; ?>

									<?php mysqli_stmt_close($stmt); ?>
								</div>
							<?php endforeach; ?>

						</div>
					</div>
				</div>
			</div>
		</section>


		<footer class="section novi-bg novi-bg-img footer-simple">
			<div class="container">
				<div class="row row-40">
					<div class="col-md-4">
						<h4>關於我們</h4>
						<ul class="list-inline" style="font-size: 40px; display: inline-block;color: #333333; ">
							<li><a class="icon novi-icon icon-default icon-custom-facebook"
									href="https://www.facebook.com/ReTurnYourBody/" target="_blank"></a></li>
							<li><a class="icon novi-icon icon-default icon-custom-linkedin"
									href="https://lin.ee/sUaUVMq" target="_blank"></a></li>
							<li><a class="icon novi-icon icon-default icon-custom-instagram"
									href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"
									target="_blank"></a></li>
						</ul>

					</div>
					<div class="col-md-4">
						<h4>快速連結</h4>
						<ul class="list-marked">
							<li><a href="u_index.php">首頁</a></li>
							<li><a href="u_link.php.php">治療師介紹</a></li>
							<li><a href="u_caseshare.php">個案分享</a></li>
							<li><a href="u_body-knowledge.php">日常小知識</a></li>
							<li> <a href="u_good+1.php">好評再+1</a></li>
							<li><a href="u_reserve.php">預約</a></li>
							<!-- <li><a href="u_reserve-record.php">查看預約資料</a></li>
							<li><a href="u_reserve-time.php">查看預約時段</a></li> -->
							<li><a href="u_history.php">歷史紀錄</a></li>
							<li><a href="u_change.php">變更密碼</a></li>
							<li> <a href="u_profile.php">個人資料</a></li>
							</a></li>
						</ul>
					</div>


					<div class="col-md-4">
						<h4>聯絡我們</h4>
						<br />
						<ul>
							<li>📍 <strong>診療地點:</strong>大重仁骨科復健科診所</li><br />
							<li>📍 <strong>地址:</strong>
								<a href="https://maps.app.goo.gl/u3TojSMqjGmdx5Pt5" class="custom-link" target="_blank"
									rel="noopener noreferrer">
									241 新北市三重區重新路五段 592 號
								</a>
							</li>
							<br />
							<li>📍 <strong>電話:</strong>(02) 2995-8283</li>
						</ul>

						<!-- <form class="rd-mailform rd-form-boxed" data-form-output="form-output-global"
							data-form-type="subscribe" method="post" action="bat/rd-mailform.php">
							<div class="form-wrap">
								<input class="form-input" type="email" name="email" data-constraints="@Email @Required"
									id="footer-mail">
								<label class="form-label" for="footer-mail">請輸入您的電子郵件</label>
							</div>
							<button class="form-button linearicons-paper-plane"></button>
						</form> -->
					</div>
				</div>
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