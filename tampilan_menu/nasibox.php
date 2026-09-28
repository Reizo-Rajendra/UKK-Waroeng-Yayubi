<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../sistem_admin/view/index.php");
    exit();
}
require_once __DIR__ . '/../sistem_admin/database/koneksi.php';

// Ambil data menu kategori Nasi Box dari database secara realtime (dinamis)
$result = $conn->query("SELECT * FROM produk WHERE status = 1 AND kategori = 'Nasi Box' ORDER BY id_produk ASC");
$nasiboxList = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $nasiboxList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menu Nasi Box - Catering Yayubi</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="menu.css?v=<?php echo time(); ?>"> 
<style>
/* ─── HERO SLIDER BANNER KOMPAK & RAPI ─── */
.hero {
    position: relative !important;
    width: calc(100% - 32px) !important;
    max-width: 1050px !important;
    margin: 16px auto 0 !important;
    height: 150px !important;
    aspect-ratio: auto !important;
    border-radius: 18px !important;
    overflow: hidden !important;
    background: linear-gradient(135deg, #E8470A 0%, #D83A00 100%) !important;
    box-shadow: 0 6px 20px -4px rgba(232, 71, 10, 0.22), 0 2px 6px rgba(0, 0, 0, 0.04) !important;
    border: 1px solid rgba(254, 215, 170, 0.45) !important;
}

@media (min-width: 768px) {
    .hero {
        height: 190px !important;
        margin: 20px auto 0 !important;
        border-radius: 20px !important;
    }
}

@media (min-width: 1200px) {
    .hero {
        height: 205px !important;
    }
}

.slides {
    display: flex !important;
    width: 300% !important;
    height: 100% !important;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.slide {
    width: calc(100% / 3) !important;
    height: 100% !important;
    position: relative !important;
    flex-shrink: 0 !important;
}

.slide img,
.slide-bg-img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    object-position: center !important;
    display: block !important;
}

/* Tombol Slider Glassmorphism */
.slider-btn {
    position: absolute !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    background: rgba(255, 255, 255, 0.88) !important;
    color: #E8470A !important;
    border: none !important;
    width: 34px !important;
    height: 34px !important;
    border-radius: 50% !important;
    font-size: 18px !important;
    font-weight: bold !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 100 !important;
    cursor: pointer !important;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15) !important;
    backdrop-filter: blur(6px) !important;
    -webkit-backdrop-filter: blur(6px) !important;
    transition: all 0.2s ease !important;
}

.slider-btn:hover {
    background: #ffffff !important;
    transform: translateY(-50%) scale(1.1) !important;
    box-shadow: 0 5px 14px rgba(0, 0, 0, 0.22) !important;
}

.prev-btn { left: 12px !important; }
.next-btn { right: 12px !important; }

/* Logo Kecil */
.hero-logos {
    position: absolute !important;
    top: 10px !important;
    left: 12px !important;
    display: flex !important;
    gap: 6px !important;
    z-index: 50 !important;
}

.hero-logos img {
    width: 30px !important;
    height: 30px !important;
    border-radius: 50% !important;
    border: 2px solid white !important;
    background: white !important;
    object-fit: cover !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18) !important;
}

/* ─── HAPUS TOTAL BAR ORANYE TEBAL ─── */
.nav-back {
    background: transparent !important;
    padding: 12px 16px 0 !important;
    max-width: 1050px !important;
    margin: 0 auto !important;
    border: none !important;
    box-shadow: none !important;
}

.nav-back button {
    background: #ffffff !important;
    border: 1.5px solid #FDBA74 !important;
    color: #E8470A !important;
    font-family: 'Poppins', sans-serif !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    padding: 6px 16px !important;
    border-radius: 50px !important;
    box-shadow: 0 2px 6px rgba(232, 71, 10, 0.1) !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    transition: all 0.2s ease !important;
}

.nav-back button::before {
    content: '←' !important;
    font-size: 14px !important;
    font-weight: 800 !important;
}

.nav-back button:hover {
    background: #E8470A !important;
    color: #ffffff !important;
    border-color: #E8470A !important;
    transform: translateX(-3px) !important;
    box-shadow: 0 4px 10px rgba(232, 71, 10, 0.25) !important;
}

.main {
    max-width: 1050px !important;
    margin: 0 auto !important;
    padding: 16px 16px 60px !important;
}
</style>
</head>
<body>

<div class="hero">
    <div class="hero-logos">
        <img src="gambar/logo halal.jpeg" alt="Halal">
        <img src="gambar/logo yayubi.jpeg" alt="Logo">
    </div>
    
    <button class="slider-btn prev-btn" id="prevBtn" type="button">‹</button>
    <button class="slider-btn next-btn" id="nextBtn" type="button">›</button>

    <div class="slides" id="slides">
        <div class="slide"><img src="gambar/Banner nasi box.png" alt="Banner Nasi Box"></div>
        <div class="slide"><img src="gambar/Banner nasi tumpeng.png" alt="Banner Tumpeng"></div>
        <div class="slide"><img src="gambar/Banner snack.png" alt="Banner Snack"></div>
    </div>
</div>

<div class="nav-back">
  <button onclick="window.history.back()">Kembali</button>
</div>

<div class="main">
    <div class="section-header">
        <div class="section-title">Menu Nasi Box</div>
        <div class="section-sub">Praktis, Lezat, dan Higienis</div>
    </div>

    <div class="content-grid">
        <!-- DAFTAR MENU NASI BOX DARI DATABASE -->
        <div class="paket-grid">
            <?php if (empty($nasiboxList)): ?>
                <p style="text-align: center; grid-column: span 2; color: #888;">Menu nasi box sedang diperbarui oleh admin.</p>
            <?php else: ?>
                <?php foreach ($nasiboxList as $m): 
                    $cardId = 'c' . $m['id_produk'];
                    $fotoName = $m['gambar'] ?: 'default.png';
                    if (file_exists(__DIR__ . '/gambar/' . $fotoName)) {
                        $fotoUrl = 'gambar/' . htmlspecialchars($fotoName);
                    } else {
                        $fotoUrl = '../sistem_admin/uploads/' . htmlspecialchars($fotoName);
                    }
                    $hargaFormatted = 'Rp' . number_format($m['harga'], 0, ',', '.');
                ?>
                    <div class="card" id="<?php echo $cardId; ?>">
                        <div class="card-detail-overlay">
                            <div class="detail-title"><?php echo htmlspecialchars($m['nama_produk']); ?></div>
                            <div class="detail-text"><?php echo htmlspecialchars($m['deskripsi'] ?: 'Porsi hemat nikmat dan bergizi.'); ?></div>
                            <div class="card-price" style="color:white"><?php echo $hargaFormatted; ?></div>
                            <button class="close-btn" onclick="closeDetail('<?php echo $cardId; ?>')">✕ Tutup</button>
                        </div>
                        <img class="card-img" src="<?php echo $fotoUrl; ?>" alt="<?php echo htmlspecialchars($m['nama_produk']); ?>" onerror="this.src='gambar/Banner nasi box.png'">
                        <div class="card-body">
                            <div class="card-tag"><?php echo htmlspecialchars($m['nama_produk']); ?></div>
                            <div class="card-desc"><?php echo htmlspecialchars(mb_strimwidth($m['deskripsi'] ?: '', 0, 75, '...')); ?></div>
                            <div class="card-price"><?php echo $hargaFormatted; ?></div>
                            <button class="card-btn" onclick="showDetail('<?php echo $cardId; ?>')">👁 Lihat Detail</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- FORM PESANAN DENGAN MENU DINAMIS -->
        <div class="order-form">
            <div class="form-header"><h2>Form Pesanan Nasi Box</h2></div>
            <form action="menu.php" method="POST" class="form-body" onsubmit="return validateMenuForm(this)">
                <input type="hidden" name="kategori" value="NASI BOX">
                
                <div class="field">
                  <label><span class="icon">📅</span> Pilih Tanggal</label>
                  <input type="date" name="tanggal" required>
                </div>

                <div class="field">
                  <label><span class="icon">🕐</span> Jam Pengambilan</label>
                  <div class="time-select">
                    <select name="jam_mulai">
                      <option>07.00</option><option>08.00</option><option>09.00</option>
                      <option>10.00</option><option>11.00</option><option>12.00</option>
                      <option>13.00</option><option>14.00</option><option>15.00</option>
                      <option>16.00</option><option>17.00</option><option>18.00</option>
                    </select>
                    <span class="time-sep">—</span>
                    <select name="jam_selesai">
                      <option>08.00</option><option>09.00</option><option>10.00</option>
                      <option>11.00</option><option>12.00</option><option>13.00</option>
                      <option>14.00</option><option>15.00</option><option>16.00</option>
                      <option>17.00</option><option>18.00</option><option>19.00</option>
                    </select>
                  </div>
                </div>

                <div class="field">
                  <label><span class="icon">👤</span> Nama Pembeli</label>
                  <input type="text" name="nama" placeholder="Masukkan nama Anda" required>
                </div>

                <div class="field">
                  <label><span class="icon">📍</span> Lokasi Tujuan</label>
                  <input type="text" name="lokasi" placeholder="Masukkan alamat lengkap" required>
                </div>

                <div class="field">
                  <label><span class="icon">📞</span> Nomor Telpon</label>
                  <input type="tel" name="telp" placeholder="Contoh: 08..." required>
                </div>
                
                <div class="field">
                    <label>🍽 Pilih Menu & Jumlah Porsi</label>
                    <div class="menu-qty-list">
                        <?php foreach ($nasiboxList as $m): 
                            $hargaNum = (int)$m['harga'];
                            $stokNum  = (int)$m['stok'];
                            $habis    = ($stokNum <= 0);
                            $menipis  = (!$habis && $stokNum <= 5);
                        ?>
                        <div class="menu-qty-row <?php echo $habis ? 'out-of-stock' : ''; ?>" 
                             data-harga="<?php echo $hargaNum; ?>" 
                             data-stok="<?php echo $stokNum; ?>"
                             data-nama="<?php echo htmlspecialchars($m['nama_produk']); ?>">
                            <div class="menu-qty-info">
                                <span class="menu-qty-name"><?php echo htmlspecialchars($m['nama_produk']); ?></span>
                                <div class="menu-qty-meta">
                                    <span class="menu-qty-price">Rp<?php echo number_format($hargaNum, 0, ',', '.'); ?>/dus</span>
                                    <?php if ($habis): ?>
                                        <span class="stok-badge stok-habis">Stok Habis</span>
                                    <?php elseif ($menipis): ?>
                                        <span class="stok-badge stok-menipis">Sisa <?php echo $stokNum; ?> porsi</span>
                                    <?php else: ?>
                                        <span class="stok-badge stok-ok">Stok: <?php echo $stokNum; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="menu-qty-control <?php echo $habis ? 'disabled' : ''; ?>">
                                <button type="button" class="qty-btn qty-minus" onclick="changeQty(this, -1)" <?php echo $habis ? 'disabled' : ''; ?>>−</button>
                                <input type="number" 
                                       name="qty[<?php echo $m['id_produk']; ?>]" 
                                       data-nama="<?php echo htmlspecialchars($m['nama_produk']); ?>"
                                       data-harga="<?php echo $hargaNum; ?>"
                                       data-stok="<?php echo $stokNum; ?>"
                                       value="0" min="0" max="<?php echo $stokNum; ?>" 
                                       class="qty-input" 
                                       onchange="updateTotal()" 
                                       <?php echo $habis ? 'disabled' : 'readonly'; ?>>
                                <button type="button" class="qty-btn qty-plus" onclick="changeQty(this, 1)" <?php echo $habis ? 'disabled' : ''; ?>>+</button>
                            </div>
                            <div class="menu-qty-subtotal" id="sub_<?php echo $m['id_produk']; ?>"><?php echo $habis ? '<span style="color:#e11d48;font-size:11px">Habis</span>' : '—'; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Live Total -->
                    <div class="order-total-box" id="totalBox" style="display:none;">
                        <span class="total-label">🧾 Total Estimasi:</span>
                        <span class="total-amount" id="totalAmount">Rp 0</span>
                    </div>
                </div>

                <div class="form-note">💳 Pembayaran Online Praktis via QRIS Midtrans — Langsung Terverifikasi Lunas</div>

                <button type="submit" class="btn-order">Pesan &amp; Bayar Sekarang</button>
            </form>
        </div>
    </div>
</div>

<style>
/* ── Quantity Stepper Styles ── */
.menu-qty-list { display:flex; flex-direction:column; gap:10px; margin-top:8px; }
.menu-qty-row { display:flex; align-items:center; justify-content:space-between; gap:8px; background:#fff8f5; border:1.5px solid #fde0cc; border-radius:12px; padding:10px 14px; transition:border-color .2s,background .2s; }
.menu-qty-row.has-qty { border-color:#E8470A; background:#fff3ec; }
.menu-qty-row.out-of-stock { background:#f9f9f9; border-color:#e2e8f0; opacity:.65; pointer-events:none; }
.menu-qty-info { flex:1; min-width:0; }
.menu-qty-meta { display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-top:2px; }
.menu-qty-name { display:block; font-size:13px; font-weight:700; color:#1a1a1a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.menu-qty-price { font-size:11px; color:#E8470A; font-weight:600; }
/* Stok Badges */
.stok-badge { font-size:10px; font-weight:700; padding:2px 8px; border-radius:50px; display:inline-block; }
.stok-ok    { background:#dcfce7; color:#15803d; }
.stok-menipis { background:#fef9c3; color:#a16207; }
.stok-habis { background:#fee2e2; color:#b91c1c; }
/* Control */
.menu-qty-control { display:flex; align-items:center; background:#fff; border:1.5px solid #fde0cc; border-radius:50px; overflow:hidden; flex-shrink:0; }
.menu-qty-control.disabled { opacity:.4; pointer-events:none; }
.qty-btn { width:32px; height:32px; background:transparent; border:none; font-size:18px; font-weight:800; color:#E8470A; cursor:pointer; display:flex; align-items:center; justify-content:center; font-family:'Poppins',sans-serif; transition:background .15s; line-height:1; }
.qty-btn:hover:not(:disabled) { background:#fff0e8; }
.qty-btn:disabled { color:#ccc; cursor:not-allowed; }
.qty-input { width:36px; text-align:center; border:none; border-left:1px solid #fde0cc; border-right:1px solid #fde0cc; font-size:14px; font-weight:700; color:#1a1a1a; font-family:'Poppins',sans-serif; background:transparent; outline:none; -moz-appearance:textfield; padding:0; height:32px; }
.qty-input::-webkit-outer-spin-button,.qty-input::-webkit-inner-spin-button{-webkit-appearance:none;}
.menu-qty-subtotal { font-size:12px; font-weight:700; color:#E8470A; min-width:70px; text-align:right; flex-shrink:0; }
.order-total-box { display:flex; align-items:center; justify-content:space-between; margin-top:14px; background:linear-gradient(135deg,#E8470A,#F97316); color:white; border-radius:14px; padding:14px 18px; }
.total-label { font-size:13px; font-weight:700; }
.total-amount { font-size:20px; font-weight:800; }
</style>

<script>
    function changeQty(btn, delta) {
        const control = btn.closest('.menu-qty-control');
        const input = control.querySelector('.qty-input');
        const stok = parseInt(input.dataset.stok) || 0;
        let val = parseInt(input.value) || 0;
        val = Math.max(0, Math.min(stok, val + delta));
        input.value = val;
        // Update tampilan tombol + jika sudah di max stok
        const plusBtn = control.querySelector('.qty-plus');
        if (plusBtn) plusBtn.disabled = (val >= stok);
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.qty-input:not(:disabled)').forEach(input => {
            const qty = parseInt(input.value) || 0;
            const harga = parseInt(input.dataset.harga) || 0;
            const stok = parseInt(input.dataset.stok) || 0;
            const id = input.name.match(/\[(\d+)\]/)?.[1];
            const subtotal = qty * harga;
            total += subtotal;
            const row = input.closest('.menu-qty-row');
            row.classList.toggle('has-qty', qty > 0);
            // Disable tombol + kalau qty sudah di max (stok)
            const plusBtn = input.closest('.menu-qty-control')?.querySelector('.qty-plus');
            if (plusBtn) plusBtn.disabled = (qty >= stok && stok > 0);
            if (id) {
                const subEl = document.getElementById('sub_' + id);
                if (subEl) subEl.textContent = qty > 0 ? 'Rp' + subtotal.toLocaleString('id-ID') : '—';
            }
        });
        const totalBox = document.getElementById('totalBox');
        const totalAmount = document.getElementById('totalAmount');
        totalBox.style.display = total > 0 ? 'flex' : 'none';
        totalAmount.textContent = 'Rp' + total.toLocaleString('id-ID');
    }

    function validateMenuForm(form) {
        const inputs = form.querySelectorAll('.qty-input:not(:disabled)');
        let hasQty = false;
        inputs.forEach(inp => { if (parseInt(inp.value) > 0) hasQty = true; });
        if (!hasQty) {
            alert('Silakan pilih minimal 1 porsi menu nasi box sebelum melanjutkan!');
            return false;
        }
        return true;
    }
    function showDetail(id) { document.getElementById(id).classList.add('zoomed'); }
    function closeDetail(id) { document.getElementById(id).classList.remove('zoomed'); }
    
    let cur = 0;
    const slidesEl = document.getElementById('slides');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    let timer;
    function updateSlider() { slidesEl.style.transform = `translateX(-${cur * (100 / 3)}%)`; }
    function startTimer() {
        clearInterval(timer);
        timer = setInterval(() => { cur = (cur + 1) % 3; updateSlider(); }, 4000);
    }
    nextBtn.addEventListener('click', () => { cur = (cur + 1) % 3; updateSlider(); startTimer(); });
    prevBtn.addEventListener('click', () => { cur = (cur - 1 + 3) % 3; updateSlider(); startTimer(); });
    startTimer();
</script>
</body>
</html>
