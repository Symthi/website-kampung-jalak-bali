<?php
session_start();
include __DIR__ . '/../../config/koneksi.php';
include __DIR__ . '/../../config/language.php';

// compute base URL (site root)
$base = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\');

// Fungsi cek login dan admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Cek apakah user adalah admin
if (!isAdmin()) {
    header("Location: {$base}/auth/login.php"); 
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
            $public_dir = 'uploads/informasi/';
            $target_dir = __DIR__ . '/../../' . $public_dir;
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
                        $gambar = $public_dir . $filename; // store public relative path
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
            // keep same public_dir/target_dir behavior as add
            $public_dir = 'uploads/informasi/';
            $target_dir = __DIR__ . '/../../' . $public_dir;
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
                        // Hapus gambar lama jika ada (filesystem path)
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
    
    // Hapus file gambar dari server (filesystem path)
    if (!empty($informasi['gambar'])) {
        $informasi_path = __DIR__ . '/../../' . $informasi['gambar'];
        if (file_exists($informasi_path)) {
            @unlink($informasi_path);
        }
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
    <title><?php echo t('manage_information'); ?> | Kampoeng Jalak Bali</title>
<<<<<<< HEAD:crud_informasi.php
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header class="dashboard-header">
        <div class="header-container">
            <!--logo-->
            <div class="logo-title">
            <img src="uploads/Rancangan Logo.png" alt="Logo Kampoeng Jalak Bali" width="50px" />
            <h1>Kampoeng Jalak Bali</h1>
            </div>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
            <div class="nav-container">
                <nav>
                    <ul>
                        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="crud_user.php" class="active"><i class="fas fa-users"></i> <?php echo t('manage_users'); ?></a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
=======
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="admin-page">
    <?php
    // gunakan header pusat agar konsisten
    $current_page = 'admin';
    include __DIR__ . '/../../includes/header.php';
    ?>
>>>>>>> 5a8afd3427364eab5bee3caf7b30eb4d0e3ba3e8:admin/crud/crud_informasi.php

    <section class="crud-section">
        <div class="container">
            <h2 class="section-title">
                <i class="fa fa-info-circle"></i> <?php echo t('manage_information'); ?>
            </h2>
            
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
            <div class="crud-panel">
                <h3 class="panel-title">
                    <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
                    <?php echo $edit_data ? t('edit') : t('add'); ?> <?php echo t('information'); ?>
                </h3>
                <form method="POST" action="" enctype="multipart/form-data" class="crud-form">
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
                                     onerror="this.src='https://source.unsplash.com/random/200x150/?article'">
                                <p>Gambar saat ini</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Isi:</label>
                        <textarea name="isi" rows="10" required><?php echo $edit_data['isi'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                            <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                            <?php echo $edit_data ? t('update') : t('add'); ?> <?php echo t('information'); ?>
                        </button>
                        
                        <?php if ($edit_data): ?>
                            <a href="crud_informasi.php" class="btn btn-warning">
                                <i class="fa fa-times"></i>
                                <?php echo t('cancel'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Daftar Informasi -->
            <div class="crud-list">
                <h3 class="list-title">
                    <i class="fa fa-list"></i> <?php echo t('information_title'); ?>
                </h3>
                <table class="crud-table">
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
                                    <img src="<?php echo $base . '/' . $informasi['gambar']; ?>" class="info-img" 
                                         onerror="this.src='https://source.unsplash.com/random/80x60/?article'">
                                <?php else: ?>
                                    <span class="muted-text">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo $informasi['judul']; ?></strong></td>
                            <td><?php echo ucfirst($informasi['kategori']); ?></td>
                            <td><?php echo date('d M Y', strtotime($informasi['tanggal_dibuat'])); ?></td>
                            <td>
                                <a href="crud_informasi.php?edit=<?php echo $informasi['id_informasi']; ?>" class="btn btn-primary">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <a href="crud_informasi.php?hapus=<?php echo $informasi['id_informasi']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Yakin hapus informasi <?php echo $informasi['judul']; ?>?')">
                                    <i class="fa fa-trash"></i> Hapus
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

<<<<<<< HEAD:crud_informasi.php
    <?php include 'footer.php'; ?>
=======
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
>>>>>>> 5a8afd3427364eab5bee3caf7b30eb4d0e3ba3e8:admin/crud/crud_informasi.php
    
    <script>
        // Toggle mobile menu
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navLinks = document.querySelector('.nav-links');

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function() {
                    navLinks.classList.toggle('show');
                });
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>