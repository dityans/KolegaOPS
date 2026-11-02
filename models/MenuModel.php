<?php
// models/MenuModel.php
// Model Katalog Menu Produk & Resep untuk KolegaOps POS Warkop

class MenuModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil seluruh data menu/produk beserta perhitungan keuntungan (Harga - HPP)
     *
     * @return array
     */
    public function getAllMenu(): array
    {
        $sql = "
            SELECT 
                id_menu AS id,
                nama_menu AS nama,
                kategori,
                hpp,
                harga,
                (harga - hpp) AS keuntungan,
                stok_menu AS stok,
                status,
                CASE 
                    WHEN LOWER(status) IN ('aktif', 'tersedia') THEN 'Aktif'
                    ELSE 'Non-Aktif'
                END AS status_label,
                update_at
            FROM menu 
            ORDER BY 
                CASE 
                    WHEN LOWER(status) IN ('aktif', 'tersedia') THEN 0 
                    ELSE 1 
                END,
                kategori ASC, 
                nama_menu ASC
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil detail satu data menu/produk berdasarkan ID
     *
     * @param int $id ID Menu (id_menu)
     * @return array|null
     */
    public function getMenuById(int $id): ?array
    {
        $sql = "
            SELECT 
                id_menu AS id,
                nama_menu AS nama,
                kategori,
                hpp,
                harga,
                (harga - hpp) AS keuntungan,
                stok_menu AS stok,
                status,
                CASE 
                    WHEN LOWER(status) IN ('aktif', 'tersedia') THEN 'Aktif'
                    ELSE 'Non-Aktif'
                END AS status_label,
                update_at
            FROM menu 
            WHERE id_menu = :id 
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Menyimpan produk baru atau memperbarui data produk yang sudah ada (Prepared Statement)
     *
     * @param array $data Data produk [id?, nama, kategori, hpp, harga, status, stok?]
     * @return bool
     */
    public function simpanMenu(array $data): bool
    {
        $id       = isset($data['id']) && (int) $data['id'] > 0 ? (int) $data['id'] : null;
        $nama     = trim($data['nama'] ?? $data['nama_menu'] ?? '');
        $kategori = trim($data['kategori'] ?? 'Minuman');
        $hpp      = (float) ($data['hpp'] ?? 0);
        $harga    = (float) ($data['harga'] ?? 0);
        $status   = trim($data['status'] ?? 'Aktif');
        $stok     = (int) ($data['stok'] ?? $data['stok_menu'] ?? 0);

        if ($nama === '') {
            return false;
        }

        if ($hpp < 0) {
            $hpp = 0;
        }
        if ($harga < 0) {
            $harga = 0;
        }
        if ($stok < 0) {
            $stok = 0;
        }

        try {
            if ($id !== null) {
                // UPDATE Produk yang sudah ada
                $sql = "
                    UPDATE menu 
                    SET nama_menu = :nama,
                        kategori = :kategori,
                        hpp = :hpp,
                        harga = :harga,
                        status = :status,
                        stok_menu = :stok,
                        update_at = CURRENT_TIMESTAMP
                    WHERE id_menu = :id
                ";

                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([
                    ':nama'     => $nama,
                    ':kategori' => $kategori,
                    ':hpp'      => $hpp,
                    ':harga'    => $harga,
                    ':status'   => $status,
                    ':stok'     => $stok,
                    ':id'       => $id,
                ]);
            } else {
                // INSERT Produk Baru
                $sql = "
                    INSERT INTO menu (nama_menu, kategori, hpp, harga, status, stok_menu)
                    VALUES (:nama, :kategori, :hpp, :harga, :status, :stok)
                ";

                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([
                    ':nama'     => $nama,
                    ':kategori' => $kategori,
                    ':hpp'      => $hpp,
                    ':harga'    => $harga,
                    ':status'   => $status,
                    ':stok'     => $stok,
                ]);
            }
        } catch (PDOException $e) {
            error_log('[MenuModel::simpanMenu] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Menghapus menu/produk berdasarkan ID
     *
     * @param int $id ID Menu (id_menu)
     * @return bool
     */
    public function hapusMenu(int $id): bool
    {
        try {
            $sql = "DELETE FROM menu WHERE id_menu = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('[MenuModel::hapusMenu] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil daftar produk terlaris berdasarkan transaksi penjualan hari ini (untuk Dashboard Owner)
     *
     * @param int $limit Jumlah data yang ditampilkan (default: 3)
     * @return array
     */
    public function getProdukTerlarisHariIni(int $limit = 3): array
    {
        // 1. Coba ambil data terlaris hari ini
        $sql = "
            SELECT 
                m.id_menu,
                m.nama_menu,
                COALESCE(SUM(dt.jumlah), 0) AS terjual,
                COALESCE(SUM(dt.subtotal), 0) AS total_penjualan
            FROM detail_transaksi dt
            JOIN menu m ON dt.id_menu = m.id_menu
            JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
            WHERE DATE(t.create_at) = CURRENT_DATE
            GROUP BY m.id_menu, m.nama_menu
            ORDER BY terjual DESC, total_penjualan DESC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Jika hari ini belum ada transaksi, ambil produk terlaris secara keseluruhan (all-time)
        if (empty($results)) {
            $sqlAllTime = "
                SELECT 
                    m.id_menu,
                    m.nama_menu,
                    COALESCE(SUM(dt.jumlah), 0) AS terjual,
                    COALESCE(SUM(dt.subtotal), 0) AS total_penjualan
                FROM menu m
                LEFT JOIN detail_transaksi dt ON m.id_menu = dt.id_menu
                GROUP BY m.id_menu, m.nama_menu
                ORDER BY terjual DESC, m.nama_menu ASC
                LIMIT :limit
            ";
            $stmtAll = $this->pdo->prepare($sqlAllTime);
            $stmtAll->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmtAll->execute();
            $results = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
        }

        return $results;
    }

    /**
     * Mengambil seluruh katalog menu aktif untuk POS kasir
     *
     * @return array
     */
    public function getAll(): array
    {
        $sql = "
            SELECT * 
            FROM menu 
            WHERE LOWER(status) IN ('aktif', 'tersedia')
            ORDER BY kategori ASC, nama_menu ASC
        ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
