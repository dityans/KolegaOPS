<?php
// controllers/MenuController.php
// Controller Katalog Menu & Pengelolaan Produk untuk KolegaOps POS Warkop

require_once BASE_PATH . '/models/MenuModel.php';

class MenuController
{
    private PDO $pdo;
    private MenuModel $menuModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->menuModel = new MenuModel($pdo);
    }

    /**
     * Menampilkan daftar seluruh produk/menu katalog warkop
     */
    public function index(): void
    {
        // 1. Pengaman Hak Akses: Khusus Owner / Admin
        require_role(['owner', 'admin']);

        // 2. Ambil data katalog produk riil dari PostgreSQL
        $daftarProduk = $this->menuModel->getAllMenu();

        // 3. Hitung statistik agregat
        $totalAktif = count(array_filter($daftarProduk, fn($p) => ($p['status_label'] ?? '') === 'Aktif'));

        $hargaList = array_map(fn($p) => (float) $p['harga'], $daftarProduk);
        $minHarga  = !empty($hargaList) ? min($hargaList) : 0;
        $maxHarga  = !empty($hargaList) ? max($hargaList) : 0;

        // Ambil flash messages
        $pesanSukses = get_flash('success');
        $pesanError  = get_flash('error');

        // 4. Muat tampilan View
        require_once BASE_PATH . '/views/owner/produk.php';
    }

    /**
     * Form penambahan produk baru
     */
    public function tambah(): void
    {
        $this->form(null);
    }

    /**
     * Form pengubahan data produk
     *
     * @param int|null $id
     */
    public function edit($id = null): void
    {
        $this->form($id);
    }

    /**
     * Menangani tampilan form tambah/edit produk sekaligus memproses penyimpanan POST
     *
     * @param int|null $id
     */
    public function form($id = null): void
    {
        // 1. Pengaman Hak Akses: Khusus Owner / Admin
        require_role(['owner', 'admin']);

        $id = (int) ($id ?? $_GET['id'] ?? 0);
        $isEdit = ($id > 0);

        // Jika mode Edit, ambil data produk dari database
        if ($isEdit) {
            $dataProduk = $this->menuModel->getMenuById($id);
            if (!$dataProduk) {
                set_flash('error', 'Data produk tidak ditemukan di database!');
                header('Location: index.php?controller=menu&action=index');
                exit;
            }
        } else {
            $dataProduk = [
                'id'           => null,
                'nama'         => '',
                'kategori'     => 'Minuman',
                'hpp'          => '',
                'harga'        => '',
                'status'       => 'Aktif',
                'status_label' => 'Aktif',
            ];
        }

        // 2. Tangani Aksi POST (Simpan Produk Baru / Perbarui Produk)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['csrf_token']) && function_exists('csrf_verify')) {
                csrf_verify($_POST['csrf_token']);
            }

            $nama     = trim($_POST['nama'] ?? '');
            $kategori = trim($_POST['kategori'] ?? 'Minuman');
            $hpp      = (float) ($_POST['hpp'] ?? 0);
            $harga    = (float) ($_POST['harga'] ?? 0);
            $status   = trim($_POST['status'] ?? 'Aktif');

            // Validasi Input
            if ($nama === '') {
                set_flash('error', 'Nama produk wajib diisi!');
                header('Location: index.php?controller=menu&action=' . ($isEdit ? "edit&id={$id}" : 'tambah'));
                exit;
            }

            if ($hpp < 0 || $harga < 0) {
                set_flash('error', 'HPP dan Harga jual tidak boleh bernilai negatif!');
                header('Location: index.php?controller=menu&action=' . ($isEdit ? "edit&id={$id}" : 'tambah'));
                exit;
            }

            $payload = [
                'id'       => $isEdit ? $id : null,
                'nama'     => $nama,
                'kategori' => $kategori,
                'hpp'      => $hpp,
                'harga'    => $harga,
                'status'   => $status,
                'stok'     => 0,
            ];

            $sukses = $this->menuModel->simpanMenu($payload);

            if ($sukses) {
                $pesan = $isEdit 
                    ? "Produk '{$nama}' berhasil diperbarui!" 
                    : "Produk baru '{$nama}' berhasil ditambahkan ke katalog!";
                set_flash('success', $pesan);
                header('Location: index.php?controller=menu&action=index');
                exit;
            } else {
                set_flash('error', 'Gagal menyimpan data produk ke database PostgreSQL.');
                header('Location: index.php?controller=menu&action=' . ($isEdit ? "edit&id={$id}" : 'tambah'));
                exit;
            }
        }

        // 3. Tampilkan Form Input (GET)
        $hppAwal    = is_numeric($dataProduk['hpp']) ? (float) $dataProduk['hpp'] : 0;
        $hargaAwal  = is_numeric($dataProduk['harga']) ? (float) $dataProduk['harga'] : 0;
        $profitAwal = $hargaAwal - $hppAwal;
        $marginAwal = $hargaAwal > 0 ? ($profitAwal / $hargaAwal) * 100 : 0;

        $pesanError = get_flash('error');

        require_once BASE_PATH . '/views/owner/form-produk.php';
    }

    /**
     * Menghapus produk dari database
     *
     * @param int|null $id
     */
    public function hapus($id = null): void
    {
        // 1. Pengaman Hak Akses: Khusus Owner / Admin
        require_role(['owner', 'admin']);

        $id = (int) ($id ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            set_flash('error', 'ID produk tidak valid!');
            header('Location: index.php?controller=menu&action=index');
            exit;
        }

        $item = $this->menuModel->getMenuById($id);
        $nama = $item ? $item['nama'] : 'Produk';

        $berhasil = $this->menuModel->hapusMenu($id);

        if ($berhasil) {
            set_flash('success', "Produk '{$nama}' berhasil dihapus dari katalog.");
        } else {
            set_flash('error', "Gagal menghapus produk '{$nama}' (mungkin produk memiliki relasi data transaksi).");
        }

        header('Location: index.php?controller=menu&action=index');
        exit;
    }
}
