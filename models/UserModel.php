<?php
// models/UserModel.php
// Model Pengguna (Users & Pegawai) untuk KolegaOps POS Warkop

class UserModel
{
    private PDO $pdo;

    /**
     * Inisialisasi Model dengan koneksi PDO PostgreSQL
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil data pengguna dari tabel users berdasarkan username (untuk login)
     *
     * @param string $username
     * @return array|null Mengembalikan array data pengguna lengkap atau null jika tidak ditemukan
     */
    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT id_user, username, password, role, status, create_at, update_at 
                FROM users 
                WHERE LOWER(username) = LOWER(:username) 
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':username' => trim($username)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Mengambil data pengguna berdasarkan ID Pengguna
     *
     * @param int $id_user
     * @return array|null
     */
    public function findById(int $id_user): ?array
    {
        $sql = "SELECT id_user, username, role, status, create_at, update_at 
                FROM users 
                WHERE id_user = :id_user 
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_user' => $id_user]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Alias untuk findById sesuai konvensi MVC
     *
     * @param int $id
     * @return array|null
     */
    public function getUserById(int $id): ?array
    {
        return $this->findById($id);
    }

    /**
     * Mengambil seluruh daftar pengguna (basic)
     *
     * @return array
     */
    public function getAll(): array
    {
        $sql = "SELECT id_user, username, role, status, create_at, update_at 
                FROM users 
                ORDER BY id_user ASC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil seluruh data akun pengguna/pegawai lengkap dengan info shift hari ini
     *
     * @return array
     */
    public function getAllUsers(): array
    {
        $sql = "
            SELECT 
                u.id_user AS id,
                u.username,
                INITCAP(u.username) AS nama,
                CASE 
                    WHEN LOWER(u.role) IN ('owner', 'admin') THEN 'Owner'
                    ELSE 'Pegawai'
                END AS role,
                u.role AS raw_role,
                CASE 
                    WHEN LOWER(u.status) = 'aktif' THEN 'Aktif'
                    ELSE 'Nonaktif'
                END AS status,
                u.status AS raw_status,
                u.create_at,
                u.update_at,
                CASE 
                    WHEN LOWER(u.role) IN ('owner', 'admin') THEN 'Pemilik'
                    WHEN s.id_shift IS NOT NULL AND s.status = 'buka' THEN s.nama_shift || ' · Aktif'
                    WHEN s.id_shift IS NOT NULL AND s.status = 'tutup' THEN s.nama_shift || ' · Ditutup'
                    ELSE '—'
                END AS shift
            FROM users u
            LEFT JOIN shift s ON u.id_user = s.id_user AND s.tanggal = CURRENT_DATE
            ORDER BY 
                CASE WHEN LOWER(u.role) IN ('owner', 'admin') THEN 0 ELSE 1 END,
                u.id_user ASC
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menyimpan data pegawai/pengguna baru ke dalam database PostgreSQL
     *
     * @param array $data Data user [username, password, role, status]
     * @return int|false Mengembalikan ID user baru jika berhasil, false jika gagal
     */
    public function simpanUser(array $data)
    {
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $role     = strtolower(trim($data['role'] ?? 'kasir'));
        $status   = strtolower(trim($data['status'] ?? 'aktif'));

        // Normalisasi role
        if ($role === 'pegawai') {
            $role = 'kasir';
        }

        if ($username === '' || $password === '') {
            return false;
        }

        // Enkripsi password menggunakan bcrypt
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $sql = "
                INSERT INTO users (username, password, role, status)
                VALUES (:username, :password, :role, :status)
                RETURNING id_user
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashedPassword,
                ':role'     => $role,
                ':status'   => $status,
            ]);

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('[UserModel::simpanUser] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Memperbarui data pegawai/pengguna (username, role, status, dan password jika diisi)
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser(int $id, array $data): bool
    {
        if ($id <= 0) {
            return false;
        }

        $username = trim($data['username'] ?? '');
        $role     = strtolower(trim($data['role'] ?? 'kasir'));
        $status   = strtolower(trim($data['status'] ?? 'aktif'));
        $password = trim($data['password'] ?? '');

        // Normalisasi role
        if ($role === 'pegawai') {
            $role = 'kasir';
        }

        if ($username === '') {
            return false;
        }

        try {
            // Jika ada perubahan password baru
            if ($password !== '') {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $sql = "
                    UPDATE users 
                    SET username = :username,
                        password = :password,
                        role = :role,
                        status = :status,
                        update_at = CURRENT_TIMESTAMP
                    WHERE id_user = :id
                ";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashedPassword,
                    ':role'     => $role,
                    ':status'   => $status,
                    ':id'       => $id,
                ]);
            } else {
                // Update tanpa mengubah password
                $sql = "
                    UPDATE users 
                    SET username = :username,
                        role = :role,
                        status = :status,
                        update_at = CURRENT_TIMESTAMP
                    WHERE id_user = :id
                ";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([
                    ':username' => $username,
                    ':role'     => $role,
                    ':status'   => $status,
                    ':id'       => $id,
                ]);
            }
        } catch (PDOException $e) {
            error_log('[UserModel::updateUser] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Menghapus akun pengguna dari database (jika tidak ada keterikatan transaksi/shift)
     *
     * @param int $id
     * @return bool
     */
    public function hapusUser(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        try {
            $sql = "DELETE FROM users WHERE id_user = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('[UserModel::hapusUser] ' . $e->getMessage());
            return false;
        }
    }
}
