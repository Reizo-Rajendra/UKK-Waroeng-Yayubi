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

$host   = getenv('MYSQLHOST')     ?: 'localhost';
$user   = getenv('MYSQLUSER')     ?: 'root';
$pass   = getenv('MYSQLPASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$port   = (int)(getenv('MYSQLPORT') ?: 3306);

$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) {
    die('<p style="color:red">Koneksi gagal: ' . $conn->connect_error . '</p>');
}

// Cari file SQL di beberapa kemungkinan path
$sql_paths = [
    __DIR__ . '/sistem_admin/project_yayubi.sql',
    '/app/sistem_admin/project_yayubi.sql',
    dirname(__DIR__) . '/sistem_admin/project_yayubi.sql',
];
$sql = false;
$found_path = '';
foreach ($sql_paths as $path) {
    if (file_exists($path)) {
        $sql = file_get_contents($path);
        $found_path = $path;
        break;
    }
}
echo "<p>📁 Root dir: <code>" . __DIR__ . "</code></p>";
echo "<p>📄 SQL path: <code>" . ($found_path ?: 'NOT FOUND') . "</code></p>";
if (!$sql) {
    echo "<p>Files found: <pre>";
    $files = glob(__DIR__ . '/sistem_admin/*');
    foreach ($files as $f) echo $f . "\n";
    echo "</pre></p>";
    die('<p style="color:red">File SQL tidak ditemukan!</p>');
}


// Hapus CREATE DATABASE dan USE statement (sudah pakai DB Railway)
$sql = preg_replace('/CREATE DATABASE.*?;\s*/is', '', $sql);
$sql = preg_replace('/USE\s+`[^`]+`\s*;\s*/is', '', $sql);

// Split per statement
$queries = array_filter(
    array_map('trim', explode(";\n", $sql)),
    fn($q) => !empty($q) && !preg_match('/^\s*(--|\/\*)/s', $q)
);

echo '<h2>🚀 Import Database Yayubi</h2>';
echo '<pre>';

$success = 0;
$errors  = 0;
foreach ($queries as $query) {
    if (empty(trim($query))) continue;
    if ($conn->query($query)) {
        $success++;
    } else {
        echo "❌ Error: " . $conn->error . "\n   Query: " . substr($query, 0, 80) . "...\n";
        $errors++;
    }
}

echo '</pre>';
echo "<p>✅ Berhasil: <strong>$success</strong> query | ❌ Error: <strong>$errors</strong> query</p>";
echo '<p style="color:orange"><strong>⚠️ PENTING: Hapus file import_db.php setelah ini!</strong></p>';

$conn->close();
?>
