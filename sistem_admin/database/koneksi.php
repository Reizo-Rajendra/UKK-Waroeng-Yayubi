<?php
// konfigurasi database
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "project_yayubi";

// buat koneksi ke database mysql
$conn = new mysqli($host, $user, $pass, $dbname);

// cek jika koneksi database gagal
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?> 