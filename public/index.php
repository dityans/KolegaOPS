<?php
// public/index.php
// Single Entry Point & Front Controller (Router) Aplikasi KolegaOps

// -----------------------------------------------------------------------------
// 1. Inisialisasi & Pemuatan Berkas Inti (Core Bootstrap)
// -----------------------------------------------------------------------------
define('BASE_PATH', dirname(__DIR__));

// Muat konfigurasi environment
$config = require_once BASE_PATH . '/config/config.php';

// Muat manajemen sesi & proteksi hak akses
require_once BASE_PATH . '/config/session.php';

// Muat koneksi database PostgreSQL (PDO)
$pdo = require_once BASE_PATH . '/config/database.php';

// Muat berkas pembantu (Security & Formatting)
require_once BASE_PATH . '/helpers/security_helper.php';
require_once BASE_PATH . '/helpers/format_helper.php';

// -----------------------------------------------------------------------------
// 2. Penangkap Parameter URL & Sanitasi
// -----------------------------------------------------------------------------
// Default tujuan bila parameter URL tidak disediakan:
// Jika sudah login, arahkan sesuai peran (role), jika belum login arahkan ke auth/login
$defaultController = 'auth';
$defaultAction     = 'login';

if (!isset($_GET['controller']) && !isset($_GET['action'])) {
    if (is_logged_in()) {
        $role = strtolower(current_user('role') ?? '');
        if ($role === 'kasir') {
            $defaultController = 'kasir';
            $defaultAction     = 'pos';
        } else {
            $defaultController = 'laporan';
            $defaultAction     = 'dashboard';
        }
    }
}

// Ambil input dari query parameter GET dan bersihkan dari karakter berbahaya (Path Traversal Protection)
$controllerRaw = $_GET['controller'] ?? $defaultController;
$actionRaw     = $_GET['action']     ?? $defaultAction;

$controllerParam = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $controllerRaw);
$actionParam     = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $actionRaw);

// Konversi nama controller ke format PascalCase + 'Controller'
// Mendukung format 'stok_bahan' maupun 'stokBahan' -> 'StokBahanController'
$camelConverted  = preg_replace('/(?<!^)[A-Z]/', '_$0', $controllerParam);
$controllerClass = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', strtolower($camelConverted)))) . 'Controller';
$controllerFile  = BASE_PATH . '/controllers/' . $controllerClass . '.php';

// -----------------------------------------------------------------------------
// 3. Pemuat Controller & Dispatcher Action
// -----------------------------------------------------------------------------
if (file_exists($controllerFile)) {
    require_once $controllerFile;

    if (class_exists($controllerClass)) {
        // Inisialisasi controller dengan menginjeksi instance PDO database
        $controllerInstance = new $controllerClass($pdo);

        // Periksa apakah method/action yang diminta tersedia dan dapat dipanggil
        if (method_exists($controllerInstance, $actionParam) && is_callable([$controllerInstance, $actionParam])) {
            // Jalankan action pada controller
            $controllerInstance->$actionParam();
            exit;
        } else {
            render_404_page("Aksi (Action) <code>" . e($actionParam) . "()</code> tidak ditemukan di dalam controller <code>" . e($controllerClass) . "</code>.");
        }
    } else {
        render_404_page("Kelas <code>" . e($controllerClass) . "</code> tidak didefinisikan di dalam berkas controller.");
    }
} else {
    render_404_page("Halaman atau Controller <code>" . e($controllerParam) . "</code> tidak ditemukan.");
}

// -----------------------------------------------------------------------------
// 4. Penanganan Error 404 (Not Found)
// -----------------------------------------------------------------------------
function render_404_page(string $pesan = 'Halaman yang Anda tuju tidak ditemukan.'): void
{
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Halaman Tidak Ditemukan | KolegaOps</title>
        <style>
            * { box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background-color: #f1f5f9;
                color: #334155;
                margin: 0;
                padding: 1.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
            }
            .error-card {
                background: #ffffff;
                max-width: 480px;
                width: 100%;
                padding: 2.5rem;
                border-radius: 14px;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
                text-align: center;
                border: 1px solid #e2e8f0;
            }
            .badge-code {
                display: inline-block;
                padding: 0.35rem 0.85rem;
                background-color: #fee2e2;
                color: #dc2626;
                font-weight: 700;
                font-size: 0.875rem;
                border-radius: 9999px;
                margin-bottom: 1rem;
            }
            h1 {
                font-size: 1.65rem;
                font-weight: 700;
                color: #0f172a;
                margin: 0 0 0.75rem;
            }
            p {
                font-size: 0.95rem;
                color: #64748b;
                line-height: 1.6;
                margin: 0 0 1.5rem;
            }
            code {
                background: #f8fafc;
                border: 1px solid #cbd5e1;
                padding: 0.15rem 0.4rem;
                border-radius: 4px;
                color: #0f172a;
                font-size: 0.9em;
            }
            .btn-home {
                display: inline-block;
                background-color: #0f172a;
                color: #ffffff;
                text-decoration: none;
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                font-size: 0.9rem;
                font-weight: 500;
                transition: background-color 0.2s ease;
            }
            .btn-home:hover {
                background-color: #1e293b;
            }
            .footer-note {
                margin-top: 1.5rem;
                font-size: 0.8rem;
                color: #94a3b8;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <span class="badge-code">Error 404</span>
            <h1>Halaman Tidak Ditemukan</h1>
            <p><?= $pesan ?></p>
            <a href="index.php" class="btn-home">Kembali ke Beranda</a>
            <div class="footer-note">KolegaOps POS Warkop &bull; Single Entry Point</div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
