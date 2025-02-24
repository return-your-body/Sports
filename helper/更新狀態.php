<?php
require '../db.php';

$appointment_id = $_POST['record_id'];  // 前端傳來的值為 appointment_id
$status_id = $_POST['status_id'];

switch ($status_id) {
    case 3: // 報到
        $stmt = $link->prepare("UPDATE appointment SET status_id=3 WHERE appointment_id=?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        echo "狀態已更新為報到！";
        break;

    case 4: // 請假
        $stmt = $link->prepare("UPDATE appointment SET status_id=4 WHERE appointment_id=?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();

        $link->query("UPDATE people SET black=black+0.5 WHERE people_id=(SELECT people_id FROM appointment WHERE appointment_id='$appointment_id')");
        echo "狀態已更新為請假，已記錄違規！";
        break;

    case 5: // 爽約
        $stmt = $link->prepare("UPDATE appointment SET status_id=5 WHERE appointment_id=?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();

        $link->query("UPDATE people SET black=black+1 WHERE people_id=(SELECT people_id FROM appointment WHERE appointment_id='$appointment_id')");
        echo "狀態已更新為爽約，已記錄違規！";
        break;

    case 8: // 看診中
        $stmt = $link->prepare("UPDATE appointment SET status_id=8 WHERE appointment_id=?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        echo "狀態已更新為看診中！";
        break;

    case 6: // 已看診
        echo "此預約已完成看診，無法變更狀態！";
        break;

    default:
        echo "無效操作！";
}

$link->close();

?>