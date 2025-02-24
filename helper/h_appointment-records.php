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
	$sql = "SELECT user.account, doctor.doctor AS name 
            FROM user 
            JOIN doctor ON user.user_id = doctor.user_id 
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

//預約紀錄

?>



<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
	<!-- Site Title-->
	<title>助手-預約紀錄</title>
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
	</style>
	<style>
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

		/*預約紀錄 */
		/* 搜尋 & 篩選 & 筆數選擇 置於同一行，並靠右對齊 */
		.search-container {
			display: flex;
			justify-content: flex-end;
			/* 讓內容靠右對齊 */
			align-items: center;
			gap: 10px;
			/* 設置元素間距 */
			margin-bottom: 10px;
			flex-wrap: wrap;
		}

		.search-container form {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.search-container select,
		.search-container input,
		.search-container button {
			padding: 5px;
			font-size: 14px;
		}

		/* 分頁 & 資訊 */
		.pagination-wrapper {
			display: flex;
			justify-content: space-between;
			/* 左右對齊 */
			align-items: center;
			margin-top: 20px;
			flex-wrap: wrap;
		}

		/* 分頁資訊 (靠右對齊) */
		.pagination-info {
			font-size: 14px;
			color: #555;
			display: flex;
			justify-content: flex-end;
			/* 讓內容靠右 */
			width: 100%;
		}

		/* 頁碼按鈕 (置中) */
		.pagination-container {
			display: flex;
			justify-content: center;
			/* 讓頁碼置中 */
			flex-grow: 1;
			gap: 10px;
		}

		.pagination-container a,
		.pagination-container strong {
			padding: 5px 10px;
			text-decoration: none;
			border: 1px solid #ddd;
			border-radius: 4px;
			color: #007bff;
		}

		.pagination-container strong {
			font-weight: bold;
			background-color: #007bff;
			color: white;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 20px;
		}

		th,
		td {
			padding: 10px;
			text-align: center;
			border: 1px solid #ddd;
			white-space: nowrap;
			/* 禁止換行 */
		}

		th {
			background-color: #f2f2f2;
			font-weight: bold;
		}


		/* 響應式處理 */
		@media (max-width: 768px) {
			.search-container {
				justify-content: center;
				flex-wrap: wrap;
			}

			.pagination-wrapper {
				flex-direction: column;
				align-items: center;
			}

			.pagination-info {
				justify-content: center;
				margin-bottom: 10px;
				width: auto;
			}

			.pagination-container {
				justify-content: center;
			}
		}

		/* 狀態 */
		/* 背景遮罩 */
		.modal-overlay {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.5);
			backdrop-filter: blur(5px);
			display: flex;
			justify-content: center;
			align-items: center;
			z-index: 1000;
			display: none;
		}

		/* 彈出式表單 */
		.modal-container {
			background: #fff;
			padding: 20px;
			border-radius: 10px;
			box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
			width: 350px;
			position: relative;
			display: flex;
			flex-direction: column;
			gap: 10px;
		}

		/* 關閉按鈕 */
		.modal-close {
			position: absolute;
			top: 10px;
			right: 10px;
			background: none;
			border: none;
			font-size: 18px;
			cursor: pointer;
		}

		/* 按鈕 */
		.modal-actions {
			display: flex;
			justify-content: space-between;
			margin-top: 15px;
		}

		.modal-actions button {
			padding: 10px 15px;
			border: none;
			border-radius: 5px;
			font-size: 16px;
			cursor: pointer;
		}

		.modal-actions .confirm {
			background-color: #007bff;
			color: white;
		}

		.modal-actions .cancel {
			background-color: #ccc;
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
								<li class="rd-nav-item"><a class="rd-nav-link" href="#">班表</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_doctorshift.php">治療師班表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_numberpeople.php">當天人數及時段</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_assistantshift.php">每月班表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_leave.php">請假申請</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
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
								<li class="rd-nav-item active"><a class="rd-nav-link" href="#">紀錄</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="h_medical-record.php">看診紀錄</a>
										</li>
										<li class="rd-dropdown-item active"><a class="rd-dropdown-link"
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
						echo $姓名;
						?>
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
					<!-- <p class="breadcrumbs-custom-subtitle">Terms of Use</p> -->
					<p class="heading-1 breadcrumbs-custom-title">預約紀錄</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="h_index.php">首頁</a></li>
						<li><a href="#">紀錄</a></li>
						<li class="active">預約紀錄</li>
					</ul>
				</div>
			</section>
		</div>
		<!--標題-->

		<br />

		<!--預約紀錄-->
		<?php
		require '../db.php';

		// 接收篩選條件
		$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
		$selected_doctor = isset($_GET['doctor']) ? trim($_GET['doctor']) : '全部';

		// 取得筆數選擇 (預設 10)
		$records_per_page = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 10;

		// 取得當前頁數
		$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
		$offset = ($page - 1) * $records_per_page;

		// SQL 查詢條件
		$where_clauses = ["s.status_name != '已看診'", "u.grade_id != 3"]; // 過濾已看診 & 助手
		$params = [];
		$types = "";

		// 若選擇特定醫生，篩選條件
		if ($selected_doctor !== '全部') {
			$where_clauses[] = "d.doctor = ?";
			$params[] = $selected_doctor;
			$types .= "s";
		}

		// 若有輸入姓名搜尋條件
		if (!empty($search_name)) {
			$where_clauses[] = "p.name LIKE ?";
			$params[] = "%{$search_name}%";
			$types .= "s";
		}

		// 組合 SQL 查詢條件
		$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

		// 計算總筆數
		$count_sql = "
    SELECT COUNT(*) AS total 
    FROM appointment a
    LEFT JOIN people p ON a.people_id = p.people_id
    LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
    LEFT JOIN user u ON d.user_id = u.user_id  -- 連結 user 表取得 grade_id
    LEFT JOIN status s ON a.status_id = s.status_id
    $where_sql
";
		$count_stmt = $link->prepare($count_sql);
		if (!empty($params)) {
			$count_stmt->bind_param($types, ...$params);
		}
		$count_stmt->execute();
		$count_result = $count_stmt->get_result();
		$total_records = $count_result->fetch_assoc()['total'] ?? 0;
		$total_pages = ceil($total_records / $records_per_page);

		// 查詢預約資料
		$data_sql = "
    SELECT 
        a.appointment_id AS id,
        COALESCE(p.name, '未預約') AS name,
        CASE 
            WHEN p.gender_id = 1 THEN '男' 
            WHEN p.gender_id = 2 THEN '女' 
            ELSE '無資料' 
        END AS gender,
        IFNULL(CONCAT(DATE_FORMAT(p.birthday, '%Y-%m-%d'), ' (', TIMESTAMPDIFF(YEAR, p.birthday, CURDATE()), '歲)'), '無資料') AS birthday_with_age,
        DATE_FORMAT(ds.date, '%Y-%m-%d') AS appointment_date,
        st.shifttime AS shifttime,
        d.doctor AS doctor_name,
        COALESCE(a.note, '無') AS note,
        COALESCE(s.status_id, 1) AS status_id,  -- 確保 `status_id` 預設為 1 (預約)
        COALESCE(s.status_name, '未設定') AS status_name  
    FROM appointment a
    LEFT JOIN people p ON a.people_id = p.people_id
    LEFT JOIN shifttime st ON a.shifttime_id = st.shifttime_id
    LEFT JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
    LEFT JOIN doctor d ON ds.doctor_id = d.doctor_id
    LEFT JOIN user u ON d.user_id = u.user_id  -- 連結 user 表取得 grade_id
    LEFT JOIN status s ON a.status_id = s.status_id  
    $where_sql
    ORDER BY ds.date, st.shifttime
    LIMIT ?, ?
";

		$data_stmt = $link->prepare($data_sql);
		$params[] = $offset;
		$params[] = $records_per_page;
		$types .= "ii";

		$data_stmt->bind_param($types, ...$params);
		$data_stmt->execute();
		$result = $data_stmt->get_result();
		?>


		<section class="section">
			<div class="container">
				<div class="row justify-content-center">
					<div class="col-lg-12">

						<!-- 搜尋 & 篩選 & 筆數選擇 -->
						<div class="search-container">
							<form method="GET" action="">
								<input type="text" name="search_name" placeholder="請輸入搜尋姓名"
									value="<?php echo htmlspecialchars($search_name); ?>">

								<select name="doctor" onchange="this.form.submit()">
									<option value="全部" <?php echo ($selected_doctor === '全部') ? 'selected' : ''; ?>>全部
									</option>
									<?php
									$doctor_query = $link->query("
        SELECT d.doctor 
        FROM doctor d
        JOIN user u ON d.user_id = u.user_id
        WHERE u.grade_id = 2
    ");
									while ($row = $doctor_query->fetch_assoc()) {
										$doctor_name = $row['doctor'];
										$selected = ($selected_doctor === $doctor_name) ? 'selected' : '';
										echo "<option value='$doctor_name' $selected>$doctor_name</option>";
									}
									?>
								</select>


								<button type="submit">搜尋</button>
							</form>

							<!-- <div class="limit-selector"> -->
							<!-- <label for="limit">每頁顯示筆數:</label> -->
							<select id="limit" name="limit" onchange="updateLimit()">
								<?php
								$limits = [3, 5, 10, 20, 50, 100];
								foreach ($limits as $limit) {
									$selected = ($limit == $records_per_page) ? "selected" : "";
									echo "<option value='$limit' $selected>$limit 筆/頁</option>";
								}
								?>
							</select>
							<!-- </div> -->
						</div>

						<script>
							function updateLimit() {
								var limit = document.getElementById("limit").value;
								window.location.href = "?search_name=<?php echo urlencode($search_name); ?>&doctor=<?php echo urlencode($selected_doctor); ?>&limit=" + limit;
							}
						</script>

						<!-- 表格 -->
						<div class="table-responsive">
							<table>
								<thead>
									<tr>
										<th>#</th>
										<th>姓名</th>
										<th>性別</th>
										<th>生日 (年齡)</th>
										<th>看診日期 (星期)</th>
										<th>看診時間</th>
										<th>治療師</th>
										<th>備註</th>
										<th>狀態</th>
										<th>選項</th>
									</tr>
								</thead>
								<tbody>
									<?php if ($result->num_rows > 0): ?>
										<?php while ($row = $result->fetch_assoc()): ?>
											<tr>
												<td><?php echo htmlspecialchars($row['id']); ?></td>
												<td><?php echo htmlspecialchars($row['name']); ?></td>
												<td><?php echo htmlspecialchars($row['gender']); ?></td>
												<td><?php echo htmlspecialchars($row['birthday_with_age']); ?></td>
												<td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
												<td><?php echo htmlspecialchars($row['shifttime']); ?></td>
												<td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
												<td><?php echo htmlspecialchars($row['note']); ?></td>
												<td>
													<select class="status-dropdown" data-id="<?php echo $row['id']; ?>"
														data-doctor-id="<?php echo $row['doctor_id']; ?>" <?php echo (in_array($row['status_id'], [4, 5, 6])) ? 'disabled' : ''; ?>>
														<option value="1" <?php echo ($row['status_id'] == 1) ? 'selected' : ''; ?>>預約</option>
														<option value="2" <?php echo ($row['status_id'] == 2) ? 'selected' : ''; ?>>修改</option>
														<option value="3" <?php echo ($row['status_id'] == 3) ? 'selected' : ''; ?>>報到</option>
														<option value="4" <?php echo ($row['status_id'] == 4) ? 'selected' : ''; ?>>請假</option>
														<option value="5" <?php echo ($row['status_id'] == 5) ? 'selected' : ''; ?>>爽約</option>
														<option value="8" <?php echo ($row['status_id'] == 8) ? 'selected' : ''; ?>>看診中</option>
														<option value="6" <?php echo ($row['status_id'] == 6) ? 'selected' : ''; ?>>已看診</option>
													</select>
												</td>


												<td>
													<a href="h_print-appointment.php?id=<?php echo $row['id']; ?>"
														target="_blank">
														<button type="button">列印預約單</button>
													</a>
												</td>
											</tr>
										<?php endwhile; ?>
									<?php else: ?>
										<tr>
											<td colspan="10">無符合條件的資料</td>
										</tr>
									<?php endif; ?>
								</tbody>
							</table>
							<!-- 分頁資訊 + 頁碼 -->
							<div class="pagination-wrapper">
								<!-- 分頁資訊 (靠右) -->
								<div class="pagination-info">
									第 <?php echo $page; ?> 頁 / 共 <?php echo $total_pages; ?> 頁（總共
									<strong><?php echo $total_records; ?></strong> 筆資料）
								</div>

								<!-- 頁碼按鈕 (置中) -->
								<div class="pagination-container">
									<?php if ($page > 1): ?>
										<a
											href="?page=<?php echo $page - 1; ?>&limit=<?php echo $records_per_page; ?>&search_name=<?php echo urlencode($search_name); ?>">上一頁</a>
									<?php endif; ?>

									<?php for ($i = 1; $i <= $total_pages; $i++): ?>
										<?php if ($i == $page): ?>
											<strong><?php echo $i; ?></strong>
										<?php else: ?>
											<a
												href="?page=<?php echo $i; ?>&limit=<?php echo $records_per_page; ?>&search_name=<?php echo urlencode($search_name); ?>"><?php echo $i; ?></a>
										<?php endif; ?>
									<?php endfor; ?>

									<?php if ($page < $total_pages): ?>
										<a
											href="?page=<?php echo $page + 1; ?>&limit=<?php echo $records_per_page; ?>&search_name=<?php echo urlencode($search_name); ?>">下一頁</a>
									<?php endif; ?>
								</div>
							</div>
						</div>

					</div>

				</div>
			</div>
		</section>



		<!-- 修改預約 Modal -->
		<div id="modal-overlay" class="modal-overlay" style="display: none;">
			<div id="modal-container" class="modal-container">
				<span id="modal-close" class="modal-close">&times;</span>
				<h2 class="modal-title">修改預約</h2>

				<label for="doctor-select">選擇治療師：</label>
				<select id="doctor-select">
					<option value="">請選擇治療師</option>
				</select>

				<label for="appointment-date">預約日期：</label>
				<input type="date" id="appointment-date" min="<?php echo date('Y-m-d'); ?>">

				<label for="appointment-time">預約時間：</label>
				<select id="appointment-time">
					<option value="">請選擇時間</option>
				</select>

				<div class="modal-actions">
					<button id="confirm-modify" class="confirm">確認修改</button>
					<button id="cancel-modify" class="cancel">取消</button>
				</div>
			</div>
		</div>

		<script>
			document.addEventListener("DOMContentLoaded", function () {
				document.querySelectorAll(".status-dropdown").forEach((select) => {
					select.addEventListener("change", function () {
						let appointmentId = this.getAttribute("data-id");
						let doctorId = this.getAttribute("data-doctor-id");
						let selectedStatus = parseInt(this.value, 10);

						if (selectedStatus === 2) { // 進入修改模式
							openEditModal(appointmentId, doctorId);
						} else { // 更新狀態
							updateStatus(appointmentId, selectedStatus, select);
						}
					});
				});

				document.getElementById("confirm-modify").addEventListener("click", function () {
					submitEditForm();
				});

				document.getElementById("modal-close").addEventListener("click", function () {
					closeModal();
				});

				document.getElementById("cancel-modify").addEventListener("click", function () {
					closeModal();
				});

				document.getElementById("doctor-select").addEventListener("change", function () {
					let selectedDoctorId = this.value;
					if (selectedDoctorId) {
						fetchAvailableDates(selectedDoctorId);
					}
				});

				document.getElementById("appointment-date").addEventListener("change", function () {
					fetchAvailableTimes(document.getElementById("doctor-select").value, this.value);
				});
			});

			function openEditModal(appointmentId, doctorId) {
				console.log("開啟修改視窗，ID:", appointmentId);

				document.getElementById("modal-overlay").style.display = "flex";

				// 確保 appointment_id 正確設定
				document.getElementById("confirm-modify").setAttribute("data-appointment-id", appointmentId);

				fetch(`修改預約資料.php?action=fetch_appointment&appointment_id=${appointmentId}`)
					.then(response => response.json())
					.then(data => {
						if (!data.success) {
							alert(data.message);
							return;
						}

						fetchDoctors(data.data.doctor_id);
					})
					.catch(error => console.error("獲取預約資料錯誤:", error));
			}


			function fetchDoctors(selectedDoctorId) {
				fetch("修改預約資料.php?action=fetch_doctors")
					.then(response => response.json())
					.then(data => {
						if (!data.success) {
							alert("無法獲取治療師列表！");
							return;
						}
						let doctorSelect = document.getElementById("doctor-select");
						doctorSelect.innerHTML = `<option value="">請選擇治療師</option>`;
						data.data.forEach(doctor => {
							doctorSelect.innerHTML += `<option value="${doctor.doctor_id}" ${doctor.doctor_id == selectedDoctorId ? 'selected' : ''}>${doctor.name}</option>`;
						});

						fetchAvailableDates(selectedDoctorId);
					})
					.catch(error => console.error("獲取治療師錯誤:", error));
			}


			function fetchAvailableDates(doctorId) {
				fetch(`修改預約資料.php?action=fetch_dates&doctor_id=${doctorId}`)
					.then(response => response.json())
					.then(data => {
						let dateSelect = document.getElementById("appointment-date");
						dateSelect.innerHTML = `<option value="">請選擇日期</option>`;
						data.forEach(date => {
							dateSelect.innerHTML += `<option value="${date}">${date}</option>`;
						});
					})
					.catch(error => console.error("獲取日期錯誤:", error));
			}

			function fetchAvailableTimes(doctorId, date) {
				fetch(`修改預約資料.php?action=fetch_times&doctor_id=${doctorId}&date=${date}`)
					.then(response => response.json())
					.then(data => {
						if (!data || data.length === 0) {
							alert("❌ 該日期無可預約時段！");
							return;
						}

						let timeSelect = document.getElementById("appointment-time");
						timeSelect.innerHTML = `<option value="">請選擇時間</option>`;
						data.forEach(time => {
							timeSelect.innerHTML += `<option value="${time}">${time}</option>`;
						});
					})
					.catch(error => console.error("獲取時段錯誤:", error));
			}


			function submitEditForm() {
				let appointmentId = document.getElementById("confirm-modify").getAttribute("data-appointment-id");
				let doctorId = document.getElementById("doctor-select").value;
				let newDate = document.getElementById("appointment-date").value;
				let newTime = document.getElementById("appointment-time").value;

				newDate = newDate.replace(/\//g, "-"); // 確保日期格式一致

				console.log("發送的數據：", {
					appointment_id: appointmentId,
					doctor_id: doctorId,
					date: newDate,
					time: newTime
				});

				if (!appointmentId || !doctorId || !newDate || !newTime) {
					alert("❌ 送出資料有誤，請檢查是否選擇完整！");
					return;
				}

				fetch("修改預約處理.php", {
					method: "POST",
					headers: { "Content-Type": "application/json" },
					body: JSON.stringify({
						appointment_id: appointmentId,
						doctor_id: doctorId,
						date: newDate,
						time: newTime
					})
				})
					.then(response => response.json())
					.then(data => {
						console.log("後端回應：", data);
						alert(data.success ? "✅ 預約修改成功！" : "❌ " + data.message);
						if (data.success) {
							location.reload();
						}
					})
					.catch(error => console.error("更新預約錯誤:", error));

			}


			function closeModal() {
				document.getElementById("modal-overlay").style.display = "none";
			}

			function updateStatus(appointmentId, selectedStatus, select) {
				fetch("更新狀態.php", {
					method: "POST",
					headers: { "Content-Type": "application/json" },
					body: JSON.stringify({ appointment_id: appointmentId, status_id: selectedStatus })
				})
					.then(response => response.json())
					.then(data => {
						alert(data.success ? data.message : "❌ " + data.error);
						if ([4, 5, 6].includes(selectedStatus)) {
							select.disabled = true;
						}
						location.reload();
					});
			}
		</script>




		<br />
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
			  <input class="form-input" type="email" name="email" data-constraints="@Email @Required" id="footer-mail">
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
	<!-- coded by Himic-->
</body>

</html>