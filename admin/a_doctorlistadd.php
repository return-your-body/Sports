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
    <title>運動筋膜放鬆-治療師資料新增/編輯</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css2?family=Exo:wght@300;400;500;600;700&amp;display=swap">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css">

    <!-- jQuery 必須先載入 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

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
                                <li class="rd-nav-item "><a class="rd-nav-link" href="a_comprehensive.php">綜合</a>
                                </li>
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
                    <p class="heading-1 breadcrumbs-custom-title">治療師資料新增/編輯</p>
                    <ul class="breadcrumbs-custom-path">
                        <li><a href="a_index.php">首頁</a></li>
                        <li><a href="">治療師資料</a></li>
                        <li class="active">治療師資料新增/編輯</li>
                    </ul>
                </div>
            </section>
        </div>


        <!-- 表單樣式 -->
        <section class="section section-lg bg-default text-center">
            <div class="container">
                <div class="row justify-content-sm-center">
                    <div class="col-md-10 col-xl-8">
                        <form id="doctorForm" enctype="multipart/form-data">

                            <div class="row row-20 row-fix">
                                <!-- 治療師姓名 -->
                                <div class="col-md-6">
                                    <div class="form-wrap form-wrap-validation">
                                        <label class="form-label-outside" for="doctor_id">治療師姓名</label>
                                        <select class="form-input" id="doctor_id" name="doctor_id" required>
                                            <option value="" selected>-- 請選擇治療師 --</option>
                                            <?php
                                            include '../db.php'; // 引入資料庫連線
                                            
                                            // 查詢治療師資料（grade_id = 2 為治療師）
                                            $query = "
                                    SELECT d.doctor_id, d.doctor
                                    FROM doctor d
                                    INNER JOIN user u ON d.user_id = u.user_id
                                    INNER JOIN grade g ON u.grade_id = g.grade_id
                                    WHERE g.grade_id = 2
                                    ";
                                            $result = mysqli_query($link, $query);

                                            if ($result) {
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<option value='" . $row['doctor_id'] . "'>" . htmlspecialchars($row['doctor']) . "</option>";
                                                }
                                            } else {
                                                echo "<option value=''>查詢錯誤</option>";
                                            }
                                            ?>
                                        </select>
                                        <!-- <p>當前選擇的治療師 ID：<span id="selectedDoctorId">未選擇</span></p> -->
                                    </div>
                                </div>

                                <!-- 學歷 -->
                                <div class="col-md-6">
                                    <div class="form-wrap form-wrap-validation">
                                        <label class="form-label-outside">學歷</label>
                                        <div id="education-container">
                                            <div class="input-group">
                                                <input class="form-input" type="text" name="education[]"
                                                    placeholder="請輸入學歷" required />
                                                <button type="button" class="btn btn-success"
                                                    onclick="addInput('education-container', 'education[]')">+</button>
                                            </div>
                                        </div>
                                        <input type="hidden" id="education-final" name="education_final">
                                    </div>
                                </div>

                                <!-- 現任職務 -->
                                <div class="col-md-6">
                                    <div class="form-wrap form-wrap-validation">
                                        <label class="form-label-outside">現任職務</label>
                                        <div id="current_position-container">
                                            <div class="input-group">
                                                <input class="form-input" type="text" name="current_position[]"
                                                    placeholder="請輸入現任職務" required />
                                                <button type="button" class="btn btn-success"
                                                    onclick="addInput('current_position-container', 'current_position[]')">+</button>
                                            </div>
                                        </div>
                                        <input type="hidden" id="current_position-final" name="current_position_final">
                                    </div>
                                </div>

                                <!-- 專長描述 -->
                                <div class="col-md-6">
                                    <div class="form-wrap form-wrap-validation">
                                        <label class="form-label-outside">專長描述</label>
                                        <div id="specialty-container">
                                            <div class="input-group">
                                                <input class="form-input" type="text" name="specialty[]"
                                                    placeholder="請輸入專長描述" required />
                                                <button type="button" class="btn btn-success"
                                                    onclick="addInput('specialty-container', 'specialty[]')">+</button>
                                            </div>
                                        </div>
                                        <input type="hidden" id="specialty-final" name="specialty_final">
                                    </div>
                                </div>

                                <!-- 專業認證 -->
                                <div class="col-md-6">
                                    <div class="form-wrap form-wrap-validation">
                                        <label class="form-label-outside">專業認證</label>
                                        <div id="certifications-container">
                                            <div class="input-group">
                                                <input class="form-input" type="text" name="certifications[]"
                                                    placeholder="請輸入專業認證" required />
                                                <button type="button" class="btn btn-success"
                                                    onclick="addInput('certifications-container', 'certifications[]')">+</button>
                                            </div>
                                        </div>
                                        <input type="hidden" id="certifications-final" name="certifications_final">
                                    </div>
                                </div>

                                <!-- 治療理念 -->
                                <div class="col-md-6">
                                    <div class="form-wrap form-wrap-validation">
                                        <label class="form-label-outside">治療理念</label>
                                        <div id="treatment_concept-container">
                                            <div class="input-group">
                                                <input class="form-input" type="text" name="treatment_concept[]"
                                                    placeholder="請輸入治療理念" required />
                                                <button type="button" class="btn btn-success"
                                                    onclick="addInput('treatment_concept-container', 'treatment_concept[]')">+</button>
                                            </div>
                                        </div>
                                        <input type="hidden" id="treatment_concept-final"
                                            name="treatment_concept_final">
                                    </div>
                                </div>

                                <!-- 圖片上傳 -->
                                <div class="col-md-12">
                                    <div class="form-wrap form-wrap-validation">
                                        <label class="form-label-outside" for="image">上傳照片</label>
                                        <input class="form-input" id="image" type="file" name="image" accept="image/*"
                                            onchange="previewImage(event)" />
                                        <br>
                                        <img id="image-preview" src="" alt="圖片預覽"
                                            style="max-width: 200px; display: none; margin-top: 10px;">
                                    </div>
                                </div>

                                <!-- 送出按鈕 -->
                                <div class="col-md-12 text-center">
                                    <button class="button button-primary" type="submit">新增治療師資料</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $("#doctor_id").change(function () {
                let doctorId = $(this).val();
                if (doctorId) {
                    $.ajax({
                        url: "獲取治療師資料.php",
                        type: "GET",
                        data: { doctor_id: doctorId },
                        dataType: "json",
                        success: function (response) {
                            // 清空輸入框內容
                            $("#education-container, #current_position-container, #specialty-container, #certifications-container, #treatment_concept-container").empty();

                            if (response.status === "success") {
                                let data = response.data;

                                // ✅ 學歷
                                let educationArray = data.education ? data.education.split("\r\n") : [""];
                                educationArray.forEach((edu, index) => {
                                    $("#education-container").append(createInputGroup("education[]", edu, index === 0));
                                });

                                // ✅ 現任職務
                                let positionArray = data.current_position ? data.current_position.split("\r\n") : [""];
                                positionArray.forEach((pos, index) => {
                                    $("#current_position-container").append(createInputGroup("current_position[]", pos, index === 0));
                                });

                                // ✅ 專長描述
                                let specialtyArray = data.specialty ? data.specialty.split("\r\n") : [""];
                                specialtyArray.forEach((spec, index) => {
                                    $("#specialty-container").append(createInputGroup("specialty[]", spec, index === 0));
                                });

                                // ✅ 專業認證
                                let certArray = data.certifications ? data.certifications.split("\r\n") : [""];
                                certArray.forEach((cert, index) => {
                                    $("#certifications-container").append(createInputGroup("certifications[]", cert, index === 0));
                                });

                                // ✅ 治療理念
                                let treatmentArray = data.treatment_concept ? data.treatment_concept.split("\r\n") : [""];
                                treatmentArray.forEach((concept, index) => {
                                    $("#treatment_concept-container").append(createInputGroup("treatment_concept[]", concept, index === 0));
                                });

                                // ✅ 圖片顯示
                                if (data.image) {
                                    $("#image-preview").attr("src", "data:image/jpeg;base64," + data.image).show();
                                } else {
                                    $("#image-preview").hide();
                                }

                            } else {
                                alert(response.message);
                                // **如果查無資料，預設顯示空的輸入框**
                                $("#education-container").append(createInputGroup("education[]", "", true));
                                $("#current_position-container").append(createInputGroup("current_position[]", "", true));
                                $("#specialty-container").append(createInputGroup("specialty[]", "", true));
                                $("#certifications-container").append(createInputGroup("certifications[]", "", true));
                                $("#treatment_concept-container").append(createInputGroup("treatment_concept[]", "", true));
                                $("#image-preview").hide();
                            }
                        },
                        error: function (xhr) {
                            console.error("AJAX 錯誤:", xhr.responseText);
                            alert("發生錯誤，請檢查伺服器日誌");
                        }
                    });
                }
            });

            // **動態新增輸入框函數**
            window.addInput = function (containerId, inputName) {
                var container = $("#" + containerId);
                var newInput = createInputGroup(inputName, "", false); // 新增的輸入框應該有 `-` 按鈕
                container.append(newInput);
            };

            // **建立輸入框的函數**
            function createInputGroup(inputName, value, isFirst) {
                let inputGroup = $('<div class="input-group mb-2"></div>');
                let input = $('<input class="form-input" type="text" name="' + inputName + '" value="' + value + '" required>');

                // ✅ 第一個輸入框顯示 `+` 按鈕，其他的顯示 `-`
                let button;
                if (isFirst) {
                    button = $('<button type="button" class="btn btn-success">+</button>');
                    button.click(function () {
                        addInput(inputGroup.parent().attr('id'), inputName);
                    });
                } else {
                    button = $('<button type="button" class="btn btn-danger">−</button>');
                    button.click(function () {
                        inputGroup.remove();
                    });
                }

                inputGroup.append(input).append(button);
                return inputGroup;
            }



            document.getElementById("doctor_id").addEventListener("change", function () {
                let selectedValue = this.value;
                if (selectedValue) {
                    document.getElementById("selectedDoctorId").textContent = selectedValue;
                } else {
                    document.getElementById("selectedDoctorId").textContent = "未選擇";
                }
            });


            $(document).ready(function () {
                $("form").on("submit", function (event) {
                    event.preventDefault();

                    // **合併多個輸入框數據**
                    combineInputs("education-container", "education-final");
                    combineInputs("current_position-container", "current_position-final");
                    combineInputs("specialty-container", "specialty-final");
                    combineInputs("certifications-container", "certifications-final");
                    combineInputs("treatment_concept-container", "treatment_concept-final");

                    var formData = new FormData(this);

                    $.ajax({
                        url: "新增治療師資料.php",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: "json",
                        success: function (response) {
                            alert(response.message);
                            if (response.status === "success") {
                                $("form")[0].reset();
                                $("#image-preview").hide();
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX 錯誤:", xhr.responseText);
                            alert("提交失敗，請檢查伺服器日誌：" + xhr.responseText);
                        }
                    });
                });

                function combineInputs(containerId, finalInputId) {
                    var values = $("#" + containerId).find("input").map(function () {
                        return $(this).val().trim();
                    }).get();
                    $("#" + finalInputId).val(values.join("\r\n"));
                }

                // ✅ 圖片預覽功能
                function previewImage(event) {
                    var reader = new FileReader();
                    reader.onload = function () {
                        document.getElementById("image-preview").src = reader.result;
                        document.getElementById("image-preview").style.display = "block";
                    };
                    reader.readAsDataURL(event.target.files[0]);
                }

                $("#image").change(previewImage);


                // ✅ 動態新增輸入框
                window.addInput = function (containerId, inputName) {
                    var container = $("#" + containerId);
                    var div = $('<div class="input-group mb-2"></div>');

                    var input = $('<input class="form-input" type="text" name="' + inputName + '" placeholder="請輸入內容" required>');

                    var removeBtn = $('<button type="button" class="btn btn-danger">−</button>');
                    removeBtn.click(function () {
                        div.remove();
                    });

                    div.append(input).append(removeBtn);
                    container.append(div);
                };

                // ✅ 合併多個輸入框為一個字串，使用 `\r\n` 分隔
                function combineInputs(containerId, finalInputId) {
                    var values = $("#" + containerId).find("input").map(function () {
                        return $(this).val().trim();
                    }).get();

                    $("#" + finalInputId).val(values.join("\r\n"));
                }
            });
        </script>

        <!-- Global Mailform Output-->
        <div class="snackbars" id="form-output-global"></div>
        <!-- Javascript-->
        <script src="js/core.min.js"></script>
        <script src="js/script.js"></script>
</body>

</html>