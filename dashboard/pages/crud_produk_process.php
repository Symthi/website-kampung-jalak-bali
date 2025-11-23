<?php
// Process CRUD Operations untuk Produk

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['error_message'])) {
        $_SESSION['error_message'] = '';
    }
    
    if (isset($_POST['tambah'])) {
        $_SESSION['error_message'] = '';
        $nama = $_POST['nama'];
        $deskripsi = $_POST['deskripsi'];
        $harga = $_POST['harga'];
        $stok = $_POST['stok'];
        $gambar = '';
        
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $public_dir = 'uploads/produk/';
            $target_dir = __DIR__ . '/../../' . $public_dir;
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_produk.' . $file_extension;
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
            $query = "INSERT INTO produk (nama, deskripsi, harga, stok, gambar) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssdis", $nama, $deskripsi, $harga, $stok, $gambar);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Produk berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan produk!";
            }
        }
        
        header("Location: ?page=produk");
        exit();
        
    } elseif (isset($_POST['edit'])) {
        $_SESSION['error_message'] = '';
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $deskripsi = $_POST['deskripsi'];
        $harga = $_POST['harga'];
        $stok = $_POST['stok'];
        $gambar_lama = $_POST['gambar_lama'];
        
        $gambar = $gambar_lama;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $public_dir = 'uploads/produk/';
            $target_dir = __DIR__ . '/../../' . $public_dir;
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_produk.' . $file_extension;
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
            $query = "UPDATE produk SET nama=?, deskripsi=?, harga=?, stok=?, gambar=? WHERE id_produk=?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssdisi", $nama, $deskripsi, $harga, $stok, $gambar, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Produk berhasil diupdate!";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate produk!";
            }
        }
        
        header("Location: ?page=produk");
        exit();
    }
}

// Hapus produk
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $query_select = "SELECT gambar FROM produk WHERE id_produk = ?";
    $stmt_select = mysqli_prepare($koneksi, $query_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $produk = mysqli_fetch_assoc($result);
    
    if (!empty($produk['gambar'])) {
        $produk_path = __DIR__ . '/../../' . $produk['gambar'];
        if (file_exists($produk_path)) {
            @unlink($produk_path);
        }
    }
    
    $query = "DELETE FROM produk WHERE id_produk=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Produk berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus produk!";
    }
    
    header("Location: ?page=produk");
    exit();
}
?>
