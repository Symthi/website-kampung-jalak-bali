<?php
// Process CRUD Operations untuk Komentar

// Hapus komentar
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM komentar WHERE id_komentar=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Komentar berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus komentar!";
    }
    
    header("Location: ?page=komentar");
    exit();
}
?>
