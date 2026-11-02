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
        <a href="index.php?controller=laporan&action=dashboard" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'laporan' && $curAction === 'dashboard') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <span>📊</span>
          <span>Dashboard</span>
        </a>
        <a href="index.php?controller=menu&action=index" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'menu') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <span>☕</span>
          <span>Produk</span>
        </a>
        <a href="index.php?controller=stok_bahan&action=index" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'stok_bahan') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <span>📦</span>
          <span>Stok Bahan</span>
        </a>
        <a href="index.php?controller=laporan&action=index" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'laporan' && $curAction === 'index') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <span>📄</span>
          <span>Laporan</span>
        </a>
        <a href="index.php?controller=laporan&action=profit" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curAction === 'profit') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <span>📈</span>
          <span>Analisis Profit</span>
        </a>
        <a href="index.php?controller=pegawai&action=index" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'pegawai') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <span class="text-base">👥</span>
          <span>Kelola Pegawai</span>
        </a>
      <?php else: ?>
        <!-- Menu Khusus Kasir -->
        <a href="index.php?controller=kasir&action=dashboard" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curAction === 'dashboard') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <span>📊</span>
          <span>Dashboard</span>
        </a>
        <a href="index.php?controller=kasir&action=pos" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curAction === 'pos') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <span>🛒</span>
          <span>Kasir POS</span>
        </a>
        <a href="index.php?controller=stok_bahan&action=index" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curController === 'stok_bahan') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <span>📦</span>
          <span>Stok</span>
        </a>
        <a href="index.php?controller=shift&action=laporan" 
           class="flex items-center space-x-3 px-4 py-3 rounded-xl transition <?= ($curAction === 'laporan') ? 'bg-blue-600 text-white font-bold shadow' : 'text-blue-200 hover:bg-blue-950/60' ?>">
          <span>📄</span>
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
