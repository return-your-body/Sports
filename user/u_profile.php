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
    <title>運動筋膜放鬆</title>
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
        /* 背景配圖與漸變顏色 */
        body {
            background: linear-gradient(to right, #e0f7fa, #b2ebf2);
            background-size: cover;
            font-family: 'Roboto', sans-serif;
        }

        /* 表單容器樣式 */
        .form-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9);
            border: 2px solid #d0e2e5;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            border-radius: 15px;
        }

        /* 標題樣式 */
        .form-container h2 {
            text-align: center;
            color: #009688;
            font-size: 28px;
            margin-bottom: 30px;
            font-weight: 700;
        }

        /* 表單字段樣式 */
        .form-row {
            margin-bottom: 20px;
        }

        .form-row label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #00796b;
        }

        .form-row input {
            width: 100%;
            padding: 12px;
            border: 2px solid #b0bec5;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f1f8e9;
            transition: all 0.3s ease;
        }

        .form-row input:focus {
            border-color: #00796b;
            background-color: #e8f5e9;
            outline: none;
        }

        /* 按鈕樣式 */
        .form-buttons {
            text-align: center;
        }

        .form-buttons button {
            padding: 12px 24px;
            background-color: #009688;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .form-buttons button:hover {
            background-color: #00796b;
            transform: translateY(-2px);
        }

        .form-buttons button:active {
            transform: translateY(0);
        }

        /* 圓形圖片樣式 */
        .profile-picture {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 4px solid #009688;
            margin: 20px auto;
            display: block;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            background-size: cover;
            /* 让图片完全覆盖容器 */
            background-position: center;
            /* 确保图片居中显示 */
            background-repeat: no-repeat;
            /* 防止背景重复 */
            overflow: hidden;
            /* 隐藏溢出部分 */
        }

        .profile-picture:hover {
            opacity: 0.85;
        }

        /* 刪除頭像按鈕樣式 */
        .delete-avatar-button {
            background-color: #ff5252;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .delete-avatar-button:hover {
            background-color: #e53935;
        }

        /* 隱藏的文件上傳 */
        #fileInput {
            display: none;
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
                    fill="none"></polyline>
                <polyline class="line-cornered stroke-animation" points="0,0 0,100 100,100" stroke-width="10"
                    fill="none"></polyline>
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
                                <!--Brand--><a class="brand-name" href="u_index.html"><img class="logo-default"
                                        src="images/logo-default-172x36.png" alt="" width="86" height="18"
                                        loading="lazy" /><img class="logo-inverse" src="images/logo-inverse-172x36.png"
                                        alt="" width="86" height="18" loading="lazy" /></a>
                            </div>
                        </div>
                        <div class="rd-navbar-nav-wrap">
                            <ul class="rd-navbar-nav">
                                <li class="rd-nav-item"><a class="rd-nav-link" href="u_index.php">主頁</a>
                                </li>

                                <li class="rd-nav-item"><a class="rd-nav-link" href="">關於我們</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">醫生介紹</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">個案分享</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">日常小知識</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="#">預約</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="u_reserve.php">立即預約</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">查看預約資料</a>
                                            <!-- 修改預約 -->
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="">查看預約時段</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="">歷史紀錄</a>
                                </li>
                                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="">歷史紀錄</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="single-teacher.html">個人資料</a>
                                        </li>
                                    </ul>
                                </li> -->

                                <li class="rd-nav-item active"><a class="rd-nav-link" href="u_profile.php">個人資料</a>
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
                                <!-- <li class="rd-nav-item"><a class="rd-nav-link" href="contacts.html">Contacts</a>
                                  </li> -->
                            </ul>
                        </div>
                        <div class="rd-navbar-collapse-toggle" data-rd-navbar-toggle=".rd-navbar-collapse"><span></span>
                        </div>
                        <div class="rd-navbar-aside-right rd-navbar-collapse">
                            <div class="rd-navbar-social">
                                <div class="rd-navbar-social-text">聯絡方式</div>
                                <ul class="list-inline">
                                    <li><a class="icon novi-icon icon-default icon-custom-facebook"
                                            href="https://www.facebook.com/ReTurnYourBody/"></a></li>
                                    <li><a class="icon novi-icon icon-default icon-custom-linkedin"
                                            href="https://lin.ee/sUaUVMq"></a></li>
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

        <!-- 個人檔案表單 Start -->
        <div class="container-fluid">
            <div class="form-container">
                <form action="所有使用者資料.php" method="post" enctype="multipart/form-data" onsubmit="return confirmData()">
                    <!-- 大頭貼上傳 -->
                    <div class="form-row text-center">
                        <label for="fileInput">
                            <!-- 如果 $profilePicture 为空，则使用預設圖 img/300.jpg -->
                            <div class="profile-picture" id="profilePicturePreview"
                                style="background-image: url('<?php echo $profilePicture ? $profilePicture : 'img/300.jpg'; ?>');">
                            </div>
                            <button type="button" class="delete-avatar-button" onclick="deleteAvatar()">刪除頭像</button>
                        </label>
                        <input id="fileInput" type="file" name="profilePicture" accept="image/*"
                            onchange="uploadImage(event)">
                    </div>

                    <!-- 表單欄位 -->
                    <div class="form-row">
                        <label for="username">姓名 :</label>
                        <input id="username" type="text" name="username" value="<?php echo $姓名; ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label for="gender">性別 :</label>
                        <input id="gender" type="text" name="gender" value="<?php echo $性別 === 1 ? '男' : '女'; ?>"
                            disabled>
                    </div>

                    <div class="form-row">
                        <label for="userdate">出生年月日 :</label>
                        <input id="userdate" type="date" name="userdate" value="<?php echo $出生年月日; ?>"
                            max="<?php echo date('Y-m-d'); ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label for="useridcard">身分證字號 :</label>
                        <input id="useridcard" type="text" name="useridcard" value="<?php echo $身分證字號; ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label for="userphone">聯絡電話 :</label>
                        <input id="userphone" type="tel" name="userphone" value="<?php echo $電話; ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label for="useremail">電子郵件 :</label>
                        <input id="useremail" type="email" name="useremail" value="<?php echo $電子郵件; ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label for="address">地址 :</label>
                        <input id="address" type="text" name="address" value="<?php echo $地址; ?>" disabled>
                    </div>

                    <!-- 操作按鈕 -->
                    <div class="form-buttons">
                        <button type="button" id="editButton" onclick="enableEdit()">修改資料</button>
                        <button type="submit" id="confirmButton" style="display:none;">確認資料</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function validateTaiwanID(identityNumber) {
                const idRegex = /^[A-Z][1-2]\d{8}$/;
                if (!idRegex.test(identityNumber)) {
                    return false;
                }
                const letterToNumberMap = {
                    "A": 10, "B": 11, "C": 12, "D": 13, "E": 14, "F": 15, "G": 16,
                    "H": 17, "J": 18, "K": 19, "L": 20, "M": 21, "N": 22, "P": 23,
                    "Q": 24, "R": 25, "S": 26, "T": 27, "U": 28, "V": 29, "X": 30,
                    "W": 31, "Y": 32, "Z": 33, "I": 34, "O": 35
                };
                const firstLetter = identityNumber[0];
                const digits = identityNumber.slice(1).split("").map(Number);
                const firstTwoDigits = [Math.floor(letterToNumberMap[firstLetter] / 10), letterToNumberMap[firstLetter] % 10];
                const fullDigits = firstTwoDigits.concat(digits);
                const weights = [1, 9, 8, 7, 6, 5, 4, 3, 2, 1];
                const checksum = fullDigits.reduce((sum, digit, idx) => sum + digit * weights[idx], 0);
                return checksum % 10 === 0;
            }

            function enableEdit() {
                document.querySelectorAll('input').forEach(input => input.disabled = false);
                document.getElementById('editButton').style.display = 'none';
                document.getElementById('confirmButton').style.display = 'inline';
            }

            function confirmData() {
                const username = document.getElementById('username').value.trim();
                const gender = document.getElementById('gender').value.trim();
                const userdate = document.getElementById('userdate').value;
                const useridcard = document.getElementById('useridcard').value.trim();
                const userphone = document.getElementById('userphone').value.trim();
                const useremail = document.getElementById('useremail').value.trim();
                const address = document.getElementById('address').value.trim();

                if (!username || !/^[\u4E00-\u9FA5a-zA-Z\s]+$/.test(username)) {
                    alert('姓名格式錯誤，僅限中文、英文、空格！');
                    return false;
                }

                if (gender !== '男' && gender !== '女') {
                    alert('性別只能填寫「男」或「女」！');
                    return false;
                }

                if (!userdate) {
                    alert('請選擇出生年月日！');
                    return false;
                }

                if (!validateTaiwanID(useridcard)) {
                    alert('身分證字號格式錯誤！');
                    return false;
                }

                const phoneRegex = /^09\d{8}$/;
                if (!phoneRegex.test(userphone)) {
                    alert('聯絡電話格式錯誤，需符合台灣手機號碼規範！');
                    return false;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(useremail)) {
                    alert('電子郵件格式錯誤！');
                    return false;
                }

                if (!address) {
                    alert('地址不可為空！');
                    return false;
                }

                return confirm(`
            請確認以下資料：
            姓名：${username}
            性別：${gender}
            出生年月日：${userdate}
            身分證字號：${useridcard}
            聯絡電話：${userphone}
            電子郵件：${useremail}
            地址：${address}
        `);
            }
        </script>
        <!-- 個人檔案表單 End -->


        <!-- Global Mailform Output-->
        <div class="snackbars" id="form-output-global"></div>
        <!-- Javascript-->
        <script src="js/core.min.js"></script>
        <script src="js/script.js"></script>
</body>

</html>