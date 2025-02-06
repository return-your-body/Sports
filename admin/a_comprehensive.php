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
		$等級 = $row['grade_id']; // 等級（例如 1: 醫生, 2: 護士, 等等）
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
	<title>Teachers</title>
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
								<li class="rd-nav-item"><a class="rd-nav-link" href="a_index.php">網頁編輯</a>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="">關於治療師</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_therapist.php">總人數時段表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_addds.php">治療師班表</a>
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
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="#">醫生管理</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_doctorlistadd.php">新增醫生資料</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_doctorlistmod.php">修改醫生資料</a>
										</li>
									</ul>
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
		<?php
		include '../db.php'; // 連接資料庫
		
		// 取得篩選條件，預設為「今日」
		$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
		$group_by = isset($_GET['group_by']) ? $_GET['group_by'] : '日'; // 預設按日
		$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
		$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

		// SQL 查詢 (根據選擇的時間分類)
		$group_column = ($group_by == '年') ? "YEAR(ds.date)" : (($group_by == '月') ? "CONCAT(YEAR(ds.date), '-', MONTH(ds.date))" : "ds.date");

		$sql = "
SELECT 
    d.doctor AS 醫生,
    $group_column AS 時間分類,
    SUM(i.price) AS 總收入
FROM medicalrecord m
JOIN item i ON m.item_id = i.item_id
JOIN appointment a ON m.appointment_id = a.appointment_id
JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
JOIN doctor d ON ds.doctor_id = d.doctor_id
JOIN user u ON d.user_id = u.user_id
WHERE ds.date BETWEEN ? AND ? AND u.grade_id = 2
" . ($doctor_id > 0 ? " AND d.doctor_id = ?" : "") . "
GROUP BY d.doctor, 時間分類
ORDER BY 時間分類 ASC;
";

		$stmt = mysqli_prepare($link, $sql);
		if ($doctor_id > 0) {
			mysqli_stmt_bind_param($stmt, "sss", $start_date, $end_date, $doctor_id);
		} else {
			mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
		}
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);

		// 轉換數據成 JSON 格式
		$data = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$data[] = $row;
		}
		mysqli_stmt_close($stmt);
		?>

		<style>
			body {
				font-family: Arial, sans-serif;
				text-align: center;
			}

			canvas {
				max-width: 80%;
				margin: 20px auto;
			}

			.chart-container {
				display: flex;
				justify-content: center;
				flex-wrap: wrap;
			}

			.chart-box {
				width: 45%;
				min-width: 300px;
				margin: 20px;
			}
		</style>

		<form id="filterForm">
			<label>選擇醫生：</label>
			<select name="doctor_id" id="doctor_id">
				<option value="0">所有醫生</option>
				<?php
				$doctor_sql = "SELECT d.doctor_id, d.doctor FROM doctor d JOIN user u ON d.user_id = u.user_id WHERE u.grade_id = 2";
				$doctor_result = mysqli_query($link, $doctor_sql);
				while ($doc = mysqli_fetch_assoc($doctor_result)) {
					echo "<option value='{$doc['doctor_id']}' " . ($doc['doctor_id'] == $doctor_id ? "selected" : "") . ">{$doc['doctor']}</option>";
				}
				?>
			</select>

			<label>選擇時間分類：</label>
			<select name="group_by" id="group_by">
				<option value="日" <?= ($group_by == '日') ? 'selected' : '' ?>>每日</option>
				<option value="月" <?= ($group_by == '月') ? 'selected' : '' ?>>每月</option>
				<option value="年" <?= ($group_by == '年') ? 'selected' : '' ?>>每年</option>
			</select>

			<label id="date_label">選擇日期：</label>
			<input type="date" name="start_date" id="start_date" value="<?= $start_date ?>">

			<label id="end_date_label">結束日期：</label>
			<input type="date" name="end_date" id="end_date" value="<?= $end_date ?>">
		</form>

		<div class="chart-container">
			<div class="chart-box">
				<canvas id="incomeChart"></canvas>
			</div>
			<div class="chart-box">
				<canvas id="percentageChart"></canvas>
			</div>
		</div>

		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		<script>
			// 取得篩選欄位
			const doctorSelect = document.getElementById("doctor_id");
			const groupBySelect = document.getElementById("group_by");
			const startDateInput = document.getElementById("start_date");
			const endDateInput = document.getElementById("end_date");
			const filterForm = document.getElementById("filterForm");

			// 監聽所有篩選條件變更，並自動更新數據
			filterForm.addEventListener("change", function () {
				updateCharts();
			});

			function updateCharts() {
				const params = new URLSearchParams({
					doctor_id: doctorSelect.value,
					group_by: groupBySelect.value,
					start_date: startDateInput.value,
					end_date: endDateInput.value
				});

				fetch("a_comprehensive.php?" + params.toString())
					.then(response => response.json())
					.then(data => {
						renderCharts(data);
					});
			}

			function renderCharts(chartData) {
				const labels = chartData.map(d => d.時間分類);
				const values = chartData.map(d => d.總收入);
				const doctors = chartData.map(d => d.醫生);

				// 長條圖 - 每日/月/年 收入
				new Chart(document.getElementById("incomeChart"), {
					type: "bar",
					data: {
						labels: labels,
						datasets: [{
							label: "收入",
							data: values,
							backgroundColor: "rgba(75, 192, 192, 0.2)",
							borderColor: "rgba(75, 192, 192, 1)",
							borderWidth: 1
						}]
					},
					options: {
						responsive: true,
						plugins: {
							legend: { display: false },
							title: { display: true, text: "收入統計 - " + groupBySelect.value }
						},
						scales: {
							y: { beginAtZero: true }
						}
					}
				});

				// 圓餅圖 - 醫生收入佔比
				new Chart(document.getElementById("percentageChart"), {
					type: "pie",
					data: {
						labels: doctors,
						datasets: [{
							label: "佔比 (%)",
							data: values,
							backgroundColor: [
								"rgba(255, 99, 132, 0.6)",
								"rgba(54, 162, 235, 0.6)",
								"rgba(255, 206, 86, 0.6)",
								"rgba(75, 192, 192, 0.6)",
								"rgba(153, 102, 255, 0.6)"
							],
							borderColor: [
								"rgba(255, 99, 132, 1)",
								"rgba(54, 162, 235, 1)",
								"rgba(255, 206, 86, 1)",
								"rgba(75, 192, 192, 1)",
								"rgba(153, 102, 255, 1)"
							],
							borderWidth: 1
						}]
					},
					options: {
						responsive: true,
						plugins: {
							title: { display: true, text: "醫生收入佔比" }
						}
					}
				});
			}

			// 預設載入當天數據
			updateCharts();
		</script>


	</div>
	<!-- Global Mailform Output-->
	<div class="snackbars" id="form-output-global"></div>
	<!-- Javascript-->
	<script src="js/core.min.js"></script>
	<script src="js/script.js"></script>
</body>

</html>