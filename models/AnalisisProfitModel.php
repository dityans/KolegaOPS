<?php
// models/AnalisisProfitModel.php
// Model Perhitungan dan Analisis Kontribusi Profit Menu untuk KolegaOps POS Warkop

class AnalisisProfitModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil daftar ranking kontribusi keuntungan menu berdasarkan filter tanggal dan kategori
     *
     * @param string $tanggal Format YYYY-MM-DD atau 'Semua'
     * @param string $kategori Nama kategori atau 'Semua'
     * @return array
     */
    public function getRankingProfit(string $tanggal = 'Semua', string $kategori = 'Semua'): array
    {
        $conditions = [];
        $params     = [];

        // Filter Tanggal Transaksi
        if ($tanggal !== 'Semua' && !empty($tanggal)) {
            $conditions[] = "DATE(t.create_at) = :tanggal";
            $params[':tanggal'] = $tanggal;
        }

        // Filter Kategori Menu (Mendukung alias Kopi / Coffee)
        if ($kategori !== 'Semua' && !empty($kategori)) {
            $katClean = strtolower(trim($kategori));
            if ($katClean === 'coffee') {
                $conditions[] = "(LOWER(m.kategori) LIKE '%kopi%' OR LOWER(m.kategori) LIKE '%coffee%')";
            } elseif ($katClean === 'non-coffee') {
                $conditions[] = "(LOWER(m.kategori) NOT LIKE '%kopi%' AND LOWER(m.kategori) NOT LIKE '%coffee%')";
            } else {
                $conditions[] = "LOWER(m.kategori) = :kategori";
                $params[':kategori'] = $katClean;
            }
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "
            SELECT 
                m.id_menu,
                m.nama_menu AS menu,
                m.kategori,
                COALESCE(m.hpp, 0) AS hpp,
                COALESCE(m.harga, 0) AS jual,
                COALESCE(SUM(dt.jumlah), 0) AS terjual,
                COALESCE(m.harga - m.hpp, 0) AS profit_unit,
                COALESCE(SUM((dt.harga_satuan - m.hpp) * dt.jumlah), 0) AS total_profit
            FROM detail_transaksi dt
            JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
            JOIN menu m ON dt.id_menu = m.id_menu
            {$whereClause}
            GROUP BY m.id_menu, m.nama_menu, m.kategori, m.hpp, m.harga
            HAVING SUM(dt.jumlah) > 0
            ORDER BY total_profit DESC, terjual DESC, m.nama_menu ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tambahkan nomor ranking berurutan (1, 2, 3, ...)
        $hasil = [];
        $rank  = 1;
        foreach ($rows as $r) {
            $hpp        = (float) $r['hpp'];
            $jual       = (float) $r['jual'];
            $terjual    = (int) $r['terjual'];
            $profitUnit = $jual - $hpp;
            $totalProfit = (float) $r['total_profit'];

            $hasil[] = [
                'rank'         => $rank++,
                'id_menu'      => (int) $r['id_menu'],
                'menu'         => $r['menu'],
                'kategori'     => $r['kategori'],
                'terjual'      => $terjual,
                'hpp'          => $hpp,
                'jual'         => $jual,
                'profit_unit'  => $profitUnit,
                'total_profit' => $totalProfit,
            ];
        }

        return $hasil;
    }

    /**
     * Mengambil daftar seluruh kategori unik menu yang terdaftar di database
     *
     * @return array
     */
    public function getDaftarKategori(): array
    {
        $sql = "
            SELECT DISTINCT kategori 
            FROM menu 
            WHERE kategori IS NOT NULL AND TRIM(kategori) != ''
            ORDER BY kategori ASC
        ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }
}
