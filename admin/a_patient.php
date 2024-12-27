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

// 引入資料庫連接檔案
require '../db.php';

// 分頁參數
$rowsPerPage = 3; // 每頁顯示筆數
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $rowsPerPage;

// 搜尋參數
$search = isset($_GET['search']) ? $_GET['search'] : '';

// SQL 查詢：根據搜尋條件獲取用戶列表
$query = "
    SELECT u.user_id AS id, u.account, p.name, p.idcard
    FROM user u
    LEFT JOIN people p ON u.user_id = p.user_id
    WHERE u.grade_id = 1 AND (p.idcard LIKE ? OR p.name LIKE ? OR u.account LIKE ?)
    LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($link, $query);
$searchWildcard = '%' . $search . '%';
mysqli_stmt_bind_param($stmt, "sssii", $searchWildcard, $searchWildcard, $searchWildcard, $rowsPerPage, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 將查詢結果存入陣列
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
	$users[] = $row;
}

// 獲取總筆數
$countQuery = "
    SELECT COUNT(*) AS total
    FROM user u
    LEFT JOIN people p ON u.user_id = p.user_id
    WHERE u.grade_id = 1 AND (p.idcard LIKE ? OR p.name LIKE ? OR u.account LIKE ?)";
$countStmt = mysqli_prepare($link, $countQuery);
mysqli_stmt_bind_param($countStmt, "sss", $searchWildcard, $searchWildcard, $searchWildcard);
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$countRow = mysqli_fetch_assoc($countResult);
$totalRows = $countRow['total'];
$totalPages = ceil($totalRows / $rowsPerPage);

// 釋放資源
mysqli_free_result($result);
mysqli_free_result($countResult);
mysqli_close($link);
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
								<li class="rd-nav-item"><a class="rd-nav-link" href="a_therapist.php">治療師時間表</a>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="a_comprehensive.php">綜合</a>
								</li>
								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="a_comprehensive.php">綜合</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">Single teacher</a>
										</li>
									</ul>
								</li> -->
								<li class="rd-nav-item active"><a class="rd-nav-link" href="a_patient.php">用戶管理</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_addhd.php">新增治療師/助手</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_blacklist.php">黑名單</a>
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
					<p class="heading-1 breadcrumbs-custom-title">用戶管理</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="a_index.php">首頁</a></li>
						<li class="active">用戶管理</li>
					</ul>
				</div>
			</section>

		</div>

		<!-- Bordered Row Table -->
		<section class="section section-lg bg-default text-center">
			<div class="container">
				<div class="row justify-content-sm-center">
					<div class="col-md-10 col-xl-8">
						<!-- 搜尋框與按鈕區塊 -->
						<form class="search-form" method="GET" action="a_patient.php"
							style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 20px; width: 100%;">
							<div style="flex: 4;">
								<input class="form-input" type="text" name="search"
									value="<?php echo htmlspecialchars($search); ?>" placeholder="請輸入身分證" style="
											padding: 10px 15px;          
											font-size: 16px;             
											width: 100%;                 
											border: 1px solid #ccc;      
											border-radius: 4px;          
											outline: none;               
											box-sizing: border-box;      
										">
							</div>
							<div style="flex: 1;">
								<button class="" type="submit" style="
											padding: 10px 15px;           
											font-size: 16px;              
											width: 100%;                  
											border: none;                 
											border-radius: 4px;           
											background-color: #00A896;    
											color: white;                 
											cursor: pointer;              
											box-sizing: border-box;       
											display: flex;                
											align-items: center;          
											justify-content: center;      
											gap: 5px;                     
										">
									<span class="icon mdi mdi-magnify"></span>搜尋
								</button>
							</div>
						</form>

						<!-- 表格區域 -->
						<div class="table-novi table-custom-responsive" style="font-size: 16px; overflow-x: auto;">
							<table class="table-custom table-custom-bordered"
								style="width: 100%; border-collapse: collapse;">
								<thead>
									<tr>
										<th style="padding: 10px; text-align: left;">#</th>
										<th style="padding: 10px; text-align: left;">帳號</th>
										<th style="padding: 10px; text-align: left;">姓名</th>
										<th style="padding: 10px; text-align: left;">身份證</th>
										<th style="padding: 10px; text-align: left;">選項</th>
									</tr>
								</thead>
								<tbody>
									<?php if (!empty($users)): ?>
										<?php foreach ($users as $index => $user): ?>
											<tr>
												<td style="padding: 10px;"><?php echo htmlspecialchars($offset + $index + 1); ?>
												</td>
												<td style="padding: 10px;"><?php echo htmlspecialchars($user['account']); ?>
												</td>
												<td style="padding: 10px;">
													<?php echo htmlspecialchars($user['name'] ?? '無資料'); ?>
												</td>
												<td style="padding: 10px;">
													<?php echo htmlspecialchars($user['idcard'] ?? '無資料'); ?>
												</td>
												<td style="padding: 10px; text-align: center;">
													<button
														style="padding: 6px 12px; font-size: 12px; border: none; background-color: #00A896; color: white; cursor: pointer; border-radius: 4px;">操作</button>
												</td>
											</tr>
										<?php endforeach; ?>
									<?php else: ?>
										<tr>
											<td colspan="5" style="padding: 10px; text-align: center;">沒有找到符合條件的資料</td>
										</tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
						<!-- 分頁顯示區域 -->
						<div id="pagination"
							style="text-align: center; margin-top: 10px; font-size: 14px; color: #333;">
							<?php if ($totalPages > 1): ?>
								<?php for ($i = 1; $i <= $totalPages; $i++): ?>
									<button
										onclick="location.href='?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>'"
										style="
								margin: 0 5px; padding: 5px 10px; border: none; background-color: <?php echo $i == $page ? '#00A896' : '#f0f0f0'; ?>; color: <?php echo $i == $page ? 'white' : 'black'; ?>; border-radius: 4px; cursor: pointer;">
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