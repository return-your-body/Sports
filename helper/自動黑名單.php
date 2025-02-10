<?php
require 'db.php';
header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

$blackScore = ($action === "請假") ? 0.5 : (($action === "爽約") ? 1 : 0);
if ($blackScore > 0) {
    $stmt = $link->prepare("UPDATE people SET black = black + ? WHERE people_id = ?");
    $stmt->bind_param("di", $blackScore, $id);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(["status" => "success", "message" => "違規記錄已更新"]);
?>
