<?php
// views/owner/stok.php
// Tampilan Daftar Stok Bahan Baku & Inventaris Warkop KolegaOps
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stok Bahan - KolegaOps Warkop Kolega</title>
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

    <!-- Header Halaman & Tombol Tambah Stok -->
    <header class="flex items-center justify-between pb-6 border-b border-slate-200 mb-6 bg-white px-8 py-6 rounded-2xl shadow-sm">
      <div>
        <h2 class="text-3xl font-extrabold text-slate-900">Stok Bahan Baku</h2>
        <p class="text-sm text-slate-500 mt-1">Kondisi persediaan seluruh warkop &bull; <?= htmlspecialchars($tanggal_hari_ini) ?></p>
      </div>
    </header>

    <!-- Notifikasi Sukses / Error -->
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

    <!-- 3 Kartu Statistik Stok -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      
      <!-- Kartu 1: Total Stok -->
      <div class="bg-brand-navy text-white p-6 rounded-2xl shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-blue-200 uppercase tracking-wider">TOTAL STOK TERCATAT</p>
        <div class="my-3">
          <h3 class="text-4xl font-extrabold"><?= (int) $totalItem ?> item</h3>
        </div>
        <p class="text-xs text-blue-200"><?= (int) $totalBahan ?> bahan baku &bull; <?= (int) $totalMenu ?> menu kemasan</p>
      </div>

      <!-- Kartu 2: Stok Aman -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">STOK AMAN</p>
        <div class="my-3">
          <h3 class="text-3xl font-extrabold text-brand-navy"><?= (int) $totalAman ?> item</h3>
        </div>
        <p class="text-xs text-slate-500">Memenuhi batas minimum operasional</p>
      </div>

      <!-- Kartu 3: Perlu Restock -->
      <div class="bg-white p-6 rounded-2xl border-2 <?= $totalKritis > 0 ? 'border-red-500' : 'border-slate-200' ?> shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-red-600 uppercase tracking-wider">PERLU RESTOCK</p>
        <div class="my-3">
          <h3 class="text-3xl font-extrabold text-red-600"><?= (int) $totalKritis ?> bahan</h3>
        </div>
        <p class="text-xs text-red-500 font-medium truncate" title="<?= htmlspecialchars($kritisTeks) ?>">
          <?= htmlspecialchars($kritisTeks) ?>
        </p>
      </div>

    </div>

    <!-- Filter & Pencarian Interaktif -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
      <div class="md:col-span-6 relative">
        <input type="text" id="searchStok" placeholder="Cari nama bahan atau item..." onkeyup="filterTable()" 
               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-brand-skyBlue shadow-sm">
      </div>
      <div class="md:col-span-3">
        <select id="filterJenis" onchange="filterTable()" 
                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-700 font-medium">
          <option value="">Semua jenis</option>
          <option value="Bahan">Bahan Baku Mentah</option>
          <option value="Menu">Menu Kemasan Siap Jual</option>
        </select>
      </div>
      <div class="md:col-span-3">
        <select id="filterStatus" onchange="filterTable()" 
                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-700 font-medium">
          <option value="">Semua status</option>
          <option value="Aman">Status: Aman</option>
          <option value="Kritis">Status: Kritis</option>
        </select>
      </div>
    </div>

    <!-- Tabel Detail Stok -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h3 class="font-bold text-slate-900 text-base">Detail Stok Bahan Baku</h3>
        <span class="text-xs text-slate-400 font-semibold">Satuan mengikuti masing-masing item</span>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm" id="stokTable">
          <thead class="bg-brand-darkNavy text-white">
            <tr>
              <th class="p-4 pl-6">NAMA BAHAN/MENU</th>
              <th class="p-4">STOK SAAT INI</th>
              <th class="p-4">MINIMUM</th>
              <th class="p-4">SATUAN</th>
              <th class="p-4">STATUS</th>
              <th class="p-4">AKSI</th>
            </tr>
          </thead>
          <tbody class="font-medium text-slate-800">
            <?php if (empty($daftarStok)): ?>
              <tr>
                <td colspan="6" class="p-8 text-center text-slate-400 italic">Belum ada data stok bahan baku di database.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($daftarStok as $idx => $s): 
                $bgRow = ($idx % 2 === 1) ? 'bg-blue-50/60' : 'bg-white';
              ?>
              <tr class="stok-row <?= $bgRow ?> border-b border-slate-100 hover:bg-blue-100/40 transition" 
                  data-status="<?= htmlspecialchars($s['status']) ?>"
                  data-jenis="<?= htmlspecialchars($s['jenis'] ?? 'Bahan') ?>">
                <td class="p-4 pl-6 font-bold text-brand-navy"><?= htmlspecialchars($s['nama']) ?></td>
                <td class="p-4 font-bold"><?= number_format((float) $s['stok'], 0, ',', '.') ?></td>
                <td class="p-4 text-slate-600"><?= number_format((float) $s['minimum'], 0, ',', '.') ?></td>
                <td class="p-4 text-slate-600"><?= htmlspecialchars($s['satuan']) ?></td>
                <td class="p-4">
                  <?php if ($s['status'] === 'Aman'): ?>
                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg font-bold text-xs inline-block">Aman</span>
                  <?php else: ?>
                    <span class="bg-red-100 text-red-600 px-3 py-1 rounded-lg font-bold text-xs inline-block">Kritis</span>
                  <?php endif; ?>
                </td>
                <td class="p-4 flex items-center space-x-2">
                  <a href="index.php?controller=stok_bahan&action=update&id=<?= (int) $s['id'] ?>" 
                     class="text-brand-navy font-bold hover:underline inline-flex items-center space-x-1">
                    <span>Update</span>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-between items-center text-sm text-slate-500 font-medium">
        <span id="stokCount">Menampilkan <?= count($daftarStok) ?> dari <?= count($daftarStok) ?> item</span>
        <span class="font-bold text-brand-navy">&larr; Halaman 1 dari 1 &rarr;</span>
      </div>
    </div>

    <!-- Catatan Pedoman Operasional -->
    <div class="bg-blue-50 border border-blue-200 p-4 rounded-2xl text-sm text-blue-900 font-medium">
      💡 <strong>Petunjuk Operasional:</strong> Satuan stok berbeda-beda berdasarkan jenis bahan (gram, ml, pcs, butir). Lakukan restock segera untuk bahan yang berstatus <strong>Kritis</strong> guna kelancaran operasional warkop.
    </div>

  </main>

  <script>
    function filterTable() {
      const search = document.getElementById('searchStok').value.toLowerCase().trim();
      const jenisFilter = document.getElementById('filterJenis').value;
      const statusFilter = document.getElementById('filterStatus').value;
      const rows = document.querySelectorAll('.stok-row');
      let visibleCount = 0;

      rows.forEach(row => {
        const namaBahan = row.cells[0].textContent.toLowerCase();
        const jenis = row.getAttribute('data-jenis');
        const status = row.getAttribute('data-status');

        const matchesSearch = (search === "" || namaBahan.includes(search));
        const matchesJenis  = (jenisFilter === "" || jenis === jenisFilter);
        const matchesStatus = (statusFilter === "" || status === statusFilter);

        if (matchesSearch && matchesJenis && matchesStatus) {
          row.style.display = "";
          visibleCount++;
        } else {
          row.style.display = "none";
        }
      });

      const totalAwal = <?= count($daftarStok) ?>;
      document.getElementById('stokCount').textContent = `Menampilkan ${visibleCount} dari ${totalAwal} item`;
    }
  </script>
</body>
</html>
