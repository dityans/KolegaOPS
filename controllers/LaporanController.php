<?php
// controllers/LaporanController.php
// Controller untuk Laporan Keseluruhan, Analisis Profit, dan Dashboard Owner KolegaOps

require_once BASE_PATH . '/models/TransaksiModel.php';
require_once BASE_PATH . '/models/MenuModel.php';
require_once BASE_PATH . '/models/StokBahanModel.php';
require_once BASE_PATH . '/models/ShiftModel.php';
require_once BASE_PATH . '/models/PengeluaranModel.php';
require_once BASE_PATH . '/models/AnalisisProfitModel.php';

class LaporanController
{
    private PDO $pdo;
    private TransaksiModel $transaksiModel;
    private MenuModel $menuModel;
    private StokBahanModel $stokBahanModel;
    private ShiftModel $shiftModel;
    private PengeluaranModel $pengeluaranModel;
    private AnalisisProfitModel $profitModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->transaksiModel   = new TransaksiModel($pdo);
        $this->menuModel        = new MenuModel($pdo);
        $this->stokBahanModel   = new StokBahanModel($pdo);
        $this->shiftModel       = new ShiftModel($pdo);
        $this->pengeluaranModel = new PengeluaranModel($pdo);
        $this->profitModel      = new AnalisisProfitModel($pdo);
    }

    /**
     * Menampilkan Dashboard Utama Owner (Ringkasan Keuangan, Diagram 7 Hari, Transaksi, & Stok)
     */
    public function dashboard(): void
    {
        // 1. Pengaman Hak Akses: Khusus role 'owner' atau 'admin'
        require_role(['owner', 'admin']);

        // 2. Tarik data riil dari database PostgreSQL via Model
        $statHariIni      = $this->transaksiModel->getStatistikHariIni();
        $rekap7Hari       = $this->transaksiModel->getPendapatan7Hari();
        $transaksiTerbaru = $this->transaksiModel->getTransaksiTerbaru(4);
        $produkTerlaris   = $this->menuModel->getProdukTerlarisHariIni(3);
        $stokKritisCount  = $this->stokBahanModel->getStokKritisCount();
        $infoShift        = $this->shiftModel->getInfoOperasionalFooter();

        // 3. Persiapkan variabel siap cetak untuk View
        $total_penjualan           = format_rupiah($statHariIni['total_penjualan']);
        $persen_vs_kemarin         = $statHariIni['persen_vs_kemarin'];
        $jumlah_transaksi          = $statHariIni['jumlah_transaksi'];
        $rata_rata_transaksi       = format_rupiah($statHariIni['rata_rata']);
        $stok_kritis_count         = $stokKritisCount;
        $pendapatan_mingguan       = $rekap7Hari['daftar_hari'];
        $total_pendapatan_mingguan = format_rupiah($rekap7Hari['total_mingguan']);
        $cash_hari_ini             = format_rupiah($statHariIni['total_cash']);
        $qris_hari_ini             = format_rupiah($statHariIni['total_qris']);
        $transaksi_terbaru         = $transaksiTerbaru;
        $produk_terlaris           = $produkTerlaris;
        $info_shift                = $infoShift;

        $namaOwner        = ucfirst((string) current_user('username'));
        $tanggal_hari_ini = format_tanggal(date('Y-m-d'));
        $waktu_update     = date('H:i');

        // 4. Render tampilan View Dashboard Owner
        require_once BASE_PATH . '/views/owner/dashboard.php';
    }

    /**
     * Menampilkan Laporan Keseluruhan Finansial & Riwayat Transaksi Sehari Penuh Lintas Shift
     */
    public function laporan(): void
    {
        // 1. Pengaman Hak Akses: Khusus role 'owner' atau 'admin'
        require_role(['owner', 'admin']);

        // 2. Tangkap parameter filter tanggal dan shift dari form GET
        $filterTanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $filterShift   = $_GET['shift'] ?? 'Semua';

        // 3. Ambil data riil dari database PostgreSQL via Model
        $filteredTransaksi = $this->transaksiModel->getRiwayatTransaksiFilter($filterTanggal, $filterShift);
        $rekapPembayaran   = $this->transaksiModel->getRekapPembayaran($filterTanggal, $filterShift);
        $pengeluaranList   = $this->pengeluaranModel->getPengeluaranByTanggal($filterTanggal, $filterShift);
        $totalPengeluaran  = $this->pengeluaranModel->getTotalPengeluaranByTanggal($filterTanggal, $filterShift);

        // 4. Hitung rekapitulasi agregat
        $totalOmzetFilter  = $rekapPembayaran['total_omzet'];
        $jumlahTrxFilter   = $rekapPembayaran['jumlah_transaksi'];
        $totalCash         = $rekapPembayaran['total_cash'];
        $totalQris         = $rekapPembayaran['total_qris'];
        $labaBersih        = $totalOmzetFilter - $totalPengeluaran;

        $filterTanggalTeks = ($filterTanggal === 'Semua') ? 'Semua Tanggal' : format_tanggal($filterTanggal);
        $waktuUpdate       = date('H:i');
        $namaOwner         = ucfirst((string) current_user('username'));

        // 5. Render tampilan View Laporan Keseluruhan
        require_once BASE_PATH . '/views/owner/laporan.php';
    }

    /**
     * Menampilkan Laporan Analisis Kontribusi Profit Menu Warkop
     */
    public function profit(): void
    {
        // 1. Pengaman Hak Akses: Khusus role 'owner' atau 'admin'
        require_role(['owner', 'admin']);

        // 2. Tangkap parameter filter tanggal dan kategori
        $filterTanggal  = $_GET['tanggal'] ?? date('Y-m-d');
        $filterKategori = $_GET['kategori'] ?? 'Semua';

        // 3. Ambil data kalkulasi profit per menu dari PostgreSQL
        $filteredProfit = $this->profitModel->getRankingProfit($filterTanggal, $filterKategori);
        $daftarKategori = $this->profitModel->getDaftarKategori();

        // 4. Hitung agregat ringkasan 4 kartu
        $totalProfitBersih   = array_sum(array_column($filteredProfit, 'total_profit'));
        $totalPorsiTerjual   = array_sum(array_column($filteredProfit, 'terjual'));
        $rataRataProfitPorsi = $totalPorsiTerjual > 0 ? (int) round($totalProfitBersih / $totalPorsiTerjual) : 0;

        $filterTanggalTeks = ($filterTanggal === 'Semua') ? 'Semua Tanggal' : format_tanggal($filterTanggal);
        $namaOwner         = ucfirst((string) current_user('username'));

        // 5. Render tampilan View Analisis Profit
        require_once BASE_PATH . '/views/owner/analisis-profit.php';
    }

    /**
     * Alias method analisisProfit
     */
    public function analisisProfit(): void
    {
        $this->profit();
    }

    /**
     * Endpoint default controller laporan (Mengarahkan ke halaman laporan keseluruhan)
     */
    public function index(): void
    {
        $this->laporan();
    }
}
