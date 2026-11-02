<?php
// views/owner/produk.php
// Tampilan Daftar Katalog Produk & Menu Owner - KolegaOps POS Warkop
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Produk Owner - KolegaOps Warkop Kolega</title>
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
    <header class="flex items-center justify-between pb-6 border-b border-slate-200 mb-6 bg-white px-8 py-6 rounded-2xl shadow-sm">
      <div>
        <h2 class="text-3xl font-extrabold text-slate-900">Produk</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola menu Warkop Kolega &bull; <?= (int) $totalAktif ?> produk aktif</p>
      </div>
      <a href="index.php?controller=menu&action=tambah" 
         class="bg-brand-navy hover:bg-brand-lightNavy text-white px-6 py-3.5 rounded-xl font-bold text-base shadow transition flex items-center space-x-2 no-underline cursor-pointer">
        <span>+ Tambah Produk</span>
      </a>
    </header>

    <!-- Notifikasi Flash Sukses / Error -->
    <?php if (!empty($pesanSukses)): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-semibold text-sm flex items-center space-x-3">
        <span>✅</span>
        <span><?= htmlspecialchars($pesanSukses) ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($pesanError)): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-semibold text-sm flex items-center space-x-3">
        <span>⚠️</span>
        <span><?= htmlspecialchars($pesanError) ?></span>
      </div>
    <?php endif; ?>

    <!-- Kartu Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      
      <!-- Kartu 1: Produk Aktif -->
      <div class="bg-brand-navy text-white p-6 rounded-2xl shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-blue-200 uppercase tracking-wider">PRODUK AKTIF</p>
        <div class="my-3">
          <h3 class="text-4xl font-extrabold"><?= (int) $totalAktif ?> produk</h3>
        </div>
        <p class="text-xs text-blue-200">Seluruh harga dalam rupiah</p>
      </div>

      <!-- Kartu 2: Rentang Harga Jual -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">RENTANG HARGA JUAL</p>
        <div class="my-3">
          <h3 class="text-3xl font-extrabold text-brand-navy">
            <?php if ($minHarga > 0 || $maxHarga > 0): ?>
              Rp <?= number_format($minHarga, 0, ',', '.') ?>&ndash;<?= number_format($maxHarga, 0, ',', '.') ?>
            <?php else: ?>
              Rp 0
            <?php endif; ?>
          </h3>
        </div>
        <p class="text-xs text-slate-500">Harga per porsi / unit</p>
      </div>

    </div>

    <!-- Filter & Pencarian Dinamis -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
      <div class="md:col-span-6 relative">
        <input type="text" id="searchProduct" placeholder="Cari nama produk..." onkeyup="filterTable()" 
               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-brand-skyBlue shadow-sm">
      </div>
      <div class="md:col-span-3">
        <select id="filterCategory" onchange="filterTable()" 
                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-700 font-medium">
          <option value="">Semua kategori</option>
          <option value="Kopi">Kopi</option>
          <option value="Minuman">Minuman</option>
          <option value="Makanan">Makanan</option>
          <option value="Camilan">Camilan</option>
        </select>
      </div>
      <div class="md:col-span-3">
        <select id="filterStatus" onchange="filterTable()" 
                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-700 font-medium">
          <option value="">Semua status</option>
          <option value="Aktif">Aktif</option>
          <option value="Non-Aktif">Non-Aktif</option>
        </select>
      </div>
    </div>

    <!-- Tabel Daftar Produk Dinamis -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h3 class="font-bold text-slate-900 text-base">Daftar Produk</h3>
        <span class="text-xs text-slate-400 font-semibold">Harga per porsi / unit</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm" id="productTable">
          <thead class="bg-brand-darkNavy text-white">
            <tr>
              <th class="p-4 pl-6">NAMA PRODUK</th>
              <th class="p-4">KATEGORI</th>
              <th class="p-4">HPP</th>
              <th class="p-4">HARGA JUAL</th>
              <th class="p-4">KEUNTUNGAN</th>
              <th class="p-4">STATUS</th>
              <th class="p-4">AKSI</th>
            </tr>
          </thead>
          <tbody class="font-medium text-slate-800">
            <?php if (empty($daftarProduk)): ?>
              <tr>
                <td colspan="7" class="p-8 text-center text-slate-400 italic">Belum ada data produk di database.</td>
              </tr>
            <?php else: ?>
              <?php $no = 0; foreach ($daftarProduk as $p): $no++;
                $keuntungan = (float) $p['keuntungan'];
                $bgRow = ($no % 2 === 0) ? 'bg-blue-50/60' : 'bg-white';
                $statusLabel = $p['status_label'] ?? 'Aktif';
              ?>
              <tr class="product-row <?= $bgRow ?> border-b border-slate-100 hover:bg-blue-100/40 transition" 
                  data-category="<?= htmlspecialchars($p['kategori']) ?>" 
                  data-status="<?= htmlspecialchars($statusLabel) ?>">
                <td class="p-4 pl-6 font-bold text-brand-navy"><?= htmlspecialchars($p['nama']) ?></td>
                <td class="p-4 text-slate-600"><?= htmlspecialchars($p['kategori']) ?></td>
                <td class="p-4 text-slate-600">Rp <?= number_format((float) $p['hpp'], 0, ',', '.') ?></td>
                <td class="p-4 font-bold">Rp <?= number_format((float) $p['harga'], 0, ',', '.') ?></td>
                <td class="p-4 font-bold text-emerald-600">Rp <?= number_format($keuntungan, 0, ',', '.') ?></td>
                <td class="p-4">
                  <?php if ($statusLabel === 'Aktif'): ?>
                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg font-bold text-xs inline-block">Aktif</span>
                  <?php else: ?>
                    <span class="bg-slate-200 text-slate-600 px-3 py-1 rounded-lg font-bold text-xs inline-block">Non-Aktif</span>
                  <?php endif; ?>
                </td>
                <td class="p-4 flex items-center space-x-2">
                  <a href="index.php?controller=menu&action=edit&id=<?= (int) $p['id'] ?>" 
                     class="text-brand-navy font-bold hover:underline inline-flex items-center space-x-1">
                    <span>Edit</span>
                  </a>
                  <a href="index.php?controller=menu&action=hapus&id=<?= (int) $p['id'] ?>" 
                     class="text-red-600 font-bold hover:text-red-800 hover:underline inline-flex items-center space-x-1 ml-4"
                     onclick="return confirm('Apakah Anda yakin ingin menghapus produk \'<?= htmlspecialchars(addslashes($p['nama'])) ?>\'?');">
                    <span>Hapus</span>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-between items-center text-sm text-slate-500 font-medium">
        <span id="productCount">Menampilkan <?= count($daftarProduk) ?> dari <?= count($daftarProduk) ?> produk</span>
        <span class="font-bold text-brand-navy">&larr; Halaman 1 dari 1 &rarr;</span>
      </div>
    </div>

    <!-- Catatan Pedoman Operasional -->
    <div class="bg-blue-50 border border-blue-200 p-4 rounded-2xl text-sm text-blue-900 font-medium">
      💡 <strong>Catatan:</strong> Estimasi keuntungan = harga jual &ndash; HPP per unit. Belum termasuk pengeluaran operasional. Harga dan HPP hanya dapat dikelola oleh Owner.
    </div>

  </main>

  <!-- Skrip Filter & Pencarian Client-Side -->
  <script>
    function filterTable() {
      const input = document.getElementById('searchProduct').value.toLowerCase().trim();
      const categoryFilter = document.getElementById('filterCategory').value.toLowerCase();
      const statusFilter = document.getElementById('filterStatus').value.toLowerCase();
      const rows = document.querySelectorAll('.product-row');
      let visibleCount = 0;

      rows.forEach(row => {
        const productName = row.cells[0].textContent.toLowerCase();
        const category = (row.getAttribute('data-category') || '').toLowerCase();
        const status = (row.getAttribute('data-status') || '').toLowerCase();

        const matchesSearch = (input === "" || productName.includes(input));
        const matchesCategory = (categoryFilter === "" || category === categoryFilter);
        const matchesStatus = (statusFilter === "" || status === statusFilter);

        if (matchesSearch && matchesCategory && matchesStatus) {
          row.style.display = "";
          visibleCount++;
        } else {
          row.style.display = "none";
        }
      });

      const totalAwal = <?= count($daftarProduk) ?>;
      document.getElementById('productCount').textContent = `Menampilkan ${visibleCount} dari ${totalAwal} produk`;
    }
  </script>
</body>
</html>
