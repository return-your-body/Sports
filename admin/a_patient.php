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

// 檢查 "帳號" 和 "姓名" 是否存在於 $_SESSION 中
if (isset($_SESSION["帳號"])) {
	// 獲取用戶帳號和姓名
	$帳號 = $_SESSION['帳號'];
} else {
	echo "<script>
            alert('會話過期或資料遺失，請重新登入。');
            window.location.href = '../index.html';
          </script>";
	exit();
}
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
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">新增治療師/助手</a>
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
						<div class="rd-navbar-aside-right rd-navbar-collapse">
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
						</div>
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
						<form class="search-form"
							style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 20px; width: 100%;">
							<!-- 搜尋框容器（設定寬度比例 4） -->
							<div style="flex: 4;">
								<!-- 輸入框：用於用戶輸入身分證號 -->
								<input class="form-input" type="text" name="search" placeholder="請輸入身分證" style="
									padding: 10px 15px;          /* 設定內邊距 */
									font-size: 16px;             /* 字體大小 */
									width: 100%;                 /* 寬度填滿容器 */
									border: 1px solid #ccc;      /* 外框顏色 */
									border-radius: 4px;          /* 圓角設定 */
									outline: none;               /* 移除點擊時的外框線 */
									box-sizing: border-box;      /* 使邊框和內邊距包含在寬度內 */
								">
							</div>

							<!-- 搜尋按鈕容器（設定寬度比例 1） -->
							<div style="flex: 1;">
								<!-- 按鈕：觸發搜尋功能 -->
								<button class="" type="submit" style="
									padding: 10px 15px;           /* 設定內邊距 */
									font-size: 16px;              /* 字體大小 */
									width: 100%;                  /* 寬度填滿容器 */
									border: none;                 /* 移除按鈕邊框 */
									border-radius: 4px;           /* 圓角設定 */
									background-color: #00A896;    /* 按鈕背景顏色 */
									color: white;                 /* 文字顏色 */
									cursor: pointer;              /* 滑鼠懸停時顯示指針 */
									box-sizing: border-box;       /* 使寬度包含邊框和內邊距 */
									display: flex;                /* 使用彈性盒模型 */
									align-items: center;          /* 內容垂直居中 */
									justify-content: center;      /* 內容水平居中 */
									gap: 5px;                     /* 圖示與文字之間的間距 */
								">
									<!-- 圖示：放大鏡 -->
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
								<tbody id="table-body">
									<!-- 動態插入的資料行 -->
								</tbody>
							</table>
						</div>

						<!-- 分頁顯示區域 -->
						<div id="pagination"
							style="text-align: center; margin-top: 10px; font-size: 14px; color: #333;"></div>

						<!-- JavaScript -->
						<script>
							// 假設的資料源：這裡是模擬的表格資料
							const tableData = [];
							for (let i = 1; i <= 47; i++) { // 模擬 47 筆資料
								tableData.push({
									id: i,
									account: `User${i}`,
									name: `Name${i}`,
									idNumber: `@user${i}`,
									option: "操作"
								});
							}

							const rowsPerPage = 10; // 每頁顯示 10 行
							let currentPage = 1;    // 當前頁碼

							// 渲染表格內容
							function renderTable(page) {
								const tableBody = document.getElementById("table-body");
								tableBody.innerHTML = ""; // 清空現有的內容

								// 計算當前頁的資料範圍
								const start = (page - 1) * rowsPerPage;
								const end = start + rowsPerPage;
								const pageData = tableData.slice(start, end);

								// 插入資料行
								pageData.forEach((row) => {
									const tr = `
				<tr>
					<td style="padding: 10px;">${row.id}</td>
					<td style="padding: 10px;">${row.account}</td>
					<td style="padding: 10px;">${row.name}</td>
					<td style="padding: 10px;">${row.idNumber}</td>
					<td style="padding: 10px; text-align: center;">
						<button style="padding: 6px 12px; font-size: 12px; border: none; background-color: #00A896; color: white; cursor: pointer; border-radius: 4px;">
							${row.option}
						</button>
					</td>
				</tr>
			`;
									tableBody.innerHTML += tr;
								});

								renderPagination(); // 更新分頁顯示
							}

							// 渲染分頁資訊
							function renderPagination() {
								const pagination = document.getElementById("pagination");
								const totalPages = Math.ceil(tableData.length / rowsPerPage); // 計算總頁數
								

								if (totalPages > 1) {
									for (let i = 1; i <= totalPages; i++) {
										pagination.innerHTML += `<button onclick="changePage(${i})" style="margin: 0 5px; padding: 5px 10px; cursor: pointer; border: none; background-color: ${i === currentPage ? "#00A896" : "#f0f0f0"
											}; color: ${i === currentPage ? "white" : "black"}; border-radius: 4px;">${i}</button>`;
									}
								}
								pagination.innerHTML += ` | 共 ${totalPages} 頁`;
							}

							// 換頁功能
							function changePage(page) {
								currentPage = page;
								renderTable(currentPage);
							}

							// 初始化渲染表格
							renderTable(currentPage);
						</script>



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