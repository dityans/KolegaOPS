<?php
// views/owner/update-stok.php
// Tampilan Form Update / Penyesuaian Stok Bahan Baku KolegaOps
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Stok - KolegaOps Warkop Kolega</title>
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
        <h2 class="text-3xl font-extrabold text-slate-900">Update Stok</h2>
        <p class="text-sm text-slate-500 mt-1">
          Bahan: <strong><?= htmlspecialchars($selectedStok['nama']) ?></strong> &bull; Dicatat oleh <?= htmlspecialchars($namaOwner) ?> &bull; <?= htmlspecialchars($tanggal_hari_ini) ?>
        </p>
      </div>
      <div class="text-sm font-bold text-slate-600 bg-slate-100 border border-slate-200 px-4 py-2 rounded-xl">
        Belum disimpan
      </div>
    </header>

    <!-- Notifikasi Error -->
    <?php if (!empty($pesanError)): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-semibold text-sm flex items-center space-x-3">
        <span>⚠️</span>
        <span><?= htmlspecialchars($pesanError) ?></span>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      
      <!-- Kolom Kiri: Form Input -->
      <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <h3 class="font-bold text-slate-900 text-lg border-b border-slate-100 pb-4 mb-6">Catat Perubahan Stok Bahan</h3>
        
        <form action="index.php?controller=stok_bahan&action=update&id=<?= (int) $selectedStok['id'] ?>" method="POST" class="space-y-6">
          <?= function_exists('csrf_field') ? csrf_field() : '' ?>
          
          <!-- Dropdown Pemilihan Bahan -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Bahan / Menu *</label>
            <select name="id_stok" onchange="location.href='index.php?controller=stok_bahan&action=update&id=' + this.value" 
                    class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-base font-medium focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-700">
              <?php foreach ($daftarStok as $item): ?>
                <option value="<?= (int) $item['id'] ?>" <?= ((int) $item['id'] === (int) $selectedStok['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($item['nama']) ?> (Sisa: <?= number_format((float) $item['stok'], 0, ',', '.') ?> <?= htmlspecialchars($item['satuan']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Informasi Stok Saat Ini & Minimum -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Stok Saat Ini</label>
              <input type="text" id="inputStokAwal" value="<?= (float) $selectedStok['stok'] ?>" 
                     class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 text-base font-medium text-slate-600 shadow-sm" readonly>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Batas Minimum Stok</label>
              <input type="text" id="inputMin" value="<?= (float) $selectedStok['minimum'] ?>" 
                     class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 text-base font-medium text-slate-600 shadow-sm" readonly>
            </div>
          </div>

          <!-- Jenis Update & Jumlah Perubahan -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Jenis Update *</label>
              <select name="jenis_update" id="selectJenis" onchange="updatePreview()" 
                      class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-base font-medium focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-700">
                <option value="Penambahan stok">Penambahan stok (+)</option>
                <option value="Pengurangan stok">Pengurangan stok (-)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Jumlah Perubahan *</label>
              <input type="number" step="any" min="0.01" name="jumlah_tambahan" id="inputTambah" value="10" oninput="updatePreview()" 
                     class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-base font-medium focus:outline-none focus:border-brand-skyBlue shadow-sm" required>
              <p class="text-xs text-slate-400 mt-1.5">Satuan: <strong><?= htmlspecialchars($selectedStok['satuan']) ?></strong></p>
            </div>
          </div>

          <!-- Keterangan / Alasan -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Alasan / Catatan *</label>
            <input type="text" name="catatan" placeholder="Contoh: Kulakan / Penerimaan barang dari supplier" 
                   class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-base font-medium focus:outline-none focus:border-brand-skyBlue shadow-sm" required>
            <p class="text-xs text-slate-400 mt-2">
              Jejak perubahan akan disimpan ke basis data PostgreSQL beserta nama akun dan stempel waktu.
            </p>
          </div>

          <!-- Tombol Simpan & Batal -->
          <div class="flex items-center space-x-4 pt-4 border-t border-slate-100">
            <button type="submit" class="bg-brand-navy hover:bg-brand-lightNavy text-white px-6 py-3.5 rounded-xl font-bold text-base shadow transition cursor-pointer">
              Simpan Update Stok
            </button>
            <a href="index.php?controller=stok_bahan&action=index" 
               class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-6 py-3.5 rounded-xl font-bold text-base transition text-center inline-block">
              Batal
            </a>
          </div>

        </form>
      </div>

      <!-- Kolom Kanan: Pratinjau Kalkulasi Real-Time -->
      <div class="lg:col-span-4 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-900 text-base">Pratinjau Stok</h3>
          </div>
          <table class="w-full text-sm">
            <thead class="bg-brand-darkNavy text-white">
              <tr>
                <th class="p-3.5 pl-6 text-left">PERHITUNGAN</th>
                <th class="p-3.5 pr-6 text-right"><?= strtoupper(htmlspecialchars($selectedStok['satuan'])) ?></th>
              </tr>
            </thead>
            <tbody class="font-medium text-slate-800 divide-y divide-slate-100">
              <tr>
                <td class="p-3.5 pl-6">Stok saat ini</td>
                <td class="p-3.5 pr-6 text-right font-bold" id="prevStokAwal"><?= number_format((float) $selectedStok['stok'], 0, ',', '.') ?></td>
              </tr>
              <tr>
                <td class="p-3.5 pl-6" id="labelJenisPreview">Penambahan</td>
                <td class="p-3.5 pr-6 text-right font-bold text-brand-navy" id="prevTambah">+10</td>
              </tr>
              <tr class="bg-blue-50/50">
                <td class="p-3.5 pl-6 font-bold text-slate-900">Stok setelah update</td>
                <td class="p-3.5 pr-6 text-right font-extrabold text-brand-navy text-base" id="prevHasil">
                  <?= number_format((float) $selectedStok['stok'] + 10, 0, ',', '.') ?>
                </td>
              </tr>
              <tr>
                <td class="p-3.5 pl-6">Batas Minimum</td>
                <td class="p-3.5 pr-6 text-right font-bold text-slate-600">
                  <?= number_format((float) $selectedStok['minimum'], 0, ',', '.') ?>
                </td>
              </tr>
            </tbody>
          </table>
          
          <div class="p-6 space-y-3 bg-white border-t border-slate-100">
            <div id="badgeStatus" class="inline-block bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg font-bold text-xs">
              Aman setelah disimpan
            </div>
            <p id="infoStatus" class="text-xs text-slate-600 font-medium leading-relaxed">
              Jumlah stok baru berada di atas batas minimum.
            </p>
          </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 p-5 rounded-2xl text-xs text-blue-900 font-medium leading-relaxed">
          ℹ️ <strong>Tips:</strong> Pastikan barang fisik sudah diterima. Pembelian kulakan stok dan kas keluar operasional dicatat terpisah agar pembukuan kas warkop tetap tertib dan transparan.
        </div>
      </div>

    </div>

  </main>

  <!-- Script Kalkulasi Real-Time -->
  <script>
    const stokAwal = parseFloat(document.getElementById('inputStokAwal').value) || 0;
    const minStok  = parseFloat(document.getElementById('inputMin').value) || 0;

    updatePreview();

    function updatePreview() {
      const selectJenis = document.getElementById('selectJenis');
      const jenis = selectJenis.value;
      const tambahInput = parseFloat(document.getElementById('inputTambah').value) || 0;
      
      let hasil = stokAwal;
      let labelTambah = "";

      if (jenis === 'Penambahan stok') {
        hasil = stokAwal + tambahInput;
        labelTambah = "+" + tambahInput.toLocaleString('id-ID');
        document.getElementById('labelJenisPreview').textContent = "Penambahan (+)";
      } else {
        hasil = Math.max(0, stokAwal - tambahInput);
        labelTambah = "-" + tambahInput.toLocaleString('id-ID');
        document.getElementById('labelJenisPreview').textContent = "Pengurangan (-)";
      }

      document.getElementById('prevTambah').textContent = labelTambah;
      document.getElementById('prevHasil').textContent = hasil.toLocaleString('id-ID');

      const badge = document.getElementById('badgeStatus');
      const info = document.getElementById('infoStatus');

      if (hasil >= minStok) {
        badge.className = "inline-block bg-emerald-100 text-emerald-700 px-3 py-1 rounded-lg font-bold text-xs";
        badge.textContent = "Aman setelah disimpan";
        info.textContent = `Jumlah baru (${hasil.toLocaleString('id-ID')}) berada di atas batas minimum stok.`;
      } else {
        badge.className = "inline-block bg-red-100 text-red-600 px-3 py-1 rounded-lg font-bold text-xs";
        badge.textContent = "Kritis setelah disimpan";
        info.textContent = `Peringatan: Stok berada di bawah batas minimum (${minStok.toLocaleString('id-ID')})! Segera jadwalkan kulakan.`;
      }
    }
  </script>
</body>
</html>
