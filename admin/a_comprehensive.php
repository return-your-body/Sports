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
		<!DOCTYPE html>
		<html lang="zh-Hant">

		<head>
			<meta charset="UTF-8">
			<title>治療師統計資料</title>
			<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
			<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
			<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
		</head>

		<body>

			<div class="container">
				<h2 class="text-center my-4">治療師統計資料</h2>

				<!-- 篩選表單 -->
				<form id="filterForm" class="row g-3 justify-content-center mb-4">
					<div class="col-auto">
						<select id="type" name="type" class="form-select">
							<option value="day" selected>單日</option>
							<option value="month">整月</option>
							<option value="year">整年</option>
						</select>
					</div>
					<div class="col-auto"><select id="year" name="year" class="form-select"></select></div>
					<div class="col-auto"><select id="month" name="month" class="form-select"></select></div>
					<div class="col-auto"><select id="day" name="day" class="form-select"></select></div>
					<div class="col-auto">
						<select id="doctor_id" name="doctor_id" class="form-select">
							<option value="0">全部</option>
							<?php
							$res = mysqli_query($link, "SELECT doctor_id, doctor FROM doctor");
							while ($row = mysqli_fetch_assoc($res))
								echo "<option value='{$row['doctor_id']}'>{$row['doctor']}</option>";
							?>
						</select>
					</div>
					<div class="col-auto"><button class="btn btn-primary" type="submit">查詢</button></div>
				</form>

				<!-- 圖表區塊 -->
				<div class="chart-container">
					<!-- 原有圖表：總工時 + 請假統計 -->
					<div class="d-flex flex-wrap justify-content-center gap-3">
						<div class="chart-box flex-grow-1" style="flex: 1 1 300px; max-width: 100%;">
							<h5 class="text-center">總工作時數</h5>
							<canvas id="workChart"></canvas>
							<div id="workNoData" class="text-danger text-center mt-2" style="display:none;">⚠ 無資料</div>
						</div>
						<div class="chart-box flex-grow-1" style="flex: 1 1 300px; max-width: 100%;">
							<h5 class="text-center">請假統計</h5>
							<canvas id="leaveChart"></canvas>
							<div id="leaveNoData" class="text-danger text-center mt-2" style="display:none;">⚠ 無資料</div>
						</div>
					</div>

					<!-- 新增三個圓餅圖 -->
					<div class="d-flex flex-wrap justify-content-center gap-3">
						<div class="chart-box flex-grow-1" style="flex: 1 1 300px; max-width: 100%;">
							<h5 class="text-center">項目數比例</h5>
							<canvas id="itemChart"></canvas>
							<div id="itemTableContainer" class="mt-2"></div>
						</div>
						<div class="chart-box flex-grow-1" style="flex: 1 1 300px; max-width: 100%;">
							<h5 class="text-center">預約人數比例</h5>
							<canvas id="appointmentChart"></canvas>
							<div id="appointmentTableContainer" class="mt-2"></div>
						</div>
						<div class="chart-box flex-grow-1" style="flex: 1 1 300px; max-width: 100%;">
							<h5 class="text-center">收入統計</h5>
							<canvas id="incomeChart"></canvas>
							<div id="incomeTableContainer" class="mt-2"></div>
						</div>
					</div>
				</div>

				<!-- 詳細資料彈跳視窗 -->
				<div id="detailModal" class="modal">
					<div class="modal-content">
						<span class="close" onclick="$('#detailModal').hide()">&times;</span>
						<h4 id="detailTitle" class="mb-3"></h4>
						<div id="detailTableContainer"></div>
					</div>
				</div>
			</div>

			<!-- JavaScript 功能區 -->
			<script>
				let workChart, leaveChart;
				let workData = [], leaveData = [];

				const chartColors = [
					'rgba(255, 205, 86, 0.7)',
					'rgba(54, 162, 235, 0.7)',
					'rgba(153, 102, 255, 0.7)',
					'rgba(75, 192, 192, 0.7)',
					'rgba(255, 99, 132, 0.7)',
					'rgba(255, 99, 132, 0.6)',
					'rgba(54, 162, 235, 0.6)',
					'rgba(255, 206, 86, 0.6)',
					'rgba(75, 192, 192, 0.6)'
				];

				// 請求資料並渲染圖表
				function fetchData() {
					const query = $('#filterForm').serialize();
					$.get('數據查詢.php', query, function (res) {
						workData = res.work || [];
						leaveData = res.leave || [];
						drawCharts(res);
						drawPieCharts(res);
					}, 'json');
				}

				// 長條圖：總工時 + 請假統計
				function drawCharts(data) {
					if (workChart) workChart.destroy();
					if (leaveChart) leaveChart.destroy();

					const ctxWork = document.getElementById('workChart').getContext('2d');
					const ctxLeave = document.getElementById('leaveChart').getContext('2d');

					if (data.work.length) {
						$('#workChart').show();
						$('#workNoData').hide();
						workChart = new Chart(ctxWork, {
							type: 'bar',
							data: {
								labels: data.work.map(i => i.doctor_name),
								datasets: [
									{ label: '總工時(小時)', data: data.work.map(i => i.total_hours), backgroundColor: chartColors[0] },
									{ label: '遲到(分鐘)', data: data.work.map(i => i.late_minutes), backgroundColor: chartColors[1] },
									{ label: '加班(分鐘)', data: data.work.map(i => i.overtime_minutes), backgroundColor: chartColors[2] }
								]
							},
							options: {
								onClick: (evt, item) => item.length && showDetail('work', workData[item[0].index])
							}
						});
					} else {
						$('#workChart').hide();
						$('#workNoData').show();
					}

					if (data.leave.length && data.leave_types.length) {
						$('#leaveChart').show();
						$('#leaveNoData').hide();
						leaveChart = new Chart(ctxLeave, {
							type: 'bar',
							data: {
								labels: data.leave.map(i => i.doctor_name),
								datasets: data.leave_types.map((type, i) => ({
									label: type,
									data: data.leave.map(d => {
										let sum = 0;
										(d.details[type] || []).forEach(v => sum += v.minutes);
										return sum;
									}),
									backgroundColor: chartColors[(i + 3) % chartColors.length]
								}))
							},
							options: {
								onClick: (evt, item) => item.length && showDetail('leave', leaveData[item[0].index])
							}
						});
					} else {
						$('#leaveChart').hide();
						$('#leaveNoData').show();
					}
				}

				// 點擊詳細彈窗表格
				function showDetail(type, detail) {
					let html = '<table class="table table-bordered"><thead><tr>';
					if (type === 'work') {
						html += '<th>日期</th><th>打卡</th><th>下班</th><th>遲到</th><th>加班</th><th>總工時</th></tr></thead><tbody>';
						detail.details.forEach(d => {
							html += `<tr><td>${d.work_date}</td><td>${d.clock_in_time}</td><td>${d.clock_out_time}</td><td>${d.late_minutes} 分</td><td>${d.overtime_minutes} 分</td><td>${d.total_hours} 小時</td></tr>`;
						});
					} else {
						html += '<th>類別</th><th>起</th><th>訖</th><th>原因</th><th>分鐘</th></tr></thead><tbody>';
						for (const [typeName, arr] of Object.entries(detail.details)) {
							arr.forEach(row => {
								html += `<tr><td>${typeName}</td><td>${row.start}</td><td>${row.end}</td><td>${row.reason}</td><td>${row.minutes} 分</td></tr>`;
							});
						}
					}
					html += '</tbody></table>';
					$('#detailTitle').text(`${detail.doctor_name} 的詳細${type === 'work' ? '工作' : '請假'}資料`);
					$('#detailTableContainer').html(html);
					$('#detailModal').show();
				}

				// 繪製三個圓餅圖
				function drawPieCharts(data) {
					renderPie('itemChart', '項目數比例', data.itemsChartData, 'item', 'count', 'itemTableContainer');
					renderPie('appointmentChart', '預約人數比例', data.appointmentChartData, 'doctor', 'count', 'appointmentTableContainer');
					renderPie('incomeChart', '收入統計', data.incomeChartData, 'item', 'total', 'incomeTableContainer');
				}

				// 圓餅圖繪製通用函式
				function renderPie(canvasId, chartTitle, dataset, labelKey, valueKey, tableContainerId) {
					const ctx = document.getElementById(canvasId).getContext('2d');

					if (!dataset || dataset.length === 0) {
						document.getElementById(tableContainerId).innerHTML = `<p class="text-danger text-center">⚠ 無資料</p>`;
						new Chart(ctx, {
							type: 'pie',
							data: { labels: [], datasets: [{ data: [] }] },
							options: { plugins: { legend: { display: false } } }
						});
						return;
					}

					const labels = dataset.map(d => d[labelKey]);
					const values = dataset.map(d => parseFloat(d[valueKey]));

					new Chart(ctx, {
						type: 'pie',
						data: {
							labels,
							datasets: [{
								data: values,
								backgroundColor: chartColors.slice(0, labels.length)
							}]
						},
						options: {
							onClick: function (evt, item) {
								if (item.length) {
									const idx = item[0].index;
									const label = labels[idx];
									const value = values[idx];
									renderPieTable(label, value, tableContainerId, chartTitle);
								}
							}
						}
					});

					document.getElementById(tableContainerId).innerHTML = `<p class="text-muted text-center">請點選圖表查看詳細資料</p>`;
				}

				// 顯示圓餅圖點擊後表格內容
				function renderPieTable(label, value, tableId, chartType) {
					let html = '<table class="table table-bordered mt-2"><thead><tr>';
					if (chartType === '項目數比例') {
						html += '<th>治療項目</th><th>使用次數</th></tr></thead><tbody>';
						html += `<tr><td>${label}</td><td>${value}</td></tr>`;
					} else if (chartType === '預約人數比例') {
						html += '<th>治療師</th><th>預約數</th></tr></thead><tbody>';
						html += `<tr><td>${label}</td><td>${value}</td></tr>`;
					} else if (chartType === '收入統計') {
						html += '<th>治療項目</th><th>總收入</th></tr></thead><tbody>';
						html += `<tr><td>${label}</td><td>${value} 元</td></tr>`;
					}
					html += '</tbody></table>';
					document.getElementById(tableId).innerHTML = html;
				}

				// 表單送出查詢
				$('#filterForm').submit(function (e) {
					e.preventDefault();
					fetchData();
				});

				// 頁面載入後 ➝ 自動填入今天日期
				$(document).ready(function () {
					const now = new Date();
					const y = now.getFullYear(), m = now.getMonth() + 1, d = now.getDate();
					for (let i = 2023; i <= y; i++) $('#year').append(`<option value="${i}" ${i === y ? 'selected' : ''}>${i}</option>`);
					for (let i = 1; i <= 12; i++) $('#month').append(`<option value="${i}" ${i === m ? 'selected' : ''}>${i}</option>`);
					for (let i = 1; i <= 31; i++) $('#day').append(`<option value="${i}" ${i === d ? 'selected' : ''}>${i}</option>`);
					fetchData(); // 預設載入資料
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