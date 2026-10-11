<?php
// konfigurasi database — support Railway (env vars) dan XAMPP lokal
$host   = getenv('MYSQLHOST')     ?: getenv('DB_HOST')     ?: 'localhost';
$user   = getenv('MYSQLUSER')     ?: getenv('DB_USER')     ?: 'root';
$pass   = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: getenv('DB_NAME')     ?: 'project_yayubi';
$port   = (int)(getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);

// buat koneksi ke database mysql
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// cek jika koneksi database gagal
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?> 