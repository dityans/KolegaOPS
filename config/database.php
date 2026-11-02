<?php
// config/database.php

$config = require __DIR__ . '/config.php';

try {
    $dsn = "pgsql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']}";
    
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Sinkronisasi zona waktu session PostgreSQL ke Asia/Jakarta (WIB)
    $timezone = $config['app_timezone'] ?? 'Asia/Jakarta';
    $pdo->exec("SET timezone = '{$timezone}';");

    return $pdo;

} catch (PDOException $e) {
    http_response_code(500);
    die("Koneksi database gagal: " . $e->getMessage());
}
