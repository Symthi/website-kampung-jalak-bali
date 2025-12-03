<?php
// Process CRUD Operations untuk Informasi
// File ini diinclude sebelum output HTML untuk menghindari header error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['error_message'])) {
        $_SESSION['error_message'] = '';
    }
    
    if (isset($_POST['tambah'])) {
        $_SESSION['error_message'] = '';
        $judul = $_POST['judul'];
        $isi = $_POST['isi'];
        $kategori = $_POST['kategori'];
        $gambar = '';
        
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $public_dir = 'uploads/informasi/';
            $target_dir = __DIR__ . '/../../' . $public_dir;
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_informasi.' . $file_extension;
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
        }
        
        if (empty($_SESSION['error_message'])) {
            $query = "INSERT INTO informasi (judul, isi, kategori, gambar) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $judul, $isi, $kategori, $gambar);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Informasi berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan informasi!";
            }
        }
        
        header("Location: ?page=informasi");
        exit();
        
    } elseif (isset($_POST['edit'])) {
        $_SESSION['error_message'] = '';
        $id = $_POST['id'];
        $judul = $_POST['judul'];
        $isi = $_POST['isi'];
        $kategori = $_POST['kategori'];
        $gambar_lama = $_POST['gambar_lama'];
        
        $gambar = $gambar_lama;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $public_dir = 'uploads/informasi/';
            $target_dir = __DIR__ . '/../../' . $public_dir;
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_informasi.' . $file_extension;
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
            $query = "UPDATE informasi SET judul=?, isi=?, kategori=?, gambar=? WHERE id_informasi=?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssssi", $judul, $isi, $kategori, $gambar, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Informasi berhasil diupdate!";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate informasi!";
            }
        }
        
        header("Location: ?page=informasi");
        exit();
    }
}

// Hapus informasi
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $query_select = "SELECT gambar FROM informasi WHERE id_informasi = ?";
    $stmt_select = mysqli_prepare($koneksi, $query_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $informasi = mysqli_fetch_assoc($result);
    
    if (!empty($informasi['gambar'])) {
        $informasi_path = __DIR__ . '/../../' . $informasi['gambar'];
        if (file_exists($informasi_path)) {
            @unlink($informasi_path);
        }
    }
    
    $query = "DELETE FROM informasi WHERE id_informasi=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Informasi berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus informasi!";
    }
    
    header("Location: ?page=informasi");
    exit();
}
?>
