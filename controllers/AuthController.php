<?php
// controllers/AuthController.php
// Controller untuk mengelola autentikasi (Login & Logout) KolegaOps

require_once BASE_PATH . '/models/UserModel.php';

class AuthController
{
    private PDO $pdo;
    private UserModel $userModel;

    /**
     * Inisialisasi controller dengan menginjeksi instance PDO database
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new UserModel($pdo);
    }

    /**
     * Menampilkan formulir login.
     * Jika pengguna sudah login, langsung diarahkan ke halaman dashboard/POS.
     */
    public function login(): void
    {
        if (is_logged_in()) {
            $this->redirectByRole(current_user('role'));
            return;
        }

        $error = get_flash('error') ?? '';
        require_once BASE_PATH . '/views/auth/login.php';
    }

    /**
     * Memproses data POST dari form login dan memverifikasi ke database
     */
    public function process(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        // Verifikasi token CSRF jika tersedia
        if (isset($_POST['csrf_token']) && function_exists('csrf_verify')) {
            csrf_verify($_POST['csrf_token']);
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validasi input tidak boleh kosong
        if ($username === '' || $password === '') {
            $error = 'Harap isi username dan password!';
            require_once BASE_PATH . '/views/auth/login.php';
            return;
        }

        try {
            // Ambil data pengguna dari database melalui UserModel
            $user = $this->userModel->findByUsername($username);

            // Validasi keberadaan akun, status keaktifan, dan kecocokan hash password
            if ($user) {
                // Periksa apakah akun dinonaktifkan
                if (isset($user['status']) && strtolower($user['status']) === 'nonaktif') {
                    $error = 'Akun Anda dinonaktifkan. Silakan hubungi owner warkop.';
                    require_once BASE_PATH . '/views/auth/login.php';
                    return;
                }

                // Verifikasi password hash menggunakan password_verify bawaan PHP
                if (password_verify($password, $user['password'])) {
                    // Simpan data login ke session secara aman
                    set_user_session($user);

                    // Arahkan ke halaman kerja sesuai role
                    $this->redirectByRole($user['role']);
                    return;
                }
            }

            // Jika username tidak ditemukan atau password salah
            $error = 'Username atau password salah!';
            require_once BASE_PATH . '/views/auth/login.php';
            return;

        } catch (PDOException $e) {
            $error = 'Terjadi kendala pada sistem database: ' . $e->getMessage();
            require_once BASE_PATH . '/views/auth/login.php';
            return;
        }
    }

    /**
     * Memproses logout pengguna
     */
    public function logout(): void
    {
        destroy_session();
        header('Location: index.php?controller=auth&action=login');
        exit;
    }

    /**
     * Pengalihan halaman (redirection) berdasarkan peran (role) pengguna:
     * - 'owner' / 'admin' -> Dashboard Owner
     * - 'kasir'           -> Halaman POS Kasir
     *
     * @param string|null $role
     */
    private function redirectByRole(?string $role): void
    {
        $roleClean = strtolower(trim((string) $role));

        if ($roleClean === 'owner' || $roleClean === 'admin') {
            header('Location: index.php?controller=laporan&action=dashboard');
        } else {
            // Role kasir diarahkan langsung ke halaman operasional POS
            header('Location: index.php?controller=kasir&action=pos');
        }
        exit;
    }
}
