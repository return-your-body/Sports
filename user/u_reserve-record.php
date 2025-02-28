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

		/* 歷史紀錄 */
		body {
			font-family: Arial, sans-serif;
			background-color: #f9f9f9;
			margin: 0;
			padding: 0;
		}

		h2 {
			text-align: center;
			margin-top: 20px;
		}

		table {
			width: 80%;
			margin: 20px auto;
			border-collapse: collapse;
			background-color: #fff;
			box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
			overflow-x: auto;
		}

		th,
		td {
			border: 1px solid #ddd;
			padding: 10px;
			text-align: center;
			white-space: nowrap;
		}

		th {
			background-color: #f4f4f4;
			font-weight: bold;
		}

		p {
			text-align: center;
			margin-top: 20px;
			color: #555;
		}

		/* 響應式樣式 */
		@media (max-width: 768px) {
			table {
				width: 100%;
				font-size: 14px;
			}

			th,
			td {
				padding: 8px;
				font-size: 12px;
				white-space: normal;
				/* 允許文字換行 */
			}
		}

		@media (max-width: 480px) {
			table {
				font-size: 12px;
			}

			th,
			td {
				padding: 6px;
			}
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
								<li class="rd-nav-item "><a class="rd-nav-link" href="u_index.php">首頁</a>
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
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link active" href="u_reserve.php">預約</a></li>
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

		<!--標題-->
		<div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
			<!-- Breadcrumbs-->
			<section class="breadcrumbs-custom breadcrumbs-custom-svg">
				<div class="container">
					<!-- <p class="breadcrumbs-custom-subtitle">What We Offer</p> -->
					<p class="heading-1 breadcrumbs-custom-title">查看預約資料</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="u_index.php">首頁</a></li>
						<li><a href="#">預約</a></li>
						<li class="active">查看預約資料</li>
					</ul>
				</div>
			</section>
		</div>

		<!-- 預約資料 -->
		<section class="section section-lg bg-default novi-bg novi-bg-img">
			<div class="container">
				<div class="row row-30 align-items-center justify-content-xxl-between">
					<div class="col-md-10">
						<?php
						require '../db.php'; // 引入資料庫連接
						
						if (!isset($_SESSION['帳號'])) {
							die("請先登入");
						}

						$帳號 = $_SESSION['帳號']; // 取得登入帳號
						
						// 查詢該使用者的所有預約資料
						$query = "
						SELECT 
							a.appointment_id, 
							COALESCE(p.name, '未知') AS patient_name, -- 患者姓名
							CASE 
								WHEN p.gender_id = 1 THEN '男' 
								WHEN p.gender_id = 2 THEN '女' 
								ELSE '未知' 
							END AS gender, -- 性別
							COALESCE(p.birthday, '未知') AS birthday, -- 生日
							COALESCE(ds.date, '未知') AS appointment_date, -- 預約日期
							COALESCE(st.shifttime, '未設定') AS shifttime, -- 預約時間段
							COALESCE(a.note, '無備註') AS note -- 備註
						FROM appointment a
						LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id -- 關聯班表
						LEFT JOIN shifttime st ON a.shifttime_id = st.shifttime_id -- 關聯時段
						LEFT JOIN people p ON a.people_id = p.people_id -- 關聯患者
						LEFT JOIN user u ON p.user_id = u.user_id -- 關聯使用者
						WHERE u.account = ?
						ORDER BY a.appointment_id DESC";

						$stmt = mysqli_prepare($link, $query);
						if (!$stmt) {
							die("SQL 錯誤：" . mysqli_error($link));
						}

						// 綁定參數並執行查詢
						mysqli_stmt_bind_param($stmt, 's', $帳號);
						mysqli_stmt_execute($stmt);
						$result = mysqli_stmt_get_result($stmt);
						?>
						<?php if (mysqli_num_rows($result) > 0): ?>
							<?php while ($row = mysqli_fetch_assoc($result)): ?>
								<table>
									<tr>
										<th>姓名</th>
										<td><?php echo htmlspecialchars($row['patient_name']); ?></td>
									</tr>
									<tr>
										<th>性別</th>
										<td><?php echo htmlspecialchars($row['gender']); ?></td>
									</tr>
									<tr>
										<th>生日</th>
										<td><?php echo htmlspecialchars($row['birthday']); ?></td>
									</tr>
									<tr>
										<th>預約日期</th>
										<td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
									</tr>
									<tr>
										<th>預約時間段</th>
										<td><?php echo htmlspecialchars($row['shifttime']); ?></td>
									</tr>
									<tr>
										<th>備註</th>
										<td><?php echo htmlspecialchars($row['note']); ?></td>
									</tr>
								</table>
							<?php endwhile; ?>
						<?php else: ?>
							<p>目前無預約記錄</p>
						<?php endif; ?>
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
					<div class="col-md-3">
						<h4>快速連結</h4>
						<ul class="list-marked">
							<li><a href="u_index.php">首頁</a></li>
							<li><a href="u_link.php.php">治療師介紹</a></li>
							<li><a href="u_caseshare.php">個案分享</a></li>
							<li><a href="u_body-knowledge.php">日常小知識</a></li>
							<li><a href="u_reserve.php">預約</a></li>
							<!-- <li><a href="u_reserve-record.php">查看預約資料</a></li>
							<li><a href="u_reserve-time.php">查看預約時段</a></li> -->
							<li><a href="u_history.php">歷史紀錄</a></li>
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