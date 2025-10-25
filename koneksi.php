<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "kampungjalak";

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Buat folder uploads jika belum ada
$folders = ['uploads', 'uploads/wisata', 'uploads/galeri'];
foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
}

// Set timezone
date_default_timezone_set('Asia/Makassar');
?>