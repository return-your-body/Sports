<?php
require '../db.php';

$record_id = $_POST['record_id'];
$status_id = $_POST['status_id'];

switch ($status_id) {
    case 2: // 修改
        $new_date = $_POST['new_date'];
        $new_time = $_POST['new_time'];
        $doctor_id = $_POST['doctor_id']; // 確保 doctor_id 有值

        $stmt = $link->prepare("UPDATE appointment_records SET date=?, time=?, doctor_id=? WHERE record_id=?");
        $stmt->bind_param("ssii", $new_date, $new_time, $doctor_id, $record_id);
        $stmt->execute();
        echo "修改成功！";
        break;

    case 3: // 報到
        $stmt = $link->prepare("UPDATE appointment_records SET status_id=3 WHERE record_id=?");
        $stmt->bind_param("i", $record_id);
        $stmt->execute();
        echo "狀態已更新為報到！";
        break;

    case 4: // 請假
        $stmt = $link->prepare("UPDATE appointment_records SET status_id=4 WHERE record_id=?");
        $stmt->bind_param("i", $record_id);
        $stmt->execute();

        $link->query("UPDATE people SET black=black+0.5 WHERE person_id=(SELECT person_id FROM appointment_records WHERE record_id='$record_id')");
        echo "狀態已更新為請假，已記錄違規！";
        break;

    case 5: // 爽約
        $stmt = $link->prepare("UPDATE appointment_records SET status_id=5 WHERE record_id=?");
        $stmt->bind_param("i", $record_id);
        $stmt->execute();

        $link->query("UPDATE people SET black=black+1 WHERE person_id=(SELECT person_id FROM appointment_records WHERE record_id='$record_id')");
        echo "狀態已更新為爽約，已記錄違規！";
        break;

    case 8: // 看診中
        $stmt = $link->prepare("UPDATE appointment_records SET status_id=8 WHERE record_id=?");
        $stmt->bind_param("i", $record_id);
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
