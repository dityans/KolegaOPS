<?php
// models/StokBahanModel.php
// Model Pengelolaan Stok Bahan Baku untuk KolegaOps POS Warkop

class StokBahanModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil seluruh data stok bahan baku beserta status (Aman/Kritis)
     *
     * @return array
     */
    public function getAllStok(): array
    {
        $sql = "
            SELECT 
                id_bahan AS id,
                nama_bahan AS nama,
                stok_bahan AS stok,
                minimum_stok AS minimum,
                satuan,
                CASE 
                    WHEN stok_bahan >= minimum_stok THEN 'Aman'
                    ELSE 'Kritis'
                END AS status,
                CASE 
                    WHEN LOWER(satuan) IN ('pcs', 'bungkus', 'botol', 'pak', 'kemasan') THEN 'Menu'
                    ELSE 'Bahan'
                END AS jenis,
                update_at
            FROM bahan_baku 
            ORDER BY 
                CASE WHEN stok_bahan < minimum_stok THEN 0 ELSE 1 END,
                id_bahan ASC
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menambahkan data bahan baku baru ke database PostgreSQL
     *
     * @param array $data Data bahan [nama_bahan, stok_awal, minimum_stok, satuan, catatan]
     * @return int|false Mengembalikan ID bahan yang baru dibuat atau false jika gagal
     */
    public function tambahStokBahan(array $data)
    {
        $nama        = trim($data['nama_bahan'] ?? '');
        $stokAwal    = (float) ($data['stok_awal'] ?? 0);
        $minimumStok = (float) ($data['minimum_stok'] ?? 0);
        $satuan      = trim($data['satuan'] ?? 'pcs');
        $catatan     = trim($data['catatan'] ?? '');

        if ($nama === '') {
            return false;
        }

        if ($stokAwal < 0) {
            $stokAwal = 0;
        }
        if ($minimumStok < 0) {
            $minimumStok = 0;
        }

        $statusStok = ($stokAwal >= $minimumStok) ? 'aman' : 'menipis';

        $sql = "
            INSERT INTO bahan_baku (nama_bahan, stok_bahan, minimum_stok, satuan, status_stok)
            VALUES (:nama_bahan, :stok_bahan, :minimum_stok, :satuan, :status_stok)
            RETURNING id_bahan
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nama_bahan'   => $nama,
            ':stok_bahan'   => $stokAwal,
            ':minimum_stok' => $minimumStok,
            ':satuan'       => $satuan,
            ':status_stok'  => $statusStok,
        ]);

        $newId = (int) $stmt->fetchColumn();

        // Catat ke riwayat stok sebagai stok awal jika ada tabel riwayat_stok
        if ($newId > 0 && $stokAwal > 0) {
            try {
                $userId = current_user('id_user') ?? 1;
                $sqlLog = "
                    INSERT INTO riwayat_stok (bahan_id, user_id, jenis, jumlah, keterangan)
                    VALUES (:bahan_id, :user_id, 'masuk', :jumlah, :keterangan)
                ";
                $stmtLog = $this->pdo->prepare($sqlLog);
                $stmtLog->execute([
                    ':bahan_id'   => $newId,
                    ':user_id'    => $userId,
                    ':jumlah'     => $stokAwal,
                    ':keterangan' => $catatan ?: 'Stok awal bahan baru ditambahkan',
                ]);
            } catch (Exception $e) {
                // Non-blocking
            }
        }

        return $newId;
    }

    /**
     * Mengambil detail satu data bahan baku berdasarkan ID
     *
     * @param int $id
     * @return array|null
     */
    public function getStokById(int $id): ?array
    {
        $sql = "
            SELECT 
                id_bahan AS id,
                nama_bahan AS nama,
                stok_bahan AS stok,
                minimum_stok AS minimum,
                satuan,
                status_stok,
                CASE 
                    WHEN stok_bahan >= minimum_stok THEN 'Aman'
                    ELSE 'Kritis'
                END AS status,
                CASE 
                    WHEN LOWER(satuan) IN ('pcs', 'bungkus', 'botol', 'pak', 'kemasan') THEN 'Menu'
                    ELSE 'Bahan'
                END AS jenis,
                update_at
            FROM bahan_baku 
            WHERE id_bahan = :id 
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Hapus data bahan baku berdasarkan ID
     *
     * @param int $id ID bahan baku (id_bahan)
     * @return bool true jika berhasil dihapus, false jika gagal
     */
    public function hapusStok(int $id): bool
    {
        try {
            $sql  = "DELETE FROM bahan_baku WHERE id_bahan = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            // Log error jika tersedia
            error_log('[StokBahanModel::hapusStok] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Memperbarui jumlah stok bahan di database dan mencatat riwayat perubahan
     *
     * @param int $id
     * @param float $stokBaru
     * @param string|null $catatan
     * @return bool
     */
    public function updateStok(int $id, float $stokBaru, ?string $catatan = null): bool
    {
        if ($stokBaru < 0) {
            $stokBaru = 0;
        }

        // Ambil data bahan terkini untuk kalkulasi status & histori
        $itemSaatIni = $this->getStokById($id);
        if (!$itemSaatIni) {
            return false;
        }

        $minStok = (float) $itemSaatIni['minimum'];
        $statusBaru = ($stokBaru >= $minStok) ? 'aman' : 'menipis';

        $sql = "
            UPDATE bahan_baku 
            SET stok_bahan = :stok, 
                status_stok = :status_stok, 
                update_at = CURRENT_TIMESTAMP 
            WHERE id_bahan = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        $berhasil = $stmt->execute([
            ':stok'        => $stokBaru,
            ':status_stok' => $statusBaru,
            ':id'          => $id,
        ]);

        // Catat ke tabel riwayat_stok jika tersedia untuk jejak audit
        if ($berhasil) {
            try {
                $stokLama = (float) $itemSaatIni['stok'];
                $selisih = $stokBaru - $stokLama;
                $jenis = ($selisih >= 0) ? 'masuk' : 'keluar';
                $userId = current_user('id_user') ?? 1;

                $sqlLog = "
                    INSERT INTO riwayat_stok (bahan_id, user_id, jenis, jumlah, keterangan) 
                    VALUES (:bahan_id, :user_id, :jenis, :jumlah, :keterangan)
                ";
                $stmtLog = $this->pdo->prepare($sqlLog);
                $stmtLog->execute([
                    ':bahan_id'   => $id,
                    ':user_id'    => $userId,
                    ':jenis'      => $jenis,
                    ':jumlah'     => abs($selisih),
                    ':keterangan' => $catatan ?: 'Penyesuaian stok manual',
                ]);
            } catch (Exception $e) {
                // Riwayat stok bersifat non-blocking jika tabel belum aktif
            }
        }

        return $berhasil;
    }

    /**
     * Menghitung total bahan baku yang berada pada kondisi kritis
     *
     * @return int
     */
    public function getStokKritisCount(): int
    {
        $sql = "
            SELECT COUNT(*) 
            FROM bahan_baku 
            WHERE stok_bahan < minimum_stok 
               OR status_stok IN ('menipis', 'habis')
        ";

        $stmt = $this->pdo->query($sql);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Mengambil daftar bahan baku kritis
     *
     * @return array
     */
    public function getDaftarStokKritis(): array
    {
        $sql = "
            SELECT id_bahan, nama_bahan, stok_bahan, satuan, minimum_stok, status_stok
            FROM bahan_baku
            WHERE stok_bahan < minimum_stok 
               OR status_stok IN ('menipis', 'habis')
            ORDER BY stok_bahan ASC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
