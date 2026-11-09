<?php
// views/pegawai/dashboard.php
// Tampilan Dashboard Pegawai & Kasir Warkop Kolega - KolegaOps POS
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Pegawai - KolegaOps Warkop Kolega</title>
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
<body class="bg-brand-bgLight text-slate-800 min-h-screen flex text-sm">

  <!-- Sidebar Navigasi KolegaOps -->
  <?php include BASE_PATH . '/views/layouts/sidebar.php'; ?>

  <!-- Area Konten Utama -->
  <main class="flex-1 p-8 overflow-y-auto min-h-screen">
    
    <!-- Header Halaman -->
    <header class="flex items-center justify-between pb-5 border-b border-slate-200 mb-6 bg-white px-8 py-5 rounded-2xl shadow-sm">
      <div>
        <h2 class="text-2xl font-extrabold text-slate-900">Dashboard</h2>
        <p class="text-xs text-slate-500 mt-1">Ringkasan hari ini &bull; <?= htmlspecialchars($tanggalTeks) ?></p>
      </div>
      <div class="text-xs font-bold text-slate-500">
        Diperbarui secara real-time
      </div>
    </header>

    <!-- Notifikasi Flash Sukses / Error -->
    <?php if (!empty($pesanSukses)): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-semibold text-xs flex items-center space-x-3">
        <span>✅</span>
        <span><?= htmlspecialchars($pesanSukses) ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($pesanError)): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-semibold text-xs flex items-center space-x-3">
        <span>⚠️</span>
        <span><?= htmlspecialchars($pesanError) ?></span>
      </div>
    <?php endif; ?>

    <!-- 3 Kartu Statistik Atas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      
      <!-- Kartu 1: Penjualan Hari Ini (Seluruh Kasir) -->
      <div class="bg-brand-navy text-white p-6 rounded-2xl shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-blue-200 uppercase tracking-wider">PENJUALAN HARI INI</p>
        <div class="my-3">
          <h3 class="text-3xl font-extrabold">Rp <?= number_format((float) ($penjualanHariIni['total_penjualan'] ?? 0), 0, ',', '.') ?></h3>
        </div>
        <p class="text-xs text-blue-200">Seluruh kasir &bull; <?= (int) ($penjualanHariIni['jumlah_transaksi'] ?? 0) ?> transaksi lunas</p>
      </div>

      <!-- Kartu 2: Penjualan Shift Saya -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">PENJUALAN SHIFT SAYA</p>
        <div class="my-2">
          <h3 class="text-3xl font-extrabold text-brand-navy">Rp <?= number_format((float) ($penjualanShiftSaya['total_penjualan'] ?? 0), 0, ',', '.') ?></h3>
        </div>
        <p class="text-xs text-slate-500"><?= htmlspecialchars($namaKasir) ?> &bull; <?= (int) ($penjualanShiftSaya['jumlah_transaksi'] ?? 0) ?> transaksi lunas</p>
      </div>

      <!-- Kartu 3: Stok Kritis Perhatian -->
      <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-6 rounded-2xl border border-orange-200 shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-orange-700 uppercase tracking-wider">STOK KRITIS PERHATIAN</p>
        <div class="my-2 flex items-center justify-between">
          <h3 class="text-3xl font-extrabold text-orange-800"><?= count($stokKritis) ?> bahan</h3>
          <span class="text-2xl">⚠️</span>
        </div>
        <p class="text-xs text-orange-600 font-medium">Segera koordinasikan restock bahan</p>
      </div>

    </div>

    <!-- Grid Konten Tengah -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      
      <!-- Kolom Kiri (7 Kolom) -->
      <div class="lg:col-span-7 space-y-6">
        
        <!-- Box Status Shift -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-wrap items-center justify-between gap-4">
          <div>
            <div class="flex items-center space-x-3">
              <h3 class="font-extrabold text-slate-900 text-base">Shift <?= htmlspecialchars($shiftAktif['nama_shift'] ?? 'Kasir') ?></h3>
              <span class="text-xs text-slate-400 font-semibold">
                <?= !empty($shiftAktif['waktu_buka']) ? date('H:i', strtotime($shiftAktif['waktu_buka'])) : '15:00' ?>&ndash;<?= !empty($shiftAktif['waktu_tutup']) ? date('H:i', strtotime($shiftAktif['waktu_tutup'])) : 'Selesai' ?>
              </span>
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium">
              <?php if (($shiftAktif['status'] ?? '') === 'buka'): ?>
                <?= htmlspecialchars($namaKasir) ?> sedang bertugas
              <?php else: ?>
                Shift telah ditutup dan direkap
              <?php endif; ?>
            </p>
          </div>

          <div>
            <?php if (($shiftAktif['status'] ?? '') === 'buka'): ?>
              <div class="flex items-center space-x-3">
                <span class="bg-emerald-100 text-emerald-700 font-bold px-3.5 py-1.5 rounded-full text-xs">Aktif Berjalan</span>
                <button type="button" onclick="bukaModalTutupShift()" 
                        class="rounded-xl bg-brand-navy hover:bg-brand-lightNavy text-white px-4 py-2.5 text-xs font-bold shadow transition cursor-pointer">
                  Tutup Shift
                </button>
              </div>
            <?php else: ?>
              <div class="flex items-center space-x-3">
                <span class="bg-slate-100 text-slate-600 font-bold px-3.5 py-1.5 rounded-full text-xs">
                  Sudah ditutup &bull; Saldo Rp <?= number_format((float) ($shiftAktif['saldo_akhir'] ?? 0), 0, ',', '.') ?>
                </span>
                <a href="index.php?controller=shift&action=laporan" 
                   class="rounded-xl bg-brand-navy text-white px-4 py-2.5 text-xs font-bold shadow hover:bg-brand-lightNavy transition">
                  Lihat Laporan
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Box Transaksi Terbaru Saya -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-900 text-base">Transaksi terbaru saya</h3>
            <a href="index.php?controller=laporan&action=laporanPegawai" class="text-xs font-bold text-brand-navy hover:underline">Semua &rarr;</a>
          </div>
          <table class="w-full text-left text-sm">
            <thead class="bg-brand-darkNavy text-white text-xs uppercase tracking-wider">
              <tr>
                <th class="p-3.5 pl-6">WAKTU</th>
                <th class="p-3.5">NOMOR TRANSAKSI</th>
                <th class="p-3.5">TOTAL</th>
                <th class="p-3.5 pr-6 text-right">STATUS</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
              <?php if (empty($transaksiTerbaru)): ?>
                <tr>
                  <td colspan="4" class="p-6 text-center text-slate-400 italic">Belum ada transaksi pada shift ini.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($transaksiTerbaru as $trx): ?>
                <tr class="hover:bg-blue-50/50 transition">
                  <td class="p-3.5 pl-6 text-slate-500"><?= htmlspecialchars($trx['waktu']) ?></td>
                  <td class="p-3.5 font-bold text-brand-navy"><?= htmlspecialchars($trx['kode_transaksi']) ?></td>
                  <td class="p-3.5 font-extrabold">Rp <?= number_format((float) $trx['total'], 0, ',', '.') ?></td>
                  <td class="p-3.5 pr-6 text-right">
                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg font-bold text-xs inline-block">Lunas</span>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Box Pembayaran Shift Saya -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-900 text-base">Pembayaran shift saya</h3>
          </div>
          <table class="w-full text-left text-sm">
            <thead class="bg-brand-darkNavy text-white text-xs uppercase tracking-wider">
              <tr>
                <th class="p-3.5 pl-6">METODE PEMBAYARAN</th>
                <th class="p-3.5 pr-6 text-right">TOTAL PENJUALAN</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
              <tr>
                <td class="p-3.5 pl-6 font-bold text-slate-700">Cash (Tunai)</td>
                <td class="p-3.5 pr-6 text-right font-extrabold text-brand-navy">
                  Rp <?= number_format((float) ($rekapPembayaran['total_cash'] ?? 0), 0, ',', '.') ?>
                </td>
              </tr>
              <tr>
                <td class="p-3.5 pl-6 font-bold text-slate-700">QRIS (Nontunai)</td>
                <td class="p-3.5 pr-6 text-right font-extrabold text-brand-navy">
                  Rp <?= number_format((float) ($rekapPembayaran['total_qris'] ?? 0), 0, ',', '.') ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

      <!-- Kolom Kanan (5 Kolom) -->
      <div class="lg:col-span-5 space-y-6">
        
        <!-- Box Stok Perlu Perhatian -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-900 text-base">Stok perlu perhatian</h3>
            <a href="index.php?controller=stok_bahan&action=index" class="text-xs font-bold text-brand-navy hover:underline">Lihat stok &rarr;</a>
          </div>
          <table class="w-full text-left text-sm">
            <thead class="bg-brand-darkNavy text-white text-xs uppercase tracking-wider">
              <tr>
                <th class="p-3 pl-6">BAHAN BAKU MENU</th>
                <th class="p-3">SISA</th>
                <th class="p-3 pr-6 text-right">MINIMUM</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
              <?php if (empty($stokKritis)): ?>
                <tr>
                  <td colspan="3" class="p-4 text-center text-slate-400 italic">Semua stok bahan dalam kondisi aman.</td>
                </tr>
              <?php else: ?>
                <?php foreach (array_slice($stokKritis, 0, 4) as $s): ?>
                <tr class="hover:bg-amber-50/40 transition">
                  <td class="p-3 pl-6 font-bold text-orange-600"><?= htmlspecialchars($s['nama_bahan']) ?></td>
                  <td class="p-3 font-semibold"><?= number_format((float) $s['stok_bahan'], 0, ',', '.') ?> <?= htmlspecialchars($s['satuan']) ?></td>
                  <td class="p-3 pr-6 text-right text-slate-500"><?= number_format((float) $s['minimum_stok'], 0, ',', '.') ?> <?= htmlspecialchars($s['satuan']) ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Box Pengeluaran Shift Saya (Interaktif) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-900 text-base">Pengeluaran shift saya</h3>
            <button type="button" onclick="bukaModalTambahPengeluaran()" 
                    class="text-xs font-bold bg-brand-navy text-white px-3.5 py-2 rounded-xl shadow hover:bg-brand-lightNavy transition cursor-pointer">
              Tambah Pengeluaran +
            </button>
          </div>
          <table class="w-full text-left text-sm">
            <thead class="bg-brand-darkNavy text-white text-xs uppercase tracking-wider">
              <tr>
                <th class="p-3 pl-6">KETERANGAN KEPERLUAN</th>
                <th class="p-3 pr-6 text-right">NOMINAL TUNAI</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
              <?php if (empty($pengeluaranShift)): ?>
                <tr>
                  <td colspan="2" class="p-4 text-center text-slate-400 italic">Belum ada pengeluaran dicatat pada shift ini.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($pengeluaranShift as $p): ?>
                <tr>
                  <td class="p-3 pl-6"><?= htmlspecialchars($p['keterangan']) ?></td>
                  <td class="p-3 pr-6 text-right font-bold text-red-600">Rp <?= number_format((float) $p['nominal'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
          <div class="p-3.5 px-6 bg-slate-50 border-t border-slate-100 flex justify-between items-center font-bold text-xs">
            <span class="text-slate-700">Total Pengeluaran Shift</span>
            <span class="text-red-600 text-sm">Rp <?= number_format($totalPengeluaranShift, 0, ',', '.') ?></span>
          </div>
        </div>

      </div>

    </div>

    <!-- MODAL DIALOG: TAMBAH PENGELUARAN SHIFT -->
    <div id="modalPengeluaran" class="fixed inset-0 bg-slate-900/50 hidden items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl relative text-xs space-y-4 animate-in fade-in zoom-in duration-150">
        <div>
          <h3 class="text-lg font-extrabold text-slate-900">Catat Pengeluaran Shift</h3>
          <p class="text-slate-500 mt-0.5">Catat keperluan operasional tunai selama shift berlangsung.</p>
        </div>

        <form action="index.php?controller=dashboard&action=tambahPengeluaran" method="POST" class="space-y-3">
          <?php if (function_exists('csrf_token')): ?>
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <?php endif; ?>
          <input type="hidden" name="id_shift" value="<?= (int) $idShift ?>">

          <div>
            <label class="block font-bold text-slate-700 mb-1">Keterangan Keperluan *</label>
            <input type="text" name="keterangan" id="inputKetPengeluaran" required placeholder="Contoh: Beli es batu / gas / tali"
                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-brand-navy">
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nominal Tunai (Rp) *</label>
            <input type="number" name="nominal" id="inputNominalPengeluaran" required min="1000" step="500" placeholder="Contoh: 15000"
                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-brand-navy">
          </div>

          <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
            <button type="button" onclick="tutupModalTambahPengeluaran()" class="px-4 py-2.5 font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">
              Batal
            </button>
            <button type="submit" class="px-5 py-2.5 font-bold text-white bg-brand-navy hover:bg-brand-lightNavy rounded-xl shadow cursor-pointer">
              Simpan Pengeluaran
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL DIALOG: TUTUP SHIFT -->
    <div id="modalTutupShift" class="fixed inset-0 bg-slate-900/50 hidden items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative text-xs space-y-4 animate-in fade-in zoom-in duration-150">
        <div>
          <h3 class="text-lg font-extrabold text-slate-900">Tutup Shift <?= htmlspecialchars($shiftAktif['nama_shift'] ?? 'Kasir') ?></h3>
          <p class="text-slate-500 mt-0.5">
            <?= htmlspecialchars($namaKasir) ?> &bull; <?= htmlspecialchars($tanggalTeks) ?>
          </p>
        </div>

        <form action="index.php?controller=dashboard&action=tutupShift" method="POST" class="space-y-4">
          <?php if (function_exists('csrf_token')): ?>
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <?php endif; ?>
          <input type="hidden" name="id_shift" value="<?= (int) $idShift ?>">

          <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2 font-medium">
            <div class="flex justify-between">
              <span class="text-slate-500">Modal awal</span>
              <span class="font-bold text-slate-800">Rp <?= number_format($modalAwal, 0, ',', '.') ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500">Penjualan tunai</span>
              <span class="font-bold text-emerald-600">+ Rp <?= number_format($cashMasuk, 0, ',', '.') ?></span>
            </div>
            <div class="flex justify-between pb-2 border-b border-slate-200">
              <span class="text-slate-500">Pengeluaran</span>
              <span class="font-bold text-red-600">- Rp <?= number_format($totalPengeluaranShift, 0, ',', '.') ?></span>
            </div>
            <div class="flex justify-between text-sm pt-1">
              <span class="font-bold text-slate-800">Uang seharusnya di laci</span>
              <span class="font-extrabold text-brand-navy">Rp <?= number_format($uangSeharusnyaLaci, 0, ',', '.') ?></span>
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Uang tunai di laci sekarang (Rp) *</label>
            <input type="number" name="uang_fisik" id="inputUangFisik" oninput="hitungSelisih()" value="<?= (int) $uangSeharusnyaLaci ?>" required
                   class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-brand-navy outline-none focus:ring-2 focus:ring-brand-navy">
          </div>

          <div id="infoSelisihBox" class="p-3 bg-blue-50 text-brand-navy font-bold rounded-xl border border-blue-200 flex items-center justify-between">
            <span>Selisih</span>
            <span id="textSelisih" class="text-sm">Rp 0 &bull; Pas</span>
          </div>

          <p class="text-[11px] text-slate-400">
            Pastikan hitungan uang pas. Setelah ditutup, kasir tidak dapat mencatat transaksi tambahan pada shift ini.
          </p>

          <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
            <button type="button" onclick="tutupModalTutupShift()" class="px-5 py-2.5 font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">
              Batal
            </button>
            <button type="submit" class="px-6 py-2.5 font-bold text-white bg-brand-navy hover:bg-brand-lightNavy rounded-xl shadow cursor-pointer">
              Konfirmasi &amp; Tutup Shift
            </button>
          </div>
        </form>
      </div>
    </div>

  </main>

  <!-- Skrip JavaScript Interaksi Modal & Perhitungan Kas -->
  <script>
    function formatRupiah(num) {
      return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    }

    // Modal Tambah Pengeluaran
    function bukaModalTambahPengeluaran() {
      const modal = document.getElementById('modalPengeluaran');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function tutupModalTambahPengeluaran() {
      const modal = document.getElementById('modalPengeluaran');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.getElementById('inputKetPengeluaran').value = '';
      document.getElementById('inputNominalPengeluaran').value = '';
    }

    // Modal Tutup Shift & Perhitungan Selisih Kas
    const uangSeharusnya = <?= (int) $uangSeharusnyaLaci ?>;

    function hitungSelisih() {
      const fisik = parseInt(document.getElementById('inputUangFisik').value) || 0;
      const selisih = fisik - uangSeharusnya;
      const box = document.getElementById('infoSelisihBox');
      const text = document.getElementById('textSelisih');

      if (selisih === 0) {
        box.className = "p-3 bg-blue-50 text-brand-navy font-bold rounded-xl border border-blue-200 flex items-center justify-between";
        text.innerText = "Rp 0 • Pas";
      } else if (selisih > 0) {
        box.className = "p-3 bg-emerald-50 text-emerald-700 font-bold rounded-xl border border-emerald-200 flex items-center justify-between";
        text.innerText = `+ ${formatRupiah(selisih)} • Lebih`;
      } else {
        box.className = "p-3 bg-red-50 text-red-600 font-bold rounded-xl border border-red-200 flex items-center justify-between";
        text.innerText = `- ${formatRupiah(Math.abs(selisih))} • Kurang`;
      }
    }

    function bukaModalTutupShift() {
      const modal = document.getElementById('modalTutupShift');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      hitungSelisih();
    }

    function tutupModalTutupShift() {
      const modal = document.getElementById('modalTutupShift');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    // Keyboard Escape Listener
    window.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        tutupModalTambahPengeluaran();
        tutupModalTutupShift();
      }
    });

    // Outside Click Listener
    document.getElementById('modalPengeluaran').addEventListener('click', function(e) {
      if (e.target === this) tutupModalTambahPengeluaran();
    });
    document.getElementById('modalTutupShift').addEventListener('click', function(e) {
      if (e.target === this) tutupModalTutupShift();
    });
  </script>
</body>
</html>
