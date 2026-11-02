<?php
// views/owner/tambah-stok.php
// Tampilan Form Tambah Bahan Baku Baru KolegaOps
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Bahan Baku Baru - KolegaOps Warkop</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              navy: '#122B70',
              darkNavy: '#0A1B46',
              lightNavy: '#1D3B8A',
              skyBlue: '#38BDF8',
              bgLight: '#F8FAFC'
            }
          }
        }
      }
    }
  </script>
</head>
<body class="bg-brand-bgLight text-slate-800 min-h-screen flex text-base">

  <!-- Sidebar Navigasi KolegaOps -->
  <?php include BASE_PATH . '/views/layouts/sidebar.php'; ?>

  <!-- Area Konten Utama -->
  <main class="flex-1 p-8 overflow-y-auto min-h-screen">
    
    <!-- Header Halaman -->
    <header class="flex items-center justify-between pb-5 border-b border-slate-200 mb-8 bg-white px-8 py-6 rounded-2xl shadow-sm">
      <div>
        <h2 class="text-3xl font-extrabold text-slate-900">Tambah Bahan Baru</h2>
        <p class="text-sm text-slate-500 mt-1">
          Pencatatan inventaris bahan baku baru &bull; Oleh <?= htmlspecialchars($namaOwner) ?> &bull; <?= htmlspecialchars($tanggal_hari_ini) ?>
        </p>
      </div>
      <a href="index.php?controller=stokBahan&action=index" 
         class="text-sm font-bold text-slate-600 hover:text-brand-navy bg-slate-100 hover:bg-slate-200 border border-slate-200 px-4 py-2.5 rounded-xl transition flex items-center space-x-1">
        <span>&larr;</span>
        <span>Kembali ke Stok</span>
      </a>
    </header>

    <!-- Notifikasi Error Jika Ada -->
    <?php if (!empty($pesanError)): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-semibold text-sm flex items-center space-x-3">
        <span>⚠️</span>
        <span><?= htmlspecialchars($pesanError) ?></span>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      
      <!-- Kolom Kiri: Form Input Data Bahan Baru -->
      <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <h3 class="font-bold text-slate-900 text-lg border-b border-slate-100 pb-4 mb-6 flex items-center space-x-2">
          <span>📦</span>
          <span>Informasi Bahan / Item Baru</span>
        </h3>
        
        <form action="index.php?controller=stokBahan&action=tambah" method="POST" class="space-y-6">
          <?= function_exists('csrf_field') ? csrf_field() : '' ?>
          
          <!-- 1. Nama Bahan -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
              Nama Bahan / Item <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama_bahan" required 
                   placeholder="Contoh: Coklat Bubuk Premium, Keju Parut, Sirup Hazelnut"
                   class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-base font-medium focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-800">
            <p class="text-xs text-slate-400 mt-1.5">Gunakan nama yang spesifik agar kasir dan barista mudah mengenali barang saat operasional.</p>
          </div>

          <!-- 2. Grid Stok Awal & Batas Minimum -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Jumlah Stok Awal <span class="text-red-500">*</span>
              </label>
              <input type="number" step="any" min="0" name="stok_awal" required value="100"
                     class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-base font-medium focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-800">
              <p class="text-xs text-slate-400 mt-1.5">Jumlah fisik barang yang tersedia saat ini di warkop.</p>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Batas Minimum Stok <span class="text-red-500">*</span>
              </label>
              <input type="number" step="any" min="0" name="minimum_stok" required value="20"
                     class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-base font-medium focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-800">
              <p class="text-xs text-slate-400 mt-1.5">Bila stok berada di bawah angka ini, sistem otomatis memberi alert <strong>Kritis</strong>.</p>
            </div>
          </div>

          <!-- 3. Grid Satuan & Kategori Item -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Satuan Ukuran <span class="text-red-500">*</span>
              </label>
              <select name="satuan" required 
                      class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-base font-medium focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-800">
                <option value="gram">gram (gr) — misal: biji kopi, gula, bubuk coklat</option>
                <option value="ml">mililiter (ml) — misal: susu kental manis, sirup</option>
                <option value="liter">liter (L) — misal: susu UHT, minyak</option>
                <option value="kg">kilogram (kg) — misal: es kristal, kentang beku</option>
                <option value="pcs">pcs / buah — misal: telur, cup kopi, sedotan</option>
                <option value="bungkus">bungkus — misal: mie instan, biskuit</option>
                <option value="pack">pack / dus — misal: roti tawar, keju lembaran</option>
                <option value="sachet">sachet — misal: teh celup, bubuk instan</option>
                <option value="botol">botol — misal: air mineral botol</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Kategori Item
              </label>
              <select name="jenis" 
                      class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-base font-medium focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-800">
                <option value="Bahan">Bahan Baku Mentah</option>
                <option value="Menu">Menu Siap Jual</option>
              </select>
            </div>
          </div>

          <!-- 4. Catatan / Alasan Kulakan -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
              Catatan / Keterangan Kulakan (Opsional)
            </label>
            <textarea name="catatan" rows="3" 
                      placeholder="Contoh: Kulakan awal di Agen Sembako Barokah, harga beli Rp 45.000 / kg"
                      class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-800"></textarea>
            <p class="text-xs text-slate-400 mt-1.5">Data penambahan awal akan otomatis tercatat ke dalam histori audit stok warkop.</p>
          </div>

          <!-- 5. Tombol Submit & Batal -->
          <div class="flex items-center space-x-4 pt-4 border-t border-slate-100">
            <button type="submit" 
                    class="bg-brand-navy hover:bg-brand-lightNavy text-white px-7 py-3.5 rounded-xl font-bold text-base shadow transition cursor-pointer flex items-center space-x-2">
              <span>Simpan Bahan Baru</span>
            </button>
            <a href="index.php?controller=stokBahan&action=index" 
               class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-6 py-3.5 rounded-xl font-bold text-base transition text-center inline-block">
              Batal
            </a>
          </div>

        </form>
      </div>

      <!-- Kolom Kanan: Panduan Pengelolaan Stok Warkop -->
      <div class="lg:col-span-4 space-y-6">
        
        <!-- Kartu Panduan Standarisasi Satuan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
          <h4 class="font-bold text-slate-900 text-base mb-3 flex items-center space-x-2">
            <span>💡</span>
            <span>Panduan Satuan Resep</span>
          </h4>
          <ul class="text-xs text-slate-600 space-y-2.5 leading-relaxed">
            <li class="flex items-start space-x-2">
              <span class="text-brand-navy font-bold">&bull;</span>
              <span><strong>Kopi & Gula:</strong> Dianjurkan menggunakan satuan <code>gram</code> agar selaras dengan takaran per cangkir (misal: 15 gr bubuk kopi).</span>
            </li>
            <li class="flex items-start space-x-2">
              <span class="text-brand-navy font-bold">&bull;</span>
              <span><strong>Susu Kental Manis & Sirup:</strong> Dianjurkan menggunakan <code>ml</code> untuk memudahkan pengukuran resep barista.</span>
            </li>
            <li class="flex items-start space-x-2">
              <span class="text-brand-navy font-bold">&bull;</span>
              <span><strong>Produk Kemasan:</strong> Gunakan <code>pcs</code> atau <code>bungkus</code> untuk mie instan, telur, dan air mineral.</span>
            </li>
          </ul>
        </div>

        <!-- Kartu Info Batas Minimum -->
        <div class="bg-blue-50 border border-blue-200 p-6 rounded-2xl text-xs text-blue-900 leading-relaxed space-y-2">
          <p class="font-bold text-sm text-brand-navy">🔔 Cara Kerja Peringatan Kritis:</p>
          <p>
            Sistem secara otomatis mengubah status item menjadi <span class="bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-bold">Kritis</span> bila sisa stok kurang dari batas minimum yang Anda atur.
          </p>
          <p>
            Status kritis ini akan muncul pada kartu ringkasan Dashboard Owner sehingga Anda dapat langsung menjadwalkan kulakan sebelum stok habis saat jam ramai.
          </p>
        </div>

      </div>

    </div>

  </main>
</body>
</html>
