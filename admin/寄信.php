<?php
require '../db.php';

function getAffectedAppointments($link, $leaves_id) {
    $stmt = $link->prepare("
        SELECT leaves.doctor_id, doctor.doctor, leaves.start_date, leaves.end_date 
        FROM leaves 
        JOIN doctor ON leaves.doctor_id = doctor.doctor_id
        WHERE leaves.leaves_id = ?
    ");
    $stmt->bind_param("i", $leaves_id);
    $stmt->execute();
    $leave = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $doctor_id = $leave['doctor_id'];
    $doctor_name = $leave['doctor'];
    $start_date = $leave['start_date'];
    $end_date = $leave['end_date'];

    $stmt = $link->prepare("
        SELECT people.name, people.email, doctorshift.date, shifttime.shifttime
        FROM appointment 
        JOIN people ON appointment.people_id = people.people_id
        JOIN doctorshift ON appointment.doctorshift_id = doctorshift.doctorshift_id
        JOIN shifttime ON appointment.shifttime_id = shifttime.shifttime_id
        WHERE doctorshift.doctor_id = ? 
        AND doctorshift.date BETWEEN ? AND ?
    ");
    $stmt->bind_param("iss", $doctor_id, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $appointments = [];
    while ($appointment = $result->fetch_assoc()) {
        $appointments[] = "姓名: " . $appointment['name'] . 
                          ", Email: " . $appointment['email'] . 
                          ", 預約時間: " . $appointment['date'] . " " . $appointment['shifttime'];
    }
    $stmt->close();

    return empty($appointments) ? null : implode("\n", $appointments);
}
?>
