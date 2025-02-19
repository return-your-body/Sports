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

// 檢查 "帳號" 是否存在於 $_SESSION 中
if (isset($_SESSION["帳號"])) {
    // 獲取用戶帳號
    $帳號 = $_SESSION['帳號'];

    // 資料庫連接
    require '../db.php';

    // 查詢該帳號的詳細資料
    $sql = "SELECT user.account, people.name 
            FROM user 
            JOIN people ON user.user_id = people.user_id 
            WHERE user.account = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $帳號);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // 抓取對應姓名
        $row = mysqli_fetch_assoc($result);
        $姓名 = $row['name'];
        $帳號名稱 = $row['account'];
    } else {
        // 如果資料不存在，提示用戶重新登入
        echo "<script>
                alert('找不到對應的帳號資料，請重新登入。');
                window.location.href = '../index.html';
              </script>";
        exit();
    }
} else {
    echo "<script>
            alert('會話過期或資料遺失，請重新登入。');
            window.location.href = '../index.html';
          </script>";
    exit();
}







// 查詢資料庫，獲取用戶詳細資料
$query = "
    SELECT 
        p.name, 
        p.gender_id, 
        p.birthday, 
        p.idcard, 
        p.phone, 
        p.address, 
        p.email, 
        p.images
    FROM 
        people AS p
    JOIN 
        user AS u 
    ON 
        p.user_id = u.user_id
    WHERE 
        u.account = '$帳號'";

$result = mysqli_query($link, $query);

// 確保資料存在
if ($result && mysqli_num_rows($result) > 0) {
    $userData = mysqli_fetch_assoc($result);
    // 如果資料庫有圖片，使用圖片；否則使用預設圖片
    $profilePicture = !empty($userData['images'])
        ? "data:image/jpeg;base64," . base64_encode($userData['images'])
        : "images/300.jpg";
} else {
    $profilePicture = "images/300.jpg"; // 預設圖片
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
            background: linear-gradient(to right, #e0f7fa, rgb(178, 242, 208));
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

        /* 聯絡我們 */
        .custom-link {
            color: rgb(246, 247, 248);
            /* 設定超連結顏色 */
            text-decoration: none;
            /* 移除超連結的下劃線 */
        }

        .custom-link:hover {
            color: #0056b3;
            /* 滑鼠懸停時的顏色，例如深藍色 */
            text-decoration: underline;
            /* 懸停時增加下劃線效果 */
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
                                <li class="rd-nav-item "><a class="rd-nav-link" href="u_index.php">首頁</a>
                                </li>

                                <li class="rd-nav-item"><a class="rd-nav-link" href="#">關於我們</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="u_link.php">治療師介紹</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="u_caseshare.php">個案分享</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="u_body-knowledge.php">日常小知識</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="rd-nav-item"><a class="rd-nav-link" href="u_reserve.php">預約</a></li>
                                <!-- <li class="rd-nav-item"><a class="rd-nav-link active" href="#">預約</a>
                                    <ul class="rd-menu rd-navbar-dropdown">
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="u_reserve.php">立即預約</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="u_reserve-record.php">查看預約資料</a>
                                        </li>
                                        <li class="rd-dropdown-item"><a class="rd-dropdown-link"
                                                href="u_reserve-time.php">查看預約時段</a>
                                        </li>
                                    </ul>
                                </li> -->
                                <li class="rd-nav-item"><a class="rd-nav-link" href="u_history.php">歷史紀錄</a>
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
                                <li class="rd-nav-item"><a class="rd-nav-link" href="u_change.php">變更密碼</a>
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
                        <!-- <div class="rd-navbar-aside-right rd-navbar-collapse">
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
                        </div> -->

                        <?php
                        echo "歡迎 ~ ";
                        // 顯示姓名
                        echo $姓名;
                        ?>
                    </div>
                </nav>
            </div>
        </header>

        <!-- 個人檔案表單 Start -->
        <div class="container-fluid">
            <div class="form-container">
                <form action="個人資料.php" method="post" enctype="multipart/form-data">

                    <!-- 大頭貼上傳 -->
                    <div class="form-row text-center">
                        <label for="fileInput">
                            <!-- 頭像圖片預覽 -->
                            <div class="profile-picture" id="profilePicturePreview"
                                style="background-image: url('<?php echo $profilePicture; ?>');">
                            </div>
                        </label>
                        <input id="fileInput" type="file" name="profilePicture" accept="image/*"
                            onchange="uploadImage(event)">
                        <button type="button" class="delete-avatar-button" onclick="deleteAvatar()">刪除頭像</button>
                    </div>

                    <!-- 表單欄位 -->
                    <div class="form-row">
                        <label for="username">姓名 :</label>
                        <input id="username" type="text" name="username"
                            value="<?php echo htmlspecialchars($userData['name']); ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label for="gender">性別 :</label>
                        <input id="gender" type="text" name="gender"
                            value="<?php echo $userData['gender_id'] == 1 ? '男' : '女'; ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label for="userdate">出生年月日 :</label>
                        <input id="userdate" type="date" name="userdate" value="<?php echo $userData['birthday']; ?>"
                            max="<?php echo date('Y-m-d'); ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label for="useridcard">身分證字號 :</label>
                        <input id="useridcard" type="text" name="useridcard"
                            value="<?php echo htmlspecialchars($userData['idcard']); ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label for="userphone">聯絡電話 :</label>
                        <input id="userphone" type="tel" name="userphone"
                            value="<?php echo htmlspecialchars($userData['phone']); ?>" disabled>
                    </div>

                    <div class="form-row">
                        <label for="useremail">電子郵件 :</label>
                        <input id="useremail" type="email" name="useremail"
                            value="<?php echo htmlspecialchars($userData['email']); ?>" disabled readonly>
                    </div>

                    <div class="form-row">
                        <label for="address">地址 :</label>
                        <input id="address" type="text" name="address"
                            value="<?php echo htmlspecialchars($userData['address']); ?>" disabled>
                    </div>

                    <!-- 操作按鈕 -->
                    <div class="form-buttons">
                        <button type="button" id="editButton">修改資料</button>
                        <button type="button" id="confirmButton" style="display:none;"
                            onclick="confirmData()">確認資料</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // 開啟欄位編輯功能
            document.getElementById('editButton').addEventListener('click', function () {
                document.querySelectorAll('input').forEach(function (input) {
                    input.disabled = false;
                });

                // 確保電子郵件欄位仍然鎖住
                document.getElementById('useremail').disabled = true;
                document.getElementById('useremail').readOnly = true; // 確保即使透過開發者工具修改，也無法輸入

                document.getElementById('editButton').style.display = 'none'; // 隱藏“修改資料”按鈕
                document.getElementById('confirmButton').style.display = 'inline'; // 顯示“確認資料”按鈕
            });


            // 上傳並預覽圖片
            function uploadImage(event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('profilePicturePreview').style.backgroundImage = `url(${e.target.result})`;
                };
                reader.readAsDataURL(file);

                // 使用 AJAX 上传头像
                const formData = new FormData();
                formData.append('profilePicture', file);

                fetch('頭像上傳.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 上传成功后更新页面上的头像
                            document.getElementById('profilePicturePreview').src = data.imageUrl;
                        } else {
                            alert('頭像上傳失敗，請重試！');
                        }
                    })
                    .catch(error => console.error('上傳錯誤:', error));
            }

            // 確認資料並顯示 alert
            function confirmData() {
                const username = document.getElementById('username').value;
                const userdate = document.getElementById('userdate').value;
                const useridcard = document.getElementById('useridcard').value;
                const userphone = document.getElementById('userphone').value;
                const useremail = document.getElementById('useremail').value;
                const address = document.getElementById('address').value;

                // 驗證欄位格式

                // 姓名驗證: 空白檢查與格式檢查（只允許中文、英文和數字）
                if (!username) {
                    alert('姓名欄位不能為空');
                    return;
                } else if (!/^[\u4E00-\u9FA5a-zA-Z0-9]+$/.test(username)) {
                    alert('姓名格式錯誤，只能包含中文、英文和數字，不能包含特殊符號');
                    return;
                }
                // 電話驗證: 空白檢查與格式檢查（只允許中文、英文和數字）
                if (!address) {
                    alert('地址欄位不能為空');
                    return;
                } else if (!/^[\u4E00-\u9FA5a-zA-Z0-9]+$/.test(address)) {
                    alert('地址格式錯誤，只能包含中文、英文和數字，不能包含特殊符號');
                    return;
                }

                // 出生年月日驗證: 空白檢查
                if (!userdate) {
                    alert('出生年月日欄位不能為空');
                    return;
                }

                // 台灣身分證字號驗證函數
                function validateTaiwanID(identityNumber) {
                    // 檢查身分證字號是否符合正則格式
                    const identityFormat = /^[A-Z][1-2]\d{8}$/;
                    if (!identityFormat.test(identityNumber)) {
                        alert("身分證字號格式錯誤，請確認格式是否正確！");
                        return false;
                    }

                    // 首字母對應的數字範圍
                    const letterToNumberMap = {
                        "A": 10, "B": 11, "C": 12, "D": 13, "E": 14, "F": 15, "G": 16,
                        "H": 17, "J": 18, "K": 19, "L": 20, "M": 21, "N": 22, "P": 23,
                        "Q": 24, "R": 25, "S": 26, "T": 27, "U": 28, "V": 29, "X": 30,
                        "W": 31, "Y": 32, "Z": 33, "I": 34, "O": 35
                    };

                    // 取得字母部分
                    const firstLetter = identityNumber[0];

                    // 檢查字母是否合法
                    if (!letterToNumberMap.hasOwnProperty(firstLetter)) {
                        alert("身分證字號的首字母無效！");
                        return false;
                    }

                    // 轉換字母為數字
                    const firstLetterNumber = letterToNumberMap[firstLetter];

                    // 拆解身分證字號，取得每個數字
                    const digits = identityNumber.slice(1).split("").map(Number);

                    // 以身分證字號的第一個字母轉換成兩位數，並與後續數字結合
                    const firstDigit = Math.floor(firstLetterNumber / 10); // 取得字母數字的十位數
                    const secondDigit = firstLetterNumber % 10;           // 取得字母數字的個位數

                    // 將所有數字組成一個陣列
                    const fullDigits = [firstDigit, secondDigit, ...digits];

                    // 計算加權總和：每一位數字與對應的權重值相乘，然後求和
                    const weights = [1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 1];  // 權重值
                    let weightedSum = 0;
                    for (let i = 0; i < fullDigits.length; i++) {
                        // console.log(fullDigits[i]);

                        // // 顯示在提示框中
                        // alert(fullDigits[i]);
                        weightedSum += fullDigits[i] * weights[i];
                    }
                    // console.log(weightedSum);

                    // // 顯示在提示框中
                    // alert(weightedSum);

                    // 檢查加權總和是否能被 10 整除
                    if (weightedSum % 10 !== 0) {
                        alert("身分證字號無效，請確認輸入的字號！");
                        return false;
                    }

                    // 如果檢查通過，返回 true
                    return true;
                }

                // 用法範例
                const identityNumber = document.getElementById("useridcard").value;  // 假設身分證欄位的ID是 useridcard
                if (!validateTaiwanID(identityNumber)) {
                    return; // 若驗證失敗，則不繼續提交表單
                }


                // 聯絡電話驗證: 台灣手機號碼格式（09開頭，後面8位數字，且不允許後8位數出現6位或以上的重複數字）
                const phonePattern = /^09\d{8}$/;
                const repeatedPattern = /(\d)\1{5,}/; // 檢查是否有6個或更多相同的數字連續出現

                if (!userphone) {
                    alert('聯絡電話欄位不能為空');
                    return;
                } else if (!phonePattern.test(userphone)) {
                    alert('聯絡電話格式錯誤，台灣手機號碼需為09開頭並有8位數字');
                    return;
                } else if (repeatedPattern.test(userphone.slice(2))) {
                    alert('聯絡電話格式錯誤，後面8位數字不可出現6位或以上重複的數字');
                    return;
                }

                // 電子郵件驗證: 空白檢查與格式檢查
                if (!useremail) {
                    alert('電子郵件欄位不能為空');
                    return;
                } else if (!/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/.test(useremail)) {
                    alert('電子郵件格式錯誤，請輸入有效的電子郵件');
                    return;
                }

                // 組合要顯示在 alert 的訊息
                const confirmMessage =
                    `請確認您的資料:\n` +
                    `姓名: ${username}\n` +
                    `出生年月日: ${userdate}\n` +
                    `身分證字號: ${useridcard}\n` +
                    `聯絡電話: ${userphone}\n` +
                    `電子郵件: ${useremail}\n` +
                    `地址: ${address}\n` +
                    `確定要提交資料嗎？`;

                // 顯示確認 alert
                if (confirm(confirmMessage)) {
                    document.querySelector('form').submit(); // 確認後提交表單
                }
            }

            // 刪除頭像並顯示預設圖片
            function deleteAvatar() {
                if (confirm("確定要刪除頭像嗎？")) {
                    fetch('刪除頭像.php', {
                        method: 'POST'
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // 更新為預設圖片
                                document.getElementById('profilePicturePreview').style.backgroundImage = "url('images/300.jpg')";
                                alert("頭像已成功刪除！");
                            } else {
                                alert("刪除頭像失敗，請重試！");
                            }
                        })
                        .catch(error => console.error("刪除錯誤:", error));
                }
            }

        </script>
        <!-- 個人檔案表單 End -->

        <footer class="section novi-bg novi-bg-img footer-simple">
            <div class="container">
                <div class="row row-40">
                    <div class="col-md-4">
                        <h4>關於我們</h4>
                        <ul class="list-inline" style="font-size: 40px; display: inline-block;color: #333333; ">
                            <li><a class="icon novi-icon icon-default icon-custom-facebook"
                                    href="https://www.facebook.com/ReTurnYourBody/" target="_blank"></a></li>
                            <li><a class="icon novi-icon icon-default icon-custom-linkedin"
                                    href="https://lin.ee/sUaUVMq" target="_blank"></a></li>
                            <li><a class="icon novi-icon icon-default icon-custom-instagram"
                                    href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"
                                    target="_blank"></a></li>
                        </ul>

                    </div>
                    <div class="col-md-3">
                        <h4>快速連結</h4>
                        <ul class="list-marked">
                            <li><a href="u_index.php">首頁</a></li>
                            <li><a href="u_link.php.php">治療師介紹</a></li>
                            <li><a href="u_caseshare.php">個案分享</a></li>
                            <li><a href="u_body-knowledge.php">日常小知識</a></li>
                            <li><a href="u_reserve.php">預約</a></li>
                            <li><a href="u_reserve-record.php">查看預約資料</a></li>
                            <li><a href="u_reserve-time.php">查看預約時段</a></li>
                            <li><a href="u_history.php">歷史紀錄</a></li>
                            <li> <a href="u_profile.php">個人資料</a></li>
                            </a></li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <h4>聯絡我們</h4>
                        <br />
                        <ul>
                            <li>📍 <strong>診療地點:</strong>大重仁骨科復健科診所</li><br />
                            <li>📍 <strong>地址:</strong>
                                <a href="https://maps.app.goo.gl/u3TojSMqjGmdx5Pt5" class="custom-link" target="_blank"
                                    rel="noopener noreferrer">
                                    241 新北市三重區重新路五段 592 號
                                </a>
                            </li>
                            <br />
                            <li>📍 <strong>電話:</strong>(02) 2995-8283</li>
                        </ul>

                        <!-- <form class="rd-mailform rd-form-boxed" data-form-output="form-output-global"
                            data-form-type="subscribe" method="post" action="bat/rd-mailform.php">
                            <div class="form-wrap">
                                <input class="form-input" type="email" name="email" data-constraints="@Email @Required"
                                    id="footer-mail">
                                <label class="form-label" for="footer-mail">請輸入您的電子郵件</label>
                            </div>
                            <button class="form-button linearicons-paper-plane"></button>
                        </form> -->
                    </div>
                </div>
            </div>
        </footer>


        <!-- Global Mailform Output-->
        <div class="snackbars" id="form-output-global"></div>
        <!-- Javascript-->
        <script src="js/core.min.js"></script>
        <script src="js/script.js"></script>
</body>

</html>