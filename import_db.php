<?php
/**
 * DB Import Script — Jalankan SEKALI di Railway untuk import database.
 * HAPUS file ini setelah selesai!
 */

// Kunci rahasia agar tidak sembarang orang akses
$secret = $_GET['key'] ?? '';
if ($secret !== 'yayubi-import-2024') {
    die('Akses ditolak. Tambahkan ?key=yayubi-import-2024 di URL.');
}

echo '<h2>🚀 Import Database Yayubi</h2>';
echo '<p>📁 App dir: <code>' . __DIR__ . '</code></p>';

// Baca env vars Railway
$host   = getenv('MYSQLHOST')     ?: 'localhost';
$user   = getenv('MYSQLUSER')     ?: 'root';
$pass   = getenv('MYSQLPASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$port   = (int)(getenv('MYSQLPORT') ?: 3306);

echo "<p>🔌 Connecting to: <code>$host:$port / $dbname</code></p>";

// Koneksi via PDO dengan timeout
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT            => 10,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ]);
    echo '<p>✅ Koneksi database berhasil!</p>';
} catch (PDOException $e) {
    die('<p style="color:red">❌ Koneksi gagal: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

// Cari file SQL
$sql_file = __DIR__ . '/sistem_admin/project_yayubi.sql';
echo "<p>📄 SQL file: <code>$sql_file</code> — " . (file_exists($sql_file) ? '✅ Found' : '❌ Not found') . '</p>';

if (!file_exists($sql_file)) {
    echo '<pre>';
    $items = glob(__DIR__ . '/sistem_admin/*') ?: [];
    foreach ($items as $f) echo $f . "\n";
    echo '</pre>';
    die('<p style="color:red">File SQL tidak ditemukan!</p>');
}

$sql = file_get_contents($sql_file);

// Hapus CREATE DATABASE dan USE (sudah pakai DB Railway)
$sql = preg_replace('/CREATE\s+DATABASE\b.*?;\s*/is', '', $sql);
$sql = preg_replace('/USE\s+`[^`]+`\s*;\s*/is', '', $sql);
$sql = preg_replace('/\/\*.*?\*\/\s*/s', '', $sql); // hapus komentar block
$sql = preg_replace('/--[^\n]*\n/s', "\n", $sql);   // hapus komentar line

// Split per statement
$queries = array_filter(array_map('trim', explode(';', $sql)));

echo '<pre>';
$success = 0;
$errors  = 0;
foreach ($queries as $query) {
    if (empty($query) || ctype_space($query)) continue;
    try {
        $pdo->exec($query);
        $success++;
    } catch (PDOException $e) {
        echo "⚠️  " . htmlspecialchars(substr($query, 0, 60)) . "...\n";
        echo "   Error: " . htmlspecialchars($e->getMessage()) . "\n\n";
        $errors++;
    }
}
echo '</pre>';

echo "<hr><p>✅ <strong>$success</strong> query berhasil | ❌ <strong>$errors</strong> error</p>";
echo '<p style="color:green"><strong>Database berhasil diimport! Website siap digunakan.</strong></p>';
echo '<p style="color:orange">⚠️ Segera hapus file <code>import_db.php</code> setelah ini!</p>';
echo '<p><a href="/sistem_admin/view/index.php">→ Ke Halaman Login</a></p>';
?>
