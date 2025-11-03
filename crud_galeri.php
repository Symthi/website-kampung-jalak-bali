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

// Proses upload gambar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $judul = $_POST['judul'];
    $keterangan = $_POST['keterangan'];
    
    // Handle file upload
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/galeri/";
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
        $target_dir = "uploads/galeri/";
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
    
    // Hapus file gambar dari server
    if ($galeri['gambar'] && file_exists($galeri['gambar'])) {
        unlink($galeri['gambar']);
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
    <title><?php echo t('manage_gallery'); ?> | Kampung Jalak Bali</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .btn { padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-success { background: #28a745; color: white; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .gallery-img { max-width: 150px; max-height: 100px; object-fit: cover; border-radius: 4px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
        .grid-item { border: 1px solid #ddd; border-radius: 8px; padding: 15px; text-align: center; background: white; }
        .grid-item img { max-width: 100%; height: 180px; object-fit: cover; border-radius: 4px; margin-bottom: 10px; }
        .gambar-preview { max-width: 200px; max-height: 150px; margin: 10px 0; border-radius: 4px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { margin: 0 0 5px 0; font-size: 14px; color: #666; }
        .stat-card .number { font-size: 24px; font-weight: bold; margin: 0; color: #1a6b3b; }
    </style>
</head>
<body>
    <header>
        <div>
            <div><h1>Kampung Jalak Bali</h1></div>
            <nav>
                <ul>
                    <li><a href="index.php"><?php echo t('home'); ?></a></li>
                    <li><a href="dashboard.php"><?php echo t('dashboard'); ?></a></li>
                    <li><a href="crud_galeri.php"><?php echo t('manage_gallery'); ?></a></li>
                    <li><a href="logout.php"><?php echo t('logout'); ?></a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section>
        <div>
            <h2><?php echo t('manage_gallery'); ?></h2>
            
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
            <div class="stats">
                <div class="stat-card">
                    <h3><?php echo t('total_images'); ?></h3>
                    <p class="number"><?php echo count($galeri_data); ?></p>
                </div>
                <div class="stat-card">
                    <h3><?php echo t('folder'); ?></h3>
                    <p class="number">uploads/galeri/</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo t('max_size'); ?></h3>
                    <p class="number">2 MB</p>
                </div>
            </div>

            <!-- Form Upload/Edit Gambar -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3><?php echo $edit_data ? t('edit') : t('add'); ?> <?php echo t('upload_image'); ?> <?php echo t('gallery_list') == 'Gallery List' ? '' : ''; ?></h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id_galeri']; ?>">
                        <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label><?php echo t('title'); ?>:</label>
                        <input type="text" name="judul" value="<?php echo $edit_data['judul'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo t('description_short'); ?>:</label>
                        <textarea name="keterangan" rows="3"><?php echo $edit_data['keterangan'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo t('upload_image'); ?>:</label>
                        <input type="file" name="gambar" accept="image/*" <?php echo !$edit_data ? 'required' : ''; ?>>
                        <small>Format: JPG, JPEG, PNG, GIF (Max: 2MB)</small>
                        
                        <?php if ($edit_data && $edit_data['gambar']): ?>
                            <div>
                                <img src="<?php echo $edit_data['gambar']; ?>" class="gambar-preview" 
                                     onerror="this.src='https://source.unsplash.com/random/200x150/?bali'">
                                <p>Gambar saat ini</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                        <?php echo $edit_data ? t('update') : t('upload'); ?> <?php echo t('upload_image'); ?>
                    </button>
                    
                    <?php if ($edit_data): ?>
                        <a href="crud_galeri.php" class="btn btn-warning"><?php echo t('cancel'); ?></a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Grid Galeri -->
            <div>
                <h3><?php echo t('gallery_list'); ?> (<?php echo $total_galeri_all; ?> <?php echo t('upload_image'); ?>)</h3>
                
                <?php if (empty($galeri_data)): ?>
                    <p style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;">
                        <?php echo t('no_gallery_images'); ?>
                    </p>
                <?php else: ?>
                
                <!-- Grid View -->
                <div class="grid">
                    <?php foreach ($galeri_data as $galeri): ?>
                    <div class="grid-item">
                        <img src="<?php echo $galeri['gambar']; ?>" 
                             alt="<?php echo $galeri['judul']; ?>"
                             onerror="this.src='https://source.unsplash.com/random/300x200/?bali'">
                        <h4><?php echo $galeri['judul']; ?></h4>
                        <p><?php echo $galeri['keterangan']; ?></p>
                        <small>Upload: <?php echo date('d M Y', strtotime($galeri['tanggal_upload'])); ?></small>
                        <div style="margin-top: 15px;">
                            <a href="crud_galeri.php?edit=<?php echo $galeri['id_galeri']; ?>" class="btn btn-primary"><?php echo t('edit'); ?></a>
                            <a href="crud_galeri.php?hapus=<?php echo $galeri['id_galeri']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('<?php echo addslashes(t('confirm_delete')); ?>')">
                                <?php echo t('delete'); ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Tabel View (Alternatif) -->
                <details style="margin-top: 30px;">
                    <summary><strong><?php echo t('gallery_list'); ?> - Table View</strong></summary>
                    <table>
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
                                    <img src="<?php echo $galeri['gambar']; ?>" 
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
                <div class="pagination" style="display:flex; gap:8px; margin-top:15px;">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p == $page): ?>
                            <span class="active" style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; background:#007bff; color:#fff; border-color:#007bff;"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $p; ?>" style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; text-decoration:none; color:#333;"><?php echo $p; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div style="margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
                <h3>Tips Upload Gambar</h3>
                <ul>
                    <li>Gunakan gambar dengan resolusi minimal 800x600 pixel</li>
                    <li>Format yang didukung: JPG, PNG, GIF</li>
                    <li>Ukuran maksimal file: 2MB</li>
                    <li>Nama file akan di-generate otomatis</li>
                    <li>Gambar akan di-compress otomatis untuk optimasi</li>
                </ul>
            </div>
        </div>
    </section>

    <footer>
        <div>
            <p>&copy; 2025 Kampung Jalak Bali | Kelola Galeri</p>
        </div>
    </footer>

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
                            preview.style.display = 'block';
                            preview.style.margin = '10px 0';
                            
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