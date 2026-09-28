<?php
// ============================================================
// KONFIGURASI MIDTRANS - Catering Yayubi
// ============================================================

// Ganti dengan Server Key dan Client Key Midtrans kamu
// Dapatkan di: https://dashboard.midtrans.com > Settings > Access Keys
define('MIDTRANS_SERVER_KEY', 'YOUR_MIDTRANS_SERVER_KEY_HERE');
define('MIDTRANS_CLIENT_KEY', 'YOUR_MIDTRANS_CLIENT_KEY_HERE');
define('MIDTRANS_IS_PRODUCTION', false);  // false = Sandbox (test tanpa uang asli)

// URL Midtrans berdasarkan mode
define('MIDTRANS_SNAP_URL', MIDTRANS_IS_PRODUCTION
    ? 'https://app.midtrans.com/snap/snap.js'
    : 'https://app.sandbox.midtrans.com/snap/snap.js'
);
define('MIDTRANS_API_URL', MIDTRANS_IS_PRODUCTION
    ? 'https://api.midtrans.com/v2'
    : 'https://api.sandbox.midtrans.com/v2'
);
define('MIDTRANS_SNAP_API_URL', MIDTRANS_IS_PRODUCTION
    ? 'https://app.midtrans.com/snap/v1/transactions'
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions'
);

/**
 * Buat Snap Token via Midtrans API
 * @param int    $order_id   ID pesanan dari database
 * @param int    $gross_amount Total harga pesanan
 * @param string $nama       Nama customer
 * @param string $telp       No HP customer
 * @param string $email      Email customer (boleh dummy)
 * @return string|false      Snap token jika berhasil, false jika gagal
 */
function midtrans_get_snap_token($order_id, $gross_amount, $nama, $telp, $email = '') {
    if ($gross_amount <= 0) {
        // Midtrans butuh minimum 1 rupiah, set minimum jika kosong
        $gross_amount = 1000;
    }

    if (empty($email)) {
        $cleanTelp = preg_replace('/[^0-9]/', '', $telp) ?: '000';
        $email = 'pembeli_' . $cleanTelp . '@yayubi.com';
    }

    $params = [
        'transaction_details' => [
            'order_id'     => 'YAYUBI-' . $order_id . '-' . time(),
            'gross_amount' => (int) $gross_amount,
        ],
        'customer_details' => [
            'first_name' => $nama,
            'phone'      => $telp,
            'email'      => $email,
        ],
        'enabled_payments' => ['qris', 'gopay', 'shopeepay', 'other_qris'],
        'qris' => [
            'acquirer' => 'gopay',
        ],
    ];

    $auth = base64_encode(MIDTRANS_SERVER_KEY . ':');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, MIDTRANS_SNAP_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . $auth,
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 201) {
        $data = json_decode($response, true);
        return $data['token'] ?? false;
    }

    return false;
}

/**
 * Verifikasi signature key dari notifikasi Midtrans
 */
function midtrans_verify_signature($order_id, $status_code, $gross_amount, $signature_key) {
    $expected = hash('sha512', $order_id . $status_code . $gross_amount . MIDTRANS_SERVER_KEY);
    return hash_equals($expected, $signature_key);
}
?>
