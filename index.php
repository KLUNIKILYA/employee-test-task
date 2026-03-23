<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'db.php';

$limit = 5; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT e.*, d.name as department_name, p.name as position_name 
        FROM employees e 
        LEFT JOIN departments d ON e.department_xml_id = d.xml_id 
        LEFT JOIN positions p ON e.work_position_id = p.id";

$countSql = "SELECT COUNT(*) FROM employees e 
             LEFT JOIN departments d ON e.department_xml_id = d.xml_id 
             LEFT JOIN positions p ON e.work_position_id = p.id";

$params = [];
if (!empty($search)) {
    $where = " WHERE e.last_name LIKE ? OR e.first_name LIKE ? OR d.name LIKE ? OR p.name LIKE ?";
    $sql .= $where;
    $countSql .= $where;
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam, $searchParam, $searchParam];
}

$sql .= " LIMIT $limit OFFSET $offset";

$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalRows = $stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Сотрудники (PHP)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2 class="mb-4">Управление сотрудниками</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'import_success'): ?>
        <div class="alert alert-success">Данные успешно импортированы!</div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-white"><strong>Импорт CSV файлов</strong></div>
        <div class="card-body">
            <form action="import.php" method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-muted small">Должности (Positions)</label>
                    <input type="file" name="positions" class="form-control form-control-sm" accept=".csv">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Отделы (Departments)</label>
                    <input type="file" name="departments" class="form-control form-control-sm" accept=".csv">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Сотрудники (Employees)</label>
                    <input type="file" name="employees" class="form-control form-control-sm" accept=".csv">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Загрузить в БД</button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between mb-3">
        <form method="GET" action="index.php" class="d-flex w-50">
            <input type="text" name="search" class="form-control me-2" placeholder="Поиск по ФИО, отделу, должности..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-secondary me-2">Найти</button>
            <a href="index.php" class="btn btn-link">Сбросить</a>
        </form>
        <div>
            <a href="export_csv.php?search=<?= urlencode($search) ?>" class="btn btn-success">Экспорт CSV</a>
            <a href="export_excel.php?search=<?= urlencode($search) ?>" class="btn btn-info text-white">Экспорт Excel</a>
        </div>
    </div>

    <table class="table table-bordered table-striped bg-white">
        <thead class="table-dark">
            <tr>
                <th>XML_ID</th>
                <th>ФИО</th>
                <th>Отдел</th>
                <th>Должность</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($employees) > 0): ?>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><?= htmlspecialchars($emp['xml_id']) ?></td>
                        <td><?= htmlspecialchars($emp['last_name'] . ' ' . $emp['first_name'] . ' ' . $emp['second_name']) ?></td>
                        <td><?= htmlspecialchars($emp['department_name']) ?></td>
                        <td><?= htmlspecialchars($emp['position_name']) ?></td>
                        <td><?= htmlspecialchars($emp['email']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">Нет данных</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
</body>
</html>