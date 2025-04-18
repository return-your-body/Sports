<?php
require '../db.php';
require_once('fpdf/fpdf.php');

header('Content-Type: application/json; charset=utf-8');

$chart = $_GET['chart'] ?? '';
$type = $_GET['type'] ?? '';
$year = $_GET['year'] ?? '';
$month = $_GET['month'] ?? '';
$day = $_GET['day'] ?? '';
$doctor_id = $_GET['doctor_id'] ?? '';

$start = "$year-$month-$day 00:00:00";
$end = "$year-$month-$day 23:59:59";
$whereDoctor = $doctor_id ? "AND d.doctor_id = '$doctor_id'" : "";

function outputError($msg) {
  echo json_encode(['error' => $msg]);
  exit();
}

switch ($chart) {

  // ✅ 總工作時數（attendance）
  case '總工作時數':
    $sql = "
      SELECT d.doctor AS doctor_name, 
             ROUND(SUM(TIMESTAMPDIFF(SECOND, a.clock_in, a.clock_out) / 3600), 2) AS total_hours
      FROM attendance a
      JOIN doctor d ON a.doctor_id = d.doctor_id
      WHERE a.clock_in IS NOT NULL AND a.clock_out IS NOT NULL
        AND a.clock_in BETWEEN '$start' AND '$end'
        $whereDoctor
      GROUP BY a.doctor_id
    ";
    break;

  // ✅ 項目數比例（medicalrecord）
  case '項目數比例':
    $sql = "
      SELECT d.doctor AS doctor_name, COUNT(m.item_id) AS item_count
      FROM medicalrecord m
      JOIN appointment a ON m.appointment_id = a.appointment_id
      JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
      JOIN doctor d ON ds.doctor_id = d.doctor_id
      WHERE m.created_at BETWEEN '$start' AND '$end'
        $whereDoctor
      GROUP BY d.doctor_id
    ";
    break;

  // ✅ 預約人數統計（appointment）
  case '治療師預約人數比例':
    $sql = "
      SELECT d.doctor AS doctor_name, COUNT(*) AS total_appointments
      FROM appointment a
      JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
      JOIN doctor d ON ds.doctor_id = d.doctor_id
      WHERE a.created_at BETWEEN '$start' AND '$end'
        $whereDoctor
      GROUP BY d.doctor_id
    ";
    break;

  // ✅ 收入統計（medicalrecord + item）
  case '收入統計':
    $sql = "
      SELECT d.doctor AS doctor_name, SUM(i.price) AS total_revenue
      FROM medicalrecord m
      JOIN item i ON m.item_id = i.item_id
      JOIN appointment a ON m.appointment_id = a.appointment_id
      JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
      JOIN doctor d ON ds.doctor_id = d.doctor_id
      WHERE m.created_at BETWEEN '$start' AND '$end'
        $whereDoctor
      GROUP BY d.doctor_id
    ";
    break;

  // ✅ 詳細資料表（點擊圓餅圖後）
  case '詳細數據':
    $item = $_GET['item'] ?? '';
    $sql = "
      SELECT d.doctor AS doctor_name, i.item AS category, COUNT(*) AS value
      FROM medicalrecord m
      JOIN item i ON m.item_id = i.item_id
      JOIN appointment a ON m.appointment_id = a.appointment_id
      JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
      JOIN doctor d ON ds.doctor_id = d.doctor_id
      WHERE m.created_at BETWEEN '$start' AND '$end'
        AND i.item = '$item'
        $whereDoctor
      GROUP BY d.doctor_id, i.item
    ";
    break;

  // ✅ 匯出功能（excel 或 pdf）
  case '匯出':
    header_remove("Content-Type");

    if ($type === 'excel') {
      header("Content-Type: application/vnd.ms-excel");
      header("Content-Disposition: attachment; filename=statistics.xls");
    } else if ($type === 'pdf') {
      require_once('fpdf/fpdf.php');
      $pdf = new FPDF();
      $pdf->AddPage();
      $pdf->SetFont('Arial', 'B', 14);
      $pdf->Cell(0, 10, "統計報表", 1, 1, 'C');
      $pdf->SetFont('Arial', '', 12);
    }

    // 匯出資料查詢（收入統計為例）
    $sql = "
      SELECT d.doctor AS doctor_name, i.item, i.price, COUNT(*) AS count, SUM(i.price) AS total
      FROM medicalrecord m
      JOIN item i ON m.item_id = i.item_id
      JOIN appointment a ON m.appointment_id = a.appointment_id
      JOIN doctorshift ds ON a.doctorshift_id = ds.doctorshift_id
      JOIN doctor d ON ds.doctor_id = d.doctor_id
      WHERE m.created_at BETWEEN '$start' AND '$end'
        $whereDoctor
      GROUP BY d.doctor, i.item
    ";

    $res = mysqli_query($link, $sql);
    if ($type === 'excel') {
      echo "<table border='1'><tr><th>治療師</th><th>項目</th><th>單價</th><th>次數</th><th>總收入</th></tr>";
      while ($row = mysqli_fetch_assoc($res)) {
        echo "<tr><td>{$row['doctor_name']}</td><td>{$row['item']}</td><td>{$row['price']}</td><td>{$row['count']}</td><td>{$row['total']}</td></tr>";
      }
      echo "</table>";
    } else if ($type === 'pdf') {
      $pdf->Cell(40, 10, "治療師", 1);
      $pdf->Cell(30, 10, "項目", 1);
      $pdf->Cell(20, 10, "單價", 1);
      $pdf->Cell(20, 10, "次數", 1);
      $pdf->Cell(30, 10, "總收入", 1);
      $pdf->Ln();
      while ($row = mysqli_fetch_assoc($res)) {
        $pdf->Cell(40, 10, $row['doctor_name'], 1);
        $pdf->Cell(30, 10, $row['item'], 1);
        $pdf->Cell(20, 10, $row['price'], 1);
        $pdf->Cell(20, 10, $row['count'], 1);
        $pdf->Cell(30, 10, $row['total'], 1);
        $pdf->Ln();
      }
      $pdf->Output();
    }
    exit();

  default:
    outputError("無效的統計圖表類型");
}

$result = mysqli_query($link, $sql);
if (!$result) outputError("資料庫查詢錯誤");

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
  $data[] = $row;
}
echo json_encode($data);
?>