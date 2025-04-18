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

		<!-- 收入 -->

		<?php
		session_start();
		require '../db.php';
		?>

		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<style>
			.chart-container {
				display: flex;
				flex-direction: column;
				align-items: center;
			}

			.chart-row {
				display: flex;
				justify-content: center;
				flex-wrap: wrap;
				gap: 20px;
			}

			.chart-box {
				width: 300px;
				text-align: center;
			}

			.full-width {
				width: 600px;
				text-align: center;
			}

			.modal {
				display: none;
				position: fixed;
				z-index: 1000;
				left: 0;
				top: 0;
				width: 100%;
				height: 100%;
				background: rgba(0, 0, 0, 0.5);
			}

			.modal-content {
				background: white;
				padding: 20px;
				margin: 10% auto;
				width: 80%;
			}

			.close {
				float: right;
				font-size: 20px;
				cursor: pointer;
			}
		</style>
		<h2 style="text-align: center">治療師統計數據</h2>
		<div style="text-align: center">
			年：<select id="year"></select>
			月：<select id="month"></select>
			日：<select id="day"></select>
			治療師：<select id="doctor">
				<option value="">全部</option>
			</select>
			<button id="searchBtn">查詢</button>
			<br /><br />
			圖表匯出：<button id="exportExcel">匯出 Excel</button>
			<button id="exportPDF">匯出 PDF</button>
		</div>
		<br />
		<div class="chart-container">
			<div class="full-width">
				<h4>總工作時數</h4>
				<canvas id="workHoursChart"></canvas>
			</div>
			<div class="chart-row">
				<div class="chart-box">
					<h4>項目數比例</h4>
					<canvas id="itemChart"></canvas>
				</div>
				<div class="chart-box">
					<h4>治療師預約人數比例</h4>
					<canvas id="appointmentChart"></canvas>
				</div>
				<div class="chart-box">
					<h4>收入統計</h4>
					<canvas id="incomeChart"></canvas>
				</div>
			</div>
		</div>

		<!-- 詳細資料表格 -->
		<div id="modalDetail" class="modal">
			<div class="modal-content">
				<span class="close">&times;</span>
				<h2>詳細統計數據</h2>
				<table border="1" width="100%">
					<thead>
						<tr>
							<th>治療師</th>
							<th>類別</th>
							<th>數量/金額</th>
						</tr>
					</thead>
					<tbody id="modalDetailTable"></tbody>
				</table>
			</div>
		</div>

		<script>
			$(document).ready(function () {
				populateDate();
				loadDoctors();
				$('#searchBtn').click(fetchAllCharts);
				$('.close').click(() => $('#modalDetail').hide());

				function populateDate() {
					const now = new Date();
					for (let i = now.getFullYear() - 3; i <= now.getFullYear() + 10; i++) {
						$('#year').append(`<option value='${i}' ${i === now.getFullYear() ? 'selected' : ''}>${i}</option>`);
					}
					for (let i = 1; i <= 12; i++) {
						$('#month').append(`<option value='${i}' ${i === now.getMonth() + 1 ? 'selected' : ''}>${i}</option>`);
					}
					for (let i = 1; i <= 31; i++) {
						$('#day').append(`<option value='${i}' ${i === now.getDate() ? 'selected' : ''}>${i}</option>`);
					}
				}

				function loadDoctors() {
					$.getJSON('獲取治療師助手.php', data => {
						$('#doctor').empty().append(`<option value=''>全部</option>`);
						data.forEach(d => {
							const id = d.doctor_id || d.id;
							const name = d.doctor || d.name;
							$('#doctor').append(`<option value='${id}'>${name}</option>`);
						});
					});
				}

				function fetchAllCharts() {
					const y = $('#year').val(), m = $('#month').val(), d = $('#day').val(), doc = $('#doctor').val();
					drawChart('workHoursChart', 'bar', '總工作時數', 'total_hours', y, m, d, doc);
					drawChart('itemChart', 'pie', '項目數比例', 'item_count', y, m, d, doc, true);
					drawChart('appointmentChart', 'pie', '治療師預約人數比例', 'total_appointments', y, m, d, doc);
					drawChart('incomeChart', 'pie', '收入統計', 'total_revenue', y, m, d, doc, true);
				}

				function drawChart(canvasId, type, chartName, valueKey, y, m, d, doc, enableClick = false) {
					$.getJSON(`數據查詢.php?chart=${chartName}&year=${y}&month=${m}&day=${d}&doctor_id=${doc}`, res => {
						const chartContainer = document.getElementById(canvasId).getContext('2d');
						if (!res.length) {
							$(`#${canvasId}`).parent().html(`<p class='text-danger'>⚠️ 查無資料</p>`);
							return;
						}
						new Chart(chartContainer, {
							type: type,
							data: {
								labels: res.map(r => r.doctor_name || r.item || r.category),
								datasets: [{
									label: chartName,
									data: res.map(r => r[valueKey]),
									backgroundColor: ['red', 'blue', 'green', 'orange', 'purple', 'cyan']
								}]
							},
							options: enableClick ? {
								onClick(evt, el) {
									if (el.length > 0) {
										const label = res[el[0].index].item || res[el[0].index].doctor_name || res[el[0].index].category;
										showDetails(label);
									}
								}
							} : {}
						});
					});
				}

				function showDetails(label) {
					$.getJSON(`數據查詢.php?chart=詳細數據&item=${label}`, function (data) {
						let rows = data.map(d => `<tr><td>${d.doctor_name}</td><td>${d.category}</td><td>${d.value}</td></tr>`).join('');
						$('#modalDetailTable').html(rows);
						$('#modalDetail').show();
					});
				}
			});

			// 匯出 Excel / PDF
			function exportData(type) {
				const year = $('#year').val();
				const month = $('#month').val();
				const day = $('#day').val();
				const doctor_id = $('#doctor').val() || '';
				const url = `數據查詢.php?chart=匯出&type=${type}&year=${year}&month=${month}&day=${day}&doctor_id=${doctor_id}`;
				window.open(url);
			}

			$('#exportExcel').click(() => exportData("excel"));
			$('#exportPDF').click(() => exportData("pdf"));
		</script>





	</div>
	<!-- Global Mailform Output-->
	<div class="snackbars" id="form-output-global"></div>
	<!-- Javascript-->
	<script src="js/core.min.js"></script>
	<script src="js/script.js"></script>
</body>

</html>