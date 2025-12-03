<?php
// Process CRUD Operations untuk Galeri

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    if (!isset($_SESSION['error_message'])) {
        $_SESSION['error_message'] = '';
    }
    $_SESSION['error_message'] = '';
    
    $judul = $_POST['judul'];
    $gambar = '';
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $public_dir = 'uploads/galeri/';
        $target_dir = __DIR__ . '/../../' . $public_dir;
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
    
        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_galeri.' . $file_extension;
        $target_file = $target_dir . $filename;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 2 * 1024 * 1024;
        
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if ($_FILES['gambar']['size'] <= $max_size) {
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                    $gambar = $public_dir . $filename;
                } else {
                    $_SESSION['error_message'] = "Gagal mengupload gambar.";
                }
            } else {
                $_SESSION['error_message'] = "Ukuran gambar terlalu besar. Maksimal 2MB.";
            }
        } else {
            $_SESSION['error_message'] = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        }
    } else {
        $_SESSION['error_message'] = "Silakan pilih gambar untuk diupload.";
    }
    
    if (empty($_SESSION['error_message'])) {
        $query = "INSERT INTO galeri (judul, gambar) VALUES (?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ss", $judul, $gambar);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Gambar berhasil ditambahkan ke galeri!";
        } else {
            $_SESSION['error_message'] = "Gagal menambahkan gambar!";
        }
    }
    
    header("Location: ?page=galeri");
    exit();
}

// Edit galeri
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    if (!isset($_SESSION['error_message'])) {
        $_SESSION['error_message'] = '';
    }
    $_SESSION['error_message'] = '';
    
    $id = $_POST['id'];
    $judul = $_POST['judul'];
    $gambar_lama = $_POST['gambar_lama'];
    
    $gambar = $gambar_lama;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $public_dir = 'uploads/galeri/';
        $target_dir = __DIR__ . '/../../' . $public_dir;
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_galeri.' . $file_extension;
        $target_file = $target_dir . $filename;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 2 * 1024 * 1024;

        if (in_array(strtolower($file_extension), $allowed_types)) {
            if ($_FILES['gambar']['size'] <= $max_size) {
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                    if (!empty($gambar_lama)) {
                        $old_path = __DIR__ . '/../../' . $gambar_lama;
                        if (file_exists($old_path)) {
                            @unlink($old_path);
                        }
                    }
                    $gambar = $public_dir . $filename;
                } else {
                    $_SESSION['error_message'] = "Gagal mengupload gambar.";
                }
            } else {
                $_SESSION['error_message'] = "Ukuran gambar terlalu besar. Maksimal 2MB.";
            }
        } else {
            $_SESSION['error_message'] = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        }
    }
    
    if (empty($_SESSION['error_message'])) {
        $query = "UPDATE galeri SET judul=?, gambar=? WHERE id_galeri=?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $judul, $gambar, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Gambar galeri berhasil diupdate!";
        } else {
            $_SESSION['error_message'] = "Gagal mengupdate gambar!";
        }
    }
    
    header("Location: ?page=galeri");
    exit();
}

// Hapus galeri
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $query_select = "SELECT gambar FROM galeri WHERE id_galeri = ?";
    $stmt_select = mysqli_prepare($koneksi, $query_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $galeri = mysqli_fetch_assoc($result);
    
    if (!empty($galeri['gambar'])) {
        $galeri_path = __DIR__ . '/../../' . $galeri['gambar'];
        if (file_exists($galeri_path)) {
            @unlink($galeri_path);
        }
    }
    
    $query = "DELETE FROM galeri WHERE id_galeri=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Gambar berhasil dihapus dari galeri!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus gambar!";
    }
    
    header("Location: ?page=galeri");
    exit();
}
?>
