<?php
// Process CRUD Operations untuk Wisata

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['error_message'])) {
        $_SESSION['error_message'] = '';
    }
    
    if (isset($_POST['tambah'])) {
        $_SESSION['error_message'] = '';
        $judul = $_POST['judul'];
        $deskripsi = $_POST['deskripsi'];
        $waktu = $_POST['waktu'];
        $jam = $_POST['jam'];
        $gambar = '';
        
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $public_dir = 'uploads/wisata/';
            $target_dir = __DIR__ . '/../../' . $public_dir;
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_wisata.' . $file_extension;
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
            $query = "INSERT INTO wisata (judul, deskripsi, gambar, waktu, jam) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "sssss", $judul, $deskripsi, $gambar, $waktu, $jam);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Wisata berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan wisata!";
            }
        }
        
        header("Location: ?page=wisata");
        exit();
        
    } elseif (isset($_POST['edit'])) {
        $_SESSION['error_message'] = '';
        $id = $_POST['id'];
        $judul = $_POST['judul'];
        $deskripsi = $_POST['deskripsi'];
        $waktu = $_POST['waktu'];
        $jam = $_POST['jam'];
        $gambar = $_POST['gambar_lama'];
        
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $public_dir = 'uploads/wisata/';
            $target_dir = __DIR__ . '/../../' . $public_dir;
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_wisata.' . $file_extension;
            $target_file = $target_dir . $filename;

            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 2 * 1024 * 1024;

            if (in_array(strtolower($file_extension), $allowed_types)) {
                if ($_FILES['gambar']['size'] <= $max_size) {
                    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                        if (!empty($gambar)) {
                            $old_path = __DIR__ . '/../../' . $gambar;
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
            $query = "UPDATE wisata SET judul=?, deskripsi=?, gambar=?, waktu=?, jam=? WHERE id_wisata=?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "sssssi", $judul, $deskripsi, $gambar, $waktu, $jam, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Wisata berhasil diupdate!";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate wisata!";
            }
        }
        
        header("Location: ?page=wisata");
        exit();
    }
}

// Hapus wisata
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $query_select = "SELECT gambar FROM wisata WHERE id_wisata = ?";
    $stmt_select = mysqli_prepare($koneksi, $query_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $wisata = mysqli_fetch_assoc($result);
    
    if (!empty($wisata['gambar'])) {
        $wisata_path = __DIR__ . '/../../' . $wisata['gambar'];
        if (file_exists($wisata_path)) {
            @unlink($wisata_path);
        }
    }
    
    $query = "DELETE FROM wisata WHERE id_wisata=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Wisata berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus wisata!";
    }
    
    header("Location: ?page=wisata");
    exit();
}
?>
