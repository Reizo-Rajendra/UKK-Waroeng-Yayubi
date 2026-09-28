<?php
// ── Koneksi database ──
require_once __DIR__ . '/../../sistem_admin/database/koneksi.php';

// ── Ambil semua produk AKTIF dari database (status=1), dikelompokkan per kategori ──
$allProduk = [];
$result = $conn->query("SELECT * FROM produk WHERE status = 1 ORDER BY FIELD(kategori,'Nasi Box','Tumpeng','Snack Box'), id_produk ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $allProduk[] = $row;
    }
}

// ── Helper: tentukan URL gambar ──
function getGambarUrl($gambar) {
    $nama = $gambar ?: 'default.png';
    // Coba folder gambar lokal di profil_bisnis/Code/Gambar/ dulu
    $localPath = __DIR__ . '/Gambar/' . $nama;
    if (file_exists($localPath)) {
        return 'Gambar/' . htmlspecialchars($nama);
    }
    // Coba folder uploads sistem_admin
    $uploadPath = __DIR__ . '/../../sistem_admin/uploads/' . $nama;
    if (file_exists($uploadPath)) {
        return '../../sistem_admin/uploads/' . htmlspecialchars($nama);
    }
    // Fallback banner
    return 'Gambar/Banner nasi box.png';
}

// ── Mapping kategori DB → slug tab filter & label badge ──
function getKategoriSlug($kategori) {
    $map = [
        'Nasi Box'  => 'nasi-box',
        'Tumpeng'   => 'tumpeng',
        'Snack Box' => 'snack',
    ];
    return $map[$kategori] ?? strtolower(str_replace(' ', '-', $kategori));
}
function getBadgeLabel($kategori) {
    return htmlspecialchars($kategori);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu Catering - Sajian Penuh Martabat</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--orange:#E8470A;--orange-light:#F97316;--orange-pale:#FDBA74;--cream:#FFF8F0;--bone:#FAF5EE;--text-dark:#1a1a1a;--text-mid:#444;--border:#f0e0d0}
    body{font-family:'Poppins',sans-serif;background:linear-gradient(to top,#E8470A 0%,#F97316 15%,#FDE8D8 35%,#FFF8F0 60%,#FAF5EE 100%);min-height:100vh;color:var(--text-dark)}

    /* ─── HERO SLIDER ─── */
    .hero{position:relative;width:calc(100% - 24px);max-width:1050px;margin:14px auto 0;height:150px;border-radius:18px;overflow:hidden;background:linear-gradient(135deg,#E8470A 0%,#c73d08 100%);box-shadow:0 6px 20px -4px rgba(232,71,10,.22),0 2px 6px rgba(0,0,0,.04);border:1px solid rgba(254,215,170,.4)}
    @media(min-width:768px){.hero{height:190px;margin:18px auto 0;border-radius:20px}}
    @media(min-width:1200px){.hero{height:205px}}
    .slides{display:flex;width:300%;height:100%;transition:transform .7s cubic-bezier(.77,0,.18,1)}
    .slide{width:calc(100%/3);height:100%;position:relative;flex-shrink:0}
    .slide img{width:100%;height:100%;object-fit:cover;display:block}
    .hero-logos{position:absolute;top:14px;left:16px;display:flex;gap:8px;z-index:10}
    .hero-logos img{width:40px;height:40px;border-radius:50%;border:2px solid rgba(255,255,255,.7);object-fit:cover;background:#fff}
    .slider-dots{position:absolute;bottom:14px;left:50%;transform:translateX(-50%);display:flex;gap:7px;z-index:10}
    .dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.5);border:none;cursor:pointer;transition:all .3s;padding:0}
    .dot.active{background:#fff;width:22px;border-radius:4px}
    .slider-arrow{position:absolute;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.25);border:none;cursor:pointer;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;z-index:10;transition:background .2s}
    .slider-arrow:hover{background:rgba(255,255,255,.45)}
    .slider-arrow.prev{left:12px}.slider-arrow.next{right:12px}
    .slide-custom{overflow:hidden}
    .slide-bg-img{width:100%;height:100%;object-fit:cover;object-position:center;display:block}

    /* ─── NAV BACK ─── */
    .btn-solid{margin:14px auto 0;max-width:1050px;padding:0 16px;display:block;background:transparent!important;box-shadow:none!important;border:none!important;transform:none!important}
    .btn-kembali{display:inline-flex;align-items:center;gap:6px;background-color:#fff;color:#E8470A;border:1.5px solid #FDBA74;padding:7px 18px;border-radius:50px;font-family:'Poppins',sans-serif;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 2px 8px rgba(232,71,10,.12);transition:all .25s ease;outline:none}
    .btn-kembali:hover{background-color:#E8470A;color:#fff;border-color:#E8470A;transform:translateX(-3px);box-shadow:0 4px 12px rgba(232,71,10,.25)}
    .btn-kembali span{font-size:16px;line-height:1}

    /* ─── MAIN ─── */
    .main{max-width:1100px;margin:0 auto;padding:32px 16px 60px}
    .section-header{text-align:center;margin-bottom:20px}
    .badge-terlaris{display:inline-flex;align-items:center;gap:7px;background:var(--orange);color:#fff;border-radius:50px;padding:6px 18px;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;margin-bottom:12px}
    .badge-terlaris::before{content:'📈';font-size:13px}
    .section-title{font-size:clamp(1.5rem,4vw,2rem);font-weight:800;color:var(--orange)}
    .section-sub{font-size:13px;color:var(--orange-light);margin-top:2px}

    /* ─── ORDER BAR ─── */
    .order-bar{display:flex;justify-content:center;align-items:center;gap:10px;flex-wrap:wrap;margin:15px auto 25px;max-width:650px}
    .order-bar-btn{display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#E8470A 0%,#F97316 100%);color:white;padding:9px 18px;border-radius:50px;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 4px 12px rgba(232,71,10,.25);transition:transform .2s,box-shadow .2s}
    .order-bar-btn:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(232,71,10,.4)}

    /* ─── SEARCH BAR ─── */
    .search-wrap{max-width:520px;margin:0 auto 20px;position:relative}
    .search-wrap input{width:100%;padding:12px 44px 12px 18px;border:2px solid #fde0cc;border-radius:50px;font-family:'Poppins',sans-serif;font-size:13px;color:var(--text-dark);background:#fff;outline:none;box-shadow:0 4px 16px rgba(232,71,10,.10);transition:border .2s,box-shadow .2s}
    .search-wrap input:focus{border-color:var(--orange-light);box-shadow:0 4px 20px rgba(232,71,10,.18)}
    .search-wrap input::placeholder{color:#bbb}
    .search-icon{position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:16px;pointer-events:none}

    /* ─── CATEGORY TABS ─── */
    .category-tabs{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin-bottom:28px}
    .tab-btn{padding:6px 16px;border-radius:50px;border:1.5px solid var(--orange);background:transparent;color:var(--orange);font-family:'Poppins',sans-serif;font-size:12px;font-weight:600;cursor:pointer;transition:background .2s,color .2s}
    .tab-btn.active,.tab-btn:hover{background:var(--orange);color:#fff}

    /* ─── CARDS ─── */
    .paket-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
    .card{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 20px rgba(232,71,10,.10);border:1px solid #fde0cc;transition:transform .2s,box-shadow .2s;position:relative}
    .card:hover{transform:translateY(-4px);box-shadow:0 10px 30px rgba(232,71,10,.18)}
    .card.hidden{display:none}
    .card-img{width:100%;height:110px;object-fit:cover;display:block;background:#f5e8de}
    .card-body{padding:9px 10px 11px}
    .card-category-badge{display:inline-block;font-size:8px;font-weight:700;color:#fff;background:var(--orange-light);border-radius:50px;padding:2px 7px;letter-spacing:.04em;text-transform:uppercase;margin-bottom:3px}
    .card-tag{font-size:10px;font-weight:700;color:var(--orange);letter-spacing:.05em;text-transform:uppercase;margin-bottom:3px}
    .card-desc{font-size:10px;color:#777;line-height:1.4;margin-bottom:8px}
    .card-price-label{font-size:9px;color:#aaa;margin-bottom:1px}
    .card-price{font-size:13px;font-weight:800;color:var(--orange)}
    .card-price span{font-size:10px;font-weight:500;color:#aaa}
    .card-btn{display:flex;align-items:center;gap:4px;justify-content:center;width:100%;margin-top:8px;padding:6px 0;border-radius:50px;border:1.5px solid var(--orange);background:transparent;color:var(--orange);font-family:'Poppins',sans-serif;font-size:10px;font-weight:600;cursor:pointer;transition:background .2s,color .2s}
    .card-btn:hover{background:var(--orange);color:#fff}

    /* ─── CARD DETAIL OVERLAY ─── */
    .card-detail-overlay{position:absolute;inset:0;background:rgba(232,71,10,.92);border-radius:14px;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:14px;opacity:0;pointer-events:none;transition:opacity .3s ease;z-index:5}
    .card.zoomed .card-detail-overlay{opacity:1;pointer-events:auto}
    .card-detail-overlay .detail-title{font-size:12px;font-weight:800;color:#fff;margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em;text-align:center}
    .card-detail-overlay .detail-text{font-size:10px;color:rgba(255,255,255,.92);text-align:center;line-height:1.5;margin-bottom:10px}
    .card-detail-overlay .detail-price{font-size:15px;font-weight:800;color:#fff;margin-bottom:3px}
    .card-detail-overlay .detail-price-label{font-size:10px;color:rgba(255,255,255,.7)}
    .card-detail-overlay .close-btn{margin-top:12px;background:rgba(255,255,255,.25);border:1.5px solid rgba(255,255,255,.5);color:#fff;border-radius:50px;padding:4px 14px;font-family:'Poppins',sans-serif;font-size:10px;font-weight:600;cursor:pointer;transition:background .2s}
    .card-detail-overlay .close-btn:hover{background:rgba(255,255,255,.4)}

    /* ─── NO RESULT ─── */
    .no-result{text-align:center;padding:40px 20px;color:#aaa;font-size:14px;display:none;grid-column:1 / -1}
    .no-result span{font-size:36px;display:block;margin-bottom:8px}

    @media(max-width:900px){.paket-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:500px){.paket-grid{grid-template-columns:1fr}}
  </style>
</head>

<body>

  <!-- ══════════ HERO SLIDER ══════════ -->
  <div class="hero">
    <div class="hero-logos">
      <img src="Gambar/logo halal.jpeg" alt="Halal">
      <img src="Gambar/logo yayubi.jpeg" alt="Yayubi">
    </div>
    <div class="slides" id="slides">
      <div class="slide slide-custom"><img class="slide-bg-img" src="Gambar/Banner nasi tumpeng.png" alt="Nasi Tumpeng"></div>
      <div class="slide slide-custom"><img class="slide-bg-img" src="Gambar/Banner nasi box.png" alt="Nasi Box"></div>
      <div class="slide slide-custom"><img class="slide-bg-img" src="Gambar/Banner snack.png" alt="Snack"></div>
    </div>
    <button class="slider-arrow prev" onclick="moveSlide(-1)">&#8249;</button>
    <button class="slider-arrow next" onclick="moveSlide(1)">&#8250;</button>
    <div class="slider-dots">
      <button class="dot active" onclick="goSlide(0)"></button>
      <button class="dot" onclick="goSlide(1)"></button>
      <button class="dot" onclick="goSlide(2)"></button>
    </div>
  </div>

  <!-- ══════════ TOMBOL KEMBALI ══════════ -->
  <div class="btn-solid">
    <a href="../../Home page/home.php" class="btn-kembali">
      <span>←</span> Kembali
    </a>
  </div>

  <!-- ══════════ MAIN ══════════ -->
  <div class="main">

    <div class="section-header">
      <div><span class="badge-terlaris">Paket Terlaris</span></div>
      <div class="section-title">Seluruh Menu</div>
      <div class="section-sub">Pilihan terbaik pelanggan kami</div>
    </div>

    <!-- ── ORDER SHORTCUT BAR ── -->
    <div class="order-bar">
      <a href="../../tampilan_menu/nasibox.php" class="order-bar-btn">🍱 Pesan Nasi Box</a>
      <a href="../../tampilan_menu/tumpeng.php" class="order-bar-btn">🍚 Pesan Tumpeng</a>
      <a href="../../tampilan_menu/snack.php" class="order-bar-btn">🍪 Pesan Snack Box</a>
    </div>

    <!-- ── SEARCH BAR ── -->
    <div class="search-wrap">
      <input type="text" id="search-input" placeholder="Cari menu makanan..." oninput="filterMenu()">
      <span class="search-icon">🔍</span>
    </div>

    <!-- ── CATEGORY TABS (dibuat dinamis dari DB) ── -->
    <div class="category-tabs">
      <button class="tab-btn active" onclick="filterCategory('semua', this)">Semua</button>
      <?php
        // Ambil kategori unik dari DB untuk tab filter
        $kategoriList = [];
        foreach ($allProduk as $p) {
            $slug = getKategoriSlug($p['kategori']);
            if (!isset($kategoriList[$slug])) {
                $kategoriList[$slug] = $p['kategori'];
            }
        }
        foreach ($kategoriList as $slug => $label):
      ?>
      <button class="tab-btn" onclick="filterCategory('<?php echo $slug; ?>', this)"><?php echo htmlspecialchars($label); ?></button>
      <?php endforeach; ?>
    </div>

    <!-- ── KARTU MENU DINAMIS DARI DATABASE ── -->
    <div class="paket-grid" id="paket-grid">

      <?php if (empty($allProduk)): ?>
        <div class="no-result" style="display:block">
          <span>🍽️</span>
          Belum ada menu tersedia. Admin sedang menyiapkan menu.
        </div>
      <?php else: ?>
        <?php foreach ($allProduk as $m):
          $cardId   = 'card_' . $m['id_produk'];
          $slug     = getKategoriSlug($m['kategori']);
          $namaData = strtolower($m['nama_produk'] . ' ' . $m['kategori']);
          $gambarUrl = getGambarUrl($m['gambar']);
          $harga    = 'Rp' . number_format($m['harga'], 0, ',', '.');
          $deskripsi = $m['deskripsi'] ?: 'Menu lezat pilihan Dapoer Yayubi.';
          $satuanMap = ['Tumpeng' => '/paket', 'Snack Box' => '/pcs'];
          $satuan    = $satuanMap[$m['kategori']] ?? '/dus';
        ?>
        <div class="card"
             id="<?php echo $cardId; ?>"
             data-category="<?php echo $slug; ?>"
             data-name="<?php echo htmlspecialchars($namaData); ?>">

          <!-- Overlay Detail -->
          <div class="card-detail-overlay">
            <div class="detail-title"><?php echo htmlspecialchars($m['nama_produk']); ?></div>
            <div class="detail-text"><?php echo htmlspecialchars($deskripsi); ?></div>
            <div class="detail-price"><?php echo $harga; ?></div>
            <div class="detail-price-label"><?php echo $satuan; ?></div>
            <button class="close-btn" onclick="closeDetail('<?php echo $cardId; ?>')">✕ Tutup</button>
          </div>

          <!-- Gambar -->
          <img class="card-img"
               src="<?php echo $gambarUrl; ?>"
               alt="<?php echo htmlspecialchars($m['nama_produk']); ?>"
               onerror="this.src='Gambar/Banner nasi box.png'">

          <!-- Body Card -->
          <div class="card-body">
            <span class="card-category-badge"><?php echo getBadgeLabel($m['kategori']); ?></span>
            <div class="card-tag"><?php echo htmlspecialchars($m['nama_produk']); ?></div>
            <div class="card-desc"><?php echo htmlspecialchars(mb_strimwidth($deskripsi, 0, 70, '...')); ?></div>
            <div class="card-price-label">Harga</div>
            <div class="card-price"><?php echo $harga; ?> <span><?php echo $satuan; ?></span></div>
            <button class="card-btn" onclick="showDetail('<?php echo $cardId; ?>')">👁 Lihat Detail →</button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- No Result Placeholder -->
      <div class="no-result" id="no-result">
        <span>🔍</span>
        Menu tidak ditemukan. Coba kata kunci lain.
      </div>

    </div><!-- /paket-grid -->

  </div><!-- /main -->

  <script>
    // ── CARD DETAIL ZOOM ──
    function showDetail(id) { document.getElementById(id).classList.add('zoomed'); }
    function closeDetail(id) { document.getElementById(id).classList.remove('zoomed'); }

    // ── SLIDER ──
    let cur = 0;
    const total = 3;
    const slidesEl = document.getElementById('slides');
    const dots = document.querySelectorAll('.dot');
    function goSlide(n) {
      cur = (n + total) % total;
      slidesEl.style.transform = `translateX(-${cur * (100 / 3)}%)`;
      dots.forEach((d, i) => d.classList.toggle('active', i === cur));
    }
    function moveSlide(dir) { goSlide(cur + dir); }
    setInterval(() => moveSlide(1), 4000);

    // ── SEARCH & FILTER ──
    let activeCategory = 'semua';

    function filterMenu() {
      const q = document.getElementById('search-input').value.toLowerCase().trim();
      applyFilter(q, activeCategory);
    }

    function filterCategory(cat, btn) {
      activeCategory = cat;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const q = document.getElementById('search-input').value.toLowerCase().trim();
      applyFilter(q, cat);
    }

    function applyFilter(q, cat) {
      const cards = document.querySelectorAll('.paket-grid .card');
      let visibleCount = 0;
      cards.forEach(card => {
        const name    = card.dataset.name    || '';
        const cardCat = card.dataset.category || '';
        const matchSearch = !q || name.includes(q);
        const matchCat    = cat === 'semua' || cardCat === cat;
        if (matchSearch && matchCat) {
          card.classList.remove('hidden');
          visibleCount++;
        } else {
          card.classList.add('hidden');
        }
      });
      const noResult = document.getElementById('no-result');
      noResult.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  </script>

</body>
</html>
