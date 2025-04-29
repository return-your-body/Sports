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
		<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

		<style>
			.chart-container {
				margin: 20px auto;
				max-width: 1200px;
			}

			.chart-row {
				display: flex;
				justify-content: space-between;
			}

			.chart-box {
				width: 48%;
				text-align: center;
			}

			.no-data {
				color: red;
				font-weight: bold;
				margin-top: 20px;
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

			.spinner-border {
				width: 3rem;
				height: 3rem;
			}
		</style>

		<div class="container" id="mainContainer">
			<h2 class="text-center my-4">治療師統計資料</h2>

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
				<div class="col-auto"><a href="a_statistics_charts.php" class="btn btn-secondary">其他圖表</a></div>
			</form>

			<div id="loadingSpinner" class="text-center" style="display:none;">
				<div class="spinner-border text-primary" role="status">
					<span class="visually-hidden">Loading...</span>
				</div>
			</div>

			<div class="text-center mb-4">
				<button class="btn btn-success" onclick="downloadChart('workChart', '總工作時數')">匯出總工時圖表(PNG)</button>
				<button class="btn btn-success" onclick="downloadChart('leaveChart', '請假統計')">匯出請假統計圖表(PNG)</button>
				<button class="btn btn-danger" onclick="downloadAllChartsPDF()">匯出全部圖表(PDF)</button>
				<button class="btn btn-primary" onclick="downloadAllDataExcel()">匯出詳細資料(Excel)</button>
			</div>

			<div class="chart-container">
				<div class="chart-row">
					<div class="chart-box">
						<h5>總工作時數</h5>
						<canvas id="workChart"></canvas>
						<div id="workEmpty" class="no-data" style="display:none;">⚠ 無資料</div>
					</div>
					<div class="chart-box">
						<h5>請假統計</h5>
						<canvas id="leaveChart"></canvas>
						<div id="leaveEmpty" class="no-data" style="display:none;">⚠ 無資料</div>
					</div>
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
			let workChart, leaveChart;
			let workData = [], leaveData = [];

			function fetchData() {
				$('#loadingSpinner').show();
				const data = $('#filterForm').serialize();
				$.get('數據查詢.php', data, function (res) {
					workData = res.work;
					leaveData = res.leave;
					calculateAbsentRate();
					drawCharts();
					$('#loadingSpinner').hide();
				}, 'json').fail(function () {
					$('#loadingSpinner').hide();
					alert('❌ 資料取得失敗');
				});
			}

			function calculateAbsentRate() {
				$('#warningArea').remove();
				let warningList = [];

				workData.forEach(doctor => {
					const absent = doctor.absent_count || 0;
					const attend = doctor.details.length - absent;
					const totalDays = absent + attend;
					if (totalDays === 0) return;
					const absentRate = (absent / totalDays) * 100;
					doctor.absent_rate = absentRate.toFixed(1);
					if (absentRate >= 20) {
						warningList.push(`${doctor.doctor_name}（曠工率 ${doctor.absent_rate}%）`);
					}
				});

				if (warningList.length) {
					let html = `
	  <div id="warningArea" class="alert alert-danger mt-3" role="alert">
		<h5>⚠ 曠工率超過20%的治療師：</h5>
		<ul>${warningList.map(name => `<li>${name}</li>`).join('')}</ul>
	  </div>
	`;
					$('#mainContainer').prepend(html);
				}
			}

			function drawCharts() {
				if (workChart) workChart.destroy();
				if (leaveChart) leaveChart.destroy();

				if (workData.length) {
					$('#workEmpty').hide();
					const ctx = document.getElementById('workChart').getContext('2d');
					workChart = new Chart(ctx, {
						type: 'bar',
						data: {
							labels: workData.map(i => i.doctor_name),
							datasets: [
								{ label: '總工時(小時)', data: workData.map(i => i.total_hours), backgroundColor: '#80deea' },
								{ label: '遲到(分鐘)', data: workData.map(i => i.late_minutes), backgroundColor: '#f48fb1' },
								{ label: '加班(分鐘)', data: workData.map(i => i.overtime_minutes), backgroundColor: '#fff176' },
								{ label: '曠工次數(次)', data: workData.map(i => i.absent_count), backgroundColor: '#ff8a65' }
							]
						},
						options: {
							onClick: (e, i) => i.length && showDetail('work', workData[i[0].index])
						}
					});
				} else {
					$('#workEmpty').show();
				}

				if (leaveData.length) {
					$('#leaveEmpty').hide();
					const ctx = document.getElementById('leaveChart').getContext('2d');
					leaveChart = new Chart(ctx, {
						type: 'bar',
						data: {
							labels: leaveData.map(i => i.doctor_name),
							datasets: leaveData[0]?.details ? Object.keys(leaveData[0].details).map((typeName, idx) => ({
								label: `${typeName}(分鐘)`,
								data: leaveData.map(d => (d.details[typeName] || []).reduce((s, v) => s + v.minutes, 0)),
								backgroundColor: `rgba(${120 + idx * 20}, ${100 + idx * 30}, ${220 - idx * 10}, 0.6)`
							})) : []
						},
						options: {
							onClick: (e, i) => i.length && showDetail('leave', leaveData[i[0].index])
						}
					});
				} else {
					$('#leaveEmpty').show();
				}
			}

			function showDetail(type, detail) {
				let html = '<table class="table table-bordered"><thead><tr>';
				if (type === 'work') {
					html += '<th>日期</th><th>上班</th><th>下班</th><th>遲到</th><th>加班</th><th>總工時</th></tr></thead><tbody>';

					let lateTotal = 0, overtimeTotal = 0, workhourTotal = 0, absentCount = 0, totalCount = 0;

					detail.details.forEach(d => {
						html += `<tr>
		<td>${d.work_date}</td>
		<td>${d.clock_in_time}</td>
		<td>${d.clock_out_time}</td>
		<td>${d.late_minutes !== '-' ? d.late_minutes + ' 分鐘' : '-'}</td>
		<td>${d.overtime_minutes !== '-' ? d.overtime_minutes + ' 分鐘' : '-'}</td>
		<td>${d.total_hours === '曠工' ? '<span style="color:red;font-weight:bold;">⚠ 曠工</span>' : d.total_hours + ' 小時'}</td>
	  </tr>`;

						if (d.total_hours !== '曠工') {
							lateTotal += parseInt(d.late_minutes) || 0;
							overtimeTotal += parseInt(d.overtime_minutes) || 0;
							workhourTotal += parseFloat(d.total_hours) || 0;
						} else {
							absentCount++;
						}
						totalCount++;
					});

					const absentRate = totalCount > 0 ? (absentCount / totalCount * 100).toFixed(1) : 0;

					html += `<tr>
	  <td colspan="6" style="text-align: right; font-weight: bold;">
		總遲到：${lateTotal} 分鐘，
		總加班：${overtimeTotal} 分鐘，
		總工時：${workhourTotal.toFixed(2)} 小時，
		曠工次數：${absentCount} 次，
		曠工率：${absentRate} %
	  </td>
	</tr>`;

				} else {
					html += '<th>請假類別</th><th>起</th><th>訖</th><th>原因</th><th>分鐘</th></tr></thead><tbody>';

					let leaveSummary = {};

					for (const [typeName, arr] of Object.entries(detail.details)) {
						arr.forEach(row => {
							html += `<tr>
		  <td>${typeName}</td>
		  <td>${row.start}</td>
		  <td>${row.end}</td>
		  <td>${row.reason}</td>
		  <td>${row.minutes} 分鐘</td>
		</tr>`;
							if (!leaveSummary[typeName]) leaveSummary[typeName] = 0;
							leaveSummary[typeName] += row.minutes;
						});
					}

					html += '</tbody></table><br><table class="table table-bordered">';
					html += '<thead><tr><th>請假類別</th><th>總請假分鐘</th></tr></thead><tbody>';

					for (const typeName in leaveSummary) {
						html += `<tr><td>${typeName}</td><td>${leaveSummary[typeName]} 分鐘</td></tr>`;
					}

					html += '</tbody></table>';
				}

				$('#detailTitle').text(`${detail.doctor_name} 的詳細${type === 'work' ? '工作' : '請假'}資料`);
				$('#detailTableContainer').html(html);
				$('#detailModal').show();
			}



			function downloadChart(canvasId, title) {
				const canvas = document.getElementById(canvasId);
				const link = document.createElement('a');
				link.href = canvas.toDataURL('image/png');
				link.download = `${title}_${new Date().toISOString().slice(0, 10)}.png`;
				link.click();
			}

			function downloadAllChartsPDF() {
				const { jsPDF } = window.jspdf;
				const pdf = new jsPDF('p', 'mm', 'a4');
				Promise.all([
					html2canvas(document.getElementById('workChart')),
					html2canvas(document.getElementById('leaveChart'))
				]).then((canvases) => {
					canvases.forEach((canvas, idx) => {
						const imgData = canvas.toDataURL('image/png');
						const imgProps = pdf.getImageProperties(imgData);
						const pdfWidth = pdf.internal.pageSize.getWidth();
						const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
						if (idx > 0) pdf.addPage();
						pdf.addImage(imgData, 'PNG', 0, 20, pdfWidth, pdfHeight - 20);
					});
					pdf.save(`治療師統計報表_${new Date().toISOString().slice(0, 10)}.pdf`);
				});
			}

			function downloadAllDataExcel() {
				let wb = XLSX.utils.book_new();

				// 🛠 出勤資料（明細 + 加總）
				let workRows = [["日期", "治療師", "上班時間", "下班時間", "遲到(分鐘)", "加班(分鐘)", "總工時(小時)"]];
				let workSummary = {}; // 暫存加總資料

				workData.forEach(doctor => {
					if (!workSummary[doctor.doctor_name]) {
						workSummary[doctor.doctor_name] = {
							late: 0,
							overtime: 0,
							total: 0,
							absent: 0,
							days: 0
						};
					}
					doctor.details.forEach(detail => {
						workRows.push([
							detail.work_date,
							doctor.doctor_name,
							detail.clock_in_time,
							detail.clock_out_time,
							detail.late_minutes !== '-' ? `${detail.late_minutes} 分鐘` : '-',
							detail.overtime_minutes !== '-' ? `${detail.overtime_minutes} 分鐘` : '-',
							detail.total_hours === '曠工' ? '⚠ 曠工' : `${detail.total_hours} 小時`
						]);

						if (detail.total_hours === '曠工') {
							workSummary[doctor.doctor_name].absent++;
						} else {
							workSummary[doctor.doctor_name].late += (detail.late_minutes !== '-' ? Number(detail.late_minutes) : 0);
							workSummary[doctor.doctor_name].overtime += (detail.overtime_minutes !== '-' ? Number(detail.overtime_minutes) : 0);
							workSummary[doctor.doctor_name].total += (detail.total_hours !== '-' ? Number(detail.total_hours) : 0);
							workSummary[doctor.doctor_name].days++;
						}
					});
				});

				// ✨ 出勤資料加總（每位治療師一行）
				workRows.push([]);
				workRows.push(["治療師", "總遲到(分鐘)", "總加班(分鐘)", "總工時(小時)", "曠工次數", "曠工率(%)"]);
				Object.keys(workSummary).forEach(name => {
					const s = workSummary[name];
					const totalDays = s.days + s.absent;
					const absentRate = totalDays > 0 ? (s.absent / totalDays * 100).toFixed(1) : '0.0';
					workRows.push([
						name,
						s.late,
						s.overtime,
						s.total.toFixed(2),
						s.absent,
						`${absentRate}%`
					]);
				});

				let workSheet = XLSX.utils.aoa_to_sheet(workRows);
				XLSX.utils.book_append_sheet(wb, workSheet, "出勤資料");

				// 🛠 請假資料（明細 + 加總）
				let leaveRows = [["治療師", "請假類別", "起", "訖", "原因", "分鐘"]];
				let leaveSummary = {}; // 暫存請假加總

				leaveData.forEach(doctor => {
					if (!leaveSummary[doctor.doctor_name]) {
						leaveSummary[doctor.doctor_name] = {};
					}
					for (const [typeName, arr] of Object.entries(doctor.details)) {
						if (!leaveSummary[doctor.doctor_name][typeName]) {
							leaveSummary[doctor.doctor_name][typeName] = 0;
						}
						arr.forEach(detail => {
							leaveRows.push([
								doctor.doctor_name,
								typeName,
								detail.start,
								detail.end,
								detail.reason,
								`${detail.minutes} 分鐘`
							]);
							leaveSummary[doctor.doctor_name][typeName] += detail.minutes;
						});
					}
				});

				// ✨ 請假資料加總（每位治療師一行）
				leaveRows.push([]);
				leaveRows.push(["治療師", "請假類別", "總請假分鐘"]);
				Object.keys(leaveSummary).forEach(name => {
					Object.keys(leaveSummary[name]).forEach(type => {
						leaveRows.push([
							name,
							type,
							`${leaveSummary[name][type]} 分鐘`
						]);
					});
				});

				let leaveSheet = XLSX.utils.aoa_to_sheet(leaveRows);
				XLSX.utils.book_append_sheet(wb, leaveSheet, "請假資料");

				// 下載
				XLSX.writeFile(wb, `治療師統計資料_${new Date().toISOString().slice(0, 10)}.xlsx`);
			}


			$('#filterForm').submit(function (e) {
				e.preventDefault();
				fetchData();
			});

			$(document).ready(function () {
				const now = new Date();
				const y = now.getFullYear(), m = now.getMonth() + 1, d = now.getDate();
				for (let i = 2023; i <= y; i++) $('#year').append(`<option value="${i}" ${i === y ? 'selected' : ''}>${i}</option>`);
				for (let i = 1; i <= 12; i++) $('#month').append(`<option value="${i}" ${i === m ? 'selected' : ''}>${i}</option>`);
				for (let i = 1; i <= 31; i++) $('#day').append(`<option value="${i}" ${i === d ? 'selected' : ''}>${i}</option>`);
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