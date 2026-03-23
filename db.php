<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/app.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$pdo->exec("
    CREATE TABLE IF NOT EXISTS positions (
        id INTEGER PRIMARY KEY,
        name TEXT NOT NULL
    );
    
    CREATE TABLE IF NOT EXISTS departments (
        xml_id TEXT PRIMARY KEY,
        parent_xml_id TEXT,
        name TEXT NOT NULL
    );
    
    CREATE TABLE IF NOT EXISTS employees (
        xml_id TEXT PRIMARY KEY,
        last_name TEXT NOT NULL,
        first_name TEXT NOT NULL,
        second_name TEXT,
        department_xml_id TEXT NOT NULL,
        work_position_id INTEGER NOT NULL,
        email TEXT,
        mobile_phone TEXT,
        login TEXT,
        password TEXT
    );
");
?>