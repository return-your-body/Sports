<?php
// 啟動 PHP 會話
session_start();

// 檢查是否登入，否則跳轉到登入頁面
if (!isset($_SESSION["登入狀態"])) {
	// 如果用戶未登入，跳轉至登入頁面並結束腳本
	header("Location: ../index.html");
	exit;
}
$帳號 = $_SESSION['帳號'];
// 防止頁面被瀏覽器快取，確保每次都加載最新內容
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

// 引入資料庫連線檔案，確保連線正確配置
require '../db.php';

// 取得每頁顯示的筆數參數，預設為 3
$rowsPerPage = isset($_GET['rowsPerPage']) ? intval($_GET['rowsPerPage']) : 3;

// 確保每頁筆數在合理範圍內，避免過大或過小
if ($rowsPerPage < 1) {
	$rowsPerPage = 3; // 預設筆數
} elseif ($rowsPerPage > 100) {
	$rowsPerPage = 100; // 限制最大筆數
}

// 獲取當前頁碼，預設為 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) {
	$page = 1; // 確保頁碼至少為 1
}

// 計算偏移量
$offset = ($page - 1) * $rowsPerPage;

// 獲取搜尋參數，如果未提供，預設為空字串
$search = isset($_GET['search']) ? $_GET['search'] : '';

// 準備 SQL 查詢語句，用於獲取符合條件的資料
$query = "
    SELECT u.user_id AS id, u.account, p.name, p.idcard, p.gender_id, p.birthday, p.phone, p.address, p.email, p.images
    FROM user u
    LEFT JOIN people p ON u.user_id = p.user_id
    WHERE u.grade_id = 1 AND (p.idcard LIKE ? OR p.name LIKE ? OR u.account LIKE ?)
    LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($link, $query);
$searchWildcard = '%' . $search . '%'; // 模糊搜尋條件
mysqli_stmt_bind_param($stmt, "sssii", $searchWildcard, $searchWildcard, $searchWildcard, $rowsPerPage, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 將查詢結果存入陣列
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
	// 如果圖片欄位存在，將 BLOB 格式圖片轉換為 Base64 格式，便於前端顯示
	if (!empty($row['images'])) {
		$row['images'] = base64_encode($row['images']);
	}
	$users[] = $row;
}

// 計算符合條件的總數量，用於分頁顯示
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

// 總筆數
$totalRows = $countRow['total'];
// 計算總頁數
$totalPages = ceil($totalRows / $rowsPerPage);

// 釋放資源並關閉資料庫連線
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
		/* 彈窗樣式 */
		.popup {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background-color: rgba(0, 0, 0, 0.5);
			justify-content: center;
			align-items: center;
			z-index: 1000;
		}

		.popup-content {
			background: white;
			border-radius: 10px;
			padding: 20px;
			width: 600px;
			box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
			position: relative;
			overflow: hidden;
			/* 防止超出內容影響佈局 */
		}

		/* 調整頭像樣式 */
		.profile-image {
			width: 150px;
			/* 修改寬度 */
			height: 150px;
			/* 修改高度 */
			border-radius: 50%;
			/* 保持圓形 */
			border: 3px solid #ccc;
			/* 增加邊框寬度 */
			object-fit: cover;
			/* 圖片保持比例填充 */
			background-color: #f0f0f0;
			/* 預設背景避免空白 */
		}



		/* 按鈕樣式 */
		.popup-btn {
			background-color: #00A896;
			color: white;
			padding: 8px 16px;
			border: none;
			border-radius: 5px;
			cursor: pointer;
			font-size: 14px;
			transition: background-color 0.3s;
		}

		.popup-btn:hover {
			background-color: #007f6e;
		}

		/* 黑名單切換樣式 */
		.blacklist-toggle {
			font-size: 14px;
			color: #333;
			cursor: pointer;
			display: inline-flex;
			align-items: center;
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
								<li class="rd-nav-item active"><a class="rd-nav-link" href="">關於治療師</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_therapist.php">治療師時間表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_addds.php">新增治療師班表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_leave.php">請假申請</a>
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
						echo $帳號;
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
						<li><a href="">用戶管理</a></li>
						<li class="active">用戶管理</li>
					</ul>
				</div>
			</section>
		</div>

		<div id="popup" class="popup">
			<div class="popup-content">
				<span class="popup-close" onclick="closePopup()">×</span>
				<div style="display: flex; gap: 20px; align-items: center;">
					<!-- 左側 -->
					<div style="flex: 1; text-align: center;">
						<img id="popup-image" src="" alt="頭像" class="profile-image">
						<div style="margin-top: 10px;">
							<!-- <button class="popup-btn" onclick="viewDetails()">詳細</button> -->
							<div style="margin-top: 10px;">
								<label class="blacklist-toggle">
									<input type="checkbox" id="blacklist-toggle"> 加入黑名單
								</label>
							</div>

						</div>
					</div>
					<!-- 右側 -->
					<div style="flex: 2;">
						<p><strong>姓名：</strong><span id="popup-name"></span></p>
						<p><strong>性別：</strong><span id="popup-gender"></span></p>
						<p><strong>出生日期：</strong><span id="popup-birthday"></span></p>
						<p><strong>身份證：</strong><span id="popup-idcard"></span></p>
						<p><strong>電話：</strong><span id="popup-phone"></span></p>
						<p><strong>地址：</strong><span id="popup-address"></span></p>
						<p><strong>電子郵件：</strong><span id="popup-email"></span></p>
					</div>
				</div>
			</div>
		</div>


		<script>
			function openPopup(user) {
				try {
					// 頭像圖片判斷
					const profileImage = document.getElementById('popup-image');

					if (user.images && user.images.trim() !== '' && user.images !== 'NULL') {
						// 如果有圖片資料，將 BLOB 格式轉為 Base64 並設置圖片來源
						const base64Image = `data:image/jpeg;base64,${user.images}`;
						profileImage.src = base64Image;
					} else {
						// 如果沒有圖片資料，使用預設圖片
						profileImage.src = 'images/300.jpg';
					}

					// 處理圖片加載失敗的情況
					profileImage.onerror = function () {
						this.src = 'images/300.jpg'; // 使用預設圖片
					};

					// 計算歲數
					let ageText = "無資料";
					if (user.birthday && user.birthday !== "無資料") {
						const birthday = new Date(user.birthday);
						const today = new Date();
						let age = today.getFullYear() - birthday.getFullYear();
						const monthDifference = today.getMonth() - birthday.getMonth();

						// 如果生日月份尚未到，或者生日月份到了但日期未到，歲數減一
						if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthday.getDate())) {
							age--;
						}
						ageText = `${age} 歲`;
					}

					// 設置其他用戶資訊
					document.getElementById('popup-name').innerText = user.name || '無資料';
					document.getElementById('popup-gender').innerText = user.gender_id === '1' ? '男性' : '女性';
					document.getElementById('popup-birthday').innerText = user.birthday ? `${user.birthday} (${ageText})` : '無資料';
					document.getElementById('popup-idcard').innerText = user.idcard || '無資料';
					document.getElementById('popup-phone').innerText = user.phone || '無資料';
					document.getElementById('popup-address').innerText = user.address || '無資料';
					document.getElementById('popup-email').innerText = user.email || '無資料';

					// 顯示彈窗
					document.getElementById('popup').style.display = 'flex';
				} catch (error) {
					console.error("無法顯示彈窗：", error);
				}
			}


			// 關閉彈窗
			function closePopup() {
				document.getElementById('popup').style.display = 'none';
			}

		</script>

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
													<button onclick='openPopup(<?php echo json_encode([
														"name" => $user["name"] ?? "無資料",
														"gender_id" => $user["gender_id"] ?? null,
														"birthday" => $user["birthday"] ?? "無資料",
														"idcard" => $user["idcard"] ?? "無資料",
														"phone" => $user["phone"] ?? "無資料",
														"address" => $user["address"] ?? "無資料",
														"email" => $user["email"] ?? "無資料",
														"images" => $user["images"] ?? "images/300.jpg",
													], JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
														style="padding: 6px 12px; font-size: 12px; border: none; background-color: #00A896; color: white; cursor: pointer; border-radius: 4px;">
														操作
													</button>

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
										onclick="location.href='?page=<?php echo $i; ?>&rowsPerPage=<?php echo $rowsPerPage; ?>&search=<?php echo htmlspecialchars($search); ?>'"
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