<?php
// controllers/DashboardController.php
// Controller Dashboard Owner dan Pegawai KolegaOps

require_once BASE_PATH . '/models/TransaksiModel.php';
require_once BASE_PATH . '/models/MenuModel.php';
require_once BASE_PATH . '/models/StokBahanModel.php';
require_once BASE_PATH . '/models/ShiftModel.php';
require_once BASE_PATH . '/models/PengeluaranModel.php';
require_once BASE_PATH . '/models/AnalisisProfitModel.php';

class DashboardController
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
    public function dashboard_owner(): void
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
     * Menampilkan Dashboard Utama Kasir / Pegawai
     */
    public function dashboard_pegawai(): void
    {
        // 1. Pengaman Hak Akses: Khusus Role Pegawai / Kasir
        require_role(['kasir', 'pegawai']);

        $idUser    = (int) (current_user('id_user') ?? 0);
        $namaKasir = ucfirst((string) (current_user('username') ?? 'Pegawai'));

        // 2. Ambil informasi shift kasir aktif
        $shiftAktif = $this->shiftModel->getShiftKasirAktif($idUser);

        // Jika kasir belum memiliki shift hari ini sama sekali, buatkan otomatis shift buka
        if (!$shiftAktif) {
            $jamSekarang = (int) date('H');
            $namaShiftDefault = ($jamSekarang < 15) ? 'Pagi' : 'Sore';
            $newShiftId = $this->shiftModel->bukaShiftBaru($idUser, $namaShiftDefault);
            $shiftAktif = $this->shiftModel->getShiftKasirAktif($idUser);
        }

        $idShift = $shiftAktif ? (int) $shiftAktif['id_shift'] : 0;

        // 3. Ambil data riil dari PostgreSQL via Model
        $penjualanHariIni    = $this->transaksiModel->getStatistikHariIni();
        $penjualanShiftSaya  = $this->transaksiModel->getPenjualanShiftKasir($idShift, $idUser);
        $rekapPembayaran     = $penjualanShiftSaya;
        $transaksiTerbaru    = $this->transaksiModel->getTransaksiTerbaruKasir($idUser, 4);
        $stokKritis          = $this->stokBahanModel->getDaftarStokKritis();
        $pengeluaranShift    = ($idShift > 0) ? $this->pengeluaranModel->getPengeluaranByShift($idShift) : [];
        $totalPengeluaranShift = ($idShift > 0) ? $this->pengeluaranModel->getTotalPengeluaranByShift($idShift) : 0;

        // 4. Kalkulasi Rekonsiliasi Kas Laci (Modal Kasir)
        $modalAwal           = 200000; // Standar uang modal float kasir warkop
        $cashMasuk           = (float) $penjualanShiftSaya['total_cash'];
        $uangSeharusnyaLaci  = $modalAwal + $cashMasuk - $totalPengeluaranShift;

        // 5. Pesan Flash Notifikasi
        $pesanSukses = get_flash('success');
        $pesanError  = get_flash('error');

        $tanggalTeks = format_tanggal(date('Y-m-d'));

        // 6. Muat tampilan View
        require_once BASE_PATH . '/views/pegawai/dashboard.php';
    }

    /**
     * Memproses pencatatan pengeluaran operasional tunai selama shift berlangsung
     */
    public function tambahPengeluaran(): void
    {
        require_role(['kasir', 'pegawai']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=dashboard&action=dashboard_pegawai');
            exit;
        }

        if (isset($_POST['csrf_token']) && function_exists('csrf_verify')) {
            csrf_verify($_POST['csrf_token']);
        }

        $idUser     = (int) (current_user('id_user') ?? 0);
        $idShift    = (int) ($_POST['id_shift'] ?? 0);
        $keterangan = trim($_POST['keterangan'] ?? '');
        $nominal    = (float) ($_POST['nominal'] ?? 0);

        if ($idShift <= 0) {
            $shift = $this->shiftModel->getShiftKasirAktif($idUser);
            $idShift = $shift ? (int) $shift['id_shift'] : 0;
        }

        if ($idShift <= 0) {
            set_flash('error', 'Tidak ditemukan sesi shift aktif untuk mencatat pengeluaran!');
            header('Location: index.php?controller=dashboard&action=dashboard_pegawai');
            exit;
        }

        if ($keterangan === '' || $nominal <= 0) {
            set_flash('error', 'Keterangan dan nominal pengeluaran wajib diisi dengan benar!');
            header('Location: index.php?controller=dashboard&action=dashboard_pegawai');
            exit;
        }

        $sukses = $this->pengeluaranModel->tambahPengeluaran($idShift, $keterangan, $nominal);

        if ($sukses) {
            set_flash('success', "Pengeluaran '{$keterangan}' sebesar Rp " . number_format($nominal, 0, ',', '.') . " berhasil dicatat.");
        } else {
            set_flash('error', 'Gagal mencatat pengeluaran operasional ke database.');
        }

        header('Location: index.php?controller=dashboard&action=dashboard_pegawai');
        exit;
    }

    /**
     * Memproses penutupan shift kasir
     */
    public function tutupShift(): void
    {
        require_role(['kasir', 'pegawai']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=dashboard&action=dashboard_pegawai');
            exit;
        }

        if (isset($_POST['csrf_token']) && function_exists('csrf_verify')) {
            csrf_verify($_POST['csrf_token']);
        }

        $idUser    = (int) (current_user('id_user') ?? 0);
        $idShift   = (int) ($_POST['id_shift'] ?? 0);
        $uangFisik = (float) ($_POST['uang_fisik'] ?? 0);

        if ($idShift <= 0) {
            $shift = $this->shiftModel->getShiftKasirAktif($idUser);
            $idShift = $shift ? (int) $shift['id_shift'] : 0;
        }

        if ($idShift <= 0) {
            set_flash('error', 'Sesi shift tidak ditemukan!');
            header('Location: index.php?controller=dashboard&action=dashboard_pegawai');
            exit;
        }

        // Ambil rekap akhir shift
        $penjualanShift        = $this->transaksiModel->getPenjualanShiftKasir($idShift, $idUser);
        $totalPengeluaranShift = $this->pengeluaranModel->getTotalPengeluaranByShift($idShift);

        $totalTunai = (float) $penjualanShift['total_cash'];
        $totalQris  = (float) $penjualanShift['total_qris'];

        $berhasil = $this->shiftModel->tutupShift($idShift, $uangFisik, $totalTunai, $totalQris, $totalPengeluaranShift);

        if ($berhasil) {
            set_flash('success', 'Shift kasir berhasil ditutup dan seluruh data transaksi telah direkap!');
        } else {
            set_flash('error', 'Gagal menutup sesi shift di database.');
        }

        header('Location: index.php?controller=dashboard&action=dashboard_pegawai');
        exit;
    }

    /**
     * Endpoint default controller (Mengarahkan ke halaman yang sesuai berdasarkan role)
     */
    public function index(): void
    {
        // Pastikan pengguna sudah login
        if (!isset($_SESSION['user'])) {
            header("Location: ../auth/login.php");
            exit;
        }

        $role = $_SESSION['user']['role'] ?? '';

        // Arahkan berdasarkan role masing-masing
        if ($role === 'owner') {
            // Panggil method atau arahkan ke tampilan laporan owner
            $this->laporanOwner(); 
        } elseif ($role === 'kasir' || $role === 'pegawai') {
            // Panggil method atau arahkan ke tampilan laporan/riwayat pegawai
            $this->laporanPegawai(); 
        } else {
            // Jika role tidak dikenal, paksa logout atau redirect ke login
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
    }
}