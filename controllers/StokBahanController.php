<?php
// controllers/StokBahanController.php
// Controller Pengelolaan Stok Bahan Baku & Inventaris Warkop KolegaOps

require_once BASE_PATH . '/models/StokBahanModel.php';

class StokBahanController
{
    private PDO $pdo;
    private StokBahanModel $stokBahanModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->stokBahanModel = new StokBahanModel($pdo);
    }

    /**
     * Menampilkan halaman daftar seluruh stok bahan dan menu siap jual
     */
    public function index(): void
    {
        // 1. Pengaman Hak Akses: Khusus Owner / Admin
        require_role(['owner', 'admin']);

        // 2. Ambil seluruh data stok riil dari database
        $daftarStok = $this->stokBahanModel->getAllStok();

        // 3. Hitung statistik agregat
        $totalItem   = count($daftarStok);
        $totalAman   = count(array_filter($daftarStok, fn($s) => $s['status'] === 'Aman'));
        $totalKritis = count(array_filter($daftarStok, fn($s) => $s['status'] === 'Kritis'));

        $totalBahan  = count(array_filter($daftarStok, fn($s) => ($s['jenis'] ?? '') === 'Bahan'));
        $totalMenu   = count(array_filter($daftarStok, fn($s) => ($s['jenis'] ?? '') === 'Menu'));

        // Nama-nama bahan yang perlu restock
        $kritisList  = array_column(array_filter($daftarStok, fn($s) => $s['status'] === 'Kritis'), 'nama');
        $kritisTeks  = !empty($kritisList) ? implode(', ', array_slice($kritisList, 0, 4)) : 'Semua bahan dalam kondisi aman';

        $tanggal_hari_ini = format_tanggal(date('Y-m-d'));
        $namaOwner        = ucfirst((string) current_user('username'));

        // Ambil flash messages jika ada
        $pesanSukses = get_flash('success');
        $pesanError  = get_flash('error');

        // 4. Muat tampilan View
        require_once BASE_PATH . '/views/owner/stok.php';
    }

    /**
     * Menampilkan form penambahan bahan baru dan memproses penyimpanan (POST)
     */
    public function tambah(): void
    {
        // 1. Pengaman Hak Akses: Khusus Owner / Admin
        require_role(['owner', 'admin']);

        // 2. Tangani Aksi POST (Simpan Bahan Baku Baru)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['csrf_token']) && function_exists('csrf_verify')) {
                csrf_verify($_POST['csrf_token']);
            }

            $namaBahan   = trim($_POST['nama_bahan'] ?? '');
            $stokAwal    = (float) ($_POST['stok_awal'] ?? 0);
            $minimumStok = (float) ($_POST['minimum_stok'] ?? 0);
            $satuan      = trim($_POST['satuan'] ?? 'pcs');
            $catatan     = trim($_POST['catatan'] ?? '');

            if ($namaBahan === '') {
                set_flash('error', 'Nama bahan baku wajib diisi!');
                header('Location: index.php?controller=stok_bahan&action=tambah');
                exit;
            }

            if ($satuan === '') {
                set_flash('error', 'Satuan bahan wajib dipilih atau diisi!');
                header('Location: index.php?controller=stok_bahan&action=tambah');
                exit;
            }

            $data = [
                'nama_bahan'   => $namaBahan,
                'stok_awal'    => $stokAwal,
                'minimum_stok' => $minimumStok,
                'satuan'       => $satuan,
                'catatan'      => $catatan,
            ];

            $newId = $this->stokBahanModel->tambahStokBahan($data);

            if ($newId) {
                set_flash('success', "Bahan baku '{$namaBahan}' berhasil ditambahkan ke dalam inventaris!");
                header('Location: index.php?controller=stok_bahan&action=index');
                exit;
            } else {
                set_flash('error', 'Gagal menambahkan bahan baku ke database!');
                header('Location: index.php?controller=stok_bahan&action=tambah');
                exit;
            }
        }

        // 3. Tangani Aksi GET (Menampilkan Halaman Form)
        $namaOwner        = ucfirst((string) current_user('username'));
        $tanggal_hari_ini = format_tanggal(date('Y-m-d'));
        $pesanError       = get_flash('error');

        require_once BASE_PATH . '/views/owner/tambah-stok.php';
    }

    /**
     * Menampilkan form update stok dan memproses aksi penambahan/pengurangan stok
     *
     * @param int|null $id ID bahan baku
     */
    public function update($id = null): void
    {
        // 1. Pengaman Hak Akses
        require_role(['owner', 'admin']);

        // 2. Tangani Aksi POST (Simpan Perubahan Stok)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['csrf_token']) && function_exists('csrf_verify')) {
                csrf_verify($_POST['csrf_token']);
            }

            $idStok         = (int) ($_POST['id_stok'] ?? $id ?? $_GET['id'] ?? 0);
            $jenisUpdate    = trim($_POST['jenis_update'] ?? 'Penambahan stok');
            $jumlahTambahan = (float) ($_POST['jumlah_tambahan'] ?? 0);
            $catatan        = trim($_POST['catatan'] ?? '');

            // Validasi Input
            if ($idStok <= 0) {
                set_flash('error', 'Pilihan bahan baku tidak valid!');
                header('Location: index.php?controller=stok_bahan&action=index');
                exit;
            }

            if ($jumlahTambahan <= 0) {
                set_flash('error', 'Jumlah perubahan stok harus lebih besar dari 0!');
                header("Location: index.php?controller=stok_bahan&action=update&id={$idStok}");
                exit;
            }

            // Ambil item dari database
            $item = $this->stokBahanModel->getStokById($idStok);
            if (!$item) {
                set_flash('error', 'Data bahan baku tidak ditemukan di database!');
                header('Location: index.php?controller=stok_bahan&action=index');
                exit;
            }

            // Hitung nilai stok baru
            $stokAwal = (float) $item['stok'];
            if ($jenisUpdate === 'Penambahan stok') {
                $stokBaru = $stokAwal + $jumlahTambahan;
            } else {
                $stokBaru = $stokAwal - $jumlahTambahan;
                if ($stokBaru < 0) {
                    $stokBaru = 0;
                }
            }

            // Simpan perubahan ke PostgreSQL
            $simpan = $this->stokBahanModel->updateStok($idStok, $stokBaru, $catatan);

            if ($simpan) {
                $namaBahan = htmlspecialchars($item['nama']);
                $satuan = htmlspecialchars($item['satuan']);
                $formatStok = number_format($stokBaru, 0, ',', '.');
                set_flash('success', "Stok {$namaBahan} berhasil diperbarui menjadi {$formatStok} {$satuan}.");
            } else {
                set_flash('error', "Gagal memperbarui stok di database!");
            }

            header('Location: index.php?controller=stok_bahan&action=index');
            exit;
        }

        // 3. Tangani Aksi GET (Menampilkan Form Update Stok)
        $idItem     = (int) ($id ?? $_GET['id'] ?? 0);
        $daftarStok = $this->stokBahanModel->getAllStok();

        if (empty($daftarStok)) {
            set_flash('error', 'Belum ada data stok bahan baku.');
            header('Location: index.php?controller=stok_bahan&action=index');
            exit;
        }

        // Tentukan bahan baku yang terpilih
        $selectedStok = null;
        if ($idItem > 0) {
            $selectedStok = $this->stokBahanModel->getStokById($idItem);
        }

        if (!$selectedStok) {
            $selectedStok = $daftarStok[0];
        }

        $namaOwner        = ucfirst((string) current_user('username'));
        $tanggal_hari_ini = format_tanggal(date('Y-m-d'));
        $pesanError       = get_flash('error');

        require_once BASE_PATH . '/views/owner/update-stok.php';
    }

    /**
     * Menghapus data bahan baku berdasarkan ID dari database
     * Hanya Owner / Admin yang diizinkan melakukan penghapusan
     *
     * @param int|null $id ID bahan baku dari URL (?id=X)
     */
    public function hapus($id = null): void
    {
        // 1. Pengaman Hak Akses: Khusus Owner / Admin
        require_role(['owner', 'admin']);

        // 2. Ambil & validasi ID dari GET param
        $id = (int) ($id ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            set_flash('error', 'ID bahan baku tidak valid!');
            header('Location: index.php?controller=stok_bahan&action=index');
            exit;
        }

        // 3. Pastikan bahan ada sebelum dihapus
        $item = $this->stokBahanModel->getStokById($id);
        if (!$item) {
            set_flash('error', 'Data bahan baku tidak ditemukan di database!');
            header('Location: index.php?controller=stok_bahan&action=index');
            exit;
        }

        // 4. Eksekusi penghapusan via Model
        $berhasil = $this->stokBahanModel->hapusStok($id);

        if ($berhasil) {
            $namaBahan = htmlspecialchars($item['nama']);
            set_flash('success', "Bahan \"{$namaBahan}\" berhasil dihapus dari inventaris.");
        } else {
            set_flash('error', 'Gagal menghapus data bahan dari database. Silakan coba lagi.');
        }

        // 5. Redirect kembali ke daftar stok
        header('Location: index.php?controller=stok_bahan&action=index');
        exit;
    }
}
