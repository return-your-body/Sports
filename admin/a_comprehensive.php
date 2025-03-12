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

		<!-- 收入 -->


		<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
		<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
		<style>
			.chart-container {
				display: flex;
				flex-direction: column;
				align-items: center;
				width: 80%;
				margin: auto;
			}

			.chart-row {
				display: flex;
				justify-content: center;
				width: 100%;
				flex-wrap: wrap;
				margin-top: 20px;
			}

			.chart-box {
				width: 30%;
				text-align: center;
			}

			.full-width {
				width: 60%;
				text-align: center;
				margin-bottom: 20px;
			}

			select,
			button {
				padding: 5px;
				margin: 5px;
			}
		</style>

		<h2 style="text-align: center">治療師統計數據</h2>
		<div style="text-align: center">
			年：<select id="year"></select>
			月：<select id="month"></select>
			日：<select id="day"></select>
			治療師:
			<select id="doctor">
				<option value="0">全部</option>
			</select>
			<button id="searchBtn">查詢</button>
		</div>

		<div class="chart-container">
			<div class="full-width">
				<h4>總工作時數</h4>
				<canvas id="workHoursChart"></canvas>
			</div>

			<div class="chart-row">
				<div class="chart-box">
					<h4>項目數比例</h4>
					<canvas id="itemChart"></canvas>
				</div>
				<div class="chart-box">
					<h4>治療師預約人數比例</h4>
					<canvas id="doctorChart"></canvas>
				</div>
				<div class="chart-box">
					<h4>收入統計</h4>
					<canvas id="incomeChart"></canvas>
				</div>
			</div>
		</div>

	<script>
    $(document).ready(function () {
        populateDateSelectors();
        fetchDoctorsAndAssistants(); // 取得所有治療師與助手
        setTimeout(fetchData, 500); // 預設載入時查詢資料

        // 綁定查詢按鈕
        $("#searchBtn").click(function () {
            fetchData(); // 按下查詢後重新查詢數據
        });

        function populateDateSelectors() {
            let yearSelect = $("#year"), monthSelect = $("#month"), daySelect = $("#day");
            let today = new Date();
            let currentYear = today.getFullYear();
            let currentMonth = today.getMonth() + 1;
            let currentDay = today.getDate();

            for (let i = currentYear - 5; i <= currentYear + 1; i++) {
                yearSelect.append(`<option value="${i}" ${i === currentYear ? 'selected' : ''}>${i}</option>`);
            }
            for (let i = 1; i <= 12; i++) {
                monthSelect.append(`<option value="${i}" ${i === currentMonth ? 'selected' : ''}>${i}</option>`);
            }
            for (let i = 1; i <= 31; i++) {
                daySelect.append(`<option value="${i}" ${i === currentDay ? 'selected' : ''}>${i}</option>`);
            }
        }

        function fetchDoctorsAndAssistants() {
            $.getJSON("獲取治療師助手.php", function (data) {
                let doctorSelect = $("#doctor");
                doctorSelect.empty();
                doctorSelect.append('<option value="0">全部</option>'); // 預設選擇全部
                data.forEach(person => {
                    doctorSelect.append(`<option value="${person.doctor_id}">${person.doctor}</option>`);
                });
            });
        }

        function fetchData() {
            let year = $("#year").val();
            let month = $("#month").val();
            let day = $("#day").val();
            let doctor = $("#doctor").val();

            console.log(`查詢條件: 年=${year}, 月=${month}, 日=${day}, 醫師=${doctor}`);

            fetchChartData("總工作時數", "workHoursChart", "bar", "總工作時數", year, month, day, doctor);
            fetchChartData("項目數比例", "itemChart", "pie", "項目數比例", year, month, day, doctor);
            fetchChartData("治療師預約人數比例", "doctorChart", "pie", "治療師預約人數比例", year, month, day, doctor);
            fetchIncomeChart(year, month, day, doctor);
        }

        function fetchChartData(chartType, canvasId, chartTypeFormat, label, year, month, day, doctor) {
            $.getJSON(`數據查詢.php?year=${year}&month=${month}&day=${day}&doctor_id=${doctor}&chart=${chartType}`, function (data) {
                if (!data.length) {
                    console.warn(`${chartType} 無數據`);
                    return;
                }

                let labels = data.map(d => d.doctor_name || d.item);
                let values = data.map(d => d.total_work_hours || d.item_count || d.total_appointments || d.total_revenue);

                let ctx = document.getElementById(canvasId).getContext("2d");
                new Chart(ctx, {
                    type: chartTypeFormat,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: label,
                            data: values,
                            backgroundColor: ['red', 'blue', 'green', 'yellow', 'purple', 'orange', 'cyan', 'pink']
                        }]
                    }
                });
            }).fail(() => {
                alert("❌ 查詢數據失敗，請檢查後端 API");
            });
        }

        function fetchIncomeChart(year, month, day, doctor) {
            $.getJSON(`數據查詢.php?year=${year}&month=${month}&day=${day}&doctor_id=${doctor}&chart=收入統計`, function (data) {
                if (!data.length) {
                    console.warn(`收入統計 無數據`);
                    return;
                }

                let labels = data.map(d => d.item);
                let values = data.map(d => d.total_revenue);

                let ctx = document.getElementById("incomeChart").getContext("2d");
                new Chart(ctx, {
                    type: "pie",
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "收入統計",
                            data: values,
                            backgroundColor: ['red', 'blue', 'green', 'yellow', 'purple']
                        }]
                    },
                    options: {
                        onClick: function (evt, elements) {
                            if (elements.length > 0) {
                                let index = elements[0].index;
                                let itemName = labels[index];
                                showDetails(itemName);
                            }
                        }
                    }
                });
            }).fail(() => {
                alert("❌ 無法獲取收入統計數據！");
            });
        }

        function showDetails(itemName) {
            $.getJSON(`數據查詢.php?chart=詳細數據&item=${itemName}`, function (data) {
                let detailHTML = data.map(d =>
                    `<tr>
                        <td>${d.doctor_name}</td>
                        <td>${d.category}</td>
                        <td>${d.value}</td>
                    </tr>`
                ).join('');

                $("#modalDetailTable tbody").html(detailHTML);
                $("#modalDetail").show();
            }).fail(() => {
                alert("❌ 無法獲取詳細數據！");
            });
        }

        $(".close").click(function () {
            $("#modalDetail").hide();
        });
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