-- =============================================================================
-- KOLEGAOPS - DATABASE SCHEMA (PostgreSQL)
-- Aplikasi Point of Sales (POS) Warkop "KolegaOps"
-- =============================================================================

-- Hapus tabel jika sudah ada (urutan drop terbalik dari pembuatan relasi)
DROP TABLE IF EXISTS detail_transaksi CASCADE;
DROP TABLE IF EXISTS transaksi CASCADE;
DROP TABLE IF EXISTS resep CASCADE;
DROP TABLE IF EXISTS bahan_baku CASCADE;
DROP TABLE IF EXISTS menu CASCADE;
DROP TABLE IF EXISTS pengeluaran_shift CASCADE;
DROP TABLE IF EXISTS shift CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- -----------------------------------------------------------------------------
-- 1. TABEL USERS (Master Pengguna / Pegawai)
-- -----------------------------------------------------------------------------
CREATE TABLE users (
    id_user SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL, -- contoh: admin, kasir, owner
    status VARCHAR(20) NOT NULL DEFAULT 'aktif', -- contoh: aktif, nonaktif
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------------------------------
-- 2. TABEL SHIFT (Rekapitulasi Sesi Operasional Kasir)
-- -----------------------------------------------------------------------------
CREATE TABLE shift (
    id_shift SERIAL PRIMARY KEY,
    nama_shift VARCHAR(10) NOT NULL, -- misal: Pagi, Siang, Malam
    tanggal DATE NOT NULL DEFAULT CURRENT_DATE,
    waktu_buka TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    waktu_tutup TIMESTAMP NULL,
    total_penjualan_tunai DECIMAL(12,2) DEFAULT 0,
    total_penjualan_qris DECIMAL(12,2) DEFAULT 0,
    total_pengeluaran DECIMAL(12,2) DEFAULT 0,
    saldo_akhir DECIMAL(12,2) DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'buka', -- buka / tutup
    id_user INT NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_shift_user FOREIGN KEY (id_user)
        REFERENCES users(id_user) ON DELETE RESTRICT
);

CREATE INDEX idx_shift_id_user ON shift(id_user);
CREATE INDEX idx_shift_status ON shift(status);
CREATE INDEX idx_shift_tanggal ON shift(tanggal);

-- -----------------------------------------------------------------------------
-- 3. TABEL PENGELUARAN_SHIFT (Kas Keluar Operasional per Shift)
-- -----------------------------------------------------------------------------
CREATE TABLE pengeluaran_shift (
    id_pengeluaran SERIAL PRIMARY KEY,
    keterangan VARCHAR(100) NOT NULL,
    nominal DECIMAL(12,2) NOT NULL,
    waktu_catat TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_shift INT NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pengeluaran_shift FOREIGN KEY (id_shift)
        REFERENCES shift(id_shift) ON DELETE CASCADE
);

CREATE INDEX idx_pengeluaran_id_shift ON pengeluaran_shift(id_shift);

-- -----------------------------------------------------------------------------
-- 4. TABEL MENU (Katalog Produk & HPP Manual)
-- -----------------------------------------------------------------------------
CREATE TABLE menu (
    id_menu SERIAL PRIMARY KEY,
    nama_menu VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL, -- misal: Kopi, Makanan, Camilan
    harga DECIMAL(12,2) NOT NULL, -- harga jual
    hpp DECIMAL(12,2) NOT NULL, -- harga modal (diinput manual oleh owner)
    stok_menu INT DEFAULT 0, -- untuk produk instan / siap jual
    status VARCHAR(20) NOT NULL DEFAULT 'tersedia', -- tersedia / habis
    gambar VARCHAR(255) NULL, -- path file gambar
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_menu_kategori ON menu(kategori);
CREATE INDEX idx_menu_status ON menu(status);

-- -----------------------------------------------------------------------------
-- 5. TABEL BAHAN_BAKU (Inventaris Bahan Mentah - Masa Depan/Opsional)
-- -----------------------------------------------------------------------------
CREATE TABLE bahan_baku (
    id_bahan SERIAL PRIMARY KEY,
    nama_bahan VARCHAR(100) NOT NULL,
    stok_bahan DECIMAL(12,2) NOT NULL DEFAULT 0,
    satuan VARCHAR(20) NOT NULL, -- gr, ml, pcs, butir
    minimum_stok DECIMAL(12,2) NOT NULL DEFAULT 0,
    status_stok VARCHAR(20) NOT NULL DEFAULT 'aman', -- aman / menipis / habis
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    update_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------------------------------
-- 6. TABEL RESEP (BOM Komposisi Bahan per Menu - Masa Depan/Opsional)
-- -----------------------------------------------------------------------------
CREATE TABLE resep (
    id_resep SERIAL PRIMARY KEY,
    id_menu INT NOT NULL,
    id_bahan INT NOT NULL,
    jumlah_pemakaian DECIMAL(12,2) NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_resep_menu FOREIGN KEY (id_menu)
        REFERENCES menu(id_menu) ON DELETE CASCADE,
    CONSTRAINT fk_resep_bahan FOREIGN KEY (id_bahan)
        REFERENCES bahan_baku(id_bahan) ON DELETE RESTRICT,
    CONSTRAINT uq_menu_bahan UNIQUE (id_menu, id_bahan)
);

CREATE INDEX idx_resep_id_menu ON resep(id_menu);
CREATE INDEX idx_resep_id_bahan ON resep(id_bahan);

-- -----------------------------------------------------------------------------
-- 7. TABEL TRANSAKSI (Transaksi Penjualan Kasir POS)
-- -----------------------------------------------------------------------------
CREATE TABLE transaksi (
    id_transaksi SERIAL PRIMARY KEY,
    kode_transaksi VARCHAR(255) UNIQUE NOT NULL,
    nama_customer VARCHAR(100) NULL, -- nama pemesan untuk struk / pemanggilan
    total_item INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    diskon DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    metode_pembayaran VARCHAR(30) NOT NULL, -- tunai / qris
    nominal_bayar DECIMAL(12,2) NOT NULL,
    kembalian DECIMAL(12,2) NOT NULL,
    catatan_transaksi VARCHAR(255) NULL,
    id_user INT NOT NULL,
    id_shift INT NOT NULL,
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaksi_user FOREIGN KEY (id_user)
        REFERENCES users(id_user) ON DELETE RESTRICT,
    CONSTRAINT fk_transaksi_shift FOREIGN KEY (id_shift)
        REFERENCES shift(id_shift) ON DELETE RESTRICT
);

CREATE INDEX idx_transaksi_id_user ON transaksi(id_user);
CREATE INDEX idx_transaksi_id_shift ON transaksi(id_shift);
CREATE INDEX idx_transaksi_kode ON transaksi(kode_transaksi);
CREATE INDEX idx_transaksi_create_at ON transaksi(create_at);

-- -----------------------------------------------------------------------------
-- 8. TABEL DETAIL_TRANSAKSI (Rincian Item Menu Terjual)
-- -----------------------------------------------------------------------------
CREATE TABLE detail_transaksi (
    id_detail SERIAL PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_menu INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL, -- snapshot harga historis saat transaksi
    subtotal DECIMAL(12,2) NOT NULL,
    catatan_item VARCHAR(255) NULL, -- misal: less sugar, es dipisah
    CONSTRAINT fk_detail_transaksi FOREIGN KEY (id_transaksi)
        REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
    CONSTRAINT fk_detail_menu FOREIGN KEY (id_menu)
        REFERENCES menu(id_menu) ON DELETE RESTRICT
);

CREATE INDEX idx_detail_id_transaksi ON detail_transaksi(id_transaksi);
CREATE INDEX idx_detail_id_menu ON detail_transaksi(id_menu);

-- =============================================================================
-- DATA SEEDER AWAL (Akun Default & Sampel Menu Warkop)
-- =============================================================================

-- Akun Default (Password hash: bcrypt)
-- owner / admin123
-- kasir / kasir123
INSERT INTO users (username, password, role, status) VALUES
    ('owner', '$2y$10$D3GvFXArHb/8Ik8RQ5NJNeJyqohmVQHcjDqt/0ukOe.EEH9vT9t0u', 'owner', 'aktif'),
    ('kasir', '$2y$10$KZJgPxJ7St12dOw63IWohe6NqPVdkG/kAdpJCCAeSYFps/mGE8Aou', 'kasir', 'aktif')
ON CONFLICT (username) DO NOTHING;

-- Sampel Menu Warkop dengan HPP & Harga Jual
INSERT INTO menu (nama_menu, kategori, harga, hpp, stok_menu, status) VALUES
    ('Kopi Hitam Tubruk', 'Kopi', 5000.00, 2000.00, 100, 'tersedia'),
    ('Kopi Susu Tradisional', 'Kopi', 8000.00, 3500.00, 100, 'tersedia'),
    ('Es Teh Manis', 'Minuman', 4000.00, 1500.00, 100, 'tersedia'),
    ('Es Coklat Warkop', 'Minuman', 9000.00, 4000.00, 50, 'tersedia'),
    ('Mie Goreng + Telur', 'Makanan', 10000.00, 5500.00, 40, 'tersedia'),
    ('Roti Bakar Coklat Keju', 'Camilan', 12000.00, 6000.00, 30, 'tersedia')
ON CONFLICT DO NOTHING;

-- Sampel Bahan Baku (Persiapan Masa Depan)
INSERT INTO bahan_baku (nama_bahan, stok_bahan, satuan, minimum_stok, status_stok) VALUES
    ('Biji Kopi Robusta Dampit', 2500.00, 'gr', 500.00, 'aman'),
    ('Susu Kental Manis', 3000.00, 'ml', 600.00, 'aman'),
    ('Gula Pasir', 5000.00, 'gr', 1000.00, 'aman'),
    ('Teh Tubruk Melati', 1500.00, 'gr', 300.00, 'aman'),
    ('Mie Instan Goreng', 40.00, 'pcs', 10.00, 'aman'),
    ('Telur Ayam', 30.00, 'butir', 10.00, 'aman')
ON CONFLICT DO NOTHING;
