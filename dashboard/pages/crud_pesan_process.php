<?php
// Process CRUD Operations untuk Pesan

// Hapus pesan
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM pesan WHERE id_pesan=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Pesan berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus pesan!";
    }
    
    header("Location: ?page=pesan");
    exit();
}

// Tandai sebagai sudah dibaca
if (isset($_GET['baca'])) {
    $id = $_GET['baca'];
    $query = "UPDATE pesan SET dibaca = 1 WHERE id_pesan = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    
    header("Location: ?page=pesan");
    exit();
}
?>
