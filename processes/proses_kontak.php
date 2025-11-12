<?php
session_start();
include __DIR__ . '/../config/koneksi.php';
include __DIR__ . '/../config/language.php';

// compute base URL (site root)
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $email = mysqli_real_escape_string($koneksi, $_POST['email'] ?? '');
    $subjek = mysqli_real_escape_string($koneksi, $_POST['subjek'] ?? '');
    $pesan = mysqli_real_escape_string($koneksi, $_POST['pesan'] ?? '');
    
    // Validasi
    if (empty($nama) || empty($email) || empty($subjek) || empty($pesan)) {
        $_SESSION['error_message'] = "Semua field harus diisi!";
        header("Location: {$base}/index.php#kontak");
        exit();
    }
    
    // Simpan ke database
    $query = "INSERT INTO pesan (nama, email, subjek, isi) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $subjek, $pesan);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Pesan Anda berhasil dikirim!";
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.";
    }
    
    header("Location: {$base}/index.php#kontak");
    exit();
} else {
    header("Location: {$base}/index.php");
    exit();
}
?>