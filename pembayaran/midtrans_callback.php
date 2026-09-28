<?php
/**
 * MIDTRANS WEBHOOK CALLBACK - Catering Yayubi
 * Menerima notifikasi status pembayaran dari Midtrans secara otomatis.
 * URL ini harus didaftarkan di Midtrans Dashboard:
 *   Settings → Configuration → Payment Notification URL
 *   Isi dengan: http://yourdomain.com/Website Yayubi/pembayaran/midtrans_callback.php
 */

require_once __DIR__ . '/../sistem_admin/database/koneksi.php';
require_once __DIR__ . '/midtrans_config.php';

// Ambil body JSON dari Midtrans
$raw_body  = file_get_contents('php://input');
$notif     = json_decode($raw_body, true);

if (!$notif) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
    exit;
}

$order_id        = $notif['order_id']        ?? '';
$status_code     = $notif['status_code']     ?? '';
$gross_amount    = $notif['gross_amount']    ?? '0';
$signature_key   = $notif['signature_key']   ?? '';
$transaction_status = $notif['transaction_status'] ?? '';
$fraud_status    = $notif['fraud_status']    ?? '';
$payment_type    = $notif['payment_type']    ?? 'QRIS';

// Verifikasi signature key untuk keamanan
if (!midtrans_verify_signature($order_id, $status_code, $gross_amount, $signature_key)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    exit;
}

// Ekstrak id_pesanan dari order_id (format: YAYUBI-{id_pesanan}-{timestamp})
$parts      = explode('-', $order_id);
$id_pesanan = (int)($parts[1] ?? 0);

if ($id_pesanan <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
    exit;
}

// Tentukan status baru berdasarkan status transaksi Midtrans
$status_pesanan    = 'Pending';
$status_pembayaran = 'Menunggu';

if ($transaction_status === 'capture') {
    if ($fraud_status === 'challenge') {
        $status_pesanan    = 'Diproses';
        $status_pembayaran = 'Menunggu Verifikasi';
    } else {
        $status_pesanan    = 'Dibayar';
        $status_pembayaran = 'Lunas';
    }
} elseif ($transaction_status === 'settlement') {
    $status_pesanan    = 'Dibayar';
    $status_pembayaran = 'Lunas';
} elseif ($transaction_status === 'pending') {
    $status_pesanan    = 'Pending';
    $status_pembayaran = 'Menunggu Pembayaran';
} elseif (in_array($transaction_status, ['deny', 'expire', 'cancel'])) {
    $status_pesanan    = 'Batal';
    $status_pembayaran = 'Gagal';
}

// Update status di tabel pesanan
$stmtUpdatePesanan = $conn->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
if ($stmtUpdatePesanan) {
    $stmtUpdatePesanan->bind_param("si", $status_pesanan, $id_pesanan);
    $stmtUpdatePesanan->execute();
}

// Cek apakah record pembayaran sudah ada
$stmtCekBayar = $conn->prepare("SELECT id_pembayaran FROM pembayaran WHERE id_pesanan = ? LIMIT 1");
$stmtCekBayar->bind_param("i", $id_pesanan);
$stmtCekBayar->execute();
$resCekBayar = $stmtCekBayar->get_result();

$grossAmountNum = (float) str_replace([',', '.00'], ['', ''], $gross_amount);
$metodeBayar    = strtoupper($payment_type);

if ($resCekBayar->num_rows > 0) {
    // Update yang sudah ada
    $rowBayar = $resCekBayar->fetch_assoc();
    $stmtUpdateBayar = $conn->prepare("UPDATE pembayaran SET metode_bayar = ?, total_bayar = ?, status_pembayaran = ? WHERE id_pembayaran = ?");
    $stmtUpdateBayar->bind_param("sdsi", $metodeBayar, $grossAmountNum, $status_pembayaran, $rowBayar['id_pembayaran']);
    $stmtUpdateBayar->execute();
} else {
    // Insert baru
    $stmtInsertBayar = $conn->prepare("INSERT INTO pembayaran (id_pesanan, metode_bayar, total_bayar, status_pembayaran) VALUES (?, ?, ?, ?)");
    $stmtInsertBayar->bind_param("isds", $id_pesanan, $metodeBayar, $grossAmountNum, $status_pembayaran);
    $stmtInsertBayar->execute();
}

http_response_code(200);
echo json_encode(['status' => 'ok', 'message' => 'Notification processed']);
exit;
?>
