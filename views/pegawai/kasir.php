<?php
// views/pegawai/kasir.php
// Tampilan Point of Sales (POS) Kasir Warkop Kolega - KolegaOps
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kasir POS - KolegaOps Warkop Kolega</title>
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
<body class="bg-brand-bgLight text-slate-800 min-h-screen flex overflow-hidden text-sm">

  <!-- Sidebar Navigasi Dinamis KolegaOps -->
  <?php require_once BASE_PATH . '/views/layouts/sidebar.php'; ?>

  <!-- Area Konten Utama Kasir -->
  <main class="flex-1 p-6 overflow-y-auto min-h-screen flex flex-col">
    
    <!-- Header Shift Kasir -->
    <div class="bg-white px-6 py-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex justify-between items-center text-sm">
      <div class="font-medium">
        <span class="font-bold text-slate-900"><?= htmlspecialchars($namaKasirLabel ?? 'Kasir') ?> Shift <?= htmlspecialchars($namaShift ?? 'Aktif') ?></span> &bull; <?= htmlspecialchars($tanggalTeks ?? date('d M Y')) ?>
      </div>
      <div class="bg-blue-50 text-brand-navy px-3 py-1 rounded-full text-xs font-bold border border-blue-200">
        Shift Aktif
      </div>
    </div>

    <!-- Notifikasi Flash Sukses / Error -->
    <?php if (!empty($pesanSukses)): ?>
      <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-semibold text-xs flex items-center space-x-3 shadow-sm">
        <span class="text-base">✅</span>
        <span><?= htmlspecialchars($pesanSukses) ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($pesanError)): ?>
      <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-semibold text-xs flex items-center space-x-3 shadow-sm">
        <span class="text-base">⚠️</span>
        <span><?= htmlspecialchars($pesanError) ?></span>
      </div>
    <?php endif; ?>

    <!-- Layout 2 Kolom -->
    <div class="grid grid-cols-12 gap-6 flex-1 h-full">
      
      <!-- Kolom Kiri: Katalog & Pencarian Menu -->
      <div class="col-span-7 space-y-4 flex flex-col h-full">
        <!-- Input Pencarian Menu -->
        <div class="relative">
          <input type="text" id="searchInput" onkeyup="filterMenu()" placeholder="Cari nama menu..." 
                 class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm pl-11 focus:outline-none focus:ring-2 focus:ring-brand-navy shadow-sm">
          <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 text-base">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search preview-icon">
              <path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/>
            </svg>
          </span>
        </div>

        <!-- Filter Kategori Horizontal (Dinamis dari Database) -->
        <div class="flex space-x-2 overflow-x-auto pb-1" id="categoryFilter">
          <button type="button" onclick="setCategory('Semua', this)" 
                  class="btn-cat px-4 py-2.5 rounded-lg bg-brand-navy text-white text-xs font-bold shadow shrink-0 cursor-pointer">
            Semua
          </button>
          <?php if (!empty($daftarKategori)): ?>
            <?php foreach ($daftarKategori as $kat): ?>
              <button type="button" onclick="setCategory('<?= htmlspecialchars($kat, ENT_QUOTES, 'UTF-8') ?>', this)" 
                      class="btn-cat px-4 py-2.5 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 shrink-0 cursor-pointer">
                <?= htmlspecialchars($kat) ?>
              </button>
            <?php endforeach; ?>
          <?php else: ?>
            <!-- Fallback Kategori Standar Warkop jika kategori kosong -->
            <button type="button" onclick="setCategory('Varian Kopi', this)" class="btn-cat px-4 py-2.5 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 shrink-0 cursor-pointer">Varian Kopi</button>
            <button type="button" onclick="setCategory('Milk Based', this)" class="btn-cat px-4 py-2.5 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 shrink-0 cursor-pointer">Milk Based</button>
            <button type="button" onclick="setCategory('Hot/Ice', this)" class="btn-cat px-4 py-2.5 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 shrink-0 cursor-pointer">Hot/Ice</button>
            <button type="button" onclick="setCategory('Snack', this)" class="btn-cat px-4 py-2.5 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 shrink-0 cursor-pointer">Snack</button>
            <button type="button" onclick="setCategory('Nakam', this)" class="btn-cat px-4 py-2.5 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 shrink-0 cursor-pointer">Nakam</button>
          <?php endif; ?>
        </div>

        <!-- Grid Daftar Menu -->
        <div class="grid grid-cols-3 gap-4 overflow-y-auto max-h-[calc(100vh-250px)] pb-6 pr-1" id="menuGrid">
          <!-- Rendered by JavaScript dari Data Database PostgreSQL -->
        </div>
      </div>

      <!-- Kolom Kanan: Keranjang & Pembayaran -->
      <div class="col-span-5 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between h-[calc(100vh-140px)]">
        <div class="flex flex-col h-full overflow-hidden">
          <div class="flex justify-between items-center pb-3 border-b border-slate-200">
            <div>
              <h3 class="font-extrabold text-slate-900 text-base">Keranjang</h3>
              <p class="text-xs text-slate-400 mt-0.5">Sesi <?= htmlspecialchars($namaShift ?? 'Sore') ?> &bull; <?= htmlspecialchars($namaKasirLabel ?? 'Kasir') ?></p>
            </div>
            <span id="cartCount" class="bg-slate-100 text-slate-700 font-bold text-xs px-3 py-1 rounded-full">0 item</span>
          </div>

          <!-- List Item Keranjang -->
          <div class="mt-4 space-y-3 overflow-y-auto flex-1 pr-1" id="cartList">
            <div id="emptyCartState" class="text-center py-12 text-slate-400">
              <span class="text-4xl">🛒</span>
              <p class="text-xs font-semibold mt-2">Keranjang masih kosong</p>
              <p class="text-[11px] text-slate-400">Pilih menu di sebelah kiri untuk menambahkan pesanan</p>
            </div>
          </div>
        </div>

        <!-- Bagian Total & Form Pembayaran -->
        <div class="pt-4 border-t border-slate-200 space-y-4 bg-white mt-2">
          <div class="flex justify-between items-center">
            <span class="text-sm font-bold text-slate-600">Total Pembayaran</span>
            <span class="text-2xl font-extrabold text-brand-navy" id="totalBayarText">Rp 0</span>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Metode Pembayaran</label>
            <div class="grid grid-cols-2 gap-2">
              <button type="button" id="btnCash" onclick="setPaymentMethod('Cash')" 
                      class="py-2.5 rounded-xl bg-brand-navy text-white text-xs font-bold shadow cursor-pointer">
                Cash
              </button>
              <button type="button" id="btnQris" onclick="setPaymentMethod('QRIS')" 
                      class="py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 cursor-pointer">
                QRIS
              </button>
            </div>
          </div>

          <div id="cashInputContainer">
            <label for="uangDiterimaInput" class="block text-xs font-bold text-slate-700 mb-1.5">Uang Diterima (Rp)</label>
            <input type="number" id="uangDiterimaInput" oninput="hitungKembalian()" placeholder="0" 
                   class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold focus:ring-2 focus:ring-brand-navy outline-none">
          </div>

          <div class="flex justify-between items-center text-sm py-1">
            <span class="text-slate-500 font-medium">Kembalian</span>
            <span class="font-extrabold text-emerald-600 text-base" id="kembalianText">Rp 0</span>
          </div>

          <button type="button" id="btnProsesBayar" onclick="prosesBayar()" disabled
                  class="w-full rounded-xl bg-slate-300 py-3.5 text-sm font-bold text-white transition cursor-not-allowed">
            Bayar
          </button>
        </div>

      </div>

    </div>

    <!-- Hidden Form untuk Submit Transaksi ke Backend Controller -->
    <form id="formCheckout" action="index.php?controller=transaksi&action=prosesCheckout" method="POST" class="hidden">
      <?= csrf_field() ?>
      <input type="hidden" name="items_json" id="inputItemsJson">
      <input type="hidden" name="metode_pembayaran" id="inputMetodePembayaran">
      <input type="hidden" name="nominal_bayar" id="inputNominalBayar">
    </form>

  </main>

  <script>
    // Injeksi katalog menu dinamis dari database PostgreSQL
    const menuData = <?= json_encode($daftarMenu ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const namaKasirAktif = "<?= htmlspecialchars($namaKasirLabel ?? 'Kasir', ENT_QUOTES, 'UTF-8') ?>";

    let cart = [];
    let currentCategory = 'Semua';
    let paymentMethod = 'Cash';

    function formatRupiah(num) {
      return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    }

    function renderMenu() {
      const grid = document.getElementById('menuGrid');
      const search = document.getElementById('searchInput').value.toLowerCase().trim();
      grid.innerHTML = '';

      const filtered = menuData.filter(item => {
        const itemNama = (item.nama || item.nama_menu || '').toLowerCase();
        const itemKat  = (item.kategori || '');
        const matchCat = (currentCategory === 'Semua') || (itemKat.toLowerCase() === currentCategory.toLowerCase());
        const matchSearch = itemNama.includes(search);
        return matchCat && matchSearch;
      });

      if (filtered.length === 0) {
        grid.innerHTML = `
          <div class="col-span-3 text-center py-12 text-slate-400">
            <span class="text-4xl">☕</span>
            <p class="text-xs font-semibold mt-2">Tidak ada menu yang sesuai</p>
          </div>
        `;
        return;
      }

      filtered.forEach(item => {
        const itemId = parseInt(item.id || item.id_menu, 10);
        const itemNama = item.nama || item.nama_menu;
        const itemKat = item.kategori || 'Umum';
        const itemHarga = parseFloat(item.harga) || 0;

        const card = document.createElement('div');
        card.className = "bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-brand-navy hover:shadow-md transition cursor-pointer";
        card.onclick = () => tambahKeKeranjang(itemId);
        
        card.innerHTML = `
          <div>
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">${itemKat}</span>
            <h4 class="font-bold text-slate-900 text-sm mt-1 leading-snug">${itemNama}</h4>
          </div>
          <div class="mt-4 flex items-center justify-between">
            <span class="text-sm font-extrabold text-brand-navy">${formatRupiah(itemHarga)}</span>
            <button type="button" onclick="event.stopPropagation(); tambahKeKeranjang(${itemId})" 
                    class="h-8 w-8 rounded-lg bg-slate-100 hover:bg-brand-navy hover:text-white flex items-center justify-center text-sm font-bold transition text-slate-700 cursor-pointer">
              ＋
            </button>
          </div>
        `;
        grid.appendChild(card);
      });
    }

    function setCategory(cat, btn) {
      currentCategory = cat;
      document.querySelectorAll('.btn-cat').forEach(b => {
        b.className = "btn-cat px-4 py-2.5 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-100 shrink-0 cursor-pointer";
      });
      btn.className = "btn-cat px-4 py-2.5 rounded-lg bg-brand-navy text-white text-xs font-bold shadow shrink-0 cursor-pointer";
      renderMenu();
    }

    function filterMenu() {
      renderMenu();
    }

    function tambahKeKeranjang(id) {
      const item = menuData.find(m => parseInt(m.id || m.id_menu, 10) === id);
      if (!item) return;

      const itemId = parseInt(item.id || item.id_menu, 10);
      const exist = cart.find(c => c.id === itemId);

      if (exist) {
        exist.qty += 1;
      } else {
        cart.push({
          id: itemId,
          id_menu: itemId,
          nama: item.nama || item.nama_menu,
          kategori: item.kategori || 'Umum',
          harga: parseFloat(item.harga) || 0,
          qty: 1
        });
      }
      renderCart();
    }

    function ubahQty(id, delta) {
      const item = cart.find(c => c.id === id);
      if (item) {
        item.qty += delta;
        if (item.qty <= 0) {
          cart = cart.filter(c => c.id !== id);
        }
      }
      renderCart();
    }

    function renderCart() {
      const container = document.getElementById('cartList');
      const cartCount = document.getElementById('cartCount');
      container.innerHTML = '';

      if (cart.length === 0) {
        container.innerHTML = `
          <div id="emptyCartState" class="text-center py-12 text-slate-400">
            <span class="text-4xl">🛒</span>
            <p class="text-xs font-semibold mt-2">Keranjang masih kosong</p>
            <p class="text-[11px] text-slate-400">Pilih menu di sebelah kiri untuk menambahkan pesanan</p>
          </div>
        `;
        cartCount.innerText = '0 item';
        updateTotal(0);
        return;
      }

      let totalItems = 0;
      let totalHarga = 0;

      cart.forEach(item => {
        totalItems += item.qty;
        totalHarga += (item.harga * item.qty);

        const el = document.createElement('div');
        el.className = "flex justify-between items-center p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs";
        el.innerHTML = `
          <div class="flex-1 pr-2">
            <p class="font-bold text-slate-800 text-sm">${item.nama}</p>
            <p class="text-xs text-slate-500 mt-0.5">${formatRupiah(item.harga)}</p>
          </div>
          <div class="flex items-center space-x-2">
            <button type="button" onclick="ubahQty(${item.id}, -1)" class="h-7 w-7 rounded-md bg-white border border-slate-300 font-bold text-slate-600 hover:bg-slate-100 flex items-center justify-center text-xs cursor-pointer">-</button>
            <span class="font-bold text-sm text-slate-800 w-5 text-center">${item.qty}</span>
            <button type="button" onclick="ubahQty(${item.id}, 1)" class="h-7 w-7 rounded-md bg-white border border-slate-300 font-bold text-slate-600 hover:bg-slate-100 flex items-center justify-center text-xs cursor-pointer">+</button>
            <span class="font-extrabold text-sm text-slate-900 ml-2 min-w-[70px] text-right">${formatRupiah(item.harga * item.qty)}</span>
          </div>
        `;
        container.appendChild(el);
      });

      cartCount.innerText = `${totalItems} item`;
      updateTotal(totalHarga);
    }

    function updateTotal(total) {
      document.getElementById('totalBayarText').innerText = formatRupiah(total);
      hitungKembalian();
    }

    function setPaymentMethod(method) {
      paymentMethod = method;
      const btnCash = document.getElementById('btnCash');
      const btnQris = document.getElementById('btnQris');
      const cashContainer = document.getElementById('cashInputContainer');

      if (method === 'Cash') {
        btnCash.className = "py-2.5 rounded-xl bg-brand-navy text-white text-xs font-bold shadow cursor-pointer";
        btnQris.className = "py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 cursor-pointer";
        cashContainer.style.display = 'block';
      } else {
        btnQris.className = "py-2.5 rounded-xl bg-brand-navy text-white text-xs font-bold shadow cursor-pointer";
        btnCash.className = "py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 cursor-pointer";
        cashContainer.style.display = 'none';
      }
      hitungKembalian();
    }

    function hitungKembalian() {
      const total = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
      const btnBayar = document.getElementById('btnProsesBayar');
      const kembalianText = document.getElementById('kembalianText');

      if (total === 0) {
        btnBayar.disabled = true;
        btnBayar.className = "w-full rounded-xl bg-slate-300 py-3.5 text-sm font-bold text-white transition cursor-not-allowed";
        btnBayar.innerText = "Bayar";
        kembalianText.innerText = "Rp 0";
        return;
      }

      if (paymentMethod === 'QRIS') {
        btnBayar.disabled = false;
        btnBayar.className = "w-full rounded-xl bg-brand-navy py-3.5 text-sm font-bold text-white shadow-md hover:bg-brand-lightNavy transition cursor-pointer";
        btnBayar.innerText = `Bayar QRIS · ${formatRupiah(total)}`;
        kembalianText.innerText = "Rp 0 (Nontunai)";
        return;
      }

      const inputVal = parseInt(document.getElementById('uangDiterimaInput').value, 10) || 0;
      const kembalian = inputVal - total;

      if (inputVal >= total) {
        btnBayar.disabled = false;
        btnBayar.className = "w-full rounded-xl bg-brand-navy py-3.5 text-sm font-bold text-white shadow-md hover:bg-brand-lightNavy transition cursor-pointer";
        btnBayar.innerText = `Bayar Cash · ${formatRupiah(total)}`;
        kembalianText.innerText = formatRupiah(kembalian);
      } else {
        btnBayar.disabled = true;
        btnBayar.className = "w-full rounded-xl bg-slate-300 py-3.5 text-sm font-bold text-white transition cursor-not-allowed";
        btnBayar.innerText = `Uang Kurang ${formatRupiah(Math.abs(kembalian))}`;
        kembalianText.innerText = "Rp 0";
      }
    }

    function prosesBayar() {
      if (cart.length === 0) {
        alert("Keranjang masih kosong!");
        return;
      }

      const total = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
      const uangDiterima = parseInt(document.getElementById('uangDiterimaInput').value, 10) || total;
      
      if (paymentMethod === 'Cash' && uangDiterima < total) {
        alert("Uang diterima kurang dari total pembayaran!");
        return;
      }

      // Isi form checkout hidden
      document.getElementById('inputItemsJson').value = JSON.stringify(cart);
      document.getElementById('inputMetodePembayaran').value = (paymentMethod === 'QRIS') ? 'qris' : 'tunai';
      document.getElementById('inputNominalBayar').value = (paymentMethod === 'QRIS') ? total : uangDiterima;

      // Submit ke controller backend
      document.getElementById('formCheckout').submit();
    }

    // Inisialisasi awal
    renderMenu();
  </script>
</body>
</html>
