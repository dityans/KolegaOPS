Struktur Folder
kolegaops/
│
├── config/
|   ├── config.php              # Menangkap configurasi dari .env
│   ├── database.php            # Koneksi PDO ke database PostgreSQL
│   └── session.php             # Pengaturan sesi login & middleware hak akses (Kasir vs Owner)
│
├── helpers/ 
│ ├── security_helper.php       # Berisi fungsi e(), generate_csrf_token(), verify_csrf_token() 
│ └── format_helper.php         # Berisi fungsi format_rupiah(), format_tanggal(), dll. 
│ 
├── controllers/
│   ├── AuthController.php          # Mengatur proses login, logout, & validasi hak akses
│   ├── ShiftController.php         # Mengatur logika 1-klik Buka Shift, Tutup Shift, & hitung agregat
│   ├── TransaksiController.php     # Mengatur proses POS, input pesanan, pembayaran (Tunai/QRIS), & toggle status menu
│   ├── PengeluaranController.php   # Mengatur input kas keluar operasional warkop per shift
│   ├── LaporanController.php       # Mengatur laporan harian/shift, profit keseluruhan, & profit per menu
│   └── InventarisController.php    # Mengatur manajemen menu, resep, dan stok bahan baku (Khusus Owner)
│
├── models/
│   ├── UserModel.php          # Query SQL untuk data pengguna & autentikasi
│   ├── ShiftModel.php         # Query SQL untuk tabel Shift_Kasir & status aktif
│   ├── TransaksiModel.php     # Query SQL untuk transaksi, detail pesanan, & status menu (Tersedia/Habis)
│   ├── PengeluaranModel.php   # Query SQL untuk tabel pengeluaran operasional
│   └── InventarisModel.php    # Query SQL untuk manajemen menu, resep, dan stok bahan baku
│
├── views/
│   ├── auth/
│   │   └── login.php          # Halaman login antarmuka kasir & owner
│   ├── kasir/
│   │   ├── dashboard.php           # Ringkasan
│   │   ├── kasir.php               # Antarmuka kasir (POS, keranjang, input pengeluaran, & tombol Tutup Shift)
│   |   ├── stok_bahan.php          # Halaman manajemen stok bahan baku & kulakan
│   │   └── laporan_transaksi.php   # Ringkasan laporan penjualan pribadi kasir selama shift berlangsung
│   ├── owner/
│   │   ├── dashboard.php         # Ringkasan performa warkop, grafik, & status shift aktif
│   │   ├── produk.php            # Halaman manajemen menu & komposisi resep
│   |   ├── stok_bahan.php        # Halaman manajemen stok bahan baku & kulakan
│   |   ├── laporan_transaksi.php # Rekapitulasi laporan harian & verifikasi shift pegawai
│   │   ├── profit_menu.php       # Laporan analisis profit penjualan berdasarkan per menu
│   |   └── kelola_pegawai.php    # Halaman Kelola Akun Pegawai
│   └── layouts/
│       ├── header.php         # Bagian atas HTML (navbar & file CSS)
│       ├── footer.php         # Bagian bawah HTML (file JS & copyright)
│       └── sidebar.php        # Menu navigasi dinamis (otomatis menyesuaikan role Kasir/Owner)
│
├── assets/                    # Aset statis front-end
│   ├── css/                   # File CSS kustom / framework styling (Bootstrap/Tailwind)
│   ├── js/                    # File JavaScript interaktif (kalkulator POS, modal, AJAX)
│   └── img/                   # Logo atau aset gambar pendukung
│
└── public/
    └── index.php              # Pintu gerbang utama aplikasi (Single Entry Point / Router)