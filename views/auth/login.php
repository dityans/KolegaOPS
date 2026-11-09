<?php
// views/auth/login.php
// View Antarmuka Login KolegaOps POS Warkop

//tes tambah komentar 
// halo
// tes github
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - KolegaOps Warkop Kolega</title>
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
<body class="bg-brand-darkNavy min-h-screen flex items-center justify-center p-4">

  <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12 min-h-[520px]">
    
    <!-- SISI KIRI: BRANDING & MOTTO -->
    <div class="md:col-span-5 bg-brand-navy p-8 text-white flex flex-col justify-between relative overflow-hidden">
      <div>
        <div class="flex items-center space-x-2 text-2xl font-bold tracking-wide">
          <h1><span class="text-white">Kolega</span><span class="text-brand-skyBlue">Ops</span></h1>
        </div>
        <p class="text-[11px] text-slate-300 tracking-widest font-semibold uppercase mt-1">WARKOP KOLEGA</p>
      </div>

      <div class="my-auto py-8">
        <h2 class="text-2xl font-extrabold leading-tight">Tiada Hari<br>Tanpa Ngopi</h2>
        <p class="text-xs text-slate-300 mt-2 leading-relaxed">
          Sistem Pengelolaan Operasional terintegrasi untuk kasir, stok, dan laporan keuangan Warkop Kolega.
        </p>
      </div>

      <div class="text-[11px] text-slate-400">
        &copy; <?= date('Y') ?> Warkop Kolega. All rights reserved.
      </div>
    </div>

    <!-- SISI KANAN: FORM LOGIN -->
    <div class="md:col-span-7 p-8 md:p-10 flex flex-col justify-center bg-white">
      <div>
        <h3 class="text-2xl font-bold text-slate-900">Masuk ke KolegaOps</h3>
        <p class="text-xs text-slate-500 mt-1">Gunakan akun yang terdaftar untuk memulai.</p>
      </div>

      <!-- Pesan Peringatan Error -->
      <?php if (!empty($error)): ?>
        <div class="mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-600 font-medium">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- Form Login Mengarah ke Controller Auth Action Process -->
      <form action="index.php?controller=auth&action=process" method="POST" class="mt-6 space-y-4">
        <!-- Token CSRF untuk Proteksi Keamanan -->
        <?= function_exists('csrf_field') ? csrf_field() : '' ?>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Username</label>
          <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required 
                 placeholder="Masukkan username"
                 class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-xs focus:border-brand-skyBlue focus:ring-1 focus:ring-brand-skyBlue outline-none transition">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
          <div class="relative">
            <input type="password" id="passwordInput" name="password" required placeholder="Masukkan password"
                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-xs pr-10 focus:border-brand-skyBlue focus:ring-1 focus:ring-brand-skyBlue outline-none transition">
            
            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none" aria-label="Lihat Password">
              <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.032 10.032 0 013.856-5.18m3.08-1.562C10.22 5.067 11.102 5 12 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18" />
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="w-full rounded-lg bg-brand-navy py-3 text-xs font-semibold text-white shadow-md hover:bg-brand-lightNavy transition mt-2 cursor-pointer">
          Login
        </button>
      </form>

      <!-- Informasi Keamanan Sistem -->
      <div class="mt-8 pt-4 border-t border-slate-100 text-center">
        <p class="text-[11px] text-slate-400">
          Pastikan Anda menggunakan kredensial akun resmi yang terdaftar pada sistem.
        </p>
      </div>
    </div>

  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById('passwordInput');
      const eyeOpen = document.getElementById('eyeOpen');
      const eyeClosed = document.getElementById('eyeClosed');

      if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
      } else {
        input.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
      }
    }
  </script>
</body>
</html>
