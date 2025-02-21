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

// 檢查 "帳號" 是否存在於 $_SESSION 中
if (isset($_SESSION["帳號"])) {
    // 獲取用戶帳號
    $帳號 = $_SESSION['帳號'];

    // 引入資料庫連接
    require '../db.php';

    // 查詢用戶詳細資料，包含 `user_id`
    $sql = "SELECT user_id, account, grade_id FROM user WHERE account = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $帳號);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $user_id = $row['user_id'];  // 取得 user_id
        $帳號名稱 = $row['account']; // 使用者帳號
        $等級 = $row['grade_id'];    // 等級（例如 1: 醫生, 2: 護士）

        // 存入 Session 讓其他頁面使用
        $_SESSION["user_id"] = $user_id;
    } else {
        // 查無此帳號，要求重新登入
        echo "<script>
                alert('找不到對應的帳號資料，請重新登入。');
                window.location.href = '../index.html';
              </script>";
        exit();
    }
}

// 關閉資料庫連接
mysqli_close($link);
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
								<li class="rd-nav-item "><a class="rd-nav-link" href="a_index.php">網頁編輯</a>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="">關於治療師</a>
									<ul class="rd-menu rd-navbar-dropdown">
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_therapist.php">總人數時段表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="a_addds.php">班表</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="a_treatment.php">新增治療項目</a>
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
                                                href="a_igadd.php">新增哀居貼文</a>
                                        </li>
									</ul>
								</li>
                                <li class="rd-nav-item active"><a class="rd-nav-link" href="a_change.php">變更密碼</a>
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

								<!-- <li class="rd-nav-item"><a class="rd-nav-link" href="#">Pages</a>
									<ul class="rd-menu rd-navbar-dropdown">

										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="privacy-policy.html">Privacy policy</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="404-page.html">404 page</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="503-page.html">503 page</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="buttons.html">Buttons</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="forms.html">Forms</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="grid-system.html">Grid system</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="tables.html">Tables</a>
										</li>
										<li class="rd-dropdown-item"><a class="rd-dropdown-link"
												href="typography.html">Typography</a>
										</li>
									</ul>
								</li>
								<li class="rd-nav-item"><a class="rd-nav-link" href="contacts.html">Contacts</a>
								</li> -->
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
									<li><a class="icon novi-icon icon-default icon-custom-linkedin" href="#"></a></li>
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
	


		<!--標題-->
		<div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
			<!-- Breadcrumbs-->
			<section class="breadcrumbs-custom breadcrumbs-custom-svg">
				<div class="container">
					<!-- <p class="breadcrumbs-custom-subtitle">Our team</p> -->
					<p class="heading-1 breadcrumbs-custom-title">變更密碼</p>
					<ul class="breadcrumbs-custom-path">
						<li><a href="a_index.php">首頁</a></li>
						<li class="active">變更密碼</li>
					</ul>
				</div>
			</section>
		</div>
		<!--標題-->

		
        <!-- 變更密碼 -->
        <section class="section section-lg bg-default text-center">
            <div class="container">
                <div class="row justify-content-sm-center">
                    <div class="col-md-10 col-xl-8">
                        <h3>變更密碼</h3>
                        <!-- Change Password Form -->
                        <form id="change-password-form">
                            <div class="row row-20 row-fix justify-content-center">

                                <!-- 舊密碼獨立一行 -->
                                <div class="col-md-10">
                                    <div class="form-wrap form-wrap-validation text-start">
                                        <label class="form-label-outside" for="old-password">舊密碼</label>
                                        <input class="form-input w-100" id="old-password" type="password"
                                            name="old-password" required />
                                        <small id="old-password-error" class="text-danger d-none">舊密碼錯誤</small>
                                    </div>
                                </div>

                                <!-- 新密碼獨立一行 -->
                                <div class="col-md-5">
                                    <div class="form-wrap form-wrap-validation text-start">
                                        <label class="form-label-outside" for="new-password">新密碼</label>
                                        <input class="form-input w-100" id="new-password" type="password"
                                            name="new-password" required />
                                        <small id="password-error" class="text-danger d-none">請輸入密碼</small>
                                    </div>
                                </div>

                                <!-- 確認密碼獨立一行 -->
                                <div class="col-md-5">
                                    <div class="form-wrap form-wrap-validation text-start">
                                        <label class="form-label-outside" for="confirm-password">確認密碼</label>
                                        <input class="form-input w-100" id="confirm-password" type="password"
                                            name="confirm-password" required />
                                        <small id="confirm-password-error"
                                            class="text-danger d-none">新密碼與確認密碼不一致</small>
                                    </div>
                                </div>

                                <!-- 隱藏的使用者 ID -->
                                <input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>">

                                <!-- 提交按鈕獨立一行 -->
                                <div class="col-md-6">
                                    <div class="form-button mt-3">
                                        <button class="button button-primary button-nina w-100"
                                            type="submit">確認變更</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function () {
                $("#change-password-form").submit(function (event) {
                    event.preventDefault(); // 阻止表單的預設提交行為

                    let oldPassword = $("#old-password").val();
                    let newPassword = $("#new-password").val();
                    let confirmPassword = $("#confirm-password").val();
                    let userId = $("#user_id").val();
                    let submitButton = $("#change-password-form button[type=submit]");

                    console.log("發送請求 - user_id:", userId);
                    console.log("舊密碼:", oldPassword);
                    console.log("新密碼:", newPassword);

                    // 清除錯誤訊息
                    $("#old-password-error, #password-error, #confirm-password-error").addClass("d-none");

                    submitButton.prop("disabled", true).text("驗證中...");

                    // **第一步：驗證舊密碼**
                    $.ajax({
                        url: "驗證舊密碼.php",
                        type: "POST",
                        data: { user_id: userId, old_password: oldPassword },
                        success: function (response) {
                            console.log("驗證舊密碼回應:", response);
                            if (response.trim() === "success") {
                                console.log("舊密碼正確，繼續驗證新密碼...");

                                // **第二步：檢查新密碼是否符合要求**
                                if (newPassword === "") {
                                    alert("請輸入新密碼");
                                    $("#password-error").removeClass("d-none");
                                    submitButton.prop("disabled", false).text("確認變更");
                                    return;
                                }
                                if (newPassword !== confirmPassword) {
                                    alert("新密碼與確認密碼不一致");
                                    $("#confirm-password-error").removeClass("d-none");
                                    submitButton.prop("disabled", false).text("確認變更");
                                    return;
                                }

                                submitButton.prop("disabled", true).text("變更密碼中...");

                                // **第三步：變更密碼**
                                $.ajax({
                                    url: "變更密碼.php",
                                    type: "POST",
                                    data: { user_id: userId, new_password: newPassword },
                                    success: function (res) {
                                        console.log("變更密碼回應:", res);
                                        alert(res);
                                        if (res.trim() === "密碼變更成功") {
                                            location.reload(); // 重新整理頁面
                                        }
                                    },
                                    error: function (xhr, status, error) {
                                        alert("變更密碼發生錯誤：" + error);
                                    },
                                    complete: function () {
                                        submitButton.prop("disabled", false).text("確認變更");
                                    }
                                });

                            } else {
                                alert("舊密碼錯誤，請重新輸入");
                                $("#old-password-error").removeClass("d-none");
                                submitButton.prop("disabled", false).text("確認變更");
                            }
                        },
                        error: function (xhr, status, error) {
                            alert("系統錯誤：" + error);
                            submitButton.prop("disabled", false).text("確認變更");
                        }
                    });
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