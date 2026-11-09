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
     * Menampilkan Laporan Keseluruhan Finansial & Riwayat Transaksi Sehari Penuh Lintas Shift
     */
    public function laporanOwner(): void
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
     * Menampilkan Laporan Shift Pegawai — Khusus transaksi dan pengeluaran shift aktif yang sedang dikerjakan hari ini
     * Data terisolasi dari data riwayat shift lain atau kasir lain.
     */
    public function laporanPegawai(): void
    {
        // 1. Pengaman Hak Akses: Khusus role 'pegawai' atau 'kasir'
        require_role(['pegawai', 'kasir']);

        // 2. Ambil identitas kasir yang sedang aktif dari sesi dan tanggal operasional hari ini
        $idUser            = (int) (current_user('id_user') ?? 0);
        $namaKasir         = (string) (current_user('username') ?? $_SESSION['user']['username'] ?? $_SESSION['user']['nama'] ?? 'Kasir');
        $namaKasirLabel    = ucfirst($namaKasir);
        $filterTanggal     = date('Y-m-d');
        $filterTanggalTeks = format_tanggal($filterTanggal);
        $waktuUpdate       = date('H:i');

        // 3. Ambil informasi sesi shift aktif kasir saat ini
        $shiftAktif = $this->shiftModel->getShiftKasirAktif($idUser);
        if (!$shiftAktif) {
            $jamSekarang      = (int) date('H');
            $namaShiftDefault = ($jamSekarang < 15) ? 'Pagi' : 'Sore';
            $this->shiftModel->bukaShiftBaru($idUser, $namaShiftDefault);
            $shiftAktif       = $this->shiftModel->getShiftKasirAktif($idUser);
        }
        $namaShiftAktif = $shiftAktif ? (string) $shiftAktif['nama_shift'] : 'Shift Berjalan';

        // 4. Panggil model transaksi dan pengeluaran terisolasi shift aktif
        $filteredTransaksi = $this->transaksiModel->getTransaksiShiftAktif($filterTanggal, $namaKasir);
        $rekapPembayaran   = $this->transaksiModel->getRekapPembayaranShiftAktif($filterTanggal, $namaKasir);
        $pengeluaranList   = $this->pengeluaranModel->getPengeluaranShiftAktif($filterTanggal, $namaKasir);
        $totalPengeluaran  = $this->pengeluaranModel->getTotalPengeluaranShiftAktif($filterTanggal, $namaKasir);

        // 5. Hitung rekapitulasi agregat shift
        $totalOmzetFilter  = (float) ($rekapPembayaran['total_omzet'] ?? 0);
        $jumlahTrxFilter   = (int)   ($rekapPembayaran['jumlah_transaksi'] ?? 0);
        $totalCash         = (float) ($rekapPembayaran['total_cash'] ?? 0);
        $totalQris         = (float) ($rekapPembayaran['total_qris'] ?? 0);
        $labaBersih        = $totalOmzetFilter - $totalPengeluaran;

        // 6. Render tampilan View Laporan Shift Pegawai
        require_once BASE_PATH . '/views/pegawai/laporan_shift.php';
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
        // Pastikan pengguna sudah login
        if (!isset($_SESSION['user'])) {
            header("Location: ../auth/login.php");
            exit;
        }

        $role = $_SESSION['user']['role'] ?? '';

        if (in_array(current_user('role'), ['owner', 'admin'])) {
            $this->laporanOwner();
        } elseif (in_array(current_user('role'), ['kasir', 'pegawai'])) {
            $this->laporanPegawai();
        } else {
            // Jika role tidak dikenal, paksa logout atau redirect ke login
            header("Location: index.php?controller=auth&action=login");
        }
    }
}
