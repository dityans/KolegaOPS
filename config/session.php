<?php
// config/session.php
// Manajemen Sesi Login & Kontrol Akses (Authorization) KolegaOps

// -----------------------------------------------------------------------------
// 1. Inisialisasi Sesi Secara Aman
// -----------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    // Pastikan session save path valid dan writable
    $savePath = session_save_path();
    if (empty($savePath) || !is_writable($savePath)) {
        $sysTemp = sys_get_temp_dir();
        if (is_writable($sysTemp)) {
            session_save_path($sysTemp);
        }
    }

    // Deteksi HTTPS
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    // Pengaturan cookie sesi yang aman
    @session_start([
        'cookie_httponly' => true,      // Mencegah akses cookie via JavaScript (Mitigasi XSS)
        'cookie_secure'   => $isHttps,   // Cookie hanya via HTTPS jika server mendukung
        'cookie_samesite' => 'Lax',     // Mencegah CSRF lintas situs
        'use_strict_mode' => 1,         // Mencegah session fixation dari ID yang tidak diinisialisasi
    ]);
}

// -----------------------------------------------------------------------------
// 2. Fungsi Login: set_user_session
// -----------------------------------------------------------------------------
if (!function_exists('set_user_session')) {
    /**
     * Menyimpan data login pengguna ke dalam sesi secara aman.
     * Meregenerasi ID sesi untuk mencegah serangan Session Fixation.
     *
     * @param array $user Data pengguna dari database (minimal: id_user, username, role)
     */
    function set_user_session(array $user): void
    {
        // Regenerasi ID sesi untuk mencegah Session Fixation bila sesi aktif
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['user'] = [
            'id_user'   => (int) ($user['id_user'] ?? $user['id'] ?? 0),
            'username'  => (string) ($user['username'] ?? ''),
            'role'      => strtolower(trim($user['role'] ?? '')),
        ];

        // Shortcut langsung untuk kemudahan akses
        $_SESSION['id_user']  = $_SESSION['user']['id_user'];
        $_SESSION['username'] = $_SESSION['user']['username'];
        $_SESSION['role']     = $_SESSION['user']['role'];
    }
}

// -----------------------------------------------------------------------------
// 3. Fungsi Cek Login: is_logged_in & current_user
// -----------------------------------------------------------------------------
if (!function_exists('is_logged_in')) {
    /**
     * Memeriksa apakah pengguna saat ini sedang dalam kondisi login aktif.
     *
     * @return bool True jika sudah login, False jika belum
     */
    function is_logged_in(): bool
    {
        return !empty($_SESSION['user']['id_user']) || !empty($_SESSION['id_user']);
    }
}

if (!function_exists('current_user')) {
    /**
     * Mengambil data pengguna yang sedang login saat ini.
     *
     * @param string|null $key Atribut tertentu (misal: 'id_user', 'username', 'role')
     * @return mixed Data user array lengkap, nilai atribut tertentu, atau null
     */
    function current_user(?string $key = null)
    {
        if (!is_logged_in()) {
            return null;
        }

        $user = $_SESSION['user'] ?? [
            'id_user'  => $_SESSION['id_user'] ?? null,
            'username' => $_SESSION['username'] ?? '',
            'role'     => $_SESSION['role'] ?? '',
        ];

        if ($key !== null) {
            return $user[$key] ?? null;
        }

        return $user;
    }
}

// -----------------------------------------------------------------------------
// 4. Fungsi Pembatas Hak Akses: require_login & require_role
// -----------------------------------------------------------------------------
if (!function_exists('require_login')) {
    /**
     * Memastikan pengguna sudah login sebelum mengakses halaman.
     * Jika belum, arahkan langsung ke halaman login.
     *
     * @param string $loginUrl URL halaman login
     */
    function require_login(string $loginUrl = 'index.php?controller=auth&action=login'): void
    {
        if (!is_logged_in()) {
            header("Location: {$loginUrl}");
            exit;
        }
    }
}

if (!function_exists('require_role')) {
    /**
     * Membatasi akses halaman hanya untuk peran (role) tertentu.
     * Contoh penggunaan:
     *   require_role('admin');                     // Khusus admin
     *   require_role(['admin', 'owner']);          // Khusus admin atau owner
     *   require_role(['kasir', 'admin', 'owner']); // Kasir dan admin
     *
     * @param string|array $allowed_roles Role atau daftar role yang diizinkan
     * @param string|null $redirectUrl Opsional: URL tujuan redirect jika akses ditolak
     */
    function require_role($allowed_roles, ?string $redirectUrl = null): void
    {
        // Pastikan pengguna sudah login terlebih dahulu
        require_login();

        $allowed = is_array($allowed_roles) ? $allowed_roles : [$allowed_roles];
        $allowed = array_map(function ($r) {
            return strtolower(trim((string) $r));
        }, $allowed);

        $userRole = strtolower(trim((string) current_user('role')));

        if (!in_array($userRole, $allowed, true)) {
            if ($redirectUrl !== null) {
                header("Location: {$redirectUrl}");
                exit;
            }
            http_response_code(403);
            exit;
        }
    }
}

// -----------------------------------------------------------------------------
// 5. Fungsi Logout: destroy_session
// -----------------------------------------------------------------------------
if (!function_exists('destroy_session')) {
    /**
     * Menghapus seluruh sesi pengguna dan menghentikan sesi aktif.
     */
    function destroy_session(): void
    {
        // 1. Kosongkan array sesi
        $_SESSION = [];

        // 2. Hapus cookie sesi dari browser jika digunakan
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // 3. Hancurkan sesi di server
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}

// -----------------------------------------------------------------------------
// 6. Helper Tambahan: Flash Message (Notifikasi satu kali antar-halaman)
// -----------------------------------------------------------------------------
if (!function_exists('set_flash')) {
    function set_flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }
}

if (!function_exists('get_flash')) {
    function get_flash(string $key): ?string
    {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }
}
