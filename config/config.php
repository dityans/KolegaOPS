<?php
// config/config.php
// Membaca variabel environment dari file .env bila ada, atau dari environment server

$envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || strpos($trimmed, '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Bersihkan kutip pembungkus
            $val = trim($val, "\"'");
            putenv("{$key}={$val}");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
}

// -----------------------------------------------------------------------------
// Pengaturan Zona Waktu Standar Aplikasi (WIB - Waktu Indonesia Barat)
// -----------------------------------------------------------------------------
$timezone = getenv('APP_TIMEZONE') ?: 'Asia/Jakarta';
date_default_timezone_set($timezone);

return [
    'app_name'     => getenv('APP_NAME')     ?: 'KolegaOPS',
    'app_url'      => getenv('APP_URL')      ?: 'http://localhost/KolegaOPS',
    'app_env'      => getenv('APP_ENV')      ?: 'development',
    'app_timezone' => $timezone,

    'db_host'      => getenv('DB_HOST')      ?: '127.0.0.1',
    'db_port'      => getenv('DB_PORT')      ?: '5432',
    'db_name'      => getenv('DB_NAME')      ?: 'kolegaops_db',
    'db_user'      => getenv('DB_USER')      ?: 'postgres',
    'db_pass'      => getenv('DB_PASS')      ?: 'postgres',
];
