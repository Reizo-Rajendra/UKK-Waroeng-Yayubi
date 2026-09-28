<?php
/**
 * HALAMAN SUKSES / STATUS PEMBAYARAN - Catering Yayubi
 */
require_once __DIR__ . '/../sistem_admin/database/koneksi.php';

$id_pesanan = (int)($_GET['id'] ?? 0);
$status     = htmlspecialchars($_GET['status'] ?? 'pending');

// Ambil detail pesanan dari database
$pesanan  = null;
$pembeli  = null;

if ($id_pesanan > 0) {
    $stmt = $conn->prepare("
        SELECT p.*, pb.nama, pb.no_hp, pb.alamat
        FROM pesanan p
        LEFT JOIN pembeli pb ON p.id_pembeli = pb.id_pembeli
        WHERE p.id_pesanan = ?
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param("i", $id_pesanan);
        $stmt->execute();
        $pesanan = $stmt->get_result()->fetch_assoc();
    }
}

$is_success = ($status !== 'failed');
$is_pending = false;

// jika pembayaran berhasil, update status pesanan jadi Dibayar dan Lunas
if ($is_success && $id_pesanan > 0) {
    // update status di tabel pesanan jadi 'Dibayar'
    $upPesanan = $conn->prepare("UPDATE pesanan SET status = 'Dibayar' WHERE id_pesanan = ?");
    if ($upPesanan) {
        $upPesanan->bind_param("i", $id_pesanan);
        $upPesanan->execute();
    }

    // catat transaksi ke tabel pembayaran dengan status 'Lunas'
    $cekPay = $conn->prepare("SELECT id_pembayaran FROM pembayaran WHERE id_pesanan = ? LIMIT 1");
    if ($cekPay) {
        $cekPay->bind_param("i", $id_pesanan);
        $cekPay->execute();
        $resPay = $cekPay->get_result();
        $totalBayar = (float)($pesanan['total_harga'] ?? 0);
        if ($resPay->num_rows > 0) {
            // kalau sudah ada data pembayaran, update jadi Lunas
            $upPay = $conn->prepare("UPDATE pembayaran SET metode_bayar = 'QRIS / Midtrans', total_bayar = ?, status_pembayaran = 'Lunas', tanggal_bayar = NOW() WHERE id_pesanan = ?");
            if ($upPay) {
                $upPay->bind_param("di", $totalBayar, $id_pesanan);
                $upPay->execute();
            }
        } else {
            // kalau belum ada, buat record baru di tabel pembayaran
            $inPay = $conn->prepare("INSERT INTO pembayaran (id_pesanan, metode_bayar, total_bayar, status_pembayaran, tanggal_bayar) VALUES (?, 'QRIS / Midtrans', ?, 'Lunas', NOW())");
            if ($inPay) {
                $inPay->bind_param("id", $id_pesanan, $totalBayar);
                $inPay->execute();
            }
        }
    }

    // Refresh data pesanan setelah update status
    if ($stmt) {
        $stmt->execute();
        $pesanan = $stmt->get_result()->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_success ? 'Pembayaran Berhasil' : 'Menunggu Pembayaran' ?> – Catering Yayubi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FFF5EE 0%, #FAF0E6 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 32px 16px 48px;
        }
        .card {
            background: white;
            max-width: 480px;
            width: 100%;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,.1);
        }

        /* Header success */
        .header-success {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            padding: 40px 24px 32px;
            text-align: center;
            color: white;
        }
        /* Header pending */
        .header-pending {
            background: linear-gradient(135deg, #d97706, #f59e0b);
            padding: 40px 24px 32px;
            text-align: center;
            color: white;
        }

        .icon-wrap {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 38px;
        }
        .header-title { font-size: 22px; font-weight: 800; margin-bottom: 6px; }
        .header-sub   { font-size: 13px; opacity: .9; }

        .body { padding: 28px 24px; }

        .order-id {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 16px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 20px;
        }
        .order-id strong { color: #1e293b; font-size: 15px; }

        .info-list { list-style: none; margin-bottom: 24px; }
        .info-list li {
            padding: 9px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
        }
        .info-list li:last-child { border-bottom: none; }
        .info-list .lbl { color: #94a3b8; font-weight: 600; flex-shrink: 0; }
        .info-list .val { color: #1e293b; font-weight: 600; text-align: right; }

        .total-row {
            background: linear-gradient(135deg, #fff3ec, #ffe8d5);
            border: 2px solid #E8470A;
            border-radius: 14px;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .total-lbl   { font-size: 13px; color: #888; font-weight: 600; }
        .total-val   { font-size: 22px; font-weight: 800; color: #E8470A; }

        .badge-success {
            background: #dcfce7;
            color: #166534;
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 20px;
        }
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 20px;
        }

        .note-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 12px;
            color: #166534;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .note-pending {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 12px;
            color: #92400e;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .btn-home {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #E8470A, #F25019);
            color: white;
            text-align: center;
            padding: 14px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            box-shadow: 0 6px 18px rgba(232,71,10,.3);
            transition: transform .2s;
        }
        .btn-home:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
<div class="card">

    <?php if ($is_success): ?>
    <div class="header-success">
        <div class="icon-wrap">✅</div>
        <div class="header-title">Pembayaran Berhasil!</div>
        <div class="header-sub">Pesanan kamu sedang kami siapkan 🍱</div>
    </div>
    <?php else: ?>
    <div class="header-pending">
        <div class="icon-wrap">⏳</div>
        <div class="header-title">Menunggu Pembayaran</div>
        <div class="header-sub">Selesaikan pembayaran untuk memproses pesanan</div>
    </div>
    <?php endif; ?>

    <div class="body">

        <div class="order-id">
            No. Pesanan: <strong>#<?= str_pad($id_pesanan, 5, '0', STR_PAD_LEFT) ?></strong>
        </div>

        <?php if ($is_success): ?>
            <div style="text-align:center"><span class="badge-success">✓ Pembayaran Terkonfirmasi</span></div>
        <?php else: ?>
            <div style="text-align:center"><span class="badge-pending">⏳ Menunggu Konfirmasi</span></div>
        <?php endif; ?>

        <?php if ($pesanan): ?>
        <ul class="info-list">
            <li>
                <span class="lbl">👤 Nama</span>
                <span class="val"><?= htmlspecialchars($pesanan['nama'] ?? '-') ?></span>
            </li>
            <li>
                <span class="lbl">📞 No. HP</span>
                <span class="val"><?= htmlspecialchars($pesanan['no_hp'] ?? '-') ?></span>
            </li>
            <li>
                <span class="lbl">📅 Tanggal Pesan</span>
                <span class="val"><?= date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'] ?? 'now')) ?></span>
            </li>
            <li>
                <span class="lbl">📋 Status</span>
                <span class="val"><?= htmlspecialchars($pesanan['status'] ?? '-') ?></span>
            </li>
        </ul>

        <?php if ((float)$pesanan['total_harga'] > 0): ?>
        <div class="total-row">
            <div class="total-lbl">Total Dibayar</div>
            <div class="total-val">Rp <?= number_format((float)$pesanan['total_harga'], 0, ',', '.') ?></div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($is_success): ?>
        <div class="note-box">
            🎉 Terima kasih! Tim Dapoer Yayubi akan segera memproses pesanan kamu.
            Pesanan akan dikirim sesuai jadwal yang dipilih.
        </div>
        <?php else: ?>
        <div class="note-pending">
            ⚠️ Jika kamu sudah melakukan pembayaran, pesanan akan diproses otomatis
            setelah konfirmasi dari bank/dompet digital.
        </div>
        <?php endif; ?>

        <a href="../Home page/home.html" class="btn-home">🏠 Kembali ke Beranda</a>
    </div>

</div>
</body>
</html>
