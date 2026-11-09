<?php
// views/layouts/sidebar.php
// Sidebar Navigasi Dinamis KolegaOps POS Warkop

$currentUser = current_user() ?? [];
$namaUser    = ucfirst((string) ($currentUser['username'] ?? 'Pengguna'));
$roleUser    = strtolower((string) ($currentUser['role'] ?? 'kasir'));
$roleLabel   = ($roleUser === 'owner' || $roleUser === 'admin') ? 'Pemilik · Owner' : 'Pegawai · Kasir';
$inisialUser = strtoupper(substr($namaUser, 0, 2));

$curController = strtolower($_GET['controller'] ?? '');
$curAction     = strtolower($_GET['action'] ?? '');
?>
<aside class="w-64 bg-brand-darkNavy text-white flex flex-col justify-between shrink-0 shadow-xl min-h-screen">
  <div>
    <!-- Logo & Brand Header -->
    <div class="p-6 border-b border-blue-900/50 flex items-center space-x-3">
      <div>
        <h1 class="text-xl font-extrabold tracking-wider text-white">Kolega<span class="text-brand-skyBlue">Ops</span></h1>
        <p class="text-[10px] text-blue-300 uppercase tracking-widest mt-0.5">WARKOP KOLEGA</p>
      </div>
    </div>

    <!-- Navigasi Menu Sesuai Role -->
    <nav class="p-4 space-y-2 text-sm font-medium">
      <?php if ($roleUser === 'owner' || $roleUser === 'admin'): ?>
        <!-- Menu Khusus Owner -->
        <a href="index.php?controller=dashboard&action=dashboard_owner" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'dashboard' && $curAction === 'dashboard_owner') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          <span>Dashboard</span>
        </a>
        <a href="index.php?controller=menu&action=index" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'menu') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-coffee preview-icon">
            <path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/>
          </svg>
          <span>Produk</span>
        </a>
        <a href="index.php?controller=stok_bahan&action=index" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'stok_bahan') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-box preview-icon">
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
          </svg>
          <span>Stok Bahan</span>
        </a>
        <a href="index.php?controller=laporan&action=index" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'laporan' && $curAction === 'index') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-spreadsheet preview-icon">
            <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M8 13h2"/><path d="M14 13h2"/><path d="M8 17h2"/><path d="M14 17h2"/>
          </svg>
          <span>Laporan</span>
        </a>
        <a href="index.php?controller=laporan&action=profit" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curAction === 'profit') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-no-axes-combined preview-icon">
            <path d="M12 16v5"/><path d="M16 14.639V21"/><path d="M20 10.656V21"/><path d="m22 3-8.646 8.646a.5.5 0 0 1-.708 0L9.354 8.354a.5.5 0 0 0-.707 0L2 15"/><path d="M4 18.463V21"/><path d="M8 14.656V21"/>
          </svg>
          <span>Analisis Profit</span>
        </a>
        <a href="index.php?controller=pegawai&action=index" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'pegawai') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users preview-icon">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/>
          </svg>
          <span>Kelola Pegawai</span>
        </a>
      <?php else: ?>
        <!-- Menu Khusus Kasir -->
        <a href="index.php?controller=dashboard&action=dashboard_pegawai" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'dashboard' && $curAction === 'dashboard_pegawai') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          <span>Dashboard</span>
        </a>
        <a href="index.php?controller=transaksi&action=pos" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curAction === 'pos') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart preview-icon">
            <path d="m2.05 2.05 1.099-.028a1 1 0 0 1 1.008.815l2.69 14.347A1 1 0 0 0 7.83 18H18"/><path d="M4.563 5h16.435a1 1 0 0 1 .981 1.204l-1.026 6.226A2 2 0 0 1 18.962 14H6.25"/><circle cx="18" cy="20" r="2"/><circle cx="8" cy="20" r="2"/>
          </svg>
          <span>Kasir POS</span>
        </a>
        <a href="index.php?controller=stok_bahan&action=index" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= (($curController === 'stok_bahan' && in_array($curAction, ['pegawai', 'pegawaiindex', 'updatestokpegawai'])) || $curController === 'stok_pegawai') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-box preview-icon">
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
          </svg>
          <span>Stok</span>
        </a>
        <a href="index.php?controller=laporan&action=laporanPegawai" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= (($curController === 'laporan' && in_array($curAction, ['laporanpegawai', 'laporanshift', 'shift'])) || ($curController === 'shift' && $curAction === 'laporan')) ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-spreadsheet preview-icon">
            <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M8 13h2"/><path d="M14 13h2"/><path d="M8 17h2"/><path d="M14 17h2"/>
          </svg>
          <span>Laporan Shift</span>
        </a>
      <?php endif; ?>
    </nav>
  </div>

  <!-- Profil Pengguna & Tombol Logout -->
  <div class="p-4 border-t border-blue-900/50 bg-blue-950/40 flex items-center justify-between">
    <div class="flex items-center space-x-3 overflow-hidden">
      <div class="h-10 w-10 rounded-xl bg-brand-skyBlue text-brand-darkNavy font-black flex items-center justify-center text-sm shadow shrink-0">
        <?= htmlspecialchars($inisialUser ?: 'KO') ?>
      </div>
      <div class="overflow-hidden">
        <h4 class="font-bold text-sm text-white truncate"><?= htmlspecialchars($namaUser) ?></h4>
        <p class="text-xs text-blue-300 truncate"><?= htmlspecialchars($roleLabel) ?></p>
      </div>
    </div>
    <button type="button" onclick="openLogoutModal()" title="Keluar Akun" 
       class="p-2 rounded-xl text-blue-300 hover:text-white hover:bg-red-600/80 transition shrink-0 cursor-pointer focus:outline-none">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
      </svg>
    </button>
  </div>
</aside>

<!-- ============================================================================= -->
<!-- MODAL DIALOG KONFIRMASI LOGOUT                                               -->
<!-- ============================================================================= -->
<div id="logoutModal" 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200 opacity-0 pointer-events-none" 
     aria-hidden="true" 
     role="dialog" 
     aria-modal="true">
  
  <!-- Backdrop Clickable -->
  <div class="fixed inset-0" onclick="closeLogoutModal()"></div>

  <!-- Dialog Card -->
  <div id="logoutModalCard" 
       class="relative bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-sm w-full p-6 text-center transform transition-all duration-200 scale-95 z-10">
    
    <!-- Icon Logout / Warning -->
    <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4 shadow-sm">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
      </svg>
    </div>

    <!-- Teks Konfirmasi -->
    <h3 class="text-base font-bold text-slate-900">Konfirmasi Keluar</h3>
    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
      Apakah Anda yakin ingin keluar dari sistem <strong class="text-slate-800">KolegaOps</strong>? Anda perlu login kembali untuk mengakses aplikasi.
    </p>

    <!-- Tombol Pilihan Aksi -->
    <div class="grid grid-cols-2 gap-3 mt-6">
      <button type="button" onclick="closeLogoutModal()" 
              class="w-full py-2.5 px-4 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 transition focus:outline-none cursor-pointer">
        Batal
      </button>
      <a href="index.php?controller=auth&action=logout" 
         class="w-full py-2.5 px-4 rounded-xl text-xs font-semibold text-white bg-red-600 hover:bg-red-700 transition shadow-sm inline-flex items-center justify-center focus:outline-none">
        Ya, Keluar
      </a>
    </div>

  </div>
</div>

<script>
  function openLogoutModal() {
    const modal = document.getElementById('logoutModal');
    const card  = document.getElementById('logoutModalCard');
    if (!modal || !card) return;

    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100');
    card.classList.remove('scale-95');
    card.classList.add('scale-100');
    modal.setAttribute('aria-hidden', 'false');
  }

  function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    const card  = document.getElementById('logoutModalCard');
    if (!modal || !card) return;

    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0', 'pointer-events-none');
    card.classList.remove('scale-100');
    card.classList.add('scale-95');
    modal.setAttribute('aria-hidden', 'true');
  }

  // Tutup modal jika tombol keyboard Escape ditekan
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      closeLogoutModal();
    }
  });
</script>
