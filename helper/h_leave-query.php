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

// **檢查登入狀態**
if (!isset($_SESSION["帳號"])) {
	echo "<script>
            alert('會話過期或資料遺失，請重新登入。');
            window.location.href = '../index.html';
          </script>";
	exit();
}

$帳號 = $_SESSION['帳號'];

// **連接資料庫**
require '../db.php';

// **獲取 doctor_id，確保只查詢 grade_id = 3 的使用者**
$sql = "SELECT d.doctor_id, d.doctor 
        FROM user u
        JOIN doctor d ON u.user_id = d.user_id
        WHERE u.account = ? AND u.grade_id = 3";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "s", $帳號);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
	$doctor_id = $row['doctor_id'];
	$doctor_name = $row['doctor'];
} else {
	echo "<script>
            alert('找不到符合條件的醫生資料，或您無權限訪問此頁面。');
            window.location.href = '../index.html';
          </script>";
	exit();
}

// **Debug: 確認 doctor_id**
echo "<!-- DEBUG: doctor_id = " . $doctor_id . " -->";

// **分頁變數**
$limit = 10; // 每頁顯示的資料筆數
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// **查詢該醫生的請假資料**
$total_query = "SELECT COUNT(*) AS total FROM leaves WHERE doctor_id = ?";
$stmt = mysqli_prepare($link, $total_query);
mysqli_stmt_bind_param($stmt, "i", $doctor_id);
mysqli_stmt_execute($stmt);
$total_result = mysqli_stmt_get_result($stmt);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ($total_records > 0) ? ceil($total_records / $limit) : 1; // 避免除以 0

// **Debug: 檢查有多少請假紀錄**
echo "<!-- DEBUG: total_records = " . $total_records . " -->";

// **查詢請假記錄**
$query = "SELECT leaves_id, leave_type, leave_type_other, start_date, end_date, 
                 reason, is_approved, rejection_reason 
          FROM leaves 
          WHERE doctor_id = ? 
          ORDER BY start_date DESC 
          LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "iii", $doctor_id, $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// **Debug: 檢查查詢結果**
echo "<!-- DEBUG: result rows = " . mysqli_num_rows($result) . " -->";

?>



<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
	<!-- Site Title-->
	<title>助手-請假查詢</title>
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


		/* 請假查詢表格樣式 */
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
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_leave.php">請假申請</a>
										</li>
										<li class="rd-dropdown-item active"><a class="rd-dropdown-link"
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
						echo $row['doctor'];
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
					<p class="heading-1 breadcrumbs-custom-title">請假資料查詢</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="d_index.php">首頁</a></li>
						<li><a href="#">班表</a></li>
						<li class="active">請假資料查詢</li>
					</ul>
				</div>
			</section>
		</div>


		<!-- 請假查詢資料 -->
		<style>
			/* 表格樣式 */
			table {
				width: 100%;
				border-collapse: collapse;
				margin: 10px 0;
			}

			th,
			td {
				border: 1px solid #ddd;
				padding: 6px;
				text-align: center;
			}

			th {
				background-color: #f4f4f4;
				font-weight: bold;
			}

			/* 審核狀態樣式 */
			.approved {
				color: green;
			}

			.rejected {
				color: red;
			}

			/* 分頁樣式 */
			/* 分頁樣式 */
			.pagination {
				display: flex;
				justify-content: center;
				align-items: center;
				margin: 20px 0;
				list-style: none;
				padding: 0;
			}

			.pagination a,
			.pagination span {
				display: inline-block;
				padding: 8px 12px;
				margin: 0 4px;
				border: 1px solid #ddd;
				text-decoration: none;
				color: #007bff;
				border-radius: 5px;
				transition: background-color 0.3s, color 0.3s;
			}

			.pagination a:hover {
				background-color: #007bff;
				color: white;
			}

			.pagination .active {
				background-color: #007bff;
				color: white;
				font-weight: bold;
				border-color: #007bff;
			}

			.pagination .disabled {
				color: #ccc;
				border-color: #ddd;
				cursor: not-allowed;
			}

			/* 總資訊樣式 */
			.total-info {
				text-align: right;
				margin: 5px 0;
				font-size: 14px;
				color: #555;
				display: flex;
				justify-content: flex-end;
				align-items: center;
			}


			/* 響應式樣式 */
			.table-responsive {
				overflow-x: auto;
				-webkit-overflow-scrolling: touch;
			}

			@media (max-width: 768px) {
				table {
					font-size: 12px;
				}

				th,
				td {
					padding: 4px;
				}
			}
		</style>

		<!-- 表格顯示 -->
		<section class="section section-lg novi-bg novi-bg-img bg-default">
			<div class="container">
				<div class="row row-40 row-lg-50">
					<div class="form-container">
						<table border="1">
							<thead>
								<tr>
									<th>請假編號</th>
									<th>治療師姓名</th>
									<th>請假類型</th>
									<th>其他請假類型</th>
									<th>開始時間</th>
									<th>結束時間</th>
									<th>請假原因</th>
									<th>審核狀態</th>
									<th>駁回原因</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ($total_records > 0) {
									while ($row = mysqli_fetch_assoc($result)): ?>
										<tr>
											<td><?php echo htmlspecialchars($row['leaves_id']); ?></td>
											<td><?php echo htmlspecialchars($doctor_name); ?></td>
											<td><?php echo htmlspecialchars($row['leave_type']); ?></td>
											<td><?php echo htmlspecialchars($row['leave_type_other'] ?? ''); ?></td>
											<td><?php echo htmlspecialchars($row['start_date']); ?></td>
											<td><?php echo htmlspecialchars($row['end_date']); ?></td>
											<td><?php echo htmlspecialchars($row['reason']); ?></td>
											<td>
												<?php
												if ($row['is_approved'] == '1') {
													echo "<span class='approved'>已通過</span>";
												} elseif ($row['is_approved'] == '0') {
													echo "<span class='rejected'>未通過</span>";
												} else {
													echo "<span style='color: gray;'>待審核</span>";
												}
												?>
											</td>
											<td>
												<?php echo ($row['is_approved'] == '0') ? htmlspecialchars($row['rejection_reason']) : '-'; ?>
											</td>
										</tr>
									<?php endwhile;
								} else {
									echo "<tr><td colspan='9' style='text-align:center;'>沒有請假紀錄</td></tr>";
								}
								?>
							</tbody>
						</table>

						<!-- 分頁資訊 -->
						<div class="total-info">
							第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁 (總共 <?php echo $total_records; ?>
							筆資料)
						</div>

						<!-- 分頁 -->
						<div class="pagination">
							<?php if ($page > 1): ?>
								<a href="?page=<?php echo $page - 1; ?>">« 上一頁</a>
							<?php else: ?>
								<span class="disabled">« 上一頁</span>
							<?php endif; ?>

							<?php for ($p = 1; $p <= $total_pages; $p++): ?>
								<?php if ($p == $page): ?>
									<span class="active"><?php echo $p; ?></span>
								<?php else: ?>
									<a href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
								<?php endif; ?>
							<?php endfor; ?>

							<?php if ($page < $total_pages): ?>
								<a href="?page=<?php echo $page + 1; ?>">下一頁 »</a>
							<?php else: ?>
								<span class="disabled">下一頁 »</span>
							<?php endif; ?>
						</div>

						<?php mysqli_close($link); ?>
					</div>
				</div>
			</div>
		</section>
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