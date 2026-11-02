<?php
// views/owner/kelola-pegawai.php
// Tampilan Kelola Akun Pegawai & Hak Akses Warkop Kolega - KolegaOps POS
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pegawai - KolegaOps Warkop Kolega</title>
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
    <header class="flex items-center justify-between pb-6 border-b border-slate-200 mb-6 bg-white px-8 py-6 rounded-2xl shadow-sm">
      <div>
        <h2 class="text-3xl font-extrabold text-slate-900">Kelola Pegawai</h2>
        <p class="text-sm text-slate-500 mt-1">Akun dan hak akses Warkop Kolega &bull; Pemilik (Owner)</p>
      </div>
      <div>
        <button type="button" onclick="bukaFormPegawai('tambah')" 
                class="bg-brand-navy hover:bg-brand-lightNavy text-white px-6 py-3.5 rounded-xl font-bold text-sm shadow transition cursor-pointer flex items-center space-x-2">
          <span>+ Tambah Pegawai</span>
        </button>
      </div>
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

    <!-- 3 Kartu Statistik Akun -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      
      <!-- Kartu 1: Total Akun -->
      <div class="bg-brand-navy text-white p-6 rounded-2xl shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-blue-200 uppercase tracking-wider">TOTAL AKUN</p>
        <div class="my-2">
          <h3 class="text-4xl font-extrabold"><?= (int) $totalAkun ?> <span class="text-lg font-normal">Akun</span></h3>
        </div>
        <p class="text-xs text-blue-200"><?= (int) $totalOwner ?> Owner &bull; <?= (int) $totalKasir ?> Pegawai</p>
      </div>

      <!-- Kartu 2: Akun Aktif -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">AKUN AKTIF</p>
        <div class="my-2">
          <h3 class="text-3xl font-extrabold text-emerald-600"><?= (int) $totalAktif ?> Akun</h3>
        </div>
        <p class="text-xs text-slate-500 truncate" title="<?= htmlspecialchars($namaAktifTeks) ?>">
          <?= htmlspecialchars($namaAktifTeks) ?>
        </p>
      </div>

      <!-- Kartu 3: Akun Nonaktif -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">AKUN NONAKTIF</p>
        <div class="my-2">
          <h3 class="text-3xl font-extrabold text-slate-400"><?= (int) $totalNonaktif ?> Akun</h3>
        </div>
        <p class="text-xs text-slate-500">Tidak dapat mengakses aplikasi</p>
      </div>

    </div>

    <!-- Filter Bar Interaktif (Pencarian, Role, Status) -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
      <div class="md:col-span-6 relative">
        <input type="text" id="filterSearch" onkeyup="filterTabelPegawai()" placeholder="Cari nama atau username..." 
               class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-brand-skyBlue shadow-sm">
      </div>
      <div class="md:col-span-3">
        <select id="filterRole" onchange="filterTabelPegawai()" 
                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-700 font-medium cursor-pointer">
          <option value="">Semua role</option>
          <option value="Owner">Owner</option>
          <option value="Pegawai">Pegawai</option>
        </select>
      </div>
      <div class="md:col-span-3">
        <select id="filterStatus" onchange="filterTabelPegawai()" 
                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-sm focus:outline-none focus:border-brand-skyBlue shadow-sm text-slate-700 font-medium cursor-pointer">
          <option value="">Semua status</option>
          <option value="Aktif">Aktif</option>
          <option value="Nonaktif">Nonaktif</option>
        </select>
      </div>
    </div>

    <!-- Tabel Daftar Akun Pegawai (Zebra Stripe Biru Lembut) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
        <h3 class="font-bold text-slate-900 text-base">Daftar Akun Pegawai</h3>
        <span class="text-xs text-slate-400 font-semibold">Akses penuh dikelola oleh Pemilik (Owner)</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm" id="tabelPegawai">
          <thead class="bg-brand-darkNavy text-white">
            <tr>
              <th class="p-4 pl-6">NAMA</th>
              <th class="p-4">USERNAME</th>
              <th class="p-4">ROLE</th>
              <th class="p-4">STATUS AKUN</th>
              <th class="p-4">SHIFT HARI INI</th>
              <th class="p-4 pr-6 text-right">AKSI</th>
            </tr>
          </thead>
          <tbody class="font-medium text-slate-800 divide-y divide-slate-100">
            <?php if (empty($daftarPegawai)): ?>
              <tr>
                <td colspan="6" class="p-8 text-center text-slate-400 italic">Belum ada akun pegawai di database.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($daftarPegawai as $index => $peg): 
                $bgRow = ($index % 2 === 1) ? 'bg-blue-100/70' : 'bg-white';
                $isCurrentUser = ((int) ($peg['id'] ?? 0) === (int) (current_user('id_user') ?? 0));
              ?>
              <tr class="pegawai-row <?= $bgRow ?> hover:bg-blue-50/60 transition" 
                  data-nama="<?= strtolower(htmlspecialchars($peg['nama'])) ?>" 
                  data-username="<?= strtolower(htmlspecialchars($peg['username'])) ?>" 
                  data-role="<?= htmlspecialchars($peg['role']) ?>" 
                  data-status="<?= htmlspecialchars($peg['status']) ?>">
                <td class="p-4 pl-6 font-bold text-brand-navy">
                  <?= htmlspecialchars($peg['nama']) ?>
                  <?php if ($isCurrentUser): ?>
                    <span class="ml-2 text-[10px] bg-blue-100 text-brand-navy px-2 py-0.5 rounded-full font-bold">Anda</span>
                  <?php endif; ?>
                </td>
                <td class="p-4 text-slate-600 font-mono"><?= htmlspecialchars($peg['username']) ?></td>
                <td class="p-4 text-slate-700 font-semibold">
                  <span class="<?= $peg['role'] === 'Owner' ? 'text-brand-navy font-bold' : 'text-slate-600' ?>">
                    <?= htmlspecialchars($peg['role']) ?>
                  </span>
                </td>
                <td class="p-4">
                  <span class="<?= $peg['status'] === 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' ?> px-3 py-1 rounded-lg font-bold text-xs inline-block status-badge">
                    <?= htmlspecialchars($peg['status']) ?>
                  </span>
                </td>
                <td class="p-4 text-slate-600"><?= htmlspecialchars($peg['shift']) ?></td>
                <td class="p-4 pr-6 text-right">
                  <button type="button" 
                          onclick='bukaFormPegawai("edit", <?= json_encode($peg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' 
                          class="text-brand-navy font-bold hover:underline bg-transparent border-none cursor-pointer inline-flex items-center space-x-1">
                    <span>Edit</span>
                    <span>&rarr;</span>
                  </button>
                  <?php if (!$isCurrentUser): ?>
                    <a href="index.php?controller=pegawai&action=hapus&id=<?= (int) $peg['id'] ?>" 
                       class="text-red-600 font-bold hover:text-red-800 hover:underline inline-flex items-center space-x-1 ml-4"
                       onclick="return confirm('Apakah Anda yakin ingin menghapus akun pegawai \'<?= htmlspecialchars(addslashes($peg['username'])) ?>\'?');">
                      <span>Hapus</span>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-between items-center text-sm text-slate-500 font-medium">
        <span id="infoJumlah">Menampilkan <?= count($daftarPegawai) ?> dari <?= count($daftarPegawai) ?> akun</span>
        <span class="font-bold text-brand-navy">&larr; Halaman 1 dari 1 &rarr;</span>
      </div>
    </div>

    <!-- Catatan Pedoman Operasional -->
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl text-xs text-blue-900 font-medium">
      💡 <strong>Petunjuk Hak Akses:</strong> Menonaktifkan akun pegawai akan memblokir login ke aplikasi POS Kasir tanpa menghapus riwayat transaksi maupun laporan shift yang pernah dicatat oleh pegawai bersangkutan.
    </div>

  </main>

  <!-- MODAL FORM GABUNGAN (TAMBAH & EDIT PEGAWAI) -->
  <div id="modalPegawai" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-150">
      
      <div class="bg-brand-darkNavy text-white px-6 py-4 flex justify-between items-center">
        <h3 id="modalTitle" class="font-extrabold text-base">Tambah Pegawai Baru</h3>
        <button type="button" onclick="tutupFormPegawai()" class="text-slate-300 hover:text-white font-bold text-2xl leading-none cursor-pointer focus:outline-none">&times;</button>
      </div>

      <form method="POST" action="index.php?controller=pegawai&action=simpan" class="p-6 space-y-4">
        
        <!-- CSRF Token -->
        <?php if (function_exists('csrf_token')): ?>
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <?php endif; ?>

        <!-- Hidden ID User untuk mode edit -->
        <input type="hidden" id="inputIdUser" name="id_user" value="0">

        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
            Username Login <span class="text-red-500">*</span>
          </label>
          <input type="text" id="inputUsername" name="username" required 
                 placeholder="Contoh: andi.kasir" 
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-skyBlue font-medium">
          <p class="text-[11px] text-slate-400 mt-1">Digunakan untuk masuk ke sistem.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
              Role Hak Akses <span class="text-red-500">*</span>
            </label>
            <select id="inputRole" name="role" 
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-skyBlue font-medium text-slate-700 cursor-pointer">
              <option value="Pegawai">Pegawai (Kasir)</option>
              <option value="Owner">Owner (Pemilik)</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
              Status Akun <span class="text-red-500">*</span>
            </label>
            <select id="inputStatus" name="status" 
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-skyBlue font-medium text-slate-700 cursor-pointer">
              <option value="Aktif">Aktif</option>
              <option value="Nonaktif">Nonaktif</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">
            Password <span id="labelPassHint" class="lowercase font-normal text-slate-400">(wajib untuk akun baru)</span>
          </label>
          <input type="password" id="inputPassword" name="password" 
                 placeholder="Minimal 4 karakter" 
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-skyBlue font-medium">
          <p id="labelPassSub" class="text-[11px] text-slate-400 mt-1">Kosongkan kolom ini jika tidak ingin mengubah password akun.</p>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
          <button type="button" onclick="tutupFormPegawai()" 
                  class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-5 py-3 rounded-xl font-bold text-xs transition cursor-pointer">
            Batal
          </button>
          <button type="submit" 
                  class="bg-brand-navy hover:bg-brand-lightNavy text-white px-6 py-3 rounded-xl font-bold text-xs transition cursor-pointer shadow">
            Simpan Data
          </button>
        </div>

      </form>
    </div>
  </div>

  <!-- JAVASCRIPT MODAL & FILTER INTERAKTIF -->
  <script>
    function bukaFormPegawai(mode, data = null) {
      const modal = document.getElementById('modalPegawai');
      const title = document.getElementById('modalTitle');
      const inputIdUser = document.getElementById('inputIdUser');
      const inputUsername = document.getElementById('inputUsername');
      const inputRole = document.getElementById('inputRole');
      const inputStatus = document.getElementById('inputStatus');
      const inputPassword = document.getElementById('inputPassword');
      const labelPassHint = document.getElementById('labelPassHint');
      const labelPassSub = document.getElementById('labelPassSub');

      if (mode === 'tambah') {
        title.innerText = 'Tambah Pegawai Baru';
        inputIdUser.value = '0';
        inputUsername.value = '';
        inputRole.value = 'Pegawai';
        inputStatus.value = 'Aktif';
        inputPassword.value = '';
        inputPassword.required = true;
        labelPassHint.innerText = '(wajib diisi)';
        labelPassSub.innerText = 'Password minimal 4 karakter.';
      } else if (mode === 'edit' && data) {
        title.innerText = 'Edit Akun · ' + (data.nama || data.username);
        inputIdUser.value = data.id || '0';
        inputUsername.value = data.username || '';
        inputRole.value = data.role || 'Pegawai';
        inputStatus.value = data.status || 'Aktif';
        inputPassword.value = '';
        inputPassword.required = false;
        labelPassHint.innerText = '(opsional, kosongkan jika tidak diubah)';
        labelPassSub.innerText = 'Kosongkan kolom ini jika tidak ingin mengubah password akun.';
      }

      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function tutupFormPegawai() {
      const modal = document.getElementById('modalPegawai');
      modal.classList.remove('flex');
      modal.classList.add('hidden');
    }

    // Tutup modal jika user menekan tombol Escape atau klik di luar modal
    window.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        tutupFormPegawai();
      }
    });

    document.getElementById('modalPegawai').addEventListener('click', function(e) {
      if (e.target === this) {
        tutupFormPegawai();
      }
    });

    // Fungsi untuk memfilter tabel berdasarkan input pencarian, role, dan status akun secara interaktif
    function filterTabelPegawai() {
      const keyword = document.getElementById('filterSearch').value.toLowerCase().trim();
      const selectedRole = document.getElementById('filterRole').value;
      const selectedStatus = document.getElementById('filterStatus').value;

      const rows = document.querySelectorAll('.pegawai-row');
      let visibleCount = 0;

      rows.forEach(function(row) {
        const nama = row.getAttribute('data-nama') || '';
        const username = row.getAttribute('data-username') || '';
        const role = row.getAttribute('data-role') || '';
        const status = row.getAttribute('data-status') || '';

        const matchKeyword = (keyword === "" || nama.includes(keyword) || username.includes(keyword));
        const matchRole    = (selectedRole === "" || role === selectedRole);
        const matchStatus  = (selectedStatus === "" || status === selectedStatus);

        if (matchKeyword && matchRole && matchStatus) {
          row.style.display = "";
          visibleCount++;
        } else {
          row.style.display = "none";
        }
      });

      const totalAwal = <?= count($daftarPegawai) ?>;
      document.getElementById('infoJumlah').innerText = `Menampilkan ${visibleCount} dari ${totalAwal} akun`;
    }
  </script>
</body>
</html>
