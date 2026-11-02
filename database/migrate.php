<?php
// database/migrate.php
// Skrip otomatis untuk membuat database & mengeksekusi schema.sql ke PostgreSQL

$config = require dirname(__DIR__) . '/config/config.php';

$host = $config['db_host'];
$port = $config['db_port'];
$user = $config['db_user'];
$pass = $config['db_pass'];
$dbname = $config['db_name'];

echo "=== KolegaOPS Database Migration Tool ===\n";
echo "Host: {$host}:{$port}\n";
echo "User: {$user}\n";
echo "Target DB: {$dbname}\n";
echo "----------------------------------------\n";

try {
    // 1. Hubungkan ke database default 'postgres' untuk memeriksa/membuat database target
    echo "1. Memeriksa koneksi ke server PostgreSQL...\n";
    $rootPdo = new PDO("pgsql:host={$host};port={$port};dbname=postgres", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $stmt = $rootPdo->prepare("SELECT 1 FROM pg_database WHERE datname = :dbname");
    $stmt->execute(['dbname' => $dbname]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        echo "   Database '{$dbname}' belum ada. Membuat database...\n";
        // CREATE DATABASE tidak bisa dijalankan di dalam blok transaksi terikat
        $rootPdo->exec("CREATE DATABASE \"{$dbname}\" ENCODING 'UTF8'");
        $rootPdo->exec("ALTER DATABASE \"{$dbname}\" SET timezone TO 'Asia/Jakarta'");
        echo "   [OK] Database '{$dbname}' berhasil dibuat dengan zona waktu Asia/Jakarta!\n";
    } else {
        $rootPdo->exec("ALTER DATABASE \"{$dbname}\" SET timezone TO 'Asia/Jakarta'");
        echo "   [OK] Database '{$dbname}' sudah ada (Zona waktu: Asia/Jakarta).\n";
    }

    // 2. Hubungkan ke database target
    echo "2. Menghubungkan ke database '{$dbname}'...\n";
    $dbPdo = new PDO("pgsql:host={$host};port={$port};dbname={$dbname}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 3. Baca dan eksekusi schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("File schema.sql tidak ditemukan di: {$schemaFile}");
    }

    echo "3. Mengeksekusi DDL & Seeder dari schema.sql...\n";
    $sql = file_get_contents($schemaFile);
    $dbPdo->exec($sql);
    echo "   [OK] Skema tabel dan data awal berhasil diimpor!\n";

    // 4. Verifikasi tabel yang berhasil dibuat
    echo "----------------------------------------\n";
    echo "Tabel yang tersedia di database:\n";
    $tables = $dbPdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name;
    ")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $t) {
        $count = $dbPdo->query("SELECT COUNT(*) FROM \"{$t}\"")->fetchColumn();
        echo " - {$t} ({$count} baris data)\n";
    }

    echo "----------------------------------------\n";
    echo "Akun Default Siap Pakai:\n";
    echo " 1. Owner : username = 'Owner' | password = 'admin123'\n";
    echo " 2. Kasir : username = 'kasir' | password = 'kasir123'\n";
    echo "=== Migrasi Berhasil Selesai! ===\n";

} catch (PDOException $e) {
    echo "\n[ERROR DATABASE]: " . $e->getMessage() . "\n";
    echo "\nPetunjuk:\n";
    echo "- Pastikan PostgreSQL sudah berjalan di komputer Anda.\n";
    echo "- Periksa username dan password di file .env (DB_USER, DB_PASS, DB_PORT).\n";
    exit(1);
} catch (Exception $e) {
    echo "\n[ERROR]: " . $e->getMessage() . "\n";
    exit(1);
}
