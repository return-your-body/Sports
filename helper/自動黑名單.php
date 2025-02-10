<?php
require '../db.php';
$current_time = date('Y-m-d H:i:s');

$update_no_show = $link->prepare("
    UPDATE appointment a
    JOIN people p ON a.people_id = p.people_id
    SET a.status_id = (SELECT status_id FROM status WHERE status_name = '爽約'), 
        p.black = p.black + 1
    WHERE a.status_id NOT IN (SELECT status_id FROM status WHERE status_name = '報到')
    AND TIMESTAMPADD(MINUTE, 10, a.appointment_time) <= ?
");
$update_no_show->bind_param("s", $current_time);
$update_no_show->execute();
$update_no_show->close();

$link->close();
?>
