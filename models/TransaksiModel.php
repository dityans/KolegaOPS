<?php
// models/TransaksiModel.php
// Model Transaksi Penjualan untuk KolegaOps POS Warkop

class TransaksiModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil ringkasan statistik transaksi hari ini (Omset, Jumlah Transaksi, Cash, QRIS, & Perbandingan Kemarin)
     *
     * @return array
     */
    public function getStatistikHariIni(): array
    {
        // 1. Data Penjualan Hari Ini
        $sqlHariIni = "
            SELECT 
                COALESCE(SUM(total), 0) AS total_penjualan,
                COUNT(id_transaksi) AS jumlah_transaksi,
                COALESCE(SUM(CASE WHEN LOWER(metode_pembayaran) IN ('tunai', 'cash') THEN total ELSE 0 END), 0) AS total_cash,
                COALESCE(SUM(CASE WHEN LOWER(metode_pembayaran) = 'qris' THEN total ELSE 0 END), 0) AS total_qris
            FROM transaksi
            WHERE DATE(create_at) = CURRENT_DATE
        ";
        $stmtHariIni = $this->pdo->query($sqlHariIni);
        $hariIni = $stmtHariIni->fetch(PDO::FETCH_ASSOC) ?: [
            'total_penjualan'  => 0,
            'jumlah_transaksi' => 0,
            'total_cash'       => 0,
            'total_qris'       => 0,
        ];

        // 2. Data Penjualan Kemarin (Sebagai perbandingan performa harian)
        $sqlKemarin = "
            SELECT COALESCE(SUM(total), 0) AS total_kemarin
            FROM transaksi
            WHERE DATE(create_at) = CURRENT_DATE - INTERVAL '1 day'
        ";
        $stmtKemarin = $this->pdo->query($sqlKemarin);
        $totalKemarin = (float) $stmtKemarin->fetchColumn();

        $totalHariIni = (float) $hariIni['total_penjualan'];
        $jumlahTrx = (int) $hariIni['jumlah_transaksi'];
        $rataRata = $jumlahTrx > 0 ? ($totalHariIni / $jumlahTrx) : 0;

        // Hitung persentase kenaikan/penurunan vs kemarin
        $persenTeks = '0% vs kemarin';
        if ($totalKemarin > 0) {
            $selisih = (($totalHariIni - $totalKemarin) / $totalKemarin) * 100;
            $tanda = $selisih >= 0 ? '▲' : '▼';
            $persenTeks = sprintf('%s %.1f%% vs kemarin', $tanda, abs($selisih));
        } elseif ($totalHariIni > 0) {
            $persenTeks = '▲ 100% vs kemarin';
        }

        return [
            'total_penjualan'    => $totalHariIni,
            'jumlah_transaksi'   => $jumlahTrx,
            'total_cash'         => (float) $hariIni['total_cash'],
            'total_qris'         => (float) $hariIni['total_qris'],
            'rata_rata'          => $rataRata,
            'persen_vs_kemarin'  => $persenTeks,
        ];
    }

    /**
     * Mengambil rekapitulasi pendapatan harian selama 7 hari terakhir untuk visualisasi diagram batang
     *
     * @return array
     */
    public function getPendapatan7Hari(): array
    {
        $sql = "
            SELECT 
                d.date::date AS tanggal,
                TO_CHAR(d.date, 'Dy') AS hari_eng,
                COALESCE(SUM(t.total), 0) AS total
            FROM generate_series(
                CURRENT_DATE - INTERVAL '6 days',
                CURRENT_DATE,
                INTERVAL '1 day'
            ) AS d(date)
            LEFT JOIN transaksi t ON DATE(t.create_at) = d.date::date
            GROUP BY d.date
            ORDER BY d.date ASC
        ";

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $petaHari = [
            'Mon' => 'Sen',
            'Tue' => 'Sel',
            'Wed' => 'Rab',
            'Thu' => 'Kam',
            'Fri' => 'Jum',
            'Sat' => 'Sab',
            'Sun' => 'Min',
        ];

        $maxNominal = 0;
        $totalMingguan = 0;
        foreach ($rows as $r) {
            $val = (float) $r['total'];
            $totalMingguan += $val;
            if ($val > $maxNominal) {
                $maxNominal = $val;
            }
        }

        $hasil = [];
        $today = date('Y-m-d');

        foreach ($rows as $r) {
            $nominal = (float) $r['total'];
            $tgl = $r['tanggal'];
            $isToday = ($tgl === $today);

            $tinggiPersen = ($maxNominal > 0) ? round(($nominal / $maxNominal) * 100) : 12;
            if ($tinggiPersen < 12) {
                $tinggiPersen = 12;
            }

            if ($nominal >= 1000000) {
                $labelNominal = number_format($nominal / 1000000, 1, ',', '.') . ' jt';
            } elseif ($nominal >= 1000) {
                $labelNominal = number_format($nominal / 1000, 0, ',', '.') . ' rb';
            } else {
                $labelNominal = (string) $nominal;
            }

            $hariId = $petaHari[$r['hari_eng']] ?? $r['hari_eng'];

            $hasil[] = [
                'tanggal'       => $tgl,
                'hari'          => $hariId,
                'nominal'       => $nominal,
                'label_nominal' => $labelNominal,
                'tinggi_persen' => $tinggiPersen,
                'is_today'      => $isToday,
            ];
        }

        return [
            'daftar_hari'    => $hasil,
            'total_mingguan' => $totalMingguan,
        ];
    }

    /**
     * Mengambil daftar transaksi terbaru
     *
     * @param int $limit
     * @return array
     */
    public function getTransaksiTerbaru(int $limit = 4): array
    {
        $sql = "
            SELECT 
                t.id_transaksi,
                t.kode_transaksi,
                t.total,
                t.metode_pembayaran,
                t.create_at,
                COALESCE(u.username, 'Kasir') AS kasir
            FROM transaksi t
            LEFT JOIN users u ON t.id_user = u.id_user
            ORDER BY t.create_at DESC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil riwayat transaksi lengkap beserta rincian item berdasarkan filter tanggal dan shift
     *
     * @param string $tanggal Format YYYY-MM-DD atau 'Semua'
     * @param string $shift Nama shift atau kasir atau 'Semua'
     * @return array
     */
    public function getRiwayatTransaksiFilter(string $tanggal = 'Semua', string $shift = 'Semua'): array
    {
        $conditions = [];
        $params     = [];

        if ($tanggal !== 'Semua' && !empty($tanggal)) {
            $conditions[] = "DATE(t.create_at) = :tanggal";
            $params[':tanggal'] = $tanggal;
        }

        if ($shift !== 'Semua' && !empty($shift)) {
            $conditions[] = "(s.nama_shift ILIKE :shift OR u.username ILIKE :shift)";
            $params[':shift'] = '%' . $shift . '%';
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // 1. Ambil transaksi utama
        $sqlTrx = "
            SELECT 
                t.id_transaksi,
                t.kode_transaksi,
                t.nama_customer,
                t.total_item,
                t.subtotal,
                t.diskon,
                t.total,
                t.metode_pembayaran,
                t.nominal_bayar,
                t.kembalian,
                t.catatan_transaksi,
                t.create_at,
                DATE(t.create_at) AS tanggal,
                TO_CHAR(t.create_at, 'HH24:MI') AS waktu,
                COALESCE(s.nama_shift, 'Umum') AS nama_shift,
                COALESCE(u.username, 'Kasir') AS nama_kasir
            FROM transaksi t
            LEFT JOIN shift s ON t.id_shift = s.id_shift
            LEFT JOIN users u ON t.id_user = u.id_user
            {$whereClause}
            ORDER BY t.create_at DESC, t.id_transaksi DESC
        ";

        $stmt = $this->pdo->prepare($sqlTrx);
        $stmt->execute($params);
        $daftarTrx = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($daftarTrx)) {
            return [];
        }

        // 2. Ambil seluruh detail_transaksi sekaligus (mencegah N+1 query problem)
        $trxIds = array_column($daftarTrx, 'id_transaksi');
        $inClause = implode(',', array_fill(0, count($trxIds), '?'));

        $sqlItems = "
            SELECT 
                dt.id_transaksi,
                dt.jumlah AS qty,
                dt.subtotal,
                dt.harga_satuan,
                COALESCE(m.nama_menu, 'Menu Dihapus') AS nama
            FROM detail_transaksi dt
            LEFT JOIN menu m ON dt.id_menu = m.id_menu
            WHERE dt.id_transaksi IN ({$inClause})
            ORDER BY dt.id_detail ASC
        ";

        $stmtItems = $this->pdo->prepare($sqlItems);
        $stmtItems->execute($trxIds);
        $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        $itemsMap = [];
        foreach ($allItems as $item) {
            $tId = $item['id_transaksi'];
            $itemsMap[$tId][] = [
                'nama'         => $item['nama'],
                'qty'          => (int) $item['qty'],
                'subtotal'     => (float) $item['subtotal'],
                'harga_satuan' => (float) $item['harga_satuan'],
            ];
        }

        // 3. Gabungkan dan format data transaksi sesuai kebutuhan view
        $hasil = [];
        foreach ($daftarTrx as $trx) {
            $tId = $trx['id_transaksi'];
            $metodeRaw = strtolower(trim((string) $trx['metode_pembayaran']));
            $metodeLabel = ($metodeRaw === 'qris') ? 'QRIS' : 'Cash';
            $shiftLabel  = $trx['nama_shift'] . ' · ' . ucfirst($trx['nama_kasir']);

            $tglTeks = function_exists('format_tanggal') 
                ? format_tanggal($trx['tanggal']) 
                : date('d M Y', strtotime($trx['tanggal']));

            $hasil[] = [
                'id_transaksi' => (int) $trx['id_transaksi'],
                'id'           => $trx['kode_transaksi'] ?: 'TRX-' . $trx['id_transaksi'],
                'kode'         => $trx['kode_transaksi'],
                'waktu'        => $trx['waktu'],
                'tanggal'      => $trx['tanggal'],
                'tgl_teks'     => $tglTeks,
                'shift'        => $shiftLabel,
                'nama_shift'   => $trx['nama_shift'],
                'nama_kasir'   => $trx['nama_kasir'],
                'metode'       => $metodeLabel,
                'total'        => (float) $trx['total'],
                'status'       => 'Lunas',
                'customer'     => $trx['nama_customer'] ?? '-',
                'items'        => $itemsMap[$tId] ?? [],
            ];
        }

        return $hasil;
    }

    /**
     * Menghitung total cash, QRIS, total omzet, dan jumlah transaksi berdasarkan filter
     *
     * @param string $tanggal
     * @param string $shift
     * @return array
     */
    public function getRekapPembayaran(string $tanggal = 'Semua', string $shift = 'Semua'): array
    {
        $conditions = [];
        $params     = [];

        if ($tanggal !== 'Semua' && !empty($tanggal)) {
            $conditions[] = "DATE(t.create_at) = :tanggal";
            $params[':tanggal'] = $tanggal;
        }

        if ($shift !== 'Semua' && !empty($shift)) {
            $conditions[] = "(s.nama_shift ILIKE :shift OR u.username ILIKE :shift)";
            $params[':shift'] = '%' . $shift . '%';
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "
            SELECT 
                COALESCE(SUM(CASE WHEN LOWER(t.metode_pembayaran) IN ('cash', 'tunai') THEN t.total ELSE 0 END), 0) AS total_cash,
                COALESCE(SUM(CASE WHEN LOWER(t.metode_pembayaran) = 'qris' THEN t.total ELSE 0 END), 0) AS total_qris,
                COALESCE(SUM(t.total), 0) AS total_omzet,
                COUNT(t.id_transaksi) AS jumlah_transaksi
            FROM transaksi t
            LEFT JOIN shift s ON t.id_shift = s.id_shift
            LEFT JOIN users u ON t.id_user = u.id_user
            {$whereClause}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_cash'       => (float) ($row['total_cash'] ?? 0),
            'total_qris'       => (float) ($row['total_qris'] ?? 0),
            'total_omzet'      => (float) ($row['total_omzet'] ?? 0),
            'jumlah_transaksi' => (int) ($row['jumlah_transaksi'] ?? 0),
        ];
    }
}
