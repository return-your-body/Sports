<?php
// 啟用 session 用於跨頁面數據共享
session_start();

// 引入資料庫連接檔案
require 'db.php';

// 檢查是否提交表單
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    // 接收並清理電子郵件地址
    $email = trim($_POST['email']);
    $_SESSION['email'] = $email; // 將電子郵件存入 session

    // 防止 SQL 注入
    $email_safe = mysqli_real_escape_string($link, $email);

    // 檢查電子郵件是否存在於資料庫
    $check_sql = "SELECT people_id FROM people WHERE email = '$email_safe'";
    $result = mysqli_query($link, $check_sql);

    if (!$result || mysqli_num_rows($result) == 0) {
        echo "<script>alert('該電子郵件未在系統中註冊！'); window.history.back();</script>";
        exit;
    }

    // 從資料庫獲取 people_id
    $row = mysqli_fetch_assoc($result);
    $people_id = $row['people_id'];

    // 生成 6 位數驗證碼
    $verification_code = random_int(100000, 999999);

    // 插入或更新驗證碼
    $check_email_sql = "SELECT * FROM email WHERE people_id = $people_id";
    $email_result = mysqli_query($link, $check_email_sql);

    if ($email_result && mysqli_num_rows($email_result) > 0) {
        // 更新驗證碼
        $update_sql = "UPDATE email SET code = $verification_code, created_at = CURRENT_TIMESTAMP WHERE people_id = $people_id";
        mysqli_query($link, $update_sql);
    } else {
        // 插入新的驗證碼
        $insert_sql = "INSERT INTO email (people_id, code, created_at) VALUES ($people_id, $verification_code, CURRENT_TIMESTAMP)";
        mysqli_query($link, $insert_sql);
    }

    // 發送郵件
    $subject = "您的驗證碼";
    $message = "您的驗證碼是：$verification_code\n請於 10 分鐘內使用。";
    $headers = "From: no-reply@example.com\r\n";

    if (mail($email, $subject, $message, $headers)) {
        echo "<script>alert('郵件已成功發送！請檢查您的收件箱。');</script>";
    } else {
        echo "<script>alert('郵件發送失敗，請稍後再試！'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('請輸入您的電子郵件地址！'); window.history.back();</script>";
}

// 關閉資料庫連接
mysqli_close($link);
?>



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
							<!-- <div class="panel-heading">
								<div class="row">
									<div class="col-lg-6 col-sm-6 col-xl-6">
										<a href="#" class="active" id="login-form-link">忘記密碼</a>
									</div>
								</div>
							</div> -->
							<div class="panel-body">
								<div class="row">
									<div class="col-lg-12">
										<!-- 驗證信箱 開始 -->
										<form id="login-form" action="verify.php" method="post" role="form"
											style="display: block;">

											<!-- 驗證碼輸入欄 -->
											<div class="form-group">
												<label class="icon-lp"><i class="fas fa-user-tie"></i></label>
												<input type="text" name="code" id="code" tabindex="1"
													class="form-control" placeholder="請輸入發送到您電子郵件的驗證碼：" value="" required>
											</div>
											<div class="form-group">
												<div class="row">
													<div class="col-sm-6 offset-sm-3">
														<input type="submit" name="login-submit" id="login-submit"
															tabindex="4" class="form-control btn btn-login" value="驗證">
													</div>
												</div>
											</div>
										</form>
										<!-- 驗證信箱 結束 -->
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