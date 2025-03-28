<!DOCTYPE html>
<html lang="en"><!-- Basic -->

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<!-- Mobile Metas -->
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">

	<!-- Site Metas -->
	<title>運動筋膜放鬆</title>
	<meta name="keywords" content="">
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Site Icons -->
	<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" href="images/apple-touch-icon.jpg">

	<!-- Bootstrap CSS -->
	<link href="css1/bootstrap.min.css" rel="stylesheet">
	<!-- Site CSS -->
	<link rel="stylesheet" href="css1/style.css">
	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="css1/all.min.css">
	<!-- Responsive CSS -->
	<link rel="stylesheet" href="css1/responsive.css">
	<!-- Custom CSS -->
	<link rel="stylesheet" href="css1/custom.css">

	<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

	<style>
		img {
			max-width: 100%;
			height: auto;
			display: block;
			margin: 0 auto;
			padding: 0;
			/* 確保沒有額外內邊距 */
		}
	</style>

</head>
<body>
	<div id="particles-js" class="main-form-box">
		<div class="md-form">
			<div class="container">
				<div class="row">
					<div class="col-md-6 offset-md-3">
						<div class="panel panel-login">
							<div class="logo-top">
								<a href="#"><img src="images/logo.png" alt="" /></a>
							</div>
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-12">
										<!-- 輸入驗證碼 開始 -->
										<form id="login-form" action="changepwd.php" method="post" role="form"
											style="display: block;">
											<input type="hidden" name="code"
												value="<?php echo htmlspecialchars($_GET['code'] ?? ''); ?>">
											<input type="hidden" name="email"
												value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
											<!-- 密碼輸入欄 -->
											<div class="form-group">
												<label class="icon-lp"><i class="fas fa-key"></i></label>
												<input type="password" name="passwd" id="passwd" tabindex="2"
													class="form-control" placeholder="密碼" required>
												<!-- 錯誤訊息 -->
												<small id="password-error" class="text-danger"
													style="display: none;">請輸入密碼</small>
											</div>
											<!-- 註冊按鈕 -->
											<div class="form-group">
												<div class="row">
													<div class="col-sm-6 offset-sm-3">
														<input type="submit" name="register-submit" id="register-submit"
															tabindex="4" class="form-control btn btn-register"
															value="確認">
													</div>
												</div>
											</div>
										</form>
										<!-- 註冊 結束 -->

										<style>
											.error {
												color: red;
												font-size: 1.2em;
											}
										</style>
										<!-- JavaScript 防呆功能 -->
										<script>
											/**
											 * 驗證密碼與確認密碼是否相同
											 * @returns {boolean} true 如果密碼一致且已填寫，否則 false
											 */
											function validateRegisterForm() {
												// 取得密碼與確認密碼欄位的值
												const password = document.getElementById("passwd").value.trim();

												// 清除之前的錯誤訊息
												document.getElementById("password-error").style.display = "none";

												let isValid = true;

												// 驗證密碼是否輸入
												if (password === "") {
													const passwordError = document.getElementById("password-error");
													passwordError.textContent = "請輸入驗證碼";
													passwordError.style.display = "block";
													isValid = false;
												}

												// 返回最終驗證結果
												return isValid;
											}

										</script>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<script src="js1/jquery.min.js"></script>
	<script src="js1/bootstrap.min.js"></script>
	<script src="js1/particles.min.js"></script>
	<script src="js1/index.js"></script>

</body>

</html>