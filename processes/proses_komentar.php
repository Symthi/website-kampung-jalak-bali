<?php
session_start();
include __DIR__ . '/../config/koneksi.php';
include __DIR__ . '/../config/language.php';

// compute base URL (site root)
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header("Location: {$base}/auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $wisata_id = $_POST['wisata_id'] ?? '';
    $komentar = $_POST['komentar'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    // Validasi
    if ($wisata_id && $komentar) {
        // Simpan komentar ke database
        $query = "INSERT INTO komentar (id_user, id_wisata, isi) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $wisata_id, $komentar);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Komentar berhasil dikirim!";
        } else {
            $_SESSION['error_message'] = "Gagal mengirim komentar!";
        }
        
        header("Location: {$base}/detail_wisata.php?id=$wisata_id");
        exit();
    } else {
        $_SESSION['error_message'] = "Komentar tidak boleh kosong!";
        header("Location: {$base}/detail_wisata.php?id=$wisata_id");
        exit();
    }
} else {
    header("Location: {$base}/index.php");
    exit();
}
?>