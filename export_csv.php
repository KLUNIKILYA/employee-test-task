<?php
ini_set('display_errors', 0); 
error_reporting(0);

require_once 'db.php';

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

header('Content-Type: text/csv; charset=windows-1251'); 
header('Content-Disposition: attachment; filename=employees_export.csv');

$output = fopen('php://output', 'w');

$headers = ['XML_ID', 'Фамилия', 'Имя', 'Отдел', 'Должность', 'Email'];
$headersEncoded = array_map(function($v) { 
    return mb_convert_encoding($v, 'Windows-1251', 'UTF-8'); 
}, $headers);

fputcsv($output, $headersEncoded, ';', '"', '\\');

foreach ($data as $row) {
    $csvRow = array_map(function($v) { 
        $val = $v !== null ? $v : '';
        return mb_convert_encoding($val, 'Windows-1251', 'UTF-8'); 
    }, $row);
    
    fputcsv($output, $csvRow, ';', '"', '\\');
}

fclose($output);
exit;