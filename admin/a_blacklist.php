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
								<li class="rd-nav-item"><a class="rd-nav-link" href="a_comprehensive.php">綜合</a>
								</li>
								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="a_comprehensive.php">綜合</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">Single teacher</a>
										</li>
									</ul>
								</li> -->
								<li class="rd-nav-item active"><a class="rd-nav-link" href="">用戶管理</a>
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
		<!-- Page Header-->
		<div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
			<!-- Breadcrumbs-->
			<section class="breadcrumbs-custom breadcrumbs-custom-svg">
				<div class="container">
					<p class="heading-1 breadcrumbs-custom-title">黑名單</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="a_index.php">首頁</a></li>
						<li><a href="">用戶管理</a></li>
						<li class="active">黑名單</li>
					</ul>
				</div>
			</section>

			<?php
			// 引入資料庫連接
			include '../db.php';

			// 自動檢查黑名單過期
			$sql = "
    SELECT people_id, blacklist_end_date 
    FROM people 
    WHERE black >= 3";
			$result = mysqli_query($link, $sql);
			while ($row = mysqli_fetch_assoc($result)) {
				if (new DateTime() > new DateTime($row['blacklist_end_date'])) {
					$reset_sql = "UPDATE people SET black = 0, blacklist_start_date = NULL, blacklist_end_date = NULL WHERE people_id = ?";
					$stmt = mysqli_prepare($link, $reset_sql);
					mysqli_stmt_bind_param($stmt, "i", $row['people_id']);
					mysqli_stmt_execute($stmt);
				}
			}

			// 自動檢查違規次數，並新增黑名單資料（剩餘時間 1 分鐘）
			$update_blacklist_sql = "
    UPDATE people
    SET blacklist_start_date = NOW(), 
        blacklist_end_date = DATE_ADD(NOW(), INTERVAL 1 MINUTE)
    WHERE black >= 3 AND blacklist_start_date IS NULL";
			mysqli_query($link, $update_blacklist_sql);

			// 處理搜尋
			$search = $_GET['search'] ?? '';
			$rowsPerPage = $_GET['rowsPerPage'] ?? 10;
			$page = $_GET['page'] ?? 1;
			$offset = ($page - 1) * $rowsPerPage;

			// 定義函數計算剩餘時間
			function calculateRemainingTime($end_date)
			{
				$current_date = new DateTime();
				$end_date = new DateTime($end_date);

				if ($current_date > $end_date) {
					return "已解除";
				} else {
					$interval = $current_date->diff($end_date);
					return $interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s;
				}
			}

			// 查詢違規次數資料
			$sql = "
    SELECT people_id, name, idcard, black, blacklist_end_date 
    FROM people 
    WHERE black > 0";
			if (!empty($search)) {
				$sql .= " AND idcard LIKE ? ";
			}
			$sql .= " LIMIT ?, ?";
			$stmt = mysqli_prepare($link, $sql);
			if (!empty($search)) {
				$likeSearch = "%$search%";
				mysqli_stmt_bind_param($stmt, "sii", $likeSearch, $offset, $rowsPerPage);
			} else {
				mysqli_stmt_bind_param($stmt, "ii", $offset, $rowsPerPage);
			}
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);

			$users = [];
			while ($row = mysqli_fetch_assoc($result)) {
				if ($row['black'] >= 3) {
					$row['remaining_time'] = calculateRemainingTime($row['blacklist_end_date']);
				} else {
					$row['remaining_time'] = "違規次數：{$row['black']}";
				}
				$users[] = $row;
			}

			// 獲取總頁數
			$count_sql = "
    SELECT COUNT(*) AS total 
    FROM people 
    WHERE black > 0";
			if (!empty($search)) {
				$count_sql .= " AND idcard LIKE ?";
				$stmt = mysqli_prepare($link, $count_sql);
				mysqli_stmt_bind_param($stmt, "s", $likeSearch);
				mysqli_stmt_execute($stmt);
				$count_result = mysqli_stmt_get_result($stmt);
			} else {
				$count_result = mysqli_query($link, $count_sql);
			}
			$totalRows = mysqli_fetch_assoc($count_result)['total'] ?? 0;
			$totalPages = ceil($totalRows / $rowsPerPage);
			?>



			<section class="section section-lg bg-default text-center">
				<div class="container">
					<div class="row justify-content-sm-center">
						<div class="col-md-10 col-xl-8">
							<!-- 搜尋框 -->
							<form class="search-form" method="GET" action="a_blacklist.php"
								style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
								<div style="flex: 4;">
									<input class="form-input" type="text" name="search"
										value="<?php echo htmlspecialchars($search); ?>" placeholder="請輸入身分證"
										style="padding: 10px 15px; font-size: 16px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
								</div>
								<div style="flex: 1;">
									<button type="submit"
										style="padding: 10px 15px; font-size: 16px; width: 100%; border: none; border-radius: 4px; background-color: #00A896; color: white;">
										<span class="icon mdi mdi-magnify"></span>搜尋
									</button>
								</div>
								<div style="flex: 1;">
									<select name="rowsPerPage" onchange="this.form.submit()"
										style="padding: 10px 15px; font-size: 16px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
										<option value="3" <?php echo $rowsPerPage == 3 ? 'selected' : ''; ?>>3筆/頁</option>
										<option value="5" <?php echo $rowsPerPage == 5 ? 'selected' : ''; ?>>5筆/頁</option>
										<option value="10" <?php echo $rowsPerPage == 10 ? 'selected' : ''; ?>>10筆/頁
										</option>
										<option value="20" <?php echo $rowsPerPage == 20 ? 'selected' : ''; ?>>20筆/頁
										</option>
									</select>
								</div>
							</form>

							<!-- 表格 -->
							<div class="table-novi table-custom-responsive" style="font-size: 16px; overflow-x: auto;">
								<table class="table-custom table-custom-bordered" style="width: 90%; /* 調整表格寬度，90% 居中 */ 
				  margin: 0 auto; /* 讓表格居中對齊 */ 
				  border-collapse: collapse;">
									<thead>
										<tr>
											<th style="padding: 20px; text-align: left;">#</th>
											<th style="padding: 20px; text-align: left;">姓名</th>
											<th style="padding: 20px; text-align: left;">身份證</th>
											<th style="padding: 20px; text-align: left;">剩餘時間 / 違規次數</th>
											<th style="padding: 20px; text-align: center;">選項</th>
										</tr>
									</thead>
									<tbody>
										<?php if (!empty($users)): ?>
											<?php foreach ($users as $index => $user): ?>
												<tr>
													<td style="padding: 20px;"><?php echo $offset + $index + 1; ?></td>
													<td style="padding: 20px;"><?php echo htmlspecialchars($user['name']); ?>
													</td>
													<td style="padding: 20px;"><?php echo htmlspecialchars($user['idcard']); ?>
													</td>
													<td style="padding: 20px;">
														<?php if (strpos($user['remaining_time'], "違規次數") !== false): ?>
															<?php echo $user['remaining_time']; ?>
														<?php else: ?>
															<span class="countdown"
																data-seconds="<?php echo $user['remaining_time']; ?>"></span>
														<?php endif; ?>
													</td>
													<td style="padding: 20px; text-align: center;">
														<button
															style="padding: 8px 16px; background-color: #00A896; color: white; border: none; border-radius: 6px;">操作</button>
														<button
															style="padding: 8px 16px; background-color: #FFB900; color: white; border: none; border-radius: 6px;">詳細</button>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php else: ?>
											<tr>
												<td colspan="5" style="text-align: center; padding: 20px;">未找到資料</td>
											</tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>

							<!-- 分頁 -->
							<div id="pagination" style="text-align: center; margin-top: 10px;">
								<?php if ($totalPages > 1): ?>
									<?php for ($i = 1; $i <= $totalPages; $i++): ?>
										<button
											onclick="location.href='?page=<?php echo $i; ?>&rowsPerPage=<?php echo $rowsPerPage; ?>&search=<?php echo htmlspecialchars($search); ?>'"
											style="margin: 0 5px; padding: 5px 10px; border: none; background-color: <?php echo $i == $page ? '#00A896' : '#f0f0f0'; ?>; color: <?php echo $i == $page ? 'white' : 'black'; ?>; border-radius: 4px;">
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

			<!-- 倒數計時 JavaScript -->
			<script>
				document.querySelectorAll('.countdown').forEach(function (element) {
					let seconds = parseInt(element.getAttribute('data-seconds'));

					function updateCountdown() {
						if (seconds <= 0) {
							element.textContent = '已解除';
						} else {
							let days = Math.floor(seconds / 86400);
							let hours = Math.floor((seconds % 86400) / 3600);
							let minutes = Math.floor((seconds % 3600) / 60);
							let sec = seconds % 60;

							element.textContent = `${days}天 ${hours}時 ${minutes}分 ${sec}秒`;
							seconds--;
							setTimeout(updateCountdown, 1000);
						}
					}

					updateCountdown();
				});
			</script>


			<!-- Global Mailform Output-->
			<div class="snackbars" id="form-output-global"></div>
			<!-- Javascript-->
			<script src="js/core.min.js"></script>
			<script src="js/script.js"></script>
</body>

</html>