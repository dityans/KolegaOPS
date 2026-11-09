<?php
// models/PengeluaranModel.php
// Model Pengelolaan Kas Pengeluaran Operasional Shift untuk KolegaOps POS Warkop

class PengeluaranModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil daftar rincian pengeluaran operasional berdasarkan tanggal dan shift
     *
     * @param string $tanggal Format YYYY-MM-DD atau 'Semua'
     * @param string $shift Nama shift / kasir atau 'Semua'
     * @return array
     */
    public function getPengeluaranByTanggal(string $tanggal = 'Semua', string $shift = 'Semua'): array
    {
        $conditions = [];
        $params     = [];

        if ($tanggal !== 'Semua' && !empty($tanggal)) {
            $conditions[] = "DATE(ps.create_at) = :tanggal";
            $params[':tanggal'] = $tanggal;
        }

        if ($shift !== 'Semua' && !empty($shift)) {
            $conditions[] = "(s.nama_shift ILIKE :shift OR u.username ILIKE :shift)";
            $params[':shift'] = '%' . $shift . '%';
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "
            SELECT 
                ps.id_pengeluaran,
                ps.keterangan,
                ps.nominal,
                ps.waktu_catat,
                ps.create_at,
                COALESCE(s.nama_shift, 'Umum') AS nama_shift,
                COALESCE(u.username, 'Kasir') AS nama_kasir
            FROM pengeluaran_shift ps
            LEFT JOIN shift s ON ps.id_shift = s.id_shift
            LEFT JOIN users u ON s.id_user = u.id_user
            {$whereClause}
            ORDER BY ps.create_at ASC, ps.id_pengeluaran ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil daftar pengeluaran khusus pada shift tertentu
     *
     * @param int $id_shift
     * @return array
     */
    public function getPengeluaranByShift(int $id_shift): array
    {
        if ($id_shift <= 0) {
            return [];
        }

        $sql = "
            SELECT 
                id_pengeluaran,
                keterangan,
                nominal,
                waktu_catat,
                create_at
            FROM pengeluaran_shift
            WHERE id_shift = :id_shift
            ORDER BY create_at DESC, id_pengeluaran DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_shift' => $id_shift]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menghitung total pengeluaran khusus pada shift tertentu
     *
     * @param int $id_shift
     * @return float
     */
    public function getTotalPengeluaranByShift(int $id_shift): float
    {
        if ($id_shift <= 0) {
            return 0.0;
        }

        $sql = "
            SELECT COALESCE(SUM(nominal), 0)
            FROM pengeluaran_shift
            WHERE id_shift = :id_shift
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_shift' => $id_shift]);

        return (float) $stmt->fetchColumn();
    }

    /**
     * Menghitung total nominal pengeluaran berdasarkan tanggal dan shift
     *
     * @param string $tanggal
     * @param string $shift
     * @return float
     */
    public function getTotalPengeluaranByTanggal(string $tanggal = 'Semua', string $shift = 'Semua'): float
    {
        $conditions = [];
        $params     = [];

        if ($tanggal !== 'Semua' && !empty($tanggal)) {
            $conditions[] = "DATE(ps.create_at) = :tanggal";
            $params[':tanggal'] = $tanggal;
        }

        if ($shift !== 'Semua' && !empty($shift)) {
            $conditions[] = "(s.nama_shift ILIKE :shift OR u.username ILIKE :shift)";
            $params[':shift'] = '%' . $shift . '%';
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "
            SELECT COALESCE(SUM(ps.nominal), 0)
            FROM pengeluaran_shift ps
            LEFT JOIN shift s ON ps.id_shift = s.id_shift
            LEFT JOIN users u ON s.id_user = u.id_user
            {$whereClause}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (float) $stmt->fetchColumn();
    }

    /**
     * Mencatat kas pengeluaran operasional baru untuk sesi shift kasir
     *
     * @param int $id_shift
     * @param string $keterangan
     * @param float $nominal
     * @return bool
     */
    public function tambahPengeluaran(int $id_shift, string $keterangan, float $nominal): bool
    {
        if ($nominal <= 0 || trim($keterangan) === '') {
            return false;
        }

        try {
            $sql = "
                INSERT INTO pengeluaran_shift (id_shift, keterangan, nominal, waktu_catat)
                VALUES (:id_shift, :keterangan, :nominal, CURRENT_TIMESTAMP)
            ";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id_shift'   => $id_shift,
                ':keterangan' => trim($keterangan),
                ':nominal'    => $nominal,
            ]);
        } catch (PDOException $e) {
            error_log('[PengeluaranModel::tambahPengeluaran] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil daftar pengeluaran operasional yang dicatat khusus pada shift aktif kasir hari ini
     *
     * @param string $tanggal Format YYYY-MM-DD
     * @param string $namaKasir Username / nama kasir
     * @return array
     */
    public function getPengeluaranShiftAktif(string $tanggal, string $namaKasir): array
    {
        $sql = "
            SELECT 
                ps.id_pengeluaran,
                ps.keterangan,
                ps.nominal,
                ps.waktu_catat,
                ps.create_at,
                COALESCE(s.nama_shift, 'Umum') AS nama_shift,
                COALESCE(u.username, 'Kasir') AS nama_kasir
            FROM pengeluaran_shift ps
            LEFT JOIN shift s ON ps.id_shift = s.id_shift
            LEFT JOIN users u ON s.id_user = u.id_user
            WHERE DATE(ps.create_at) = :tanggal
              AND u.username ILIKE :kasir
              AND (
                  s.status = 'buka'
                  OR s.id_shift = (
                      SELECT id_shift FROM shift 
                      WHERE id_user = u.id_user AND tanggal = :tanggal 
                      ORDER BY waktu_buka DESC LIMIT 1
                  )
                  OR ps.id_shift IS NULL
              )
            ORDER BY ps.create_at ASC, ps.id_pengeluaran ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tanggal' => $tanggal,
            ':kasir'   => '%' . $namaKasir . '%',
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menghitung total nominal pengeluaran khusus shift aktif kasir yang berjalan hari ini
     *
     * @param string $tanggal Format YYYY-MM-DD
     * @param string $namaKasir Username / nama kasir
     * @return float
     */
    public function getTotalPengeluaranShiftAktif(string $tanggal, string $namaKasir): float
    {
        $sql = "
            SELECT COALESCE(SUM(ps.nominal), 0)
            FROM pengeluaran_shift ps
            LEFT JOIN shift s ON ps.id_shift = s.id_shift
            LEFT JOIN users u ON s.id_user = u.id_user
            WHERE DATE(ps.create_at) = :tanggal
              AND u.username ILIKE :kasir
              AND (
                  s.status = 'buka'
                  OR s.id_shift = (
                      SELECT id_shift FROM shift 
                      WHERE id_user = u.id_user AND tanggal = :tanggal 
                      ORDER BY waktu_buka DESC LIMIT 1
                  )
                  OR ps.id_shift IS NULL
              )
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tanggal' => $tanggal,
            ':kasir'   => '%' . $namaKasir . '%',
        ]);

        return (float) $stmt->fetchColumn();
    }
}
