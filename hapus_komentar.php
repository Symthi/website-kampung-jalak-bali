<?php
session_start();
include 'koneksi.php';
include 'language.php';

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$komentar_id = $_GET['id'] ?? '';
$wisata_id = $_GET['wisata_id'] ?? '';

if ($komentar_id) {
    // Cek apakah user berhak menghapus (admin atau pemilik komentar)
    $query = "SELECT * FROM komentar WHERE id_komentar = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $komentar_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $komentar = mysqli_fetch_assoc($result);
    
    if ($komentar && (isAdmin() || $komentar['id_user'] == $_SESSION['user_id'])) {
        // Hapus komentar
        $query_hapus = "DELETE FROM komentar WHERE id_komentar = ?";
        $stmt_hapus = mysqli_prepare($koneksi, $query_hapus);
        mysqli_stmt_bind_param($stmt_hapus, "i", $komentar_id);
        mysqli_stmt_execute($stmt_hapus);
        
        $_SESSION['success_message'] = "Komentar berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Anda tidak berhak menghapus komentar ini!";
    }
}

header("Location: detail_wisata.php?id=" . $wisata_id);
exit();
?>