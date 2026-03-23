<?php
require_once 'db.php';

function parseCSV($file) {
    $data = [];
    if (($handle = fopen($file, "r")) !== FALSE) {
        
        $firstLine = fgets($handle);
        $delimiter = strpos($firstLine, ';') !== false ? ';' : ',';
        rewind($handle); 

        $header = fgetcsv($handle, 1000, $delimiter, "\"", "\\");
        
        if (!$header) return [];

        $header = array_map(function($val) { 
            $val = preg_replace('/^\xEF\xBB\xBF/', '', $val); 
            return trim(mb_convert_encoding($val, 'UTF-8', 'Windows-1251')); 
        }, $header);

        while (($row = fgetcsv($handle, 1000, $delimiter, "\"", "\\")) !== FALSE) {
            if (array_filter($row) === []) continue;

            $row = array_map(function($val) { 
                return trim(mb_convert_encoding($val, 'UTF-8', 'Windows-1251')); 
            }, $row);
            
            if (count($header) === count($row)) {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        if (!empty($_FILES['positions']['tmp_name'])) {
            $positions = parseCSV($_FILES['positions']['tmp_name']);
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO positions (id, name) VALUES (?, ?)");
            foreach ($positions as $p) {
                if (isset($p['ID'])) {
                    $stmt->execute([(int)$p['ID'], $p['NAME'] ?? '']);
                }
            }
        }

        if (!empty($_FILES['departments']['tmp_name'])) {
            $departments = parseCSV($_FILES['departments']['tmp_name']);
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO departments (xml_id, parent_xml_id, name) VALUES (?, ?, ?)");
            foreach ($departments as $d) {
                if (isset($d['XML_ID'])) {
                    $parent = !empty($d['PARENT_XML_ID']) ? $d['PARENT_XML_ID'] : null;
                    $stmt->execute([$d['XML_ID'], $parent, $d['NAME_DEPARTMENT'] ?? '']);
                }
            }
        }

        if (!empty($_FILES['employees']['tmp_name'])) {
            $employees = parseCSV($_FILES['employees']['tmp_name']);
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO employees (xml_id, last_name, first_name, second_name, department_xml_id, work_position_id, email, mobile_phone, login, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($employees as $e) {
                if (isset($e['XML_ID'], $e['DEPARTMENT'], $e['WORK_POSITION'])) {
                    $stmt->execute([
                        $e['XML_ID'], 
                        $e['LAST_NAME'] ?? '', 
                        $e['NAME'] ?? '', 
                        $e['SECOND_NAME'] ?? null,
                        $e['DEPARTMENT'], 
                        (int)$e['WORK_POSITION'],
                        $e['EMAIL'] ?? null, 
                        $e['MOBILE_PHONE'] ?? null, 
                        $e['LOGIN'] ?? null, 
                        $e['PASSWORD'] ?? null
                    ]);
                }
            }
        }

        $pdo->commit();
        header("Location: index.php?msg=import_success");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Ошибка импорта: " . $e->getMessage());
    }
}
?>