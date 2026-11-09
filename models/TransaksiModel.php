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

    /**
     * Mengambil rekapitulasi penjualan khusus kasir yang login pada shift aktif atau hari ini
     *
     * @param int|null $id_shift
     * @param int $id_user
     * @return array
     */
    public function getPenjualanShiftKasir(?int $id_shift, int $id_user): array
    {
        $conditions = ["t.id_user = :id_user"];
        $params     = [':id_user' => $id_user];

        if ($id_shift && $id_shift > 0) {
            $conditions[] = "t.id_shift = :id_shift";
            $params[':id_shift'] = $id_shift;
        } else {
            $conditions[] = "DATE(t.create_at) = CURRENT_DATE";
        }

        $whereClause = 'WHERE ' . implode(' AND ', $conditions);

        $sql = "
            SELECT 
                COALESCE(SUM(t.total), 0) AS total_penjualan,
                COUNT(t.id_transaksi) AS jumlah_transaksi,
                COALESCE(SUM(CASE WHEN LOWER(t.metode_pembayaran) IN ('tunai', 'cash') THEN t.total ELSE 0 END), 0) AS total_cash,
                COALESCE(SUM(CASE WHEN LOWER(t.metode_pembayaran) = 'qris' THEN t.total ELSE 0 END), 0) AS total_qris
            FROM transaksi t
            {$whereClause}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_penjualan'  => (float) ($row['total_penjualan'] ?? 0),
            'jumlah_transaksi' => (int) ($row['jumlah_transaksi'] ?? 0),
            'total_cash'       => (float) ($row['total_cash'] ?? 0),
            'total_qris'       => (float) ($row['total_qris'] ?? 0),
        ];
    }

    /**
     * Mengambil daftar transaksi terbaru yang diinput oleh kasir tertentu
     *
     * @param int $id_user
     * @param int $limit
     * @return array
     */
    public function getTransaksiTerbaruKasir(int $id_user, int $limit = 4): array
    {
        $sql = "
            SELECT 
                t.id_transaksi,
                t.kode_transaksi,
                t.total,
                t.metode_pembayaran,
                TO_CHAR(t.create_at, 'HH24:MI') AS waktu,
                t.create_at
            FROM transaksi t
            WHERE t.id_user = :id_user AND DATE(t.create_at) = CURRENT_DATE
            ORDER BY t.create_at DESC, t.id_transaksi DESC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menyimpan transaksi penjualan baru beserta detail item-nya dalam satu PDO transaction.
     * Juga mencoba mengurangi stok bahan baku via tabel resep (non-blocking).
     *
     * @param array $data {
     *   id_user, id_shift, nama_customer, total_item, subtotal,
     *   diskon, total, metode_pembayaran, nominal_bayar, kembalian,
     *   catatan_transaksi, items[]{id_menu, jumlah, harga_satuan, subtotal, catatan_item}
     * }
     * @return array { success: bool, id_transaksi: int, kode_transaksi: string, error: string }
     */
    public function simpanTransaksi(array $data): array
    {
        // Generate kode transaksi unik: KG-YYMMDD-XXX
        $kode = 'KG-' . date('ymd') . '-' . str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT);

        try {
            $this->pdo->beginTransaction();

            // ── 1. Insert header transaksi ────────────────────────────────────
            $sqlTrx = "
                INSERT INTO transaksi (
                    kode_transaksi, nama_customer, total_item, subtotal,
                    diskon, total, metode_pembayaran, nominal_bayar, kembalian,
                    catatan_transaksi, id_user, id_shift
                ) VALUES (
                    :kode, :nama_customer, :total_item, :subtotal,
                    :diskon, :total, :metode_pembayaran, :nominal_bayar, :kembalian,
                    :catatan_transaksi, :id_user, :id_shift
                ) RETURNING id_transaksi
            ";

            $stmtTrx = $this->pdo->prepare($sqlTrx);
            $stmtTrx->execute([
                ':kode'               => $kode,
                ':nama_customer'      => $data['nama_customer']      ?? null,
                ':total_item'         => (int)   ($data['total_item']         ?? 0),
                ':subtotal'           => (float)  ($data['subtotal']           ?? 0),
                ':diskon'             => (float)  ($data['diskon']             ?? 0),
                ':total'              => (float)  ($data['total']              ?? 0),
                ':metode_pembayaran'  => $data['metode_pembayaran']  ?? 'tunai',
                ':nominal_bayar'      => (float)  ($data['nominal_bayar']      ?? 0),
                ':kembalian'          => (float)  ($data['kembalian']          ?? 0),
                ':catatan_transaksi'  => $data['catatan_transaksi']  ?? null,
                ':id_user'            => (int)   ($data['id_user']            ?? 0),
                ':id_shift'           => (int)   ($data['id_shift']           ?? 0),
            ]);

            $idTransaksi = (int) $stmtTrx->fetchColumn();

            if ($idTransaksi <= 0) {
                throw new \RuntimeException('Gagal mendapatkan id_transaksi setelah INSERT.');
            }

            // ── 2. Insert detail item-item transaksi ──────────────────────────
            $sqlDetail = "
                INSERT INTO detail_transaksi
                    (id_transaksi, id_menu, jumlah, harga_satuan, subtotal, catatan_item)
                VALUES
                    (:id_transaksi, :id_menu, :jumlah, :harga_satuan, :subtotal, :catatan_item)
            ";
            $stmtDetail = $this->pdo->prepare($sqlDetail);

            foreach ($data['items'] as $item) {
                $stmtDetail->execute([
                    ':id_transaksi' => $idTransaksi,
                    ':id_menu'      => (int)   ($item['id_menu']      ?? 0),
                    ':jumlah'       => (int)   ($item['jumlah']       ?? 1),
                    ':harga_satuan' => (float)  ($item['harga_satuan'] ?? 0),
                    ':subtotal'     => (float)  ($item['subtotal']     ?? 0),
                    ':catatan_item' => $item['catatan_item'] ?? null,
                ]);
            }

            $this->pdo->commit();

            // ── 3. Kurangi stok bahan baku via tabel resep & stok menu ──────
            try {
                // Kurangi stok produk jadi di tabel menu
                $sqlMenuStok = "
                    UPDATE menu
                    SET stok_menu = GREATEST(stok_menu - :jumlah, 0),
                        update_at = CURRENT_TIMESTAMP
                    WHERE id_menu = :id_menu
                ";
                $stmtMenuStok = $this->pdo->prepare($sqlMenuStok);

                // Cek komposisi resep untuk pengurangan bahan mentah di bahan_baku
                $sqlResep = "
                    SELECT r.id_bahan, (r.jumlah_pemakaian * :jumlah) AS total_pakai
                    FROM resep r
                    WHERE r.id_menu = :id_menu
                ";
                $stmtResep = $this->pdo->prepare($sqlResep);

                $sqlKurang = "
                    UPDATE bahan_baku
                    SET stok_bahan = GREATEST(stok_bahan - :kurang, 0),
                        status_stok = CASE WHEN GREATEST(stok_bahan - :kurang, 0) < minimum_stok THEN 'menipis' ELSE 'aman' END,
                        update_at   = CURRENT_TIMESTAMP
                    WHERE id_bahan = :id_bahan
                ";
                $stmtKurang = $this->pdo->prepare($sqlKurang);

                foreach ($data['items'] as $item) {
                    $idM = (int) ($item['id_menu'] ?? 0);
                    $qty = (int) ($item['jumlah']  ?? 1);

                    // 3a. Kurangi stok menu
                    if ($idM > 0) {
                        $stmtMenuStok->execute([
                            ':jumlah'  => $qty,
                            ':id_menu' => $idM,
                        ]);
                    }

                    // 3b. Kurangi bahan baku jika menu punya resep
                    $stmtResep->execute([
                        ':id_menu' => $idM,
                        ':jumlah'  => $qty,
                    ]);
                    $pemakaian = $stmtResep->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($pemakaian as $p) {
                        $stmtKurang->execute([
                            ':kurang'   => (float) $p['total_pakai'],
                            ':id_bahan' => (int)   $p['id_bahan'],
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Pengurangan stok bersifat non-blocking (transaksi utama tetap sah)
                error_log('[KolegaOps] Stok reduction warning: ' . $e->getMessage());
            }

            return [
                'success'        => true,
                'id_transaksi'   => $idTransaksi,
                'kode_transaksi' => $kode,
                'error'          => '',
            ];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[KolegaOps] simpanTransaksi error: ' . $e->getMessage());
            return [
                'success'        => false,
                'id_transaksi'   => 0,
                'kode_transaksi' => '',
                'error'          => $e->getMessage(),
            ];
        }
    }

    /**
     * Mengambil detail satu transaksi beserta item-itemnya berdasarkan id_transaksi.
     *
     * @param int $id_transaksi
     * @return array|null  Array berisi data transaksi + 'items', atau null jika tidak ditemukan.
     */
    public function getDetailTransaksi(int $id_transaksi): ?array
    {
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
                TO_CHAR(t.create_at, 'HH24:MI') AS waktu,
                DATE(t.create_at)                AS tanggal,
                COALESCE(s.nama_shift, 'Umum')   AS nama_shift,
                COALESCE(u.username, 'Kasir')    AS nama_kasir
            FROM transaksi t
            LEFT JOIN shift s ON t.id_shift = s.id_shift
            LEFT JOIN users u ON t.id_user  = u.id_user
            WHERE t.id_transaksi = :id_transaksi
            LIMIT 1
        ";

        $stmtTrx = $this->pdo->prepare($sqlTrx);
        $stmtTrx->bindValue(':id_transaksi', $id_transaksi, PDO::PARAM_INT);
        $stmtTrx->execute();
        $trx = $stmtTrx->fetch(PDO::FETCH_ASSOC);

        if (!$trx) {
            return null;
        }

        $sqlItems = "
            SELECT
                dt.jumlah        AS qty,
                dt.harga_satuan,
                dt.subtotal,
                dt.catatan_item,
                COALESCE(m.nama_menu, 'Menu Dihapus') AS nama,
                COALESCE(m.nama_menu, 'Menu Dihapus') AS nama_menu,
                COALESCE(m.kategori, 'Umum')          AS kategori
            FROM detail_transaksi dt
            LEFT JOIN menu m ON dt.id_menu = m.id_menu
            WHERE dt.id_transaksi = :id_transaksi
            ORDER BY dt.id_detail ASC
        ";

        $stmtItems = $this->pdo->prepare($sqlItems);
        $stmtItems->bindValue(':id_transaksi', $id_transaksi, PDO::PARAM_INT);
        $stmtItems->execute();

        $trx['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        return $trx;
    }

    /**
     * Mengambil daftar riwayat transaksi kasir khusus pada shift aktif hari ini
     * Data terisolasi hanya untuk kasir yang bertugas pada tanggal dan shift aktif tersebut.
     *
     * @param string $tanggal Format YYYY-MM-DD
     * @param string $namaKasir Username / nama kasir
     * @return array
     */
    public function getTransaksiShiftAktif(string $tanggal, string $namaKasir): array
    {
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
            WHERE DATE(t.create_at) = :tanggal
              AND u.username ILIKE :kasir
              AND (
                  s.status = 'buka'
                  OR s.id_shift = (
                      SELECT id_shift FROM shift 
                      WHERE id_user = u.id_user AND tanggal = :tanggal 
                      ORDER BY waktu_buka DESC LIMIT 1
                  )
                  OR t.id_shift IS NULL
              )
            ORDER BY t.create_at DESC, t.id_transaksi DESC
        ";

        $stmt = $this->pdo->prepare($sqlTrx);
        $stmt->execute([
            ':tanggal' => $tanggal,
            ':kasir'   => '%' . $namaKasir . '%',
        ]);
        $daftarTrx = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($daftarTrx)) {
            return [];
        }

        // Ambil seluruh detail_transaksi sekaligus (mencegah N+1 query problem)
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
     * Menghitung rekapitulasi agregat pembayaran khusus shift aktif kasir yang bertugas hari ini
     *
     * @param string $tanggal Format YYYY-MM-DD
     * @param string $namaKasir Username / nama kasir
     * @return array { jumlah_transaksi: int, total_omzet: float, total_cash: float, total_qris: float }
     */
    public function getRekapPembayaranShiftAktif(string $tanggal, string $namaKasir): array
    {
        $sql = "
            SELECT 
                COUNT(t.id_transaksi) AS jumlah_transaksi,
                COALESCE(SUM(t.total), 0) AS total_omzet,
                COALESCE(SUM(CASE WHEN LOWER(t.metode_pembayaran) IN ('cash', 'tunai') THEN t.total ELSE 0 END), 0) AS total_cash,
                COALESCE(SUM(CASE WHEN LOWER(t.metode_pembayaran) = 'qris' THEN t.total ELSE 0 END), 0) AS total_qris
            FROM transaksi t
            LEFT JOIN shift s ON t.id_shift = s.id_shift
            LEFT JOIN users u ON t.id_user = u.id_user
            WHERE DATE(t.create_at) = :tanggal
              AND u.username ILIKE :kasir
              AND (
                  s.status = 'buka'
                  OR s.id_shift = (
                      SELECT id_shift FROM shift 
                      WHERE id_user = u.id_user AND tanggal = :tanggal 
                      ORDER BY waktu_buka DESC LIMIT 1
                  )
                  OR t.id_shift IS NULL
              )
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tanggal' => $tanggal,
            ':kasir'   => '%' . $namaKasir . '%',
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'jumlah_transaksi' => (int)   ($row['jumlah_transaksi'] ?? 0),
            'total_omzet'      => (float) ($row['total_omzet'] ?? 0),
            'total_cash'       => (float) ($row['total_cash'] ?? 0),
            'total_qris'       => (float) ($row['total_qris'] ?? 0),
        ];
    }
}
