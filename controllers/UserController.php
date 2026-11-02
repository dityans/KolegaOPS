<?php
// controllers/UserController.php
// Controller Pengelolaan Akun Pegawai & Pengguna untuk KolegaOps POS Warkop

require_once BASE_PATH . '/models/UserModel.php';

class UserController
{
    private PDO $pdo;
    private UserModel $userModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new UserModel($pdo);
    }

    /**
     * Menampilkan halaman daftar akun pegawai & hak akses warkop
     */
    public function index(): void
    {
        // 1. Pengaman Hak Akses: Khusus Owner / Admin
        require_role(['owner', 'admin']);

        // 2. Ambil seluruh data akun dari database PostgreSQL
        $daftarPegawai = $this->userModel->getAllUsers();

        // 3. Hitung statistik agregat
        $totalAkun     = count($daftarPegawai);
        $totalAktif    = count(array_filter($daftarPegawai, fn($u) => ($u['status'] ?? '') === 'Aktif'));
        $totalNonaktif = count(array_filter($daftarPegawai, fn($u) => ($u['status'] ?? '') === 'Nonaktif'));

        $totalOwner = count(array_filter($daftarPegawai, fn($u) => ($u['role'] ?? '') === 'Owner'));
        $totalKasir = count(array_filter($daftarPegawai, fn($u) => ($u['role'] ?? '') === 'Pegawai'));

        // Nama-nama pengguna aktif untuk subteks kartu
        $namaAktifArr  = array_column(array_filter($daftarPegawai, fn($u) => ($u['status'] ?? '') === 'Aktif'), 'nama');
        $namaAktifTeks = !empty($namaAktifArr) ? implode(', ', array_slice($namaAktifArr, 0, 4)) : 'Belum ada akun aktif';

        // Tangkap notifikasi flash
        $pesanSukses = get_flash('success');
        $pesanError  = get_flash('error');

        // 4. Render tampilan View
        require_once BASE_PATH . '/views/owner/kelola-pegawai.php';
    }

    /**
     * Memproses penyimpanan akun pegawai (Tambah Baru atau Update Edit) dari modal form
     */
    public function simpan(): void
    {
        // 1. Pengaman Hak Akses: Khusus Owner / Admin
        require_role(['owner', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=pegawai&action=index');
            exit;
        }

        // 2. Verifikasi Token CSRF
        if (isset($_POST['csrf_token']) && function_exists('csrf_verify')) {
            csrf_verify($_POST['csrf_token']);
        }

        $idUser   = (int) ($_POST['id_user'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $role     = trim($_POST['role'] ?? 'Pegawai');
        $status   = trim($_POST['status'] ?? 'Aktif');
        $password = trim($_POST['password'] ?? '');

        // 3. Validasi Input
        if ($username === '') {
            set_flash('error', 'Username pegawai tidak boleh kosong!');
            header('Location: index.php?controller=pegawai&action=index');
            exit;
        }

        // Cek username duplikat
        $existing = $this->userModel->findByUsername($username);
        if ($existing && (int) $existing['id_user'] !== $idUser) {
            set_flash('error', "Username '{$username}' sudah digunakan oleh pengguna lain!");
            header('Location: index.php?controller=pegawai&action=index');
            exit;
        }

        // 4. Proses Simpan / Update
        if ($idUser > 0) {
            // MODE UPDATE
            $dataUpdate = [
                'username' => $username,
                'role'     => $role,
                'status'   => $status,
                'password' => $password, // kosong jika tidak ingin ubah password
            ];

            $sukses = $this->userModel->updateUser($idUser, $dataUpdate);

            if ($sukses) {
                set_flash('success', "Akun pegawai '{$username}' berhasil diperbarui!");
            } else {
                set_flash('error', "Gagal memperbarui data akun pegawai.");
            }
        } else {
            // MODE TAMBAH BARU
            if ($password === '') {
                set_flash('error', 'Password wajib diisi untuk pembuatan akun pegawai baru!');
                header('Location: index.php?controller=pegawai&action=index');
                exit;
            }

            if (strlen($password) < 4) {
                set_flash('error', 'Password akun minimal terdiri dari 4 karakter!');
                header('Location: index.php?controller=pegawai&action=index');
                exit;
            }

            $dataBaru = [
                'username' => $username,
                'password' => $password,
                'role'     => $role,
                'status'   => $status,
            ];

            $newId = $this->userModel->simpanUser($dataBaru);

            if ($newId) {
                set_flash('success', "Akun pegawai baru '{$username}' berhasil ditambahkan!");
            } else {
                set_flash('error', "Gagal menambahkan akun pegawai baru ke database.");
            }
        }

        header('Location: index.php?controller=pegawai&action=index');
        exit;
    }

    /**
     * Menghapus akun pegawai
     *
     * @param int|null $id
     */
    public function hapus($id = null): void
    {
        require_role(['owner', 'admin']);

        $id = (int) ($id ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            set_flash('error', 'ID akun tidak valid!');
            header('Location: index.php?controller=pegawai&action=index');
            exit;
        }

        // Cegah menghapus akun sendiri yang sedang login
        $currentUserId = (int) (current_user('id_user') ?? 0);
        if ($id === $currentUserId) {
            set_flash('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif!');
            header('Location: index.php?controller=pegawai&action=index');
            exit;
        }

        $user = $this->userModel->getUserById($id);
        $username = $user ? $user['username'] : 'Pegawai';

        $berhasil = $this->userModel->hapusUser($id);

        if ($berhasil) {
            set_flash('success', "Akun pegawai '{$username}' berhasil dihapus dari database.");
        } else {
            set_flash('error', "Gagal menghapus akun (akun ini memiliki riwayat transaksi atau shift). Sebaiknya ubah statusnya menjadi Nonaktif.");
        }

        header('Location: index.php?controller=pegawai&action=index');
        exit;
    }
}
