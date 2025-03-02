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

// 查詢尚未審核的請假申請數量
$pendingCountResult = $link->query(
	"SELECT COUNT(*) as pending_count 
     FROM leaves 
     WHERE is_approved IS NULL"
);
$pendingCount = $pendingCountResult->fetch_assoc()['pending_count'];

// 釋放資源並關閉資料庫連線
mysqli_free_result($result);
mysqli_free_result($countResult);
mysqli_close($link);
?>


<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
	<!-- Site Title-->
	<title>運動筋膜放鬆-用戶管理</title>
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

		/* 美化違規紀錄表格 */
		.violation-table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 10px;
			font-size: 16px;
			background: #fff;
		}

		.violation-table th {
			padding: 12px;
			text-align: left;
			background-color: #f4f4f4;
			font-weight: bold;
		}

		.violation-table td {
			padding: 12px;
			text-align: left;
			border-bottom: 1px solid #ddd;
		}

		/* 滑鼠懸停時變色 */
		.violation-table tbody tr:hover {
			background-color: #f9f9f9;
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
								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="a_comprehensive.php">綜合</a>
								</li> -->
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
						<div style="margin-top: 10px; display: flex; justify-content: center; gap: 10px;">
							<!-- <a href="#" onclick="toggleMedicalRecord()">病例</a>
							<a>|</a> -->
							<!-- 這個按鈕讓使用者點擊後顯示 / 隱藏 違規紀錄 -->
							<a href="javascript:void(0);" onclick="viewViolationRecord()">違規</a>


						</div>
					</div>
					<!-- 右側 -->
					<div style="flex: 2;">
						<p><strong>姓名：</strong><span id="popup-name"></span></p>
						<p><strong>性別：</strong><span id="popup-gender"></span></p>
						<p><strong>出生日期：</strong><span id="popup-birthday"></span></p>
						<p><strong>身份證：</strong><span id="popup-idcard" data-id=""></span></p>
						<p><strong>電話：</strong><span id="popup-phone"></span></p>
						<p><strong>地址：</strong><span id="popup-address"></span></p>
						<p><strong>電子郵件：</strong><span id="popup-email"></span></p>
					</div>
				</div>

				<!-- **違規紀錄區塊** -->
				<div id="violation-record" style="margin-top: 20px; display: none;">
					<h3>違規紀錄</h3>
					<div id="violation-details" style="max-height: 300px; overflow-y: auto;">載入中...</div>
				</div>

				<!-- 病例紀錄區塊（初始隱藏） -->
				<!-- <div id="medical-records" style="display: none; margin-top: 20px;">
					<h3>病歷紀錄</h3>
					<table class="violation-table">
						<thead>
							<tr>
								<th>治療師</th>
								<th>看診日期</th>
								<th>時間</th>
								<th>狀態</th>
								<th>治療項目</th>
								<th>總費用</th>
							</tr>
						</thead>
						<tbody id="medical-records-body">
						</tbody>
					</table>
				</div> -->
			</div>
		</div>



		<script>
			/**
			* 顯示使用者詳細資訊的彈窗
			* @param {Object} user - 包含使用者資訊的物件
			*/
			function openPopup(user) {
				try {
					// 取得彈窗內的頭像元素
					const profileImage = document.getElementById('popup-image');

					// 檢查是否有有效的頭像數據
					if (user.images && user.images.trim() !== '' && user.images !== 'NULL' && user.images !== null) {
						// 如果有圖片數據，則轉換為 Base64 格式並設置圖片來源
						const base64Image = `data:image/jpeg;base64,${user.images}`;
						profileImage.src = base64Image;
					} else {
						// 如果沒有圖片數據，則使用預設圖片
						profileImage.src = 'images/300.jpg';
					}

					// 若圖片加載失敗，則替換為預設圖片
					profileImage.onerror = function () {
						this.src = 'images/300.jpg';
					};

					// 設定用戶的基本資訊到彈窗內的對應欄位
					document.getElementById("popup-name").textContent = user.name || "無資料";  // 設定姓名
					document.getElementById("popup-gender").textContent = user.gender_id == 1 ? "男性" : "女性"; // 設定性別
					document.getElementById("popup-birthday").textContent = user.birthday || "無資料"; // 設定生日
					document.getElementById("popup-idcard").textContent = user.idcard || "無資料"; // 設定身份證號碼
					document.getElementById("popup-idcard").setAttribute("data-id", user.people_id || ""); // 設定 people_id 供違規紀錄查詢
					document.getElementById("popup-phone").textContent = user.phone || "無資料"; // 設定電話
					document.getElementById("popup-address").textContent = user.address || "無資料"; // 設定地址
					document.getElementById("popup-email").textContent = user.email || "無資料"; // 設定電子郵件

					// 顯示彈窗
					document.getElementById("popup").style.display = "flex";

					// **隱藏違規紀錄區塊，讓使用者點擊「違規」按鈕後才顯示**
					document.getElementById("violation-record").style.display = "none";
					document.getElementById("violation-details").innerHTML = ""; // 清空違規內容，避免殘留上次查詢的資料
				} catch (error) {
					console.error("無法顯示彈窗：", error);
				}
			}




			// 關閉彈窗
			function closePopup() {
				document.getElementById('popup').style.display = 'none';
			}

			/**
			* 切換違規紀錄的顯示/隱藏狀態
			*/
			function viewViolationRecord() {
				let violationSection = document.getElementById("violation-record");

				// **如果違規紀錄已經顯示，則隱藏它**
				if (violationSection.style.display === "block") {
					violationSection.style.display = "none"; // 隱藏違規紀錄
					return;
				}

				// **如果違規紀錄未顯示，則發送請求取得資料並顯示**
				let peopleId = document.getElementById("popup-idcard").getAttribute("data-id");

				// 確保 peopleId 存在，避免發送錯誤請求
				if (!peopleId || peopleId === "null" || peopleId === "undefined") {
					alert("錯誤：未找到使用者 ID！");
					return;
				}

				// 顯示違規區塊，載入新內容
				violationSection.style.display = "block";
				document.getElementById("violation-details").innerHTML = "載入中...";

				// 發送 AJAX 請求到 `顯示違規紀錄2.php`
				let xhr = new XMLHttpRequest();
				xhr.open("POST", "顯示違規紀錄2.php", true);
				xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xhr.send("people_id=" + encodeURIComponent(peopleId));

				xhr.onload = function () {
					document.getElementById("violation-details").innerHTML = xhr.responseText;
				};
			}


			/**
			* 查詢並顯示或隱藏病例紀錄
			*/
			// function toggleMedicalRecord() {
			// 	let peopleId = document.getElementById("popup-idcard").getAttribute("data-id"); // 取得使用者 ID

			// 	if (!peopleId || peopleId === "null") {
			// 		alert("錯誤：未找到使用者 ID！");
			// 		return;
			// 	}

			// 	let medicalRecordsDiv = document.getElementById("medical-records");
			// 	let medicalRecordsBody = document.getElementById("medical-records-body");

			// 	// 如果已顯示，則隱藏
			// 	if (medicalRecordsDiv.style.display === "block") {
			// 		medicalRecordsDiv.style.display = "none";
			// 		return;
			// 	}

			// 	// 顯示病歷區塊，並清空舊數據
			// 	medicalRecordsDiv.style.display = "block";
			// 	medicalRecordsBody.innerHTML = "<tr><td colspan='5'>載入中...</td></tr>";

			// 	// 發送 AJAX 請求到 PHP
			// 	let xhr = new XMLHttpRequest();
			// 	xhr.open("POST", "查詢病例.php", true);
			// 	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			// 	xhr.send("people_id=" + encodeURIComponent(peopleId));

			// 	xhr.onload = function () {
			// 		if (xhr.status === 200) {
			// 			medicalRecordsBody.innerHTML = xhr.responseText; // 插入表格內容
			// 		} else {
			// 			medicalRecordsBody.innerHTML = "<tr><td colspan='5'>查詢失敗</td></tr>";
			// 		}
			// 	};
			// }



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
														"people_id" => $user["id"] ?? null  // ✅ 確保傳遞 people_id
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