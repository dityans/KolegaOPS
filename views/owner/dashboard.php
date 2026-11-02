<?php
// views/owner/dashboard.php
// Tampilan Dashboard Utama Pemilik (Owner) KolegaOps
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Owner - KolegaOps Warkop Kolega</title>
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
              bgLight: '#F1F5F9'
            }
          }
        }
      }
    }
  </script>
</head>
<body class="bg-brand-bgLight text-slate-800 min-h-screen flex text-sm">

  <!-- Navigasi Sidebar KolegaOps -->
  <?php include BASE_PATH . '/views/layouts/sidebar.php'; ?>

  <!-- Area Konten Utama -->
  <main class="flex-1 p-8 overflow-y-auto min-h-screen">

    <!-- Header Halaman -->
    <header class="flex items-center justify-between pb-5 border-b border-slate-200 mb-6 bg-white px-8 py-5 rounded-2xl shadow-sm">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900">Dashboard</h2>
        <p class="text-xs text-slate-500 mt-1">Ringkasan hari ini &bull; <?= htmlspecialchars($tanggal_hari_ini) ?></p>
      </div>
      <div class="text-xs font-bold text-slate-500 bg-slate-100 px-3.5 py-2 rounded-xl">
        Diperbarui <?= htmlspecialchars($waktu_update) ?>
      </div>
    </header>

    <!-- 3 Kartu Statistik Atas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      
      <!-- 1. Total Penjualan Hari Ini -->
      <div class="bg-brand-navy text-white p-6 rounded-2xl shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-blue-200 uppercase tracking-wider">TOTAL PENJUALAN</p>
        <div class="my-3">
          <h3 class="text-3xl lg:text-4xl font-extrabold"><?= htmlspecialchars($total_penjualan) ?></h3>
        </div>
        <p class="text-xs text-blue-200"><?= htmlspecialchars($persen_vs_kemarin) ?></p>
      </div>

      <!-- 2. Jumlah Transaksi & Rata-rata -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">JUMLAH TRANSAKSI</p>
        <div class="my-2">
          <h3 class="text-3xl font-extrabold text-brand-navy"><?= (int) $jumlah_transaksi ?></h3>
        </div>
        <p class="text-xs text-slate-500">Lunas &bull; Rata-rata <?= htmlspecialchars($rata_rata_transaksi) ?></p>
      </div>

      <!-- 3. Stok Bahan Kritis -->
      <div class="bg-white p-6 rounded-2xl border border-red-200 shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-red-600 uppercase tracking-wider">STOK KRITIS</p>
        <div class="my-2">
          <h3 class="text-3xl font-extrabold text-red-600"><?= (int) $stok_kritis_count ?> bahan</h3>
        </div>
        <a href="index.php?controller=stok_bahan&action=index" class="text-xs font-bold text-brand-navy hover:underline">
          Lihat stok &rarr;
        </a>
      </div>

    </div>

    <!-- Grid Konten Tengah -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">

      <!-- Kolom Kiri: Grafik Pendapatan 7 Hari & Rincian Pembayaran -->
      <div class="lg:col-span-7 space-y-6">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
          <div class="p-6 pb-2 flex justify-between items-center">
            <h3 class="font-bold text-slate-900 text-base">Pendapatan 7 hari terakhir</h3>
            <span class="text-xs text-slate-600 font-bold bg-slate-100 px-3 py-1 rounded-lg">
              Total <?= htmlspecialchars($total_pendapatan_mingguan) ?>
            </span>
          </div>

          <!-- Diagram Batang Dinamis -->
          <div class="h-96 px-6 pt-6 pb-4 flex items-end justify-between gap-3 sm:gap-4">
            <?php foreach ($pendapatan_mingguan as $bar): ?>
              <div class="flex flex-col items-center flex-1 h-full justify-end">
                <span class="text-[10px] font-bold <?= $bar['is_today'] ? 'text-brand-navy' : 'text-slate-500' ?> mb-1">
                  <?= htmlspecialchars($bar['label_nominal']) ?>
                </span>
                <div class="w-full <?= $bar['is_today'] ? 'bg-brand-navy' : 'bg-blue-200 hover:bg-brand-skyBlue' ?> rounded-t-lg transition-all duration-300" 
                     style="height: <?= (int) $bar['tinggi_persen'] ?>%;" 
                     title="Tanggal: <?= htmlspecialchars($bar['tanggal']) ?> - Total: Rp <?= number_format($bar['nominal'], 0, ',', '.') ?>"></div>
                <span class="text-xs font-bold <?= $bar['is_today'] ? 'text-slate-900 font-black' : 'text-slate-600' ?> mt-2">
                  <?= htmlspecialchars($bar['hari']) ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Komposisi Cash dan QRIS Hari Ini -->
          <div class="bg-brand-darkNavy text-white px-8 py-5 mt-4 grid grid-cols-2 divide-x divide-blue-900/60 w-full">
            <div class="pr-6">
              <p class="text-[11px] font-bold text-blue-300 uppercase tracking-wider">CASH HARI INI</p>
              <h4 class="text-xl font-extrabold mt-1"><?= htmlspecialchars($cash_hari_ini) ?></h4>
            </div>
            <div class="pl-8">
              <p class="text-[11px] font-bold text-blue-300 uppercase tracking-wider">QRIS HARI INI</p>
              <h4 class="text-xl font-extrabold mt-1"><?= htmlspecialchars($qris_hari_ini) ?></h4>
            </div>
          </div>
        </div>

      </div>

      <!-- Kolom Kanan: Transaksi Terbaru & Produk Terlaris -->
      <div class="lg:col-span-5 space-y-6">

        <!-- 1. Transaksi Terbaru -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-900 text-sm">Transaksi terbaru</h3>
            <a href="index.php?controller=laporan&action=index" class="text-xs font-bold text-brand-navy hover:underline">Semua &rarr;</a>
          </div>
          <table class="w-full text-left text-xs">
            <thead class="bg-brand-darkNavy text-white">
              <tr>
                <th class="p-3 pl-6">WAKTU</th>
                <th class="p-3">KASIR</th>
                <th class="p-3">TOTAL</th>
                <th class="p-3 pr-6 text-right">STATUS</th>
              </tr>
            </thead>
            <tbody class="font-medium text-slate-800">
              <?php if (empty($transaksi_terbaru)): ?>
                <tr>
                  <td colspan="4" class="p-6 text-center text-slate-400 italic">Belum ada transaksi hari ini.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($transaksi_terbaru as $idx => $trx): ?>
                  <tr class="<?= ($idx % 2 === 1) ? 'bg-blue-50/60' : 'bg-white' ?> border-b border-slate-100">
                    <td class="p-3 pl-6 text-slate-500"><?= htmlspecialchars(format_waktu($trx['create_at'])) ?></td>
                    <td class="p-3 font-bold text-brand-navy"><?= htmlspecialchars(ucfirst($trx['kasir'])) ?></td>
                    <td class="p-3 font-bold"><?= htmlspecialchars(format_rupiah($trx['total'])) ?></td>
                    <td class="p-3 pr-6 text-right">
                      <span class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-lg font-bold">Lunas</span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- 2. Produk Terlaris Hari Ini -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-900 text-sm">Produk terlaris hari ini</h3>
            <span class="text-[11px] text-slate-400 font-semibold">Berdasarkan jumlah</span>
          </div>
          <table class="w-full text-left text-xs">
            <thead class="bg-brand-darkNavy text-white">
              <tr>
                <th class="p-3 pl-6">PRODUK</th>
                <th class="p-3">TERJUAL</th>
                <th class="p-3 pr-6 text-right">PENJUALAN</th>
              </tr>
            </thead>
            <tbody class="font-medium text-slate-800">
              <?php if (empty($produk_terlaris)): ?>
                <tr>
                  <td colspan="3" class="p-6 text-center text-slate-400 italic">Belum ada data penjualan produk.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($produk_terlaris as $idx => $prod): ?>
                  <tr class="<?= ($idx % 2 === 1) ? 'bg-blue-50/60' : 'bg-white' ?> border-b border-slate-100 last:border-b-0">
                    <td class="p-3 pl-6 font-bold text-slate-900"><?= htmlspecialchars($prod['nama_menu']) ?></td>
                    <td class="p-3 font-semibold"><?= (int) $prod['terjual'] ?></td>
                    <td class="p-3 pr-6 text-right font-bold text-brand-navy">
                      <?= htmlspecialchars(format_rupiah($prod['total_penjualan'])) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>

    <!-- Footer Bar Operasional Real-time -->
    <div class="bg-blue-50 border border-blue-200 p-4 rounded-2xl text-xs text-blue-900 font-medium flex items-center justify-between">
      <span><?= htmlspecialchars($info_shift) ?></span>
      <a href="index.php?controller=laporan&action=index" class="font-bold text-brand-navy hover:underline">
        Lihat Laporan Lengkap &rarr;
      </a>
    </div>

  </main>
</body>
</html>
