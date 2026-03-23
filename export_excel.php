<?php
require_once 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT e.xml_id, e.last_name, e.first_name, d.name as dep_name, p.name as pos_name, e.email 
        FROM employees e 
        LEFT JOIN departments d ON e.department_xml_id = d.xml_id 
        LEFT JOIN positions p ON e.work_position_id = p.id";

$params = [];
if (!empty($search)) {
    $sql .= " WHERE e.last_name LIKE ? OR e.first_name LIKE ? OR d.name LIKE ? OR p.name LIKE ?";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'XML_ID');
$sheet->setCellValue('B1', 'Фамилия');
$sheet->setCellValue('C1', 'Имя');
$sheet->setCellValue('D1', 'Отдел');
$sheet->setCellValue('E1', 'Должность');
$sheet->setCellValue('F1', 'Email');
$sheet->getStyle('A1:F1')->getFont()->setBold(true);

$rowNum = 2;
foreach ($data as $row) {
    $sheet->setCellValue('A' . $rowNum, $row['xml_id']);
    $sheet->setCellValue('B' . $rowNum, $row['last_name']);
    $sheet->setCellValue('C' . $rowNum, $row['first_name']);
    $sheet->setCellValue('D' . $rowNum, $row['dep_name']);
    $sheet->setCellValue('E' . $rowNum, $row['pos_name']);
    $sheet->setCellValue('F' . $rowNum, $row['email']);
    $rowNum++;
}

foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="employees_export.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;