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
                                                href="a_doctorlistmod.php">修改治療師資料</a>
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
                    <p class="heading-1 breadcrumbs-custom-title">新增治療項目</p>
                    <ul class="breadcrumbs-custom-path">
                        <li><a href="a_index.php">首頁</a></li>
                        <li><a href="">關於治療師</a></li>
                        <li class="active">新增治療項目</li>
                    </ul>
                </div>
            </section>
        </div>

        <?php
        // 引入資料庫連線檔案
        include '../db.php';

        // 接收參數並設置默認值
        $search = isset($_GET['search']) ? mysqli_real_escape_string($link, $_GET['search']) : ''; // 搜尋關鍵字
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1; // 當前頁數，默認為第 1 頁
        $rowsPerPage = isset($_GET['rowsPerPage']) ? (int) $_GET['rowsPerPage'] : 3; // 每頁顯示筆數，默認為 3 筆
        $offset = ($page - 1) * $rowsPerPage; // 計算查詢偏移量
        
        // 查詢總筆數
        $countQuery = "
    SELECT COUNT(*) AS total 
    FROM item 
    WHERE item LIKE '%$search%'"; // 篩選符合搜尋條件的項目數量
        $countResult = mysqli_query($link, $countQuery);
        $totalRows = mysqli_fetch_assoc($countResult)['total']; // 總筆數
        $totalPages = ceil($totalRows / $rowsPerPage); // 計算總頁數
        
        // 查詢資料，加入分頁邏輯
        $query = "
    SELECT * 
    FROM item 
    WHERE item LIKE '%$search%' 
    LIMIT $rowsPerPage OFFSET $offset"; // 根據搜尋條件和分頁設定查詢資料
        $result = mysqli_query($link, $query);

        // 初始化結果陣列
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row; // 將每條資料存入陣列
        }
        ?>

        <section class="section section-lg bg-default text-center">
            <div class="container">

                <form class="search-form" method="GET" action="a_treatment.php"
                    style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 20px; width: 100%;">
                    <!-- 搜尋輸入框 -->
                    <div style="flex: 4;">
                        <input class="form-input" type="text" name="search"
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                            placeholder="請輸入項目名稱"
                            style="padding: 10px 15px; font-size: 16px; width: 100%; border: 1px solid #ccc; border-radius: 4px; outline: none; box-sizing: border-box;">
                    </div>
                    <!-- 搜尋按鈕 -->
                    <div style="flex: 1;">
                        <button type="submit"
                            style="padding: 10px 15px; font-size: 16px; width: 100%; border: none; border-radius: 4px; background-color: #00A896; color: white; cursor: pointer; box-sizing: border-box;">
                            <span class="icon mdi mdi-magnify"></span> 搜尋
                        </button>
                    </div>
                    <!-- 返回按鈕（僅當有搜尋參數時顯示） -->
                    <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
                        <div style="flex: 0.5;">
                            <button type="button" onclick="window.location.href='a_treatment.php'"
                                style="padding: 10px 15px; font-size: 16px; width: 100%; border: none; border-radius: 4px; background-color: #ccc; color: #333; cursor: pointer; box-sizing: border-box;">
                                返回
                            </button>
                        </div>
                    <?php endif; ?>
                    <!-- 分頁選單 -->
                    <div style="flex: 1;">
                        <select name="rowsPerPage" onchange="this.form.submit()"
                            style="padding: 10px 15px; font-size: 16px; width: 100%; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="3" <?php echo $rowsPerPage == 3 ? 'selected' : ''; ?>>3筆/頁</option>
                            <option value="5" <?php echo $rowsPerPage == 5 ? 'selected' : ''; ?>>5筆/頁</option>
                            <option value="10" <?php echo $rowsPerPage == 10 ? 'selected' : ''; ?>>10筆/頁</option>
                            <option value="20" <?php echo $rowsPerPage == 20 ? 'selected' : ''; ?>>20筆/頁</option>
                        </select>
                    </div>
                    <style>
                        /* 可選：讓視窗顯示時居中 */
                        #addItemModal {
                            display: none;
                            /* 默認隱藏 */
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0, 0, 0, 0.5);
                            /* 半透明背景 */
                            z-index: 1000;
                            justify-content: center;
                            align-items: center;
                        }
                    </style>

                    <script>
                        function showModal() {
                            document.getElementById('addItemModal').style.display = 'flex';
                        }

                        function closeModal() {
                            document.getElementById('addItemModal').style.display = 'none';
                        }
                    </script>


                    <!-- 新增按鈕 -->
                    <div style="flex: 0.25; text-align: center;">
                        <!-- 點擊「+」觸發視窗 -->
                        <a href="javascript:void(0)" onclick="showModal()" style="font-size: 30px;
                                                                                    color: #00A896;
                                                                                    text-decoration: none;
                                                                                    cursor: pointer;
                                                                                    font-weight: bold;">
                            +
                        </a>
                    </div>
                </form>

                <!-- 懸浮視窗 -->
                <div id="addItemModal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;">
                    <div style="
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);">
                        <h3 style="margin-top: 0; text-align: center;">新增治療項目</h3>
                        <form id="addItemForm" method="POST" action="新增治療項目.php">
                            <div style="margin-bottom: 10px;">
                                <label for="itemName" style="display: block; margin-bottom: 5px;">項目名稱</label>
                                <input type="text" id="itemName" name="item" required style="
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ccc;
                    border-radius: 4px;">
                            </div>
                            <div style="margin-bottom: 10px;">
                                <label for="caption" style="display: block; margin-bottom: 5px;">項目說明</label>
                                <textarea id="caption" name="caption" rows="3" style="
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ccc;
                    border-radius: 4px;"></textarea>
                            </div>
                            <div style="margin-bottom: 10px;">
                                <label for="price" style="display: block; margin-bottom: 5px;">項目費用</label>
                                <input type="number" id="price" name="price" required style="
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ccc;
                    border-radius: 4px;">
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 15px;">
                                <button type="button" onclick="closeModal()" style="
                    padding: 8px 12px;
                    background: #ccc;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;">
                                    取消
                                </button>
                                <button type="submit" style="
                    padding: 8px 12px;
                    background: #00A896;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;">
                                    新增
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-novi table-custom-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>項目名稱</th>
                                <th>項目說明</th>
                                <th>項目費用</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                                        <td><?php echo htmlspecialchars($item['item']); ?></td>
                                        <td><?php echo wordwrap(htmlspecialchars($item['caption']), 10, "<br>", true); ?></td>
                                        <td><?php echo htmlspecialchars($item['price']); ?></td>
                                        <td>
                                            <!-- 修改按鈕 -->
                                            <button
                                                onclick="openEditModal(<?php echo $item['item_id']; ?>, '<?php echo htmlspecialchars($item['item']); ?>', '<?php echo htmlspecialchars($item['caption']); ?>', '<?php echo htmlspecialchars($item['price']); ?>')"
                                                style="
                                padding: 6px 12px;
                                font-size: 16px;
                                border: none;
                                background-color: #00A896;
                                color: white;
                                cursor: pointer;
                                border-radius: 4px;">
                                                修改
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #999;">未找到相關資料</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>


                <!-- 懸浮視窗 -->
                <div id="editModal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;">
                    <div style="
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        width: 400px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);">
                        <h3 style="margin-top: 0; text-align: center;">修改治療項目</h3>
                        <form id="editItemForm" method="POST" action="修改治療項目.php">
                            <!-- 隱藏欄位：用於記錄 item_id -->
                            <input type="hidden" id="editItemId" name="item_id">

                            <!-- 項目名稱 -->
                            <div style="margin-bottom: 10px;">
                                <label for="editItemName" style="display: block; margin-bottom: 5px;">項目名稱</label>
                                <input type="text" id="editItemName" name="item" required style="
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ccc;
                    border-radius: 4px;">
                            </div>

                            <!-- 項目說明 -->
                            <div style="margin-bottom: 10px;">
                                <label for="editCaption" style="display: block; margin-bottom: 5px;">項目說明</label>
                                <textarea id="editCaption" name="caption" rows="3" style="
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ccc;
                    border-radius: 4px;"></textarea>
                            </div>

                            <!-- 項目費用 -->
                            <div style="margin-bottom: 10px;">
                                <label for="editPrice" style="display: block; margin-bottom: 5px;">項目費用</label>
                                <input type="number" id="editPrice" name="price" required style="
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ccc;
                    border-radius: 4px;">
                            </div>

                            <!-- 按鈕 -->
                            <div style="display: flex; justify-content: space-between; margin-top: 15px;">
                                <button type="button" onclick="closeEditModal()" style="
                    padding: 8px 12px;
                    background: #ccc;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;">
                                    取消
                                </button>
                                <button type="submit" style="
                    padding: 8px 12px;
                    background: #00A896;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;">
                                    儲存
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    // 顯示修改視窗，並填充對應數據
                    function openEditModal(id, item, caption, price) {
                        document.getElementById('editModal').style.display = 'flex';
                        document.getElementById('editItemId').value = id;
                        document.getElementById('editItemName').value = item;
                        document.getElementById('editCaption').value = caption;
                        document.getElementById('editPrice').value = price;
                    }

                    // 隱藏修改視窗
                    function closeEditModal() {
                        document.getElementById('editModal').style.display = 'none';
                    }
                </script>


                <!-- 分頁顯示區域 -->
                <div id="pagination" style="text-align: center; margin-top: 10px; font-size: 14px; color: #333;">
                    <?php if ($totalPages > 1): ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <button
                                onclick="location.href='?page=<?php echo $i; ?>&rowsPerPage=<?php echo $rowsPerPage; ?>&search=<?php echo htmlspecialchars($search); ?>'"
                                style="margin: 0 5px; padding: 5px 10px; border: none; background-color: <?php echo $i == $page ? '#00A896' : '#f0f0f0'; ?>; color: <?php echo $i == $page ? 'white' : 'black'; ?>; border-radius: 4px; cursor: pointer;">
                                <?php echo $i; ?>
                            </button>
                        <?php endfor; ?>
                        <span>| 共 <?php echo $totalPages; ?> 頁</span>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>



    <!-- Global Mailform Output-->
    <div class="snackbars" id="form-output-global"></div>
    <!-- Javascript-->
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>