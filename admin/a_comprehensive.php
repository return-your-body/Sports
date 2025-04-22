<?php
session_start();

if (!isset($_SESSION["登入狀態"])) {
	// 如果未登入或會話過期，跳轉至登入頁面
	header("Location: ../index.html");
	exit;
}

// 防止頁面被瀏覽器緩存
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

// 檢查是否有 "帳號" 在 Session 中
if (isset($_SESSION["帳號"])) {
	// 獲取用戶帳號
	$帳號 = $_SESSION['帳號'];

	// 引入資料庫連接檔案
	require '../db.php';

	// 查詢用戶詳細資料（從資料庫中獲取對應資料）
	$sql = "SELECT account, grade_id FROM user WHERE account = ?";
	$stmt = mysqli_prepare($link, $sql);
	mysqli_stmt_bind_param($stmt, "s", $帳號);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	if ($result && mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
		$帳號名稱 = $row['account']; // 使用者帳號
		$等級 = $row['grade_id']; // 等級（例如 1: 治療師, 2: 護士, 等等）
	} else {
		// 如果查詢不到對應的資料
		echo "<script>
                alert('找不到對應的帳號資料，請重新登入。');
                window.location.href = '../index.html';
              </script>";
		exit();
	}
}
// 查詢尚未審核的請假申請數量
$pendingCountResult = $link->query(
	"SELECT COUNT(*) as pending_count 
     FROM leaves 
     WHERE is_approved IS NULL"
);
$pendingCount = $pendingCountResult->fetch_assoc()['pending_count'];
?>

<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
	<!-- Site Title-->
	<title>治療師管理統計儀表板</title>
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
		.rd-dropdown-link span {
			background-color: red;
			color: white;
			font-size: 12px;
			border-radius: 50%;
			padding: 2px 6px;
			margin-left: 5px;
			display: inline-block;
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
								<!--Brand--><a class="brand-name" href="index.php"><img class="logo-default"
										src="images/logo-default-172x36.png" alt="" width="86" height="18"
										loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
										alt="" width="86" height="18" loading="lazy" /></a>
							</div>
						</div>
						<div class="rd-navbar-nav-wrap">
							<ul class="rd-navbar-nav">
								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="a_index.php">網頁編輯</a> -->
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="">關於治療師</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_therapist.php">總人數時段表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_addds.php">班表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_treatment.php">新增治療項目</a>
										</li>
										<li class="rd-dropdown-item">
											<a class="rd-dropdown-link" href="a_leave.php">
												請假申請
												<?php if ($pendingCount > 0): ?>
													<span style="
						background-color: red;
						color: white;
						font-size: 12px;
						border-radius: 50%;
						padding: 2px 6px;
						margin-left: 5px;
						display: inline-block;
					">
														<?php echo $pendingCount; ?>
													</span>
												<?php endif; ?>
											</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item active"><a class="rd-nav-link" href="a_comprehensive.php">綜合</a>
								</li>
								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="a_comprehensive.php">綜合</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">Single teacher</a>
										</li>
									</ul>
								</li> -->

								<li class="rd-nav-item"><a class="rd-nav-link" href="">用戶管理</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_patient.php">用戶管理</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_addhd.php">新增治療師/助手</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_blacklist.php">黑名單</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_doctorlistadd.php">新增治療師資料</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_igadd.php">新增哀居貼文</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="a_change.php">變更密碼</a>
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
											href="https://www.facebook.com/ReTurnYourBody/"></a></li>
									<li><a class="icon novi-icon icon-default icon-custom-instagram"
											href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"></a>
									</li>
								</ul>
							</div>
						</div> -->
						<?php
						echo "歡迎 ~ ";
						// 顯示姓名
						echo $帳號名稱;
						?>
					</div>
				</nav>
			</div>
		</header>

		<!-- 統計圖 時速收入 -->

		<?php require '../db.php'; ?>
		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<style>
			.chart-container {
				margin: 20px auto;
				max-width: 1200px;
			}

			.chart-row {
				display: flex;
				flex-wrap: wrap;
				gap: 16px;
				justify-content: space-between;
			}

			.chart-box {
				width: 48%;
				min-width: 300px;
			}

			.modal {
				display: none;
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background: rgba(0, 0, 0, 0.6);
				z-index: 9999;
			}

			.modal-content {
				background: white;
				padding: 20px;
				margin: 10% auto;
				width: 90%;
				max-width: 1000px;
			}

			.close {
				float: right;
				font-size: 24px;
				cursor: pointer;
			}
		</style>

		<div class="chart-container">
			<form id="filterForm" class="mb-4 d-flex flex-wrap gap-2 justify-content-center">
				<select id="type" name="type" class="form-select w-auto">
					<option value="day">單日</option>
					<option value="month">月份</option>
					<option value="year">年度</option>
				</select>
				<select id="year" name="year" class="form-select w-auto"></select>
				<select id="month" name="month" class="form-select w-auto"></select>
				<select id="day" name="day" class="form-select w-auto"></select>
				<select id="doctor_id" name="doctor_id" class="form-select w-auto">
					<option value="0">全部</option>
					<?php
					$res = mysqli_query($link, "SELECT * FROM doctor");
					while ($r = mysqli_fetch_assoc($res)) {
						echo "<option value='{$r['doctor_id']}'>{$r['doctor']}</option>";
					}
					?>
				</select>
				<button type="submit" class="btn btn-primary">查詢</button>
			</form>

			<div class="chart-row">
				<div class="chart-box">
					<h5 class="text-center">總工作時數</h5>
					<canvas id="workChart"></canvas>
					<div id="workNoData" class="text-danger text-center mt-2" style="display:none;">⚠ 無資料</div>
				</div>
				<div class="chart-box">
					<h5 class="text-center">請假統計</h5>
					<canvas id="leaveChart"></canvas>
					<div id="leaveNoData" class="text-danger text-center mt-2" style="display:none;">⚠ 無資料</div>
				</div>
			</div>

			<div class="chart-row">
				<div class="chart-box">
					<h5 class="text-center">項目數比例</h5>
					<canvas id="itemChart"></canvas>
					<div id="itemTableContainer" class="mt-2"></div>
				</div>
				<div class="chart-box">
					<h5 class="text-center">預約人數比例</h5>
					<canvas id="appointmentChart"></canvas>
					<div id="appointmentTableContainer" class="mt-2"></div>
				</div>
				<div class="chart-box">
					<h5 class="text-center">收入統計</h5>
					<canvas id="incomeChart"></canvas>
					<div id="incomeTableContainer" class="mt-2"></div>
				</div>
			</div>
		</div>

		<div id="detailModal" class="modal">
			<div class="modal-content">
				<span class="close" onclick="$('#detailModal').hide()">×</span>
				<h4 id="detailTitle" class="mb-3"></h4>
				<div id="detailTableContainer"></div>
			</div>
		</div>

		<script>
			const chartColors = ['rgba(255, 205, 86, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(255, 99, 132, 0.7)'];
			let workChart, leaveChart;

			function fetchData() {
				$.get("數據查詢.php", $('#filterForm').serialize(), function (res) {
					drawWork(res.workChartData);
					drawLeave(res.leave);
					drawPie("itemChart", res.itemsChartData, 'item', 'count', 'itemTableContainer');
					drawPie("appointmentChart", res.appointmentChartData, 'doctor', 'count', 'appointmentTableContainer');
					drawPie("incomeChart", res.incomeChartData, 'item', 'total', 'incomeTableContainer');
				}, 'json');
			}

			function drawWork(data) {
				if (workChart) workChart.destroy();
				const ctx = document.getElementById("workChart").getContext("2d");
				if (!data || !data.length) {
					$('#workNoData').show();
					new Chart(ctx, { type: 'bar', data: { labels: [], datasets: [] } });
					return;
				}
				$('#workNoData').hide();
				const labels = data.map(d => d.doctor_name);
				const total = data.map(d => d.total_hours);
				const late = data.map(d => d.late_minutes);
				const ot = data.map(d => d.overtime_minutes);
				workChart = new Chart(ctx, {
					type: 'bar',
					data: {
						labels,
						datasets: [
							{ label: "總工時(小時)", data: total, backgroundColor: chartColors[0] },
							{ label: "遲到(分鐘)", data: late, backgroundColor: chartColors[1] },
							{ label: "加班(分鐘)", data: ot, backgroundColor: chartColors[2] }
						]
					},
					options: {
						onClick: function (evt, elements) {
							if (elements.length) {
								const index = elements[0].index;
								showDetails(data[index]);
							}
						}
					}
				});
			}

			function drawLeave(data) {
				if (leaveChart) leaveChart.destroy();
				const ctx = document.getElementById("leaveChart").getContext("2d");
				if (!data || !data.length) {
					$('#leaveNoData').show();
					new Chart(ctx, { type: 'bar', data: { labels: [], datasets: [] } });
					return;
				}
				$('#leaveNoData').hide();
				const summary = {};
				data.forEach(d => {
					for (const type in d.details) {
						const total = d.details[type].reduce((sum, row) => sum + parseInt(row.minutes), 0);
						summary[type] = (summary[type] || 0) + total;
					}
				});
				const types = Object.keys(summary);
				const values = types.map(t => summary[t]);
				leaveChart = new Chart(ctx, {
					type: 'bar',
					data: {
						labels: types,
						datasets: [{ label: '請假分鐘數', data: values, backgroundColor: chartColors }]
					}
				});
			}

			function drawPie(id, dataset, labelKey, valueKey, containerId) {
				const ctx = document.getElementById(id).getContext("2d");
				if (!dataset || !dataset.length) {
					document.getElementById(containerId).innerHTML = '<p class="text-danger text-center">⚠ 無資料</p>';
					new Chart(ctx, { type: 'pie', data: { labels: [], datasets: [] } });
					return;
				}
				const labels = dataset.map(d => d[labelKey]);
				const values = dataset.map(d => parseFloat(d[valueKey]));
				new Chart(ctx, {
					type: 'pie',
					data: { labels, datasets: [{ data: values, backgroundColor: chartColors }] }
				});
				document.getElementById(containerId).innerHTML = '<p class="text-muted text-center">請點選圖表查看詳細資料</p>';
			}

			function showDetails(detail) {
				let html = '<table class="table table-bordered"><thead><tr><th>日期</th><th>打卡</th><th>下班</th><th>遲到</th><th>加班</th><th>總工時</th></tr></thead><tbody>';
				detail.details.forEach(row => {
					html += `<tr><td>${row.work_date}</td><td>${row.clock_in_time}</td><td>${row.clock_out_time ?? '-'}</td><td>${row.late_minutes} 分</td><td>${row.overtime_minutes} 分</td><td>${row.total_hours} 小時</td></tr>`;
				});
				html += '</tbody></table>';
				$('#detailTitle').text(`${detail.doctor_name} 的詳細工作資料`);
				$('#detailTableContainer').html(html);
				$('#detailModal').show();
			}

			$(document).ready(function () {
				const now = new Date(), y = now.getFullYear(), m = now.getMonth() + 1, d = now.getDate();
				for (let i = 2023; i <= y; i++) $('#year').append(`<option value="${i}" ${i === y ? 'selected' : ''}>${i}</option>`);
				for (let i = 1; i <= 12; i++) $('#month').append(`<option value="${i}" ${i === m ? 'selected' : ''}>${i}</option>`);
				for (let i = 1; i <= 31; i++) $('#day').append(`<option value="${i}" ${i === d ? 'selected' : ''}>${i}</option>`);
				fetchData();
			});

			$('#filterForm').submit(function (e) {
				e.preventDefault();
				fetchData();
			});
		</script>





	</div>
	<!-- Global Mailform Output-->
	<div class="snackbars" id="form-output-global"></div>
	<!-- Javascript-->
	<script src="js/core.min.js"></script>
	<script src="js/script.js"></script>
</body>

</html>