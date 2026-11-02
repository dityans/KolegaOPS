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
     * Mengambil ringkasan teks operasional harian untuk footer dashboard
     *
     * @return string
     */
    public function getInfoOperasionalFooter(): string
    {
        // 1. Cek shift aktif / terakhir
        $shift = $this->getShiftAktif();

        // 2. Hitung total kas pengeluaran operasional hari ini
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
