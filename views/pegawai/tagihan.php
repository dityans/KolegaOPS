<?php
// views/pegawai/tagihan.php
// Tampilan Konfirmasi Pembayaran & Pratinjau Struk Kasir - KolegaOps
$itemsPesanan = $transaksi['items'] ?? [];
$isTunai      = in_array(strtolower((string) $transaksi['metode_pembayaran']), ['tunai', 'cash'], true);
$metodeLabel  = $isTunai ? 'Cash' : 'QRIS';
$nominalBayar = (float) ($transaksi['nominal_bayar'] ?? $transaksi['total']);
$kembalian    = (float) ($transaksi['kembalian'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran &amp; Struk - KolegaOps</title>
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

  <!-- Sidebar Navigasi Dinamis KolegaOps -->
  <?php require_once BASE_PATH . '/views/layouts/sidebar.php'; ?>

  <!-- Konten Utama -->
  <main class="flex-1 p-8 overflow-y-auto min-h-screen">
    
    <!-- Header Atas Layar Pembayaran -->
    <header class="bg-white border border-slate-200 px-6 py-4 rounded-xl flex justify-between items-center shadow-sm mb-6">
      <div>
        <h1 class="text-xl font-extrabold text-brand-navy">Pembayaran</h1>
        <p class="text-xs text-slate-500 mt-0.5">
          <span><?= htmlspecialchars($namaKasirLabel ?? 'Kasir') ?></span> &bull; Shift <?= htmlspecialchars($transaksi['nama_shift'] ?? 'Sore') ?> &bull; <span><?= htmlspecialchars($tanggalTeks ?? date('d M Y')) ?></span>
        </p>
      </div>
      <span class="bg-blue-50 text-brand-navy border border-blue-200 px-3 py-1 rounded-full text-xs font-bold">
        Shift aktif
      </span>
    </header>

    <div class="grid grid-cols-12 gap-6">
      
      <!-- Kolom Kiri: Informasi Transaksi & Detail Pesanan -->
      <div class="col-span-7 space-y-6">
        
        <!-- Box Informasi Transaksi -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <div class="flex justify-between items-center pb-4 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-900 text-sm">Informasi transaksi</h3>
            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Terkonfirmasi</span>
          </div>

          <div class="grid grid-cols-2 gap-y-4 text-xs">
            <div>
              <p class="text-slate-400 font-medium">ID transaksi</p>
              <p class="font-extrabold text-brand-navy text-sm mt-0.5"><?= htmlspecialchars($transaksi['kode_transaksi']) ?></p>
            </div>
            <div>
              <p class="text-slate-400 font-medium">Tanggal transaksi</p>
              <p class="font-bold text-slate-800 mt-0.5"><?= htmlspecialchars($tanggalTeks) ?></p>
            </div>
            <div>
              <p class="text-slate-400 font-medium">Nama kasir</p>
              <p class="font-bold text-slate-800 mt-0.5"><?= htmlspecialchars($namaKasirLabel) ?></p>
            </div>
            <div>
              <p class="text-slate-400 font-medium">Shift aktif</p>
              <p class="font-bold text-slate-800 mt-0.5"><?= htmlspecialchars($transaksi['nama_shift'] ?? 'Shift') ?></p>
            </div>
            <div>
              <p class="text-slate-400 font-medium">Waktu transaksi</p>
              <p class="font-bold text-slate-800 mt-0.5"><?= htmlspecialchars($waktuTeks) ?> WIB</p>
            </div>
            <div>
              <p class="text-slate-400 font-medium">Waktu pembayaran</p>
              <p class="font-bold text-slate-800 mt-0.5"><?= htmlspecialchars($waktuTeks) ?> WIB <?= htmlspecialchars($tanggalTeks) ?></p>
            </div>
          </div>
        </div>

        <!-- Box Detail Pesanan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-900 text-sm">Detail pesanan</h3>
            <span class="text-xs text-slate-400 font-medium"><?= (int) ($transaksi['total_item'] ?? count($itemsPesanan)) ?> item</span>
          </div>
          <div class="px-6 py-2 bg-slate-50 border-b border-slate-100 text-[11px] text-slate-500">
            Dari keranjang Kasir &bull; Pesanan tidak perlu diinput ulang.
          </div>

          <table class="w-full text-left text-xs">
            <thead class="bg-slate-100 text-slate-700">
              <tr>
                <th class="p-3 pl-6">Nama menu</th>
                <th class="p-3">Kategori</th>
                <th class="p-3 text-center">Jumlah</th>
                <th class="p-3 text-right">Harga satuan</th>
                <th class="p-3 pr-6 text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-800 font-medium">
              <?php if (empty($itemsPesanan)): ?>
                <tr>
                  <td colspan="5" class="p-4 pl-6 text-slate-400 italic text-center">Tidak ada rincian item.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($itemsPesanan as $item): ?>
                  <tr>
                    <td class="p-3 pl-6 font-bold text-brand-navy"><?= htmlspecialchars($item['nama'] ?? $item['nama_menu']) ?></td>
                    <td class="p-3 text-slate-500"><?= htmlspecialchars($item['kategori'] ?? 'Menu') ?></td>
                    <td class="p-3 text-center"><?= (int) $item['qty'] ?></td>
                    <td class="p-3 text-right">Rp <?= number_format((float) $item['harga_satuan'], 0, ',', '.') ?></td>
                    <td class="p-3 pr-6 text-right font-bold">Rp <?= number_format((float) $item['subtotal'], 0, ',', '.') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
            <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
              <tr>
                <td colspan="4" class="p-3.5 pl-6 text-slate-700">Total &bull; <?= (int) ($transaksi['total_item'] ?? count($itemsPesanan)) ?> item</td>
                <td class="p-3.5 pr-6 text-right text-brand-navy text-sm">Rp <?= number_format((float) $transaksi['total'], 0, ',', '.') ?></td>
              </tr>
            </tfoot>
          </table>
        </div>

      </div>

      <!-- Kolom Kanan: Ringkasan Pembayaran & Aksi -->
      <div class="col-span-5 space-y-6">
        
        <!-- Box Ringkasan Pembayaran -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
          <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-sm">Ringkasan pembayaran</h3>
            <span class="text-xs text-slate-400 font-medium"><?= (int) ($transaksi['total_item'] ?? count($itemsPesanan)) ?> item</span>
          </div>

          <div>
            <p class="text-xs text-slate-500">Total pembayaran</p>
            <div class="flex justify-between items-center mt-1">
              <span class="text-3xl font-extrabold text-brand-navy">Rp <?= number_format((float) $transaksi['total'], 0, ',', '.') ?></span>
              <span class="text-xs font-bold bg-slate-100 px-3 py-1 rounded-lg text-slate-700"><?= htmlspecialchars($metodeLabel) ?></span>
            </div>
          </div>

          <!-- Banner Sukses -->
          <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 space-y-1">
            <div class="flex items-center space-x-2 text-emerald-800 font-bold text-xs">
              <span class="text-base">✅</span>
              <span>Transaksi Berhasil</span>
            </div>
            <p class="text-[11px] text-emerald-700">
              <?= $isTunai ? 'Pembayaran cash telah dikonfirmasi.' : 'Pembayaran nontunai QRIS berhasil diverifikasi.' ?>
            </p>
          </div>

          <div class="space-y-2 text-xs pt-2">
            <div class="flex justify-between">
              <span class="text-slate-500">Uang diterima</span>
              <span class="font-bold text-slate-800">Rp <?= number_format($nominalBayar, 0, ',', '.') ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500">Kembalian</span>
              <span class="font-extrabold text-emerald-600 text-sm">Rp <?= number_format($kembalian, 0, ',', '.') ?></span>
            </div>
          </div>

          <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-500 font-medium">
            <?php if ($isTunai): ?>
              Serahkan kembalian <strong class="text-slate-800">Rp <?= number_format($kembalian, 0, ',', '.') ?></strong> kepada pelanggan.
            <?php else: ?>
              Pembayaran nontunai QRIS sukses tanpa uang kembalian fisik.
            <?php endif; ?>
          </div>

          <!-- Tombol Aksi -->
          <div class="space-y-2.5 pt-2">
            <button type="button" onclick="bukaModalStruk()" 
                    class="w-full rounded-xl bg-brand-navy py-3 text-xs font-bold text-white shadow hover:bg-brand-lightNavy transition cursor-pointer flex items-center justify-center space-x-2">
              <span>🖨️</span>
              <span>Lihat &amp; Cetak Struk</span>
            </button>
            <a href="index.php?controller=transaksi&action=pos" 
               class="block w-full text-center rounded-xl border border-slate-300 py-3 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
              Kembali ke Kasir
            </a>
          </div>
        </div>

      </div>

    </div>

  </main>

  <!-- Modal Pratinjau Struk Kasir -->
  <div id="modalStrukTagihan" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden border border-slate-200">
      <div class="bg-brand-darkNavy text-white px-6 py-4 flex justify-between items-center">
        <div>
          <h3 class="font-extrabold text-base">Struk Transaksi Warkop Kolega</h3>
          <p class="text-xs text-blue-300 font-mono mt-0.5"><?= htmlspecialchars($transaksi['kode_transaksi']) ?></p>
        </div>
        <button type="button" onclick="tutupModalStruk()" class="text-slate-300 hover:text-white font-bold text-2xl leading-none cursor-pointer focus:outline-none">&times;</button>
      </div>

      <div class="p-6 space-y-4 text-xs font-mono" id="areaCetakStruk">
        <div class="text-center pb-2 border-b border-dashed border-slate-300">
          <h4 class="font-bold text-sm text-slate-900">WARKOP KOLEGA</h4>
          <p class="text-[10px] text-slate-500">KolegaOps POS System</p>
        </div>

        <div class="border-b border-dashed border-slate-300 pb-2 text-[11px] text-slate-600 space-y-1">
          <div class="flex justify-between">
            <span>No:</span>
            <span class="font-bold text-slate-800"><?= htmlspecialchars($transaksi['kode_transaksi']) ?></span>
          </div>
          <div class="flex justify-between">
            <span>Waktu:</span>
            <span><?= htmlspecialchars($waktuTeks) ?> &bull; <?= htmlspecialchars($tanggalTeks) ?></span>
          </div>
          <div class="flex justify-between">
            <span>Kasir / Shift:</span>
            <span><?= htmlspecialchars($namaKasirLabel) ?> (<?= htmlspecialchars($transaksi['nama_shift'] ?? 'Sore') ?>)</span>
          </div>
          <div class="flex justify-between">
            <span>Metode:</span>
            <span class="font-bold"><?= htmlspecialchars($metodeLabel) ?></span>
          </div>
        </div>

        <div class="space-y-1.5 border-b border-dashed border-slate-300 pb-2 text-[11px]">
          <?php foreach ($itemsPesanan as $it): ?>
            <div class="flex justify-between items-center">
              <span><?= htmlspecialchars($it['nama'] ?? $it['nama_menu']) ?> x<?= (int) $it['qty'] ?></span>
              <span class="font-bold">Rp <?= number_format((float) $it['subtotal'], 0, ',', '.') ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="space-y-1 text-xs">
          <div class="flex justify-between font-bold text-slate-900">
            <span>TOTAL:</span>
            <span>Rp <?= number_format((float) $transaksi['total'], 0, ',', '.') ?></span>
          </div>
          <div class="flex justify-between text-slate-600">
            <span>Diterima:</span>
            <span>Rp <?= number_format($nominalBayar, 0, ',', '.') ?></span>
          </div>
          <div class="flex justify-between text-emerald-700 font-bold">
            <span>Kembalian:</span>
            <span>Rp <?= number_format($kembalian, 0, ',', '.') ?></span>
          </div>
        </div>

        <div class="text-center pt-3 text-[10px] text-slate-400 border-t border-dashed border-slate-200">
          Terima kasih atas kunjungan Anda di Warkop Kolega!
        </div>
      </div>

      <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex justify-end space-x-3">
        <button type="button" onclick="tutupModalStruk()" 
                class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-5 py-2.5 rounded-xl font-bold text-xs transition cursor-pointer">
          Tutup
        </button>
        <button type="button" onclick="window.print()" 
                class="bg-brand-navy hover:bg-brand-lightNavy text-white px-5 py-2.5 rounded-xl font-bold text-xs transition cursor-pointer flex items-center space-x-1.5">
          <span>🖨️</span>
          <span>Cetak</span>
        </button>
      </div>
    </div>
  </div>

  <script>
    function bukaModalStruk() {
      const m = document.getElementById('modalStrukTagihan');
      if (m) {
        m.classList.remove('hidden');
        m.classList.add('flex');
      }
    }

    function tutupModalStruk() {
      const m = document.getElementById('modalStrukTagihan');
      if (m) {
        m.classList.remove('flex');
        m.classList.add('hidden');
      }
    }

    window.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        tutupModalStruk();
      }
    });

    document.getElementById('modalStrukTagihan').addEventListener('click', function(e) {
      if (e.target === this) {
        tutupModalStruk();
      }
    });
  </script>
</body>
</html>
