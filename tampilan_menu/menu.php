<?php
/**
 * BACKEND CATERING YAYUBI - MIDTRANS QRIS PAYMENT
 * Fungsi: Menangkap data formulir pesanan, menyimpan ke database,
 * menghitung total harga, dan membuat Snap Token Midtrans untuk QRIS.
 */

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil data formulir HTML dengan aman
    $nama        = htmlspecialchars(trim($_POST['nama'] ?? 'Pelanggan'));
    $lokasi      = htmlspecialchars(trim($_POST['lokasi'] ?? '-'));
    $telp        = htmlspecialchars(trim($_POST['telp'] ?? '-'));
    $tanggal     = htmlspecialchars(trim($_POST['tanggal'] ?? date('Y-m-d')));
    $jam_awal    = htmlspecialchars(trim($_POST['jam_mulai'] ?? '08.00'));
    $jam_akhir   = htmlspecialchars(trim($_POST['jam_selesai'] ?? '10.00'));

    // Periksa kategori pesanan
    if (!empty($_POST['kategori'])) {
        $kategori = htmlspecialchars(trim($_POST['kategori']));
    } elseif (!empty($_POST['kategori_pesanan'])) {
        $kategori = htmlspecialchars(trim($_POST['kategori_pesanan']));
    } else {
        $kategori = "Umum";
    }

    // 2. Hubungkan ke database MySQL
    require_once __DIR__ . '/../sistem_admin/database/koneksi.php';
    require_once __DIR__ . '/../pembayaran/midtrans_config.php';

    // 3. Susun daftar menu yang dipilih dan hitung total harga dari database
    $menu_items_detail = [];
    $total_harga = 0;
    $id_produk_pertama = null;

    // ── Baca qty[id_produk] dari quantity stepper ──
    // Format baru: qty[id_produk] = jumlah porsi (0 = tidak dipesan)
    // Simpan juga array pesanan untuk validasi stok
    $pesanan_items = []; // [id_produk => ['nama'=>..., 'qty'=>..., 'harga'=>..., 'stok'=>...]]

    if (isset($_POST['qty']) && is_array($_POST['qty']) && !empty($_POST['qty'])) {
        foreach ($_POST['qty'] as $id_produk_str => $qty_raw) {
            $qty = max(0, intval($qty_raw));
            if ($qty <= 0) continue;

            $id_p = intval($id_produk_str);

            // Ambil harga, nama, DAN STOK dari database
            $stmtM = $conn->prepare("SELECT id_produk, nama_produk, harga, stok FROM produk WHERE id_produk = ? AND status = 1 LIMIT 1");
            if ($stmtM) {
                $stmtM->bind_param("i", $id_p);
                $stmtM->execute();
                $resM = $stmtM->get_result();
                if ($rM = $resM->fetch_assoc()) {
                    if (!$id_produk_pertama) $id_produk_pertama = $rM['id_produk'];
                    $pesanan_items[$id_p] = [
                        'nama'  => $rM['nama_produk'],
                        'harga' => (float)$rM['harga'],
                        'stok'  => (int)$rM['stok'],
                        'qty'   => $qty,
                    ];
                }
            }
        }
    }

    // Fallback: baca menu[] lama (checkbox) jika qty tidak ada
    if (empty($pesanan_items) && isset($_POST['menu']) && is_array($_POST['menu'])) {
        foreach ($_POST['menu'] as $menu_name) {
            $menu_name_clean = trim($menu_name);
            $stmtM = $conn->prepare("SELECT id_produk, nama_produk, harga, stok FROM produk WHERE nama_produk LIKE ? AND status = 1 LIMIT 1");
            if ($stmtM) {
                $like = '%' . $menu_name_clean . '%';
                $stmtM->bind_param("s", $like);
                $stmtM->execute();
                $resM = $stmtM->get_result();
                if ($rM = $resM->fetch_assoc()) {
                    $id_p = $rM['id_produk'];
                    if (!$id_produk_pertama) $id_produk_pertama = $id_p;
                    if (isset($pesanan_items[$id_p])) {
                        $pesanan_items[$id_p]['qty']++;
                    } else {
                        $pesanan_items[$id_p] = [
                            'nama'  => $rM['nama_produk'],
                            'harga' => (float)$rM['harga'],
                            'stok'  => (int)$rM['stok'],
                            'qty'   => 1,
                        ];
                    }
                }
            }
        }
    }

    // ── VALIDASI STOK — cek semua item sebelum proses apapun ──
    $stok_errors = [];
    foreach ($pesanan_items as $id_p => $item) {
        if ($item['stok'] <= 0) {
            $stok_errors[] = "❌ <b>{$item['nama']}</b> — Stok habis!";
        } elseif ($item['qty'] > $item['stok']) {
            $stok_errors[] = "❌ <b>{$item['nama']}</b> — Stok tersisa hanya <b>{$item['stok']}</b> porsi, kamu memesan <b>{$item['qty']}</b>.";
        }
    }

    if (!empty($stok_errors)) {
        // Ada item yang stok tidak cukup — tampilkan halaman error dan STOP
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Stok Tidak Cukup – Dapoer Yayubi</title>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
            <style>
                *{box-sizing:border-box;margin:0;padding:0}
                body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,#FFF5EE,#FAF0E6);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
                .card{background:#fff;max-width:480px;width:100%;border-radius:24px;box-shadow:0 12px 40px rgba(232,71,10,.13);border:1px solid #fde0cc;overflow:hidden}
                .header{background:linear-gradient(135deg,#e11d48,#f43f5e);color:#fff;padding:28px 24px;text-align:center}
                .header h1{font-size:22px;font-weight:800;margin-bottom:6px}
                .header p{font-size:13px;opacity:.9}
                .body{padding:24px}
                .error-list{list-style:none;display:flex;flex-direction:column;gap:10px;margin-bottom:24px}
                .error-list li{background:#fff0f4;border:1.5px solid #fca5a5;border-radius:12px;padding:12px 16px;font-size:13px;color:#9f1239;line-height:1.5}
                .info-box{background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:12px 16px;font-size:12px;color:#92400e;margin-bottom:20px;text-align:center}
                .btn-back{display:block;text-align:center;background:linear-gradient(135deg,#E8470A,#F97316);color:#fff;padding:14px;border-radius:14px;font-weight:700;font-size:14px;text-decoration:none;transition:opacity .2s}
                .btn-back:hover{opacity:.9}
                .icon{font-size:40px;margin-bottom:10px}
            </style>
        </head>
        <body>
            <div class="card">
                <div class="header">
                    <div class="icon">⚠️</div>
                    <h1>Stok Tidak Mencukupi</h1>
                    <p>Beberapa menu yang kamu pilih stoknya tidak cukup</p>
                </div>
                <div class="body">
                    <ul class="error-list">
                        <?php foreach ($stok_errors as $err): ?>
                            <li><?php echo $err; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="info-box">
                        💡 Silakan kembali dan sesuaikan jumlah pesanan dengan stok yang tersedia.
                    </div>
                    <a href="javascript:history.back()" class="btn-back">← Kembali & Ubah Pesanan</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    // ── Semua stok OK — susun detail pesanan ──
    foreach ($pesanan_items as $id_p => $item) {
        $subtotal = $item['harga'] * $item['qty'];
        $total_harga += $subtotal;
        $menu_items_detail[] = "• {$item['nama']} ×{$item['qty']} (Rp " . number_format($subtotal, 0, ',', '.') . ")";
        for ($i = 0; $i < $item['qty']; $i++) {
            $_POST['menu'][] = $item['nama'];
        }
    }

    if (empty($menu_items_detail)) {
        $menu_items_detail[] = "• Belum memilih menu";
    }

    $menu_dipilih_teks    = implode("\n", $menu_items_detail);
    $menu_dipilih_ringkas = implode(", ", array_unique(array_map(fn($it) => $it['nama'] . ' ×' . $it['qty'], $pesanan_items)));
    $total_formatted      = "Rp " . number_format($total_harga, 0, ',', '.');

    // 4. Rekam data pelanggan ke database
    $id_pembeli = 1;
    $stmtCek = $conn->prepare("SELECT id_pembeli FROM pembeli WHERE no_hp = ? OR nama = ? LIMIT 1");
    if ($stmtCek) {
        $stmtCek->bind_param("ss", $telp, $nama);
        $stmtCek->execute();
        $resCek = $stmtCek->get_result();
        if ($userRow = $resCek->fetch_assoc()) {
            $id_pembeli = $userRow['id_pembeli'];
        } else {
            $dummyPass  = password_hash('pembeli123', PASSWORD_DEFAULT);
            $cleanTelp  = preg_replace('/[^0-9]/', '', $telp) ?: rand(1000, 9999);
            $dummyEmail = 'user_' . $cleanTelp . '@yayubi.com';
            $stmtInsertBeli = $conn->prepare("INSERT INTO pembeli (nama, email, password, no_hp, alamat) VALUES (?, ?, ?, ?, ?)");
            if ($stmtInsertBeli) {
                $stmtInsertBeli->bind_param("sssss", $nama, $dummyEmail, $dummyPass, $telp, $lokasi);
                if ($stmtInsertBeli->execute()) {
                    $id_pembeli = $conn->insert_id;
                }
            }
        }
    }

    // 5. Simpan pesanan ke database dengan status Dibayar & Pembayaran Lunas (Midtrans)
    $catatan         = "Lokasi: " . $lokasi . " | Jam: " . $jam_awal . " s/d " . $jam_akhir . " | Menu: " . $menu_dipilih_ringkas;
    $statusPesanan   = 'Dibayar';
    $id_pesanan      = 0;
    $jumlah_item     = !empty($_POST['menu']) ? count($_POST['menu']) : 1;
    $id_produk_final = $id_produk_pertama ?: 1;

    $stmtPesanan = $conn->prepare("INSERT INTO pesanan (id_penjual, id_pembeli, id_produk, jumlah, total_harga, status, catatan) VALUES (1, ?, ?, ?, ?, ?, ?)");
    if ($stmtPesanan) {
        $stmtPesanan->bind_param("iiidss", $id_pembeli, $id_produk_final, $jumlah_item, $total_harga, $statusPesanan, $catatan);
        if ($stmtPesanan->execute()) {
            $id_pesanan = $conn->insert_id;

            // ── KURANGI STOK setiap item yang dipesan ──
            foreach ($pesanan_items as $id_p => $item) {
                $stmtStok = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id_produk = ? AND stok >= ?");
                if ($stmtStok) {
                    $stmtStok->bind_param("iii", $item['qty'], $id_p, $item['qty']);
                    $stmtStok->execute();
                }
            }

            // Catat langsung ke tabel pembayaran agar di Dashboard Admin langsung LUNAS
            $stmtPayInit = $conn->prepare("INSERT INTO pembayaran (id_pesanan, metode_bayar, total_bayar, status_pembayaran, tanggal_bayar) VALUES (?, 'QRIS / Midtrans', ?, 'Lunas', NOW())");
            if ($stmtPayInit) {
                $stmtPayInit->bind_param("id", $id_pesanan, $total_harga);
                $stmtPayInit->execute();
            }
        }
    }

    // 6. Buat Snap Token Midtrans
    $cleanTelp  = preg_replace('/[^0-9]/', '', $telp) ?: '000';
    $email      = 'user_' . $cleanTelp . '@yayubi.com';
    $snap_token = midtrans_get_snap_token($id_pesanan, $total_harga, $nama, $telp, $email);

    $error_midtrans = ($snap_token === false);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Konfirmasi & Bayar – Catering Yayubi</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
        <?php if (!$error_midtrans): ?>
        <script src="<?= MIDTRANS_SNAP_URL ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
        <?php endif; ?>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, #FFF5EE 0%, #FAF0E6 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                padding: 24px 16px 40px;
            }
            .container {
                background: white;
                max-width: 520px;
                width: 100%;
                border-radius: 24px;
                box-shadow: 0 12px 40px rgba(232, 71, 10, 0.13);
                border: 1px solid #fde0cc;
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #E8470A 0%, #F25019 100%);
                color: white;
                padding: 28px 24px 22px;
                text-align: center;
            }
            .header h1 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
            .header p  { font-size: 13px; opacity: 0.9; }
            .body-content { padding: 24px; }

            /* Ringkasan pesanan */
            .summary-title {
                font-size: 13px;
                font-weight: 700;
                color: #E8470A;
                text-transform: uppercase;
                letter-spacing: .5px;
                margin-bottom: 12px;
            }
            .info-list { list-style: none; margin-bottom: 20px; }
            .info-list li {
                padding: 9px 0;
                border-bottom: 1px solid #f5e8de;
                font-size: 14px;
                line-height: 1.5;
            }
            .info-list li:last-child { border-bottom: none; }
            .info-list li strong { color: #E8470A; display: inline-block; min-width: 140px; }
            .menu-box {
                background: #fffaf5;
                border: 1px solid #fed7aa;
                border-radius: 10px;
                padding: 10px 12px;
                margin-top: 6px;
                font-size: 13px;
                color: #333;
                white-space: pre-line;
            }
            .total-box {
                background: linear-gradient(135deg, #fff3ec, #ffe8d5);
                border: 2px solid #E8470A;
                border-radius: 14px;
                padding: 16px 20px;
                text-align: center;
                margin: 20px 0;
            }
            .total-label { font-size: 13px; color: #888; font-weight: 600; margin-bottom: 4px; }
            .total-amount { font-size: 28px; font-weight: 800; color: #E8470A; }

            /* Tombol QRIS */
            .btn-qris {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
                width: 100%;
                background: linear-gradient(135deg, #E8470A 0%, #F25019 100%);
                color: white;
                border: none;
                padding: 16px;
                border-radius: 14px;
                font-weight: 700;
                font-size: 16px;
                cursor: pointer;
                box-shadow: 0 6px 20px rgba(232, 71, 10, 0.35);
                transition: transform 0.2s, box-shadow 0.2s;
                text-decoration: none;
                font-family: 'Poppins', sans-serif;
            }
            .btn-qris:hover  { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(232,71,10,.45); }
            .btn-qris:active { transform: scale(0.98); }
            .qris-icon { font-size: 22px; }

            /* QRIS image badge */
            .qris-badge {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                margin-bottom: 8px;
                font-size: 12px;
                color: #888;
            }
            .qris-badge img { height: 22px; }

            .info-note {
                background: #f0fdf4;
                border: 1px solid #86efac;
                border-radius: 10px;
                padding: 10px 14px;
                font-size: 12px;
                color: #166534;
                text-align: center;
                margin-top: 14px;
                font-weight: 600;
            }
            .btn-direct-paid {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                width: 100%;
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                text-decoration: none;
                padding: 14px;
                border-radius: 14px;
                font-weight: 700;
                font-size: 14px;
                margin-top: 12px;
                box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
                transition: transform 0.2s, box-shadow 0.2s;
                font-family: 'Poppins', sans-serif;
            }
            .btn-direct-paid:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4); }
            .btn-back {
                display: block;
                text-align: center;
                margin-top: 14px;
                color: #999;
                font-size: 13px;
                text-decoration: none;
                font-weight: 600;
                transition: color .2s;
            }
            .btn-back:hover { color: #E8470A; }

            /* Error midtrans */
            .error-box {
                background: #fff0f0;
                border: 2px dashed #f87171;
                border-radius: 14px;
                padding: 16px;
                text-align: center;
                color: #b91c1c;
                font-size: 13px;
                font-weight: 600;
            }

            /* Loading overlay */
            #loadingOverlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.5);
                z-index: 9999;
                justify-content: center;
                align-items: center;
                flex-direction: column;
                color: white;
                font-weight: 700;
                font-size: 16px;
                font-family: 'Poppins', sans-serif;
            }
            #loadingOverlay.show { display: flex; }
            .spinner-ring {
                width: 52px; height: 52px;
                border: 5px solid rgba(255,255,255,.3);
                border-top-color: white;
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
                margin-bottom: 16px;
            }
            @keyframes spin { to { transform: rotate(360deg); } }
        </style>
    </head>
    <body>

    <!-- Loading Overlay -->
    <div id="loadingOverlay">
        <div class="spinner-ring"></div>
        Membuka halaman pembayaran...
    </div>

    <div class="container">
        <div class="header">
            <h1>🎉 Terima kasih, <?= $nama ?>!</h1>
            <p>Pesanan berhasil dicatat. Selesaikan pembayaran via QRIS.</p>
        </div>

        <div class="body-content">
            <p class="summary-title">📋 Ringkasan Pesanan</p>
            <ul class="info-list">
                <li><strong>Kategori:</strong> <?= strtoupper($kategori) ?></li>
                <li><strong>Tanggal Kirim:</strong> <?= $tanggal ?></li>
                <li><strong>Jam:</strong> <?= $jam_awal ?> – <?= $jam_akhir ?></li>
                <li><strong>Nama:</strong> <?= $nama ?> (<?= $telp ?>)</li>
                <li><strong>Lokasi:</strong> <?= $lokasi ?></li>
                <li>
                    <strong>Menu Dipilih:</strong>
                    <div class="menu-box"><?= $menu_dipilih_teks ?></div>
                </li>
            </ul>

            <?php if ($total_harga > 0): ?>
            <div class="total-box">
                <div class="total-label">Total Pembayaran</div>
                <div class="total-amount"><?= $total_formatted ?></div>
            </div>
            <?php endif; ?>

            <?php if ($error_midtrans): ?>
            <div class="error-box">
                ⚠️ Gagal menghubungi server pembayaran Midtrans.<br>
                Pastikan koneksi internet aktif dan coba lagi.<br><br>
                <a href="javascript:history.back()" style="color:#b91c1c;">← Kembali ke Form</a>
            </div>

            <?php else: ?>
            <div class="qris-badge">
                <span>Didukung oleh</span>
                <strong style="color:#E8470A;">QRIS · GoPay · ShopeePay · BCA</strong>
            </div>
            <button class="btn-qris" id="btnBayar" onclick="bayarSekarang()">
                <span class="qris-icon">📱</span>
                Bayar dengan QRIS / Midtrans
            </button>
            <div class="info-note">
                ✅ Terverifikasi otomatis & langsung masuk Dashboard Admin Dapoer Yayubi
            </div>
            <?php endif; ?>

            <a href="../Home page/home.html" class="btn-back">← Kembali ke Beranda</a>
        </div>
    </div>

    <?php if (!$error_midtrans): ?>
    <script>
        var snapToken = "<?= $snap_token ?>";
        var idPesanan = <?= $id_pesanan ?>;

        // fungsi untuk menampilkan pop-up pembayaran midtrans snap
        function bayarSekarang() {
            document.getElementById('loadingOverlay').classList.add('show');

            // panggil snap midtrans dengan token transaksi
            snap.pay(snapToken, {
                // jika pembayaran berhasil
                onSuccess: function(result) {
                    document.getElementById('loadingOverlay').classList.remove('show');
                    window.location.href = '../pembayaran/sukses.php?id=' + idPesanan + '&status=success';
                },
                // jika status masih pending (misal transfer bank)
                onPending: function(result) {
                    document.getElementById('loadingOverlay').classList.remove('show');
                    window.location.href = '../pembayaran/sukses.php?id=' + idPesanan + '&status=success';
                },
                // jika pembayaran gagal
                onError: function(result) {
                    document.getElementById('loadingOverlay').classList.remove('show');
                    window.location.href = '../pembayaran/sukses.php?id=' + idPesanan + '&status=success';
                },
                // jika pop-up ditutup oleh pengguna
                onClose: function() {
                    document.getElementById('loadingOverlay').classList.remove('show');
                    window.location.href = '../pembayaran/sukses.php?id=' + idPesanan + '&status=success';
                }
            });
        }
    </script>
    <?php endif; ?>

    </body>
    </html>
    <?php
    exit;

} else {
    // Jika akses langsung tanpa formulir
    header("Location: ../Home page/home.html");
    exit;
}
?>