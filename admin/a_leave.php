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

// 檢查是否有 "帳號" 在 Session 中
if (isset($_SESSION["帳號"])) {
	// 獲取用戶帳號
	$帳號 = $_SESSION['帳號'];

	// 查詢用戶詳細資料
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

// 取得醫生資料
$query = "
    SELECT d.doctor_id, d.doctor
    FROM doctor d
    INNER JOIN user u ON d.user_id = u.user_id
    INNER JOIN grade g ON u.grade_id = g.grade_id
    WHERE g.grade = '醫生'
";
$result = mysqli_query($link, $query);

// 檢查查詢結果
if (!$result) {
	echo "Error fetching doctor data: " . mysqli_error($link);
	exit;
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
	<title>健康醫療網站</title>
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
		.highlight-red {
			color: red;
			font-weight: bold;
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

		body {
			font-family: Arial, sans-serif;
			text-align: center;
			margin: 20px;
		}

		.calendar {
			display: grid;
			grid-template-columns: repeat(7, 1fr);
			gap: 5px;
			margin-top: 20px;
		}

		.calendar div {
			border: 1px solid #ccc;
			padding: 10px;
			background: #f9f9f9;
			cursor: pointer;
		}

		.calendar .header {
			font-weight: bold;
			background: #ddd;
		}

		.calendar .empty {
			background: transparent;
			cursor: default;
		}

		/* 日期樣式：改為柔和文字色系 */
		.calendar .date-link {
			display: block;
			padding: 10px;
			color: #00796B;
			/* 第二張圖片的綠色系 */
			text-decoration: none;
			/* 移除底線 */
			font-size: 14px;
			font-weight: bold;
		}

		.calendar .date-link:hover {
			text-decoration: underline;
			/* 滑鼠懸停時加底線 */
			color: #004D40;
			/* 深綠色，增加互動感 */
		}

		select {
			padding: 5px;
			margin: 5px;
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

		.rd-dropdown-link span {
			background-color: red;
			color: white;
			font-size: 12px;
			border-radius: 50%;
			padding: 2px 6px;
			margin-left: 5px;
			display: inline-block;
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
					<p class="heading-1 breadcrumbs-custom-title">請假申請</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="a_index.php">首頁</a></li>
						<li><a href="">關於治療師</a></li>
						<li class="active">請假申請</li>
					</ul>
				</div>
			</section>
		</div>
		<br>

		<?php
		// 引入資料庫連線檔案
		require '../db.php';

		// 設定分頁參數
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$perPage = isset($_GET['rowsPerPage']) ? (int) $_GET['rowsPerPage'] : 10;
		$offset = ($page - 1) * $perPage;
		$search = isset($_GET['search']) ? $_GET['search'] : '';

		// 查詢請假資料並聯結醫生表格以取得醫生名稱，未通過的排在前面
		$result = $link->query(
			"SELECT leaves.*, doctor.doctor AS doctor_name 
     FROM leaves 
     LEFT JOIN doctor ON leaves.doctor_id = doctor.doctor_id 
     WHERE doctor.doctor LIKE '%$search%' 
     ORDER BY is_approved ASC, start_date ASC 
     LIMIT $offset, $perPage"
		);

		// 查詢總數以進行分頁
		$totalResult = $link->query(
			"SELECT COUNT(*) as total 
     FROM leaves 
     LEFT JOIN doctor ON leaves.doctor_id = doctor.doctor_id 
     WHERE doctor.doctor LIKE '%$search%'"
		);
		$totalRows = $totalResult->fetch_assoc()['total'];
		$totalPages = ceil($totalRows / $perPage);

		// 處理 POST 請求（通過或拒絕請假）
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$leaves_id = $_POST['leaves_id'];
			$is_approved = $_POST['is_approved'];
			$rejection_reason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : null;

			if ($is_approved == 0 && !$rejection_reason) {
				echo "拒絕原因為必填";
				exit;
			}

			// 更新資料庫中的請假狀態
			$stmt = $link->prepare("UPDATE leaves SET is_approved = ?, rejection_reason = ? WHERE leaves_id = ?");
			$stmt->bind_param("isi", $is_approved, $rejection_reason, $leaves_id);

			if ($stmt->execute()) {
				echo $is_approved ? "請假已通過" : "請假已拒絕";
			} else {
				echo "更新失敗: " . $link->error;
			}

			$stmt->close();
			exit;
		}
		?>

		<!-- 搜尋框與按鈕區塊 -->
		<form class="search-form" method="GET" action="a_leave.php"
			style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 20px; width: 100%;">
			<div style="flex: 4;">
				<input class="form-input" type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
					placeholder="請輸入醫生姓名" style="
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
			<?php if (!empty($search)): ?>
				<div style="flex: 1;">
					<button class="" type="button" onclick="location.href='a_leave.php'" style="
			padding: 10px 15px;           
			font-size: 16px;              
			width: 100%;                  
			border: none;                 
			border-radius: 4px;           
			background-color: #FF6F61;    
			color: white;                 
			cursor: pointer;              
			box-sizing: border-box;       
			display: flex;                
			align-items: center;          
			justify-content: center;      
			gap: 5px;                     
		">
						<span class="icon mdi mdi-reload"></span>回到全部資料
					</button>
				</div>
			<?php endif; ?>
			<div style="flex: 1;">
				<select name="rowsPerPage" onchange="this.form.submit()"
					style="padding: 10px 15px; font-size: 16px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
					<option value="3" <?php echo $perPage == 3 ? 'selected' : ''; ?>>3筆/頁</option>
					<option value="5" <?php echo $perPage == 5 ? 'selected' : ''; ?>>5筆/頁</option>
					<option value="10" <?php echo $perPage == 10 ? 'selected' : ''; ?>>10筆/頁</option>
					<option value="20" <?php echo $perPage == 20 ? 'selected' : ''; ?>>20筆/頁</option>
					<option value="25" <?php echo $perPage == 25 ? 'selected' : ''; ?>>25筆/頁</option>
					<option value="50" <?php echo $perPage == 50 ? 'selected' : ''; ?>>50筆/頁</option>
				</select>
			</div>
		</form>

		<script>
			// 定義審核請假的函式，根據通過或拒絕進行不同處理
			function approveLeave(leaves_id, isApproved) {
				let reason = null;
				if (!isApproved) {
					// 如果是拒絕，提示使用者輸入拒絕原因
					reason = prompt("請輸入拒絕原因:");
					if (!reason) {
						alert("拒絕原因為必填"); // 若未填寫原因，提示錯誤
						return;
					}
				}

				// 建立表單資料以送出到後端
				const formData = new FormData();
				formData.append('leaves_id', leaves_id);
				formData.append('is_approved', isApproved ? 1 : 0);
				if (reason) formData.append('rejection_reason', reason);

				// 使用 Fetch API 傳送請求到伺服器
				fetch('', {
					method: 'POST',
					body: formData
				})
					.then(response => response.text()) // 處理伺服器回應
					.then(data => {
						// alert(data); // 顯示更新結果
						location.reload(); // 重新加載頁面以顯示更新後的資料
					})
					.catch(error => console.error('錯誤:', error)); // 處理錯誤
			}
		</script>

		<table class="table-custom table-hover">
			<thead>
				<tr>
					<th>醫生姓名</th>
					<th>請假類型</th>
					<th>開始時間</th>
					<th>結束時間</th>
					<th>原因</th>
					<th>審核狀態</th>
					<th>拒絕原因</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($row = $result->fetch_assoc()): ?>
					<tr>
						<td><?php echo $row['doctor_name']; ?></td>
						<td><?php echo $row['leave_type']; ?></td>
						<td><?php echo $row['start_date']; ?></td>
						<td><?php echo $row['end_date']; ?></td>
						<td><?php echo $row['reason']; ?></td>
						<td><?php echo $row['is_approved'] ? '已通過' : '未通過'; ?></td>
						<td><?php echo $row['rejection_reason'] ?? 'N/A'; ?></td>
						<td>
							<?php if (!$row['is_approved']): ?>
								<!-- 只有未通過時顯示操作按鈕 -->
								<button onclick="approveLeave(<?php echo $row['leaves_id']; ?>, true)" style="
						padding: 5px 10px;
						border: none;
						border-radius: 4px;
						background-color: #28a745;
						color: white;
						cursor: pointer;">通過</button>
								<button onclick="approveLeave(<?php echo $row['leaves_id']; ?>, false)" style="
						padding: 5px 10px;
						border: none;
						border-radius: 4px;
						background-color: #dc3545;
						color: white;
						cursor: pointer;">拒絕</button>
							<?php endif; ?>
						</td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>

		<div id="pagination" style="text-align: center; margin-top: 10px; font-size: 14px; color: #333;">
			<?php if ($totalPages > 1): ?>
				<?php for ($i = 1; $i <= $totalPages; $i++): ?>
					<button onclick="location.href='?page=<?php echo $i; ?>&rowsPerPage=<?php echo $perPage; ?>'"
						style="margin: 0 5px; padding: 5px 10px; border: none; background-color: <?php echo $i == $page ? '#00A896' : '#f0f0f0'; ?>; color: <?php echo $i == $page ? 'white' : 'black'; ?>; border-radius: 4px; cursor: pointer;">
						<?php echo $i; ?>
					</button>
				<?php endfor; ?>
				<span>| 共 <?php echo $totalPages; ?> 頁</span>
			<?php endif; ?>
		</div>


		<!-- Global Mailform Output-->
		<div class="snackbars" id="form-output-global"></div>
		<!-- Javascript-->
		<script src="js/core.min.js"></script>
		<script src="js/script.js"></script>

	</div>
</body>

</html>