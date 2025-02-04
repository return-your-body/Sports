<?php
require '../db.php';

$id = $_POST['id'];
$status = $_POST['status'];

if ($status == '修改') {
    $date = $_POST['date'];
    $time = $_POST['time'];

    $stmt = $link->prepare("UPDATE appointment SET date = ?, shifttime_id = ?, status_id = (SELECT status_id FROM status WHERE status_name = '修改') WHERE appointment_id = ?");
    $stmt->bind_param("sii", $date, $time, $id);
} else {
    $stmt = $link->prepare("UPDATE appointment SET status_id = (SELECT status_id FROM status WHERE status_name = ?) WHERE appointment_id = ?");
    $stmt->bind_param("si", $status, $id);
}

$stmt->execute();
$stmt->close();

// **處理黑名單邏輯**
if ($status == '請假' || $status == '爽約') {
    $people_id_stmt = $link->prepare("SELECT people_id FROM appointment WHERE appointment_id = ?");
    $people_id_stmt->bind_param("i", $id);
    $people_id_stmt->execute();
    $result = $people_id_stmt->get_result();
    $people_id = $result->fetch_assoc()['people_id'];

    $points = ($status == '請假') ? 0.5 : 1;

    $update_violation_stmt = $link->prepare("UPDATE people SET black = black + ? WHERE people_id = ?");
    $update_violation_stmt->bind_param("di", $points, $people_id);
    $update_violation_stmt->execute();

    $check_blacklist_stmt = $link->prepare("SELECT black FROM people WHERE people_id = ?");
    $check_blacklist_stmt->bind_param("i", $people_id);
    $check_blacklist_stmt->execute();
    $black_count = $check_blacklist_stmt->get_result()->fetch_assoc()['black'];

    if ($black_count >= 3) {
        $blacklist_stmt = $link->prepare("INSERT INTO blacklist (people_id, black_id, violation_date) VALUES (?, 3, NOW())");
        $blacklist_stmt->bind_param("i", $people_id);
        $blacklist_stmt->execute();
    }
}

echo "更新成功";
?>
