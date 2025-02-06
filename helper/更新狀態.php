<?php
require_once 'db_connect.php'; // 連接資料庫

header("Content-Type: application/json");

$response = ["status" => "error", "message" => "無效的請求"];

// 檢查是否為 POST 請求
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $id = $_POST["id"] ?? "";

    if (!$id || !in_array($action, ["modify", "report", "leave", "absence"])) {
        echo json_encode($response);
        exit;
    }

    if ($action === "modify") {
        // **修改日期和時間**
        $newDate = $_POST["date"] ?? "";
        $newTime = $_POST["time"] ?? "";

        if (!$newDate || !$newTime) {
            $response["message"] = "請輸入完整的日期與時間";
            echo json_encode($response);
            exit;
        }

        $stmt = $link->prepare("UPDATE appointments SET date = ?, time = ?, status = '修改' WHERE id = ?");
        $stmt->bind_param("ssi", $newDate, $newTime, $id);
        $stmt->execute();

        $response["status"] = "success";
        $response["message"] = "修改成功";
    } elseif ($action === "report") {
        $stmt = $link->prepare("UPDATE appointments SET status = '報到' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $response["status"] = "success";
        $response["message"] = "狀態已更新為報到！";
    } elseif ($action === "leave") {
        $stmt = $link->prepare("UPDATE appointments SET status = '請假' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $link->prepare("UPDATE people SET black = black + 0.5 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $response["status"] = "success";
        $response["message"] = "狀態已更新為請假，已記錄違規！";
    } elseif ($action === "absence") {
        $stmt = $link->prepare("UPDATE appointments SET status = '爽約' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $link->prepare("UPDATE people SET black = black + 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $response["status"] = "success";
        $response["message"] = "狀態已更新為爽約，已記錄違規！";
    }

    echo json_encode($response);
}
?>
