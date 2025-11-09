<?php
session_start();
include 'koneksi.php';
include 'language.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $email = mysqli_real_escape_string($koneksi, $_POST['email'] ?? '');
    $subjek = mysqli_real_escape_string($koneksi, $_POST['subjek'] ?? '');
    $pesan = mysqli_real_escape_string($koneksi, $_POST['pesan'] ?? '');
    
    // Validasi
    if (empty($nama) || empty($email) || empty($subjek) || empty($pesan)) {
        $_SESSION['error_message'] = "Semua field harus diisi!";
        header("Location: index.php#kontak");
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
    
    header("Location: index.php#kontak");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>