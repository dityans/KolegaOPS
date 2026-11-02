<?php
// views/owner/analisis-profit.php
// Tampilan Laporan Analisis Kontribusi Profit Menu - KolegaOps POS Warkop
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analisis Profit - KolegaOps Warkop Kolega</title>
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
<body class="bg-brand-bgLight text-slate-800 min-h-screen flex text-base relative">

  <!-- Sidebar Navigasi KolegaOps -->
  <?php include BASE_PATH . '/views/layouts/sidebar.php'; ?>

  <!-- Area Konten Utama -->
  <main class="flex-1 p-8 overflow-y-auto min-h-screen">
    
    <!-- Header Halaman -->
    <header class="pb-6 border-b border-slate-200 mb-6 bg-white px-8 py-6 rounded-2xl shadow-sm">
      <h2 class="text-3xl font-extrabold text-slate-900">Laporan Analisis Profit</h2>
      <p class="text-sm text-slate-500 mt-1">Lihat kontribusi keuntungan setiap menu berdasarkan transaksi pada tanggal terpilih.</p>
    </header>

    <!-- 4 Kartu Statistik Ringkasan Profit -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
      
      <!-- Kartu 1: Total Profit Bersih -->
      <div class="bg-brand-navy text-white p-6 rounded-2xl shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-blue-200 uppercase tracking-wider">TOTAL PROFIT BERSIH</p>
        <div class="my-2">
          <h3 class="text-3xl font-extrabold">Rp <?= number_format($totalProfitBersih, 0, ',', '.') ?></h3>
        </div>
        <p class="text-xs text-blue-200">dari <?= (int) $totalPorsiTerjual ?> porsi terjual</p>
      </div>

      <!-- Kartu 2: Total Porsi Terjual -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">TOTAL PORSI TERJUAL</p>
        <div class="my-2">
          <h3 class="text-3xl font-extrabold text-slate-900"><?= (int) $totalPorsiTerjual ?> porsi</h3>
        </div>
        <p class="text-xs text-slate-500 truncate" title="<?= htmlspecialchars($filterTanggalTeks) ?>">
          Tanggal: <?= htmlspecialchars($filterTanggalTeks) ?>
        </p>
      </div>

      <!-- Kartu 3: Filter Kategori Aktif -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">FILTER AKTIF</p>
        <div class="my-2">
          <h3 class="text-2xl font-extrabold text-brand-navy truncate" title="<?= htmlspecialchars($filterKategori) ?>">
            <?= htmlspecialchars($filterKategori) ?>
          </h3>
        </div>
        <p class="text-xs text-slate-500">Kategori menu terpilih</p>
      </div>

      <!-- Kartu 4: Rata-Rata Profit per Porsi -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">RATA-RATA PROFIT / PORSI</p>
        <div class="my-2">
          <h3 class="text-3xl font-extrabold text-brand-navy">Rp <?= number_format($rataRataProfitPorsi, 0, ',', '.') ?></h3>
        </div>
        <p class="text-xs text-slate-500">Total profit &divide; porsi terjual</p>
      </div>

    </div>

    <!-- Filter Bar Interaktif -->
    <form method="GET" action="index.php" class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
      <input type="hidden" name="controller" value="laporan">
      <input type="hidden" name="action" value="profit">

      <div class="md:col-span-5">
        <input type="date" name="tanggal" value="<?= htmlspecialchars((string) $filterTanggal) ?>" 
               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-700 font-medium cursor-pointer">
      </div>

      <div class="md:col-span-5">
        <select name="kategori" 
                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-700 font-medium cursor-pointer">
          <option value="Semua" <?= $filterKategori === 'Semua' ? 'selected' : '' ?>>Semua Kategori</option>
          
          <?php 
          // Opsi kategori dari database jika tersedia, digabung dengan opsi standar
          $katOptions = ['Kopi', 'Minuman', 'Makanan', 'Camilan'];
          if (!empty($daftarKategori)) {
              $katOptions = array_values(array_unique(array_merge($katOptions, $daftarKategori)));
          }
          foreach ($katOptions as $kat):
          ?>
            <option value="<?= htmlspecialchars($kat) ?>" <?= strcasecmp($filterKategori, $kat) === 0 ? 'selected' : '' ?>>
              <?= htmlspecialchars($kat) ?>
            </option>
          <?php endforeach; ?>
          <option value="Coffee" <?= strcasecmp($filterKategori, 'Coffee') === 0 ? 'selected' : '' ?>>Coffee (Kopi)</option>
          <option value="Non-Coffee" <?= strcasecmp($filterKategori, 'Non-Coffee') === 0 ? 'selected' : '' ?>>Non-Coffee</option>
        </select>
      </div>

      <div class="md:col-span-2">
        <button type="submit" 
                class="w-full bg-brand-navy hover:bg-brand-lightNavy text-white py-3.5 rounded-xl font-bold text-sm shadow transition cursor-pointer">
          Terapkan Filter
        </button>
      </div>
    </form>

    <!-- Tabel Ranking Kontribusi Profit Menu -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h3 class="font-bold text-slate-900 text-base">Ranking Kontribusi Profit Menu</h3>
        <span class="text-xs text-slate-400 font-semibold">Diurutkan berdasarkan total profit tertinggi &rarr; terendah</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm" id="tableProfit">
          <thead class="bg-brand-darkNavy text-white">
            <tr>
              <th class="p-4 pl-6">RANK</th>
              <th class="p-4">MENU</th>
              <th class="p-4">KATEGORI</th>
              <th class="p-4">TERJUAL</th>
              <th class="p-4">HPP / UNIT</th>
              <th class="p-4">HARGA JUAL</th>
              <th class="p-4">PROFIT / UNIT</th>
              <th class="p-4 pr-6 text-right">TOTAL PROFIT</th>
            </tr>
          </thead>
          <tbody class="font-medium text-slate-800 divide-y divide-slate-100">
            <?php if (!empty($filteredProfit)): ?>
              <?php foreach ($filteredProfit as $index => $row): 
                $bgRow = ($index % 2 === 1) ? 'bg-blue-100/70' : 'bg-white';
              ?>
              <tr class="<?= $bgRow ?> hover:bg-blue-50/60 transition">
                <td class="p-4 pl-6 font-bold text-slate-600">
                  <?php if ($row['rank'] === 1): ?>
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-800 font-extrabold text-xs">🥇 1</span>
                  <?php elseif ($row['rank'] === 2): ?>
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-extrabold text-xs">🥈 2</span>
                  <?php elseif ($row['rank'] === 3): ?>
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-50 text-amber-700 border border-amber-300 font-extrabold text-xs">🥉 3</span>
                  <?php else: ?>
                    <span class="text-slate-500 font-semibold pl-2"><?= $row['rank'] ?></span>
                  <?php endif; ?>
                </td>
                <td class="p-4 font-bold text-brand-navy"><?= htmlspecialchars($row['menu']) ?></td>
                <td class="p-4 text-slate-600">
                  <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md text-xs font-semibold">
                    <?= htmlspecialchars($row['kategori']) ?>
                  </span>
                </td>
                <td class="p-4 text-slate-700 font-semibold"><?= (int) $row['terjual'] ?> porsi</td>
                <td class="p-4 text-slate-600">Rp <?= number_format((float) $row['hpp'], 0, ',', '.') ?></td>
                <td class="p-4 text-slate-600">Rp <?= number_format((float) $row['jual'], 0, ',', '.') ?></td>
                <td class="p-4 text-slate-700 font-semibold">Rp <?= number_format((float) $row['profit_unit'], 0, ',', '.') ?></td>
                <td class="p-4 pr-6 text-right font-bold text-emerald-600 text-base">
                  Rp <?= number_format((float) $row['total_profit'], 0, ',', '.') ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="p-8 text-center text-slate-400 font-medium italic">
                  Tidak ada data penjualan/profit untuk tanggal atau kategori tersebut.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex flex-wrap justify-between items-center text-sm font-bold text-slate-700 gap-4">
        <span>Total periode ini: <span class="text-brand-navy ml-2"><?= (int) $totalPorsiTerjual ?> porsi</span></span>
        <div class="flex items-center space-x-6">
          <span class="text-xs text-slate-500 font-normal">Filter: <?= htmlspecialchars($filterKategori) ?></span>
          <span class="text-emerald-600 text-base font-extrabold">Rp <?= number_format($totalProfitBersih, 0, ',', '.') ?></span>
        </div>
      </div>

      <div class="px-6 py-3 bg-white border-t border-slate-100 flex justify-between items-center text-sm text-slate-500 font-medium">
        <span>Menampilkan <?= count($filteredProfit) ?> menu terjual</span>
        <span class="font-bold text-brand-navy">&larr; Halaman 1 dari 1 &rarr;</span>
      </div>
    </div>

    <!-- Catatan Pedoman Operasional -->
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl text-xs text-blue-900 font-medium">
      💡 <strong>Rumus Perhitungan:</strong> <code>Profit per Unit = Harga Jual - HPP</code> &bull; <code>Total Profit = Profit per Unit &times; Porsi Terjual</code>. Laporan ini mengevaluasi menu dengan margin keuntungan terbaik untuk pertimbangan promosi warkop.
    </div>

  </main>

</body>
</html>
