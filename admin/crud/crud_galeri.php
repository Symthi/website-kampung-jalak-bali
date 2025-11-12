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

// Proses upload gambar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $judul = $_POST['judul'];
    $keterangan = $_POST['keterangan'];
    
    // Handle file upload
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
    } else {
        $_SESSION['error_message'] = "Silakan pilih gambar untuk diupload.";
    }
    
    if (empty($_SESSION['error_message'])) {
        $query = "INSERT INTO galeri (judul, gambar, keterangan) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sss", $judul, $gambar, $keterangan);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Gambar berhasil ditambahkan ke galeri!";
        } else {
            $_SESSION['error_message'] = "Gagal menambahkan gambar!";
        }
    }
    
    header("Location: crud_galeri.php");
    exit();
}

// Proses edit galeri
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id'];
    $judul = $_POST['judul'];
    $keterangan = $_POST['keterangan'];
    $gambar_lama = $_POST['gambar_lama'];
    
    // Handle file upload jika ada gambar baru
    $gambar = $gambar_lama;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        // use same public_dir/target_dir pattern as the add flow
        $public_dir = 'uploads/galeri/';
        $target_dir = __DIR__ . '/../../' . $public_dir;
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_galeri.' . $file_extension;
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
                    // store public relative path
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
        $query = "UPDATE galeri SET judul=?, gambar=?, keterangan=? WHERE id_galeri=?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $judul, $gambar, $keterangan, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Gambar galeri berhasil diupdate!";
        } else {
            $_SESSION['error_message'] = "Gagal mengupdate gambar!";
        }
    }
    
    header("Location: crud_galeri.php");
    exit();
}

// Hapus galeri
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Ambil data gambar untuk dihapus dari server
    $query_select = "SELECT gambar FROM galeri WHERE id_galeri = ?";
    $stmt_select = mysqli_prepare($koneksi, $query_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $galeri = mysqli_fetch_assoc($result);
    
    // Hapus file gambar dari server (filesystem path)
    if (!empty($galeri['gambar'])) {
        $galeri_path = __DIR__ . '/../../' . $galeri['gambar'];
        if (file_exists($galeri_path)) {
            @unlink($galeri_path);
        }
    }
    
    // Hapus dari database
    $query = "DELETE FROM galeri WHERE id_galeri=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Gambar berhasil dihapus dari galeri!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus gambar!";
    }
    
    header("Location: crud_galeri.php");
    exit();
}

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM galeri WHERE id_galeri=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_data = mysqli_fetch_assoc($result);
}

// Ambil data galeri dengan pagination
$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM galeri");
$total_galeri_all = mysqli_fetch_assoc($total_q)['cnt'];
$query = "SELECT * FROM galeri ORDER BY tanggal_upload DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$galeri_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('manage_gallery'); ?> | Kampoeng Jalak Bali</title>
<<<<<<< HEAD:crud_galeri.php
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
>>>>>>> 5a8afd3427364eab5bee3caf7b30eb4d0e3ba3e8:admin/crud/crud_galeri.php

    <section class="crud-section">
        <div class="container">
            <h2 class="section-title">
                <i class="fa fa-images"></i> <?php echo t('manage_gallery'); ?>
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

            <!-- Statistik -->
            <div class="crud-panel">
                <h3 class="panel-title">
                    <i class="fa fa-chart-bar"></i> <?php echo t('statistics'); ?>
                </h3>
                <div class="dashboard-stats">
                    <div class="dashboard-card">
                        <i class="fa fa-images"></i>
                        <div class="stat-title"><?php echo t('total_images'); ?></div>
                        <div class="stat-number"><?php echo count($galeri_data); ?></div>
                    </div>
                    <div class="dashboard-card">
                        <i class="fa fa-folder"></i>
                        <div class="stat-title"><?php echo t('folder'); ?></div>
                        <div class="stat-number">uploads/galeri/</div>
                    </div>
                    <div class="dashboard-card">
                        <i class="fa fa-file-archive"></i>
                        <div class="stat-title"><?php echo t('max_size'); ?></div>
                        <div class="stat-number">2 MB</div>
                    </div>
                </div>
            </div>

            <!-- Form Upload/Edit Gambar -->
<<<<<<< HEAD:crud_galeri.php
            <div class="crud-panel">
                <h3 class="panel-title">
                    <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
                    <?php echo $edit_data ? t('edit') : t('add'); ?> <?php echo t('upload_image'); ?> <?php echo t('gallery_list') == 'Gallery List' ? '' : ''; ?>
                </h3>
=======
            <div class="crud-panel form-panel">
                <div class="panel-header">
                    <h3 class="panel-title">
                        <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
                        <?php echo $edit_data ? t('edit') : t('add'); ?> <?php echo t('upload_image'); ?>
                    </h3>
                </div>
>>>>>>> 5a8afd3427364eab5bee3caf7b30eb4d0e3ba3e8:admin/crud/crud_galeri.php
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id_galeri']; ?>">
                        <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label"><?php echo t('title'); ?></label>
                        <input class="form-input" type="text" name="judul" value="<?php echo $edit_data['judul'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php echo t('description_short'); ?></label>
                        <textarea class="form-textarea" name="keterangan" rows="3"><?php echo $edit_data['keterangan'] ?? ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php echo t('upload_image'); ?></label>
                        <input class="form-input" type="file" name="gambar" accept="image/*" <?php echo !$edit_data ? 'required' : ''; ?>>
                        <small class="muted-text">Format: JPG, JPEG, PNG, GIF (Max: 2MB)</small>

                        <?php if ($edit_data && $edit_data['gambar']): ?>
                            <div>
                                <img src="<?php echo $edit_data['gambar']; ?>" class="gambar-preview" 
                                     onerror="this.src='https://source.unsplash.com/random/200x150/?bali'" alt="preview">
                                <p class="muted-text">Gambar saat ini</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions" style="grid-column:1 / -1; display:flex; gap:0.75rem; justify-content:flex-end;">
                        <?php if ($edit_data): ?>
                            <a href="crud_galeri.php" class="btn btn-warning btn-icon"><i class="fa fa-times"></i> <?php echo t('cancel'); ?></a>
                        <?php endif; ?>
                        <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary btn-icon">
                            <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-upload'; ?>"></i>
                            <?php echo $edit_data ? t('update') : t('upload'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Grid Galeri -->
            <div class="crud-list">
                <h3 class="list-title">
                    <i class="fa fa-list"></i> <?php echo t('gallery_list'); ?> 
                    <span class="badge"><?php echo $total_galeri_all; ?> <?php echo t('upload_image'); ?></span>
                </h3>
                
                <?php if (empty($galeri_data)): ?>
                    <p class="panel center">
                        <?php echo t('no_gallery_images'); ?>
                    </p>
                <?php else: ?>
                
                <!-- Grid View -->
                <div class="grid">
                    <?php foreach ($galeri_data as $galeri): ?>
                    <div class="grid-item">
                        <img src="<?php echo $base . '/' . $galeri['gambar']; ?>" 
                             alt="<?php echo $galeri['judul']; ?>"
                             onerror="this.src='https://source.unsplash.com/random/300x200/?bali'">
                        <h4><?php echo $galeri['judul']; ?></h4>
                        <p><?php echo $galeri['keterangan']; ?></p>
                        <small>Upload: <?php echo date('d M Y', strtotime($galeri['tanggal_upload'])); ?></small>
                        <div class="mt-15">
                            <a href="crud_galeri.php?edit=<?php echo $galeri['id_galeri']; ?>" class="btn btn-primary">
                                <i class="fa fa-edit"></i> <?php echo t('edit'); ?>
                            </a>
                            <a href="crud_galeri.php?hapus=<?php echo $galeri['id_galeri']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('<?php echo addslashes(t('confirm_delete')); ?>')">
                                <i class="fa fa-trash"></i> <?php echo t('delete'); ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Tabel View (Alternatif) -->
                <details class="mt-30">
                    <summary><strong><?php echo t('gallery_list'); ?> - Table View</strong></summary>
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th><?php echo t('upload_image'); ?></th>
                                <th><?php echo t('title'); ?></th>
                                <th><?php echo t('description_short'); ?></th>
                                <th><?php echo t('created_at'); ?></th>
                                <th><?php echo t('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($galeri_data as $index => $galeri): ?>
                            <tr>
                                <td><?php echo $offset + $index + 1; ?></td>
                                <td>
                                    <img src="<?php echo $base . '/' . $galeri['gambar']; ?>" 
                                         alt="<?php echo $galeri['judul']; ?>"
                                         class="gallery-img"
                                         onerror="this.src='https://source.unsplash.com/random/100x100/?bali'">
                                </td>
                                <td><strong><?php echo $galeri['judul']; ?></strong></td>
                                <td><?php echo $galeri['keterangan']; ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($galeri['tanggal_upload'])); ?></td>
                                <td>
                                    <a href="crud_galeri.php?edit=<?php echo $galeri['id_galeri']; ?>" class="btn btn-primary"><?php echo t('edit'); ?></a>
                                    <a href="crud_galeri.php?hapus=<?php echo $galeri['id_galeri']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('<?php echo addslashes(t('confirm_delete')); ?>')">
                                        <?php echo t('delete'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </details>
                <?php endif; ?>
                <?php 
                $total_pages = (int)ceil($total_galeri_all / $per_page);
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

            <!-- Quick Actions -->
            <div class="crud-panel mt-30">
                <h3 class="panel-title">
                    <i class="fa fa-lightbulb"></i> Tips Upload Gambar
                </h3>
                <ul class="tips-list">
                    <li><i class="fa fa-check"></i> Gunakan gambar dengan resolusi minimal 800x600 pixel</li>
                    <li><i class="fa fa-check"></i> Format yang didukung: JPG, PNG, GIF</li>
                    <li><i class="fa fa-check"></i> Ukuran maksimal file: 2MB</li>
                    <li><i class="fa fa-check"></i> Nama file akan di-generate otomatis</li>
                    <li><i class="fa fa-check"></i> Gambar akan di-compress otomatis untuk optimasi</li>
                </ul>
            </div>
        </div>
    </section>

<<<<<<< HEAD:crud_galeri.php
    <?php include 'footer.php'; ?>
=======
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
>>>>>>> 5a8afd3427364eab5bee3caf7b30eb4d0e3ba3e8:admin/crud/crud_galeri.php

    <script>
        // Preview gambar sebelum upload
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.querySelector('input[type="file"]');
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // Hapus preview lama jika ada
                            const oldPreview = document.querySelector('.gambar-preview');
                            if (oldPreview) {
                                oldPreview.remove();
                            }
                            
                            // Buat preview baru
                            const preview = document.createElement('img');
                            preview.src = e.target.result;
                            preview.className = 'gambar-preview';
                            
                            // Sisipkan setelah input file
                            fileInput.parentNode.appendChild(preview);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>