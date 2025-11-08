<?php
session_start();
include 'koneksi.php';
include 'language.php';

// Fungsi cek login dan admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Cek apakah user adalah admin
if (!isAdmin()) {
    header("Location: login.php");
    exit();
}

// CRUD Operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah'])) {
        // Tambah produk
        $nama = $_POST['nama'];
        $deskripsi = $_POST['deskripsi'];
        $harga = $_POST['harga'];
        $stok = $_POST['stok'];
        $gambar = '';
        
        // Handle file upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $target_dir = "uploads/produk/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_produk.' . $file_extension;
            $target_file = $target_dir . $filename;
            
            // Validasi file
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (in_array(strtolower($file_extension), $allowed_types)) {
                if ($_FILES['gambar']['size'] <= $max_size) {
                    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                        $gambar = $target_file;
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
        
    } elseif (isset($_POST['edit'])) {
        // Edit produk
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $deskripsi = $_POST['deskripsi'];
        $harga = $_POST['harga'];
        $stok = $_POST['stok'];
        $gambar_lama = $_POST['gambar_lama'];
        
        // Handle file upload jika ada gambar baru
        $gambar = $gambar_lama;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $target_dir = "uploads/produk/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_produk.' . $file_extension;
            $target_file = $target_dir . $filename;
            
            // Validasi file
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (in_array(strtolower($file_extension), $allowed_types)) {
                if ($_FILES['gambar']['size'] <= $max_size) {
                    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                        // Hapus gambar lama jika ada
                        if (!empty($gambar_lama) && file_exists($gambar_lama)) {
                            unlink($gambar_lama);
                        }
                        $gambar = $target_file;
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
    }
}

// Hapus produk
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Ambil data gambar untuk dihapus dari server
    $query_select = "SELECT gambar FROM produk WHERE id_produk = ?";
    $stmt_select = mysqli_prepare($koneksi, $query_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $produk = mysqli_fetch_assoc($result);
    
    // Hapus file gambar dari server
    if ($produk['gambar'] && file_exists($produk['gambar'])) {
        unlink($produk['gambar']);
    }
    
    // Hapus dari database
    $query = "DELETE FROM produk WHERE id_produk=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Produk berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus produk!";
    }
    
    header("Location: crud_produk.php");
    exit();
}

// Ambil data produk dengan pagination
$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM produk");
$total_produk_all = mysqli_fetch_assoc($total_q)['cnt'];
$query = "SELECT * FROM produk ORDER BY tanggal_ditambahkan DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$produk_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM produk WHERE id_produk=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_data = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
       <title><?php echo t('manage_products'); ?> | Kampung Jalak Bali</title>

    <style>
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .btn { padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .gambar-preview { max-width: 200px; max-height: 150px; margin: 10px 0; }
    </style>
    <style>
        .pagination { display: flex; gap: 8px; margin-top: 15px; }
        .pagination a, .pagination span { padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; }
        .pagination .active { background: #007bff; color: #fff; border-color: #007bff; }
    </style>
</head>
<body>
    <header>
        <div>
            <div><h1>Kampung Jalak Bali</h1></div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="crud_produk.php"><?php echo t('manage_products'); ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section>
        <div>
            <h2><?php echo t('manage_products'); ?></h2>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Form Tambah/Edit Produk -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3><?php echo $edit_data ? t('edit') : t('add'); ?> <?php echo t('products'); ?></h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id_produk']; ?>">
                        <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Nama Produk:</label>
                        <input type="text" name="nama" value="<?php echo $edit_data['nama'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi:</label>
                        <textarea name="deskripsi" rows="5" required><?php echo $edit_data['deskripsi'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Harga (Rp):</label>
                        <input type="number" name="harga" value="<?php echo $edit_data['harga'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Stok:</label>
                        <input type="number" name="stok" value="<?php echo $edit_data['stok'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Gambar:</label>
                        <input type="file" name="gambar" accept="image/*" <?php echo !$edit_data ? 'required' : ''; ?>>
                        <small>Format: JPG, JPEG, PNG, GIF (Max: 2MB)</small>
                        
                        <?php if ($edit_data && $edit_data['gambar']): ?>
                            <div>
                                <img src="<?php echo $edit_data['gambar']; ?>" class="gambar-preview" 
                                     onerror="this.style.display='none'">
                                <p>Gambar saat ini</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                        <?php echo $edit_data ? t('update') : t('add'); ?> <?php echo t('products'); ?>
                    </button>
                    
                    <?php if ($edit_data): ?>
                        <a href="crud_produk.php" class="btn btn-warning"><?php echo t('cancel'); ?></a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Daftar Produk -->
            <div>
                <h3><?php echo t('products_title'); ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produk_data as $index => $produk): ?>
                        <tr>
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td>
                                <?php if ($produk['gambar']): ?>
                                    <img src="<?php echo $produk['gambar']; ?>" style="max-width: 80px; max-height: 60px; object-fit: cover;" 
                                         onerror="this.src='https://source.unsplash.com/random/80x60/?merchandise'">
                                <?php else: ?>
                                    <img src="https://source.unsplash.com/random/80x60/?merchandise" style="max-width: 80px; max-height: 60px; object-fit: cover;">
                                <?php endif; ?>
                            </td>
                            <td><?php echo $produk['nama']; ?></td>
                            <td>Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></td>
                            <td><?php echo $produk['stok']; ?></td>
                            <td><?php echo date('d M Y', strtotime($produk['tanggal_ditambahkan'])); ?></td>
                            <td>
                                <a href="crud_produk.php?edit=<?php echo $produk['id_produk']; ?>" class="btn btn-primary">Edit</a>
                                <a href="crud_produk.php?hapus=<?php echo $produk['id_produk']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Yakin hapus produk <?php echo $produk['nama']; ?>?')">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php 
                $total_pages = (int)ceil($total_produk_all / $per_page);
                if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p == $page): ?>
                            <span class="active"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <div>
            <p>&copy; 2025 Kampung Jalak Bali | Kelola Produk</p>
        </div>
    </footer>
</body>
</html>
<?php mysqli_close($koneksi); ?>