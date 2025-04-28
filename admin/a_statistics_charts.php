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
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		<script
			src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.0.0/chartjs-plugin-datalabels.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

		<style>
			.chart-row {
				display: flex;
				flex-wrap: wrap;
				justify-content: space-between;
				gap: 20px;
			}

			.chart-box {
				flex: 1 1 32%;
				min-height: 300px;
				text-align: center;
				position: relative;
			}

			.text-danger-bold {
				font-weight: bold;
				color: red;
				margin-top: 10px;
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

		<div class="container" id="mainContainer">
			<h2 class="text-center my-4">項目／預約／收入 統計圖表</h2>

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
						while ($row = mysqli_fetch_assoc($res)) {
							echo "<option value='{$row['doctor_id']}'>{$row['doctor']}</option>";
						}
						?>
					</select>
				</div>
				<div class="col-auto">
					<select id="item_name" name="item_name" class="form-select">
						<option value="全部">全部項目</option>
						<?php
						$res = mysqli_query($link, "SELECT DISTINCT item FROM item");
						while ($row = mysqli_fetch_assoc($res)) {
							echo "<option value='{$row['item']}'>{$row['item']}</option>";
						}
						?>
					</select>
				</div>
				<div class="col-auto"><button class="btn btn-primary" type="submit">查詢</button></div>
				<div class="col-auto">
					<a href="a_comprehensive.php" class="btn btn-secondary">返回其他圖表</a>
				</div>
			</form>

			<div class="text-center mb-4">
				<button class="btn btn-success"
					onclick="downloadChart('itemChart', '項目數比例', 'png')">匯出項目數圖(PNG)</button>
				<!-- <button class="btn btn-success"
					onclick="downloadChart('itemChart', '項目數比例', 'jpg')">匯出項目數圖(JPG)</button> -->
				<button class="btn btn-success"
					onclick="downloadChart('appointmentChart', '預約人數比例', 'png')">匯出預約人數圖(PNG)</button>
				<!-- <button class="btn btn-success"
					onclick="downloadChart('appointmentChart', '預約人數比例', 'jpg')">匯出預約人數圖(JPG)</button> -->
				<button class="btn btn-success"
					onclick="downloadChart('incomeChart', '收入統計', 'png')">匯出收入統計圖(PNG)</button>
				<!-- <button class="btn btn-success"
					onclick="downloadChart('incomeChart', '收入統計', 'jpg')">匯出收入統計圖(JPG)</button> -->
				<button class="btn btn-danger" onclick="downloadAllChartsPDF()">匯出全部圖表(PDF)</button>
				<button class="btn btn-primary" onclick="downloadAllDataExcel()">匯出詳細資料(Excel)</button>
			</div>

			<div id="loadingSpinner" class="text-center my-4" style="display:none;">
				<div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
					<span class="visually-hidden">Loading...</span>
				</div>
			</div>

			<div class="chart-row">
				<div class="chart-box">
					<h5 class="text-center">項目數比例</h5>
					<canvas id="itemChart"></canvas>
					<div id="itemEmpty" class="text-danger-bold"></div>
				</div>
				<div class="chart-box">
					<h5 class="text-center">預約人數比例</h5>
					<canvas id="appointmentChart"></canvas>
					<div id="appointmentEmpty" class="text-danger-bold"></div>
				</div>
				<div class="chart-box">
					<h5 class="text-center">收入統計</h5>
					<canvas id="incomeChart"></canvas>
					<div id="incomeEmpty" class="text-danger-bold"></div>
				</div>
			</div>

			<div id="detailModal" class="modal">
				<div class="modal-content">
					<span class="close" onclick="$('#detailModal').hide()">&times;</span>
					<h4 id="detailTitle" class="mb-3"></h4>
					<div id="detailTableContainer"></div>
				</div>
			</div>
		</div>

		<script>
			let charts = {};
			let globalData = {};

			function fetchChartData() {
				$('#loadingSpinner').show();
				const data = $('#filterForm').serialize();
				$.get('數據查詢2.php', data, function (res) {
					globalData = res;
					renderPie('itemChart', res.item_summary, 'item_name', 'count');
					renderPie('appointmentChart', res.appointment_counts, 'doctor_name', 'count');
					renderPie('incomeChart', res.income_summary, 'item_name', 'total_income');
					$('#loadingSpinner').hide();
				}, 'json').fail(function () {
					$('#loadingSpinner').hide();
				});
			}

			function renderPie(id, data, labelField, valueField) {
				const ctx = document.getElementById(id)?.getContext('2d');
				if (charts[id]) charts[id].destroy();
				const emptyDiv = document.getElementById(id.replace('Chart', 'Empty'));

				if (!ctx || !data.length) {
					if (emptyDiv) emptyDiv.innerHTML = "⚠ 無資料";
					return;
				}
				if (emptyDiv) emptyDiv.innerHTML = "";

				charts[id] = new Chart(ctx, {
					type: 'pie',
					data: {
						labels: data.map(d => d[labelField]),
						datasets: [{
							data: data.map(d => d[valueField]),
							backgroundColor: generateColors(data.length)
						}]
					},
					options: {
						plugins: {
							datalabels: {
								color: '#333',
								font: { weight: 'bold' },
								formatter: (value, ctx) => `${ctx.chart.data.labels[ctx.dataIndex]} (${value})`
							}
						},
						onClick: (e, i) => {
							if (i.length) showDetail(id, i[0].index);
						}
					},
					plugins: [ChartDataLabels]
				});
			}

			function generateColors(count) {
				const colors = [];
				for (let i = 0; i < count; i++) {
					const r = Math.floor(Math.random() * 200) + 30;
					const g = Math.floor(Math.random() * 200) + 30;
					const b = Math.floor(Math.random() * 200) + 30;
					colors.push(`rgba(${r}, ${g}, ${b}, 0.7)`);
				}
				return colors;
			}

			function showDetail(chartId, index) {
				let detailList = [];
				if (chartId === 'itemChart') {
					detailList = globalData.item_details;
				} else if (chartId === 'appointmentChart') {
					detailList = globalData.appointment_details;
				} else if (chartId === 'incomeChart') {
					detailList = globalData.income_details;
				}

				if (!detailList.length) return;
				let html = '<table class="table table-bordered"><thead><tr>';
				if (chartId === 'itemChart') {
					html += '<th>日期</th><th>治療師</th><th>項目</th><th>次數</th>';
				} else if (chartId === 'appointmentChart') {
					html += '<th>日期</th><th>治療師</th><th>預約者</th><th>時間</th><th>使用項目</th>';
				} else {
					html += '<th>日期</th><th>治療師</th><th>項目</th><th>單價</th><th>總收入</th>';
				}
				html += '</tr></thead><tbody>';

				detailList.forEach(d => {
					html += '<tr>';
					if (chartId === 'itemChart') {
						html += `<td>${d.record_date || '-'}</td><td>${d.doctor_name || '-'}</td><td>${d.item_name || '-'}</td><td>${d.count}</td>`;
					} else if (chartId === 'appointmentChart') {
						html += `<td>${d.record_date || '-'}</td><td>${d.doctor_name || '-'}</td><td>${d.user_name || '-'}</td><td>${d.appointment_time || '-'}</td><td>${d.item_names || '-'}</td>`;
					} else {
						html += `<td>${d.record_date || '-'}</td><td>${d.doctor_name || '-'}</td><td>${d.item_name || '-'}</td><td>${d.unit_price}</td><td>${d.total_income}</td>`;
					}
					html += '</tr>';
				});

				html += '</tbody></table>';
				$('#detailTitle').text('詳細資料');
				$('#detailTableContainer').html(html);
				$('#detailModal').show();
			}

			function downloadChart(id, title, type) {
				const canvas = document.getElementById(id);
				const link = document.createElement('a');
				link.href = canvas.toDataURL(`image/${type}`);
				link.download = `${title}_${new Date().toISOString().slice(0, 10)}.${type}`;
				link.click();
			}

			function downloadAllChartsPDF() {
				const { jsPDF } = window.jspdf;
				const pdf = new jsPDF('p', 'mm', 'a4');
				Promise.all([
					html2canvas(document.getElementById('itemChart')),
					html2canvas(document.getElementById('appointmentChart')),
					html2canvas(document.getElementById('incomeChart'))
				]).then((canvases) => {
					canvases.forEach((canvas, idx) => {
						const imgData = canvas.toDataURL('image/png');
						const pdfWidth = pdf.internal.pageSize.getWidth();
						const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
						if (idx > 0) pdf.addPage();
						pdf.addImage(imgData, 'PNG', 0, 20, pdfWidth, pdfHeight - 20);
					});
					pdf.save(`項目預約收入統計_${new Date().toISOString().slice(0, 10)}.pdf`);
				});
			}

			function downloadAllDataExcel() {
				let wb = XLSX.utils.book_new();
				// Same logic to export detailed table to Excel...
			}

			$('#filterForm').submit(function (e) {
				e.preventDefault();
				fetchChartData();
			});

			$(document).ready(function () {
				const now = new Date();
				for (let i = 2023; i <= now.getFullYear(); i++) $('#year').append(`<option value="${i}" ${i === now.getFullYear() ? 'selected' : ''}>${i}</option>`);
				for (let i = 1; i <= 12; i++) $('#month').append(`<option value="${i}" ${i === now.getMonth() + 1 ? 'selected' : ''}>${i}</option>`);
				for (let i = 1; i <= 31; i++) $('#day').append(`<option value="${i}" ${i === now.getDate() ? 'selected' : ''}>${i}</option>`);
				fetchChartData();
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