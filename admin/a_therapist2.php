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
	<title>Single course</title>
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
								<li class="rd-nav-item active"><a class="rd-nav-link" href="">關於治療師</a>
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
								<li class="rd-nav-item"><a class="rd-nav-link" href="a_comprehensive.php">綜合</a>
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
												href="a_doctorlistmod.php">修改治療師資料</a>
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
		<!-- Page Header-->
		<div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
			<!-- Breadcrumbs-->
			<section class="breadcrumbs-custom breadcrumbs-custom-svg">
				<div class="container">
					<p class="heading-1 breadcrumbs-custom-title">預約資料</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="a_index.php">關於治療師</a></li>
						<li><a href="">治療師時間表</a></li>
						<li class="active">預約資料</li>
					</ul>
				</div>
			</section>
		</div>

		<?php
		// 引入資料庫連線檔案
		require '../db.php';

		// 接收前端傳遞的日期和治療師參數，並設置默認值
		$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); // 默認為今日日期
		$filter_doctor = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : 'all'; // 默認為「所有治療師」
		
		// 構建查詢條件
		$where_conditions = "1=1"; // 起始條件為 1=1，保證 SQL 查詢語法正確
		
		// 如果有日期參數，將其加入查詢條件，並防止 SQL 注入
		if (!empty($filter_date)) {
			$filter_date = mysqli_real_escape_string($link, $filter_date);
			$where_conditions .= " AND doctorshift.date = '$filter_date'";
		}

		// 如果選擇了特定的治療師，將其加入查詢條件
		if ($filter_doctor !== 'all') {
			$filter_doctor = mysqli_real_escape_string($link, $filter_doctor);
			$where_conditions .= " AND doctorshift.doctor_id = '$filter_doctor'";
		}

		// 接收分頁參數，並設置默認值
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1; // 當前頁數，默認為第 1 頁
		$rowsPerPage = isset($_GET['rowsPerPage']) ? (int) $_GET['rowsPerPage'] : 3; // 每頁顯示筆數，默認為 3 筆
		$offset = ($page - 1) * $rowsPerPage; // 計算查詢偏移量
		
		// 查詢總筆數
		$countQuery = "
    SELECT COUNT(*) AS total
    FROM appointment
    JOIN shifttime ON appointment.shifttime_id = shifttime.shifttime_id
    JOIN people ON appointment.people_id = people.people_id
    JOIN doctorshift ON appointment.doctorshift_id = doctorshift.doctorshift_id
    JOIN doctor ON doctorshift.doctor_id = doctor.doctor_id
    WHERE $where_conditions
";
		$countResult = mysqli_query($link, $countQuery);
		$totalRows = mysqli_fetch_assoc($countResult)['total'];
		$totalPages = ceil($totalRows / $rowsPerPage); // 計算總頁數
		
		// 修改主查詢，加入 LIMIT 和 OFFSET
		$query = "
    SELECT
        doctorshift.date AS visit_date,
        shifttime.shifttime AS visit_time,
        people.name AS patient_name,
        doctor.doctor AS doctor_name,
        appointment.created_at AS appointment_time
    FROM
        appointment
    JOIN
        shifttime ON appointment.shifttime_id = shifttime.shifttime_id
    JOIN
        people ON appointment.people_id = people.people_id
    JOIN
        doctorshift ON appointment.doctorshift_id = doctorshift.doctorshift_id
    JOIN
        doctor ON doctorshift.doctor_id = doctor.doctor_id
    WHERE
        $where_conditions
    ORDER BY
        doctorshift.date ASC,
        shifttime.shifttime ASC
    LIMIT $rowsPerPage OFFSET $offset
";


		// 執行 SQL 查詢，並檢查是否有錯誤
		$result = mysqli_query($link, $query);
		if (!$result) {
			die("查詢失敗：" . mysqli_error($link)); // 如果查詢失敗，輸出錯誤訊息
		}

		// 初始化一個空的陣列來存儲查詢結果
		$appointments = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$appointments[] = $row; // 將每一行數據添加到陣列中
		}

		// 釋放查詢結果資源
		mysqli_free_result($result);

		?>


		<!-- Bordered Row Table -->
		<section class="section section-lg bg-default text-center">
			<div class="container">
				<div class="row justify-content-sm-center">
					<div class="col-md-10 col-xl-8">
						<!-- 搜尋框與按鈕區塊 -->
						<form class="search-form" method="GET" action="a_therapist2.php"
							style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 20px; width: 100%;">
							<!-- 回到上一頁按鈕 -->

							<a href="a_therapist.php" class="button button-xs button-primary button-nina">返回上一頁</a>

							<label for="year">年份:</label>
							<select id="year" name="year" onchange="filterByDate()"></select>

							<label for="month">月份:</label>
							<select id="month" name="month" onchange="filterByDate()"></select>

							<label for="day">日期:</label>
							<select id="day" name="day" onchange="filterByDate()"></select>
							<!-- 選擇治療師 -->
							<label for="the">治療師:</label>
							<select id="the" name="doctor_id">
								<!-- 新增「所有治療師」選項 -->
								<option value="all">所有治療師</option>
								<?php
								include '../db.php'; // 引入資料庫連線檔案
								
								// 查詢等級為「治療師」的資料
								$query = "
        SELECT d.doctor_id, d.doctor
        FROM doctor d
        INNER JOIN user u ON d.user_id = u.user_id
        INNER JOIN grade g ON u.grade_id = g.grade_id
        WHERE g.grade_id = 2
    ";
								$result = mysqli_query($link, $query);

								// 檢查查詢結果
								if (!$result) {
									echo "Error fetching doctor data: " . mysqli_error($link);
									exit;
								}

								// 輸出查詢結果到下拉選單
								while ($row = mysqli_fetch_assoc($result)) {
									echo "<option value='" . $row['doctor_id'] . "'>" . htmlspecialchars($row['doctor']) . "</option>";
								}
								?>
							</select>


							<div style="flex: 1;">
								<select name="rowsPerPage" onchange="this.form.submit()"
									style="padding: 10px 15px; font-size: 16px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
									<option value="3" <?php echo $rowsPerPage == 3 ? 'selected' : ''; ?>>3筆/頁</option>
									<option value="5" <?php echo $rowsPerPage == 5 ? 'selected' : ''; ?>>5筆/頁</option>
									<option value="10" <?php echo $rowsPerPage == 10 ? 'selected' : ''; ?>>10筆/頁</option>
									<option value="20" <?php echo $rowsPerPage == 20 ? 'selected' : ''; ?>>20筆/頁</option>
									<option value="25" <?php echo $rowsPerPage == 25 ? 'selected' : ''; ?>>25筆/頁</option>
									<option value="50" <?php echo $rowsPerPage == 50 ? 'selected' : ''; ?>>50筆/頁</option>
								</select>
							</div>
						</form>





						<!-- 表格渲染區域 -->
						<div class="table-novi table-custom-responsive" style="font-size: 16px; overflow-x: auto;">
							<table class="table-custom table-custom-bordered"
								style="width: 100%; border-collapse: collapse;">
								<thead>
									<tr>
										<th style="padding: 10px; text-align: left;">看診日期</th>
										<th style="padding: 10px; text-align: left;">看診時間</th>
										<th style="padding: 10px; text-align: left;">患者姓名</th>
										<th style="padding: 10px; text-align: left;">治療師姓名</th>
										<th style="padding: 10px; text-align: left;">預約時間</th>
										<th style="padding: 10px; text-align: center;">選項</th>
									</tr>
								</thead>
								<tbody>
									<?php if (!empty($appointments)): ?>
										<?php foreach ($appointments as $appointment): ?>
											<tr>
												<td style="padding: 10px;">
													<?php echo htmlspecialchars($appointment['visit_date']); ?>
												</td>
												<td style="padding: 10px;">
													<?php echo htmlspecialchars($appointment['visit_time']); ?>
												</td>
												<td style="padding: 10px;">
													<?php echo htmlspecialchars($appointment['patient_name']); ?>
												</td>
												<td style="padding: 10px;">
													<?php echo htmlspecialchars($appointment['doctor_name']); ?>
												</td>
												<td style="padding: 10px;">
													<?php echo htmlspecialchars($appointment['appointment_time']); ?>
												</td>
												<td style="padding: 10px; text-align: center;">
													<button
														style="padding: 6px 12px; font-size: 12px; border: none; background-color: #00A896; color: white; cursor: pointer; border-radius: 4px;">
														操作
													</button>
												</td>
											</tr>
										<?php endforeach; ?>
									<?php else: ?>
										<tr>
											<td colspan="6" style="padding: 10px; text-align: center; color: #999;">
												沒有找到符合條件的資料</td>
										</tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div>


						<script>
							const yearSelect = document.getElementById('year');
							const monthSelect = document.getElementById('month');
							const daySelect = document.getElementById('day');
							const doctorSelect = document.getElementById('the'); // 治療師篩選

							// 初始化 URL 參數
							const urlParams = new URLSearchParams(window.location.search);
							const selectedDate = urlParams.get('date') || new Date().toISOString().split('T')[0];
							const selectedDoctor = urlParams.get('doctor_id') || 'all';
							const [currentYear, currentMonth, currentDay] = selectedDate.split('-').map(Number);

							// 初始化年份選單
							for (let year = currentYear - 5; year <= currentYear + 5; year++) {
								const option = document.createElement('option');
								option.value = year;
								option.textContent = year;
								if (year === currentYear) option.selected = true;
								yearSelect.appendChild(option);
							}

							// 初始化月份選單
							for (let month = 1; month <= 12; month++) {
								const option = document.createElement('option');
								option.value = String(month).padStart(2, '0');
								option.textContent = String(month).padStart(2, '0');
								if (month === currentMonth) option.selected = true;
								monthSelect.appendChild(option);
							}

							// 更新日期選單
							function updateDays() {
								const year = parseInt(yearSelect.value);
								const month = parseInt(monthSelect.value);
								const daysInMonth = new Date(year, month, 0).getDate();

								daySelect.innerHTML = '';
								for (let day = 1; day <= daysInMonth; day++) {
									const option = document.createElement('option');
									option.value = String(day).padStart(2, '0');
									option.textContent = String(day).padStart(2, '0');
									if (day === currentDay && year === currentYear && month === currentMonth) {
										option.selected = true;
									}
									daySelect.appendChild(option);
								}
							}

							// 篩選功能
							function filterByDate() {
								const selectedYear = document.getElementById('year').value;
								const selectedMonth = document.getElementById('month').value.padStart(2, '0');
								const selectedDay = document.getElementById('day').value.padStart(2, '0');
								const selectedDate = `${selectedYear}-${selectedMonth}-${selectedDay}`;
								const selectedDoctor = document.getElementById('the').value;

								// 重定向到新 URL，帶有篩選參數
								window.location.href = `a_therapist2.php?date=${selectedDate}&doctor_id=${selectedDoctor}`;
							}



							// 初始化治療師選單
							if (doctorSelect) {
								Array.from(doctorSelect.options).forEach((option) => {
									if (option.value === selectedDoctor) {
										option.selected = true;
									}
								});
							}

							// 綁定事件
							yearSelect.addEventListener('change', () => {
								updateDays();
								filterByDate();
							});

							monthSelect.addEventListener('change', () => {
								updateDays();
								filterByDate();
							});

							daySelect.addEventListener('change', filterByDate);
							doctorSelect.addEventListener('change', filterByDate);

							// 初始化日期選單
							updateDays();
						</script>


						<!-- 分頁顯示區域 -->
						<div id="pagination"
							style="text-align: center; margin-top: 10px; font-size: 14px; color: #333;">
							<?php if ($totalPages > 1): ?>
								<?php for ($i = 1; $i <= $totalPages; $i++): ?>
									<button
										onclick="location.href='?page=<?php echo $i; ?>&rowsPerPage=<?php echo $rowsPerPage; ?>&date=<?php echo htmlspecialchars($filter_date); ?>&doctor_id=<?php echo htmlspecialchars($filter_doctor); ?>'"
										style="margin: 0 5px; padding: 5px 10px; border: none; background-color: <?php echo $i == $page ? '#00A896' : '#f0f0f0'; ?>; color: <?php echo $i == $page ? 'white' : 'black'; ?>; border-radius: 4px; cursor: pointer;">
										<?php echo $i; ?>
									</button>
								<?php endfor; ?>
								<span>| 共 <?php echo $totalPages; ?> 頁</span>
							<?php endif; ?>
						</div>



					</div>
				</div>
			</div>
		</section>

		<!-- Global Mailform Output-->
		<div class="snackbars" id="form-output-global"></div>
		<!-- Javascript-->
		<script src="js/core.min.js"></script>
		<script src="js/script.js"></script>
</body>

</html>