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
        // Tambah informasi
        $judul = $_POST['judul'];
        $isi = $_POST['isi'];
        $kategori = $_POST['kategori'];
        $gambar = '';
        
        // Handle file upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $target_dir = "uploads/informasi/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_informasi.' . $file_extension;
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
            $query = "INSERT INTO informasi (judul, isi, kategori, gambar) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $judul, $isi, $kategori, $gambar);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Informasi berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan informasi!";
            }
        }
        
    } elseif (isset($_POST['edit'])) {
        // Edit informasi
        $id = $_POST['id'];
        $judul = $_POST['judul'];
        $isi = $_POST['isi'];
        $kategori = $_POST['kategori'];
        $gambar_lama = $_POST['gambar_lama'];
        
        // Handle file upload jika ada gambar baru
        $gambar = $gambar_lama;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $target_dir = "uploads/informasi/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_informasi.' . $file_extension;
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
            $query = "UPDATE informasi SET judul=?, isi=?, kategori=?, gambar=? WHERE id_informasi=?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssssi", $judul, $isi, $kategori, $gambar, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Informasi berhasil diupdate!";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate informasi!";
            }
        }
    }
}

// Hapus informasi
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Ambil data gambar untuk dihapus dari server
    $query_select = "SELECT gambar FROM informasi WHERE id_informasi = ?";
    $stmt_select = mysqli_prepare($koneksi, $query_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $informasi = mysqli_fetch_assoc($result);
    
    // Hapus file gambar dari server
    if ($informasi['gambar'] && file_exists($informasi['gambar'])) {
        unlink($informasi['gambar']);
    }
    
    // Hapus dari database
    $query = "DELETE FROM informasi WHERE id_informasi=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Informasi berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus informasi!";
    }
    
    header("Location: crud_informasi.php");
    exit();
}

// Ambil data informasi dengan pagination
$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM informasi");
$total_informasi_all = mysqli_fetch_assoc($total_q)['cnt'];
$query = "SELECT * FROM informasi ORDER BY tanggal_dibuat DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$informasi_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM informasi WHERE id_informasi=?";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('manage_information'); ?> | Kampung Jalak Bali</title>
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
        .gambar-preview { max-width: 200px; max-height: 150px; margin: 10px 0; border-radius: 4px; }
        .info-img { max-width: 80px; max-height: 60px; object-fit: cover; border-radius: 4px; }
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
                    <li><a href="crud_informasi.php"><?php echo t('manage_information'); ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section>
        <div>
            <h2><?php echo t('manage_information'); ?></h2>
            
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

            <!-- Form Tambah/Edit Informasi -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3><?php echo $edit_data ? t('edit') : t('add'); ?> <?php echo t('information'); ?></h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id_informasi']; ?>">
                        <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label><?php echo t('title'); ?>:</label>
                        <input type="text" name="judul" value="<?php echo $edit_data['judul'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori:</label>
                        <select name="kategori" required>
                            <option value="berita" <?php echo ($edit_data['kategori'] ?? '') === 'berita' ? 'selected' : ''; ?>>Berita</option>
                            <option value="artikel" <?php echo ($edit_data['kategori'] ?? '') === 'artikel' ? 'selected' : ''; ?>>Artikel</option>
                            <option value="pengumuman" <?php echo ($edit_data['kategori'] ?? '') === 'pengumuman' ? 'selected' : ''; ?>>Pengumuman</option>
                            <option value="event" <?php echo ($edit_data['kategori'] ?? '') === 'event' ? 'selected' : ''; ?>>Event</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Gambar (Optional):</label>
                        <input type="file" name="gambar" accept="image/*">
                        <small>Format: JPG, JPEG, PNG, GIF (Max: 2MB) - Opsional</small>
                        
                        <?php if ($edit_data && $edit_data['gambar']): ?>
                            <div>
                                <img src="<?php echo $edit_data['gambar']; ?>" class="gambar-preview" 
                                     onerror="this.style.display='none'">
                                <p>Gambar saat ini</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Isi:</label>
                        <textarea name="isi" rows="10" required><?php echo $edit_data['isi'] ?? ''; ?></textarea>
                    </div>
                    
                    <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                        <?php echo $edit_data ? t('update') : t('add'); ?> <?php echo t('information'); ?>
                    </button>
                    
                    <?php if ($edit_data): ?>
                        <a href="crud_informasi.php" class="btn btn-warning"><?php echo t('cancel'); ?></a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Daftar Informasi -->
            <div>
                <h3><?php echo t('information_title'); ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th><?php echo t('title'); ?></th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($informasi_data as $index => $informasi): ?>
                        <tr>
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td>
                                <?php if ($informasi['gambar']): ?>
                                    <img src="<?php echo $informasi['gambar']; ?>" class="info-img" 
                                         onerror="this.src='https://source.unsplash.com/random/80x60/?article'">
                                <?php else: ?>
                                    <span style="color: #666;">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo $informasi['judul']; ?></strong></td>
                            <td><?php echo ucfirst($informasi['kategori']); ?></td>
                            <td><?php echo date('d M Y', strtotime($informasi['tanggal_dibuat'])); ?></td>
                            <td>
                                <a href="crud_informasi.php?edit=<?php echo $informasi['id_informasi']; ?>" class="btn btn-primary">Edit</a>
                                <a href="crud_informasi.php?hapus=<?php echo $informasi['id_informasi']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Yakin hapus informasi <?php echo $informasi['judul']; ?>?')">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php 
                $total_pages = (int)ceil($total_informasi_all / $per_page);
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
            <p>&copy; 2025 Kampung Jalak Bali | Kelola Informasi</p>
        </div>
    </footer>
</body>
</html>
<?php mysqli_close($koneksi); ?>