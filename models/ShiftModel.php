<?php
// models/ShiftModel.php
// Model Pengelolaan Shift Kasir & Rekapitulasi Operasional

class ShiftModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil informasi shift terkini / aktif hari ini
     *
     * @return array|null
     */
    public function getShiftAktif(): ?array
    {
        $sql = "
            SELECT 
                s.id_shift,
                s.nama_shift,
                s.status,
                s.waktu_buka,
                s.waktu_tutup,
                s.total_pengeluaran,
                s.saldo_akhir,
                COALESCE(u.username, 'Kasir') AS nama_kasir
            FROM shift s
            LEFT JOIN users u ON s.id_user = u.id_user
            ORDER BY s.id_shift DESC
            LIMIT 1
        ";

        $stmt = $this->pdo->query($sql);
        $shift = $stmt->fetch(PDO::FETCH_ASSOC);

        return $shift ?: null;
    }

    /**
     * Mengambil shift yang sedang aktif (status = 'buka') untuk kasir tertentu
     * Jika tidak ada yang buka, ambil shift terakhir kasir tersebut hari ini
     *
     * @param int $id_user
     * @return array|null
     */
    public function getShiftKasirAktif(int $id_user): ?array
    {
        // 1. Cek shift berstatus 'buka' milik kasir
        $sql = "
            SELECT 
                s.id_shift,
                s.nama_shift,
                s.tanggal,
                s.status,
                s.waktu_buka,
                s.waktu_tutup,
                s.total_penjualan_tunai,
                s.total_penjualan_qris,
                s.total_pengeluaran,
                s.saldo_akhir,
                COALESCE(u.username, 'Kasir') AS nama_kasir
            FROM shift s
            LEFT JOIN users u ON s.id_user = u.id_user
            WHERE s.id_user = :id_user AND s.status = 'buka'
            ORDER BY s.id_shift DESC
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_user' => $id_user]);
        $shift = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($shift) {
            return $shift;
        }

        // 2. Jika tidak ada yang buka, cek shift terakhir hari ini milik kasir
        $sqlHariIni = "
            SELECT 
                s.id_shift,
                s.nama_shift,
                s.tanggal,
                s.status,
                s.waktu_buka,
                s.waktu_tutup,
                s.total_penjualan_tunai,
                s.total_penjualan_qris,
                s.total_pengeluaran,
                s.saldo_akhir,
                COALESCE(u.username, 'Kasir') AS nama_kasir
            FROM shift s
            LEFT JOIN users u ON s.id_user = u.id_user
            WHERE s.id_user = :id_user AND s.tanggal = CURRENT_DATE
            ORDER BY s.id_shift DESC
            LIMIT 1
        ";

        $stmtToday = $this->pdo->prepare($sqlHariIni);
        $stmtToday->execute([':id_user' => $id_user]);
        $shiftToday = $stmtToday->fetch(PDO::FETCH_ASSOC);

        return $shiftToday ?: null;
    }

    /**
     * Membuka shift baru untuk kasir
     *
     * @param int $id_user
     * @param string $nama_shift
     * @return int
     */
    public function bukaShiftBaru(int $id_user, string $nama_shift = 'Sore'): int
    {
        try {
            $sql = "
                INSERT INTO shift (nama_shift, tanggal, waktu_buka, status, id_user, total_penjualan_tunai, total_penjualan_qris, total_pengeluaran, saldo_akhir)
                VALUES (:nama_shift, CURRENT_DATE, CURRENT_TIMESTAMP, 'buka', :id_user, 0, 0, 0, 0)
                RETURNING id_shift
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nama_shift' => $nama_shift,
                ':id_user'    => $id_user,
            ]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('[ShiftModel::bukaShiftBaru] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Menutup shift kasir dan merekap saldo akhir
     *
     * @param int $id_shift
     * @param float $saldo_akhir
     * @param float $total_tunai
     * @param float $total_qris
     * @param float $total_pengeluaran
     * @return bool
     */
    public function tutupShift(int $id_shift, float $saldo_akhir, float $total_tunai, float $total_qris, float $total_pengeluaran): bool
    {
        if ($id_shift <= 0) {
            return false;
        }

        try {
            $sql = "
                UPDATE shift
                SET status = 'tutup',
                    waktu_tutup = CURRENT_TIMESTAMP,
                    saldo_akhir = :saldo_akhir,
                    total_penjualan_tunai = :total_tunai,
                    total_penjualan_qris = :total_qris,
                    total_pengeluaran = :total_pengeluaran,
                    update_at = CURRENT_TIMESTAMP
                WHERE id_shift = :id_shift
            ";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':saldo_akhir'        => $saldo_akhir,
                ':total_tunai'        => $total_tunai,
                ':total_qris'         => $total_qris,
                ':total_pengeluaran'  => $total_pengeluaran,
                ':id_shift'           => $id_shift,
            ]);
        } catch (PDOException $e) {
            error_log('[ShiftModel::tutupShift] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil ringkasan teks operasional harian untuk footer dashboard
     *
     * @return string
     */
    public function getInfoOperasionalFooter(): string
    {
        $shift = $this->getShiftAktif();

        $stmtExp = $this->pdo->query("
            SELECT COALESCE(SUM(nominal), 0) 
            FROM pengeluaran_shift 
            WHERE DATE(create_at) = CURRENT_DATE
        ");
        $totalPengeluaran = (float) $stmtExp->fetchColumn();

        if ($shift) {
            $namaShift = htmlspecialchars($shift['nama_shift']);
            $namaKasir = htmlspecialchars($shift['nama_kasir']);
            $statusShift = ($shift['status'] === 'buka') ? 'sedang aktif' : 'telah ditutup';
            $nominalPengeluaran = number_format($totalPengeluaran, 0, ',', '.');

            return "ℹ️ Operasional: Shift {$namaShift} {$statusShift} oleh {$namaKasir} · Pengeluaran hari ini Rp {$nominalPengeluaran}.";
        }

        $nominalPengeluaran = number_format($totalPengeluaran, 0, ',', '.');
        return "ℹ️ Operasional: Belum ada shift kasir yang dibuka hari ini · Pengeluaran tercatat Rp {$nominalPengeluaran}.";
    }
}
