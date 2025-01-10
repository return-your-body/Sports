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
?>

<!DOCTYPE html>
<html lang="en">

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
                                <li class="rd-nav-item "><a class="rd-nav-link" href="">用戶管理</a>
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

                                <li class="rd-nav-item active"><a class="rd-nav-link" href="a_doctorlist.php">醫生資料</a>
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
                    <p class="heading-1 breadcrumbs-custom-title">管理醫生資料</p>
                    <ul class="breadcrumbs-custom-path">
                        <li><a href="a_index.php">首頁</a></li>
                        <li><a href="">管理</a></li>
                        <li class="active">管理醫生資料</li>
                    </ul>
                </div>
            </section>

        </div>


        <?php
        // 引入資料庫連線檔案
        require '../db.php';

        // 初始化變數
        $doctorprofile_id = $doctor_id = $education = $current_position = $specialty = $certifications = $treatment_concept = "";

        if (isset($_GET['edit_id'])) {
            // 編輯模式：根據ID抓取資料
            $edit_id = $_GET['edit_id'];
            $query = "SELECT * FROM doctorprofile WHERE doctorprofile_id = $edit_id";
            $result = mysqli_query($link, $query);

            if ($result && $row = mysqli_fetch_assoc($result)) {
                $doctorprofile_id = $row['doctorprofile_id'];
                $doctor_id = $row['doctor_id'];
                $education = $row['education'];
                $current_position = $row['current_position'];
                $specialty = $row['specialty'];
                $certifications = $row['certifications'];
                $treatment_concept = $row['treatment_concept'];
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 處理表單提交
            $doctor_id = $_POST['doctor_id'];
            $education = $_POST['education'];
            $current_position = $_POST['current_position'];
            $specialty = $_POST['specialty'];
            $certifications = $_POST['certifications'];
            $treatment_concept = $_POST['treatment_concept'];

            if (isset($_POST['doctorprofile_id']) && !empty($_POST['doctorprofile_id'])) {
                // 更新資料
                $doctorprofile_id = $_POST['doctorprofile_id'];
                $query = "UPDATE doctorprofile SET doctor_id='$doctor_id', education='$education', current_position='$current_position', 
                      specialty='$specialty', certifications='$certifications', treatment_concept='$treatment_concept', updated_at=NOW() 
                      WHERE doctorprofile_id=$doctorprofile_id";
            } else {
                // 新增資料
                $query = "INSERT INTO doctorprofile (doctor_id, education, current_position, specialty, certifications, treatment_concept, created_at, updated_at) 
                      VALUES ('$doctor_id', '$education', '$current_position', '$specialty', '$certifications', '$treatment_concept', NOW(), NOW())";
            }

            if (mysqli_query($link, $query)) {
                echo "<p>資料成功保存！</p>";
            } else {
                echo "<p>錯誤: " . mysqli_error($link) . "</p>";
            }
        }
        ?>

        <form method="POST" action="">
            <input type="hidden" name="doctorprofile_id" value="<?php echo $doctorprofile_id; ?>">

            <label for="doctor_id">醫生 ID:</label><br>
            <input type="text" name="doctor_id" id="doctor_id" value="<?php echo $doctor_id; ?>" required><br><br>

            <label for="education">學歷:</label><br>
            <textarea name="education" id="education"><?php echo $education; ?></textarea><br><br>

            <label for="current_position">目前職位:</label><br>
            <textarea name="current_position" id="current_position"><?php echo $current_position; ?></textarea><br><br>

            <label for="specialty">專長:</label><br>
            <textarea name="specialty" id="specialty"><?php echo $specialty; ?></textarea><br><br>

            <label for="certifications">證書:</label><br>
            <textarea name="certifications" id="certifications"><?php echo $certifications; ?></textarea><br><br>

            <label for="treatment_concept">治療理念:</label><br>
            <textarea name="treatment_concept"
                id="treatment_concept"><?php echo $treatment_concept; ?></textarea><br><br>

            <button type="submit">保存</button>
        </form>

        <h3>現有資料</h3>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>醫生 ID</th>
                <th>學歷</th>
                <th>目前職位</th>
                <th>專長</th>
                <th>證書</th>
                <th>治療理念</th>
                <th>操作</th>
            </tr>
            <?php
            // 抓取所有資料
            $query = "SELECT * FROM doctorprofile";
            $result = mysqli_query($link, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['doctorprofile_id'] . "</td>";
                    echo "<td>" . $row['doctor_id'] . "</td>";
                    echo "<td>" . $row['education'] . "</td>";
                    echo "<td>" . $row['current_position'] . "</td>";
                    echo "<td>" . $row['specialty'] . "</td>";
                    echo "<td>" . $row['certifications'] . "</td>";
                    echo "<td>" . $row['treatment_concept'] . "</td>";
                    echo "<td><a href='?edit_id=" . $row['doctorprofile_id'] . "'>編輯</a></td>";
                    echo "</tr>";
                }
            }
            ?>
        </table>

</body>

</html>