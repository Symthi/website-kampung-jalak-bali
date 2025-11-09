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
        // Tambah wisata
        $judul = $_POST['judul'];
        $deskripsi = $_POST['deskripsi'];
        $durasi = $_POST['durasi'];
        $gambar = '';
        
        // Handle file upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $target_dir = "uploads/wisata/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_wisata.' . $file_extension;
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
            $query = "INSERT INTO wisata (judul, deskripsi, gambar, durasi) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $judul, $deskripsi, $gambar, $durasi);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Wisata berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan wisata!";
            }
        }
        
    } elseif (isset($_POST['edit'])) {
        // Edit wisata
        $id = $_POST['id'];
        $judul = $_POST['judul'];
        $deskripsi = $_POST['deskripsi'];
        $durasi = $_POST['durasi'];
        $gambar = $_POST['gambar_lama'];
        
        // Handle file upload jika ada gambar baru
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $target_dir = "uploads/wisata/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_wisata.' . $file_extension;
            $target_file = $target_dir . $filename;
            
            // Validasi file
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (in_array(strtolower($file_extension), $allowed_types)) {
                if ($_FILES['gambar']['size'] <= $max_size) {
                    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                        // Hapus gambar lama jika ada
                        if (!empty($gambar) && file_exists($gambar)) {
                            unlink($gambar);
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
            $query = "UPDATE wisata SET judul=?, deskripsi=?, gambar=?, durasi=? WHERE id_wisata=?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssssi", $judul, $deskripsi, $gambar, $durasi, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Wisata berhasil diupdate!";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate wisata!";
            }
        }
    }
}

// Hapus wisata
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    // Ambil data gambar untuk dihapus dari server
    $query_select = "SELECT gambar FROM wisata WHERE id_wisata = ?";
    $stmt_select = mysqli_prepare($koneksi, $query_select);
    mysqli_stmt_bind_param($stmt_select, "i", $id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $wisata = mysqli_fetch_assoc($result);
    
    // Hapus file gambar dari server
    if ($wisata['gambar'] && file_exists($wisata['gambar'])) {
        unlink($wisata['gambar']);
    }
    
    // Hapus dari database
    $query = "DELETE FROM wisata WHERE id_wisata=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Wisata berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus wisata!";
    }
    
    header("Location: crud_wisata.php");
    exit();
}

// Ambil data wisata dengan pagination
$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM wisata");
$total_wisata_all = mysqli_fetch_assoc($total_q)['cnt'];
$query = "SELECT * FROM wisata ORDER BY tanggal_ditambahkan DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$wisata_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM wisata WHERE id_wisata=?";
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
    <title><?php echo t('manage_tourism'); ?> | Kampoeng Jalak Bali</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="admin-page">
    <?php
    // gunakan header pusat agar konsisten
    $current_page = 'admin';
    include 'header.php';
    ?>
    <section class="crud-section">
        <div class="container">
            <h2 class="section-title"><i class="fa fa-map-marked-alt"></i> <?php echo t('manage_tourism'); ?></h2>
            
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

            <!-- Form Tambah/Edit Wisata -->
            <div class="crud-panel">
                <h3 class="panel-title">
                    <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
                    <?php echo $edit_data ? t('edit') : t('add'); ?> <?php echo t('tourism'); ?>
                </h3>
                <form method="POST" action="" enctype="multipart/form-data" class="crud-form">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id_wisata']; ?>">
                        <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label><?php echo t('title'); ?>:</label>
                        <input type="text" name="judul" value="<?php echo $edit_data['judul'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo t('description_short'); ?>:</label>
                        <textarea name="deskripsi" rows="5" required><?php echo $edit_data['deskripsi'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo t('upload_image'); ?>:</label>
                        <input type="file" name="gambar" accept="image/*" <?php echo !$edit_data ? 'required' : ''; ?>>
                        <small>Format: JPG, JPEG, PNG, GIF (Max: 2MB)</small>
                        
                        <?php if ($edit_data && $edit_data['gambar']): ?>
                            <div>
                                <img src="<?php echo $edit_data['gambar']; ?>" class="gambar-preview" 
                                     onerror="this.src='https://source.unsplash.com/random/200x150/?bali'">
                                <p><?php echo t('upload_image'); ?> saat ini</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo t('duration'); ?>:</label>
                        <input type="text" name="durasi" value="<?php echo $edit_data['durasi'] ?? ''; ?>" required>
                    </div>
                    
                    <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                        <?php echo $edit_data ? t('update') : t('add'); ?> <?php echo t('tourism'); ?>
                    </button>
                    
                    <?php if ($edit_data): ?>
                        <a href="crud_wisata.php" class="btn btn-warning"><?php echo t('cancel'); ?></a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Daftar Wisata -->
            <div class="crud-list">
                <h3 class="list-title">
                    <i class="fa fa-list"></i>
                    <?php echo t('tourism_title'); ?> / <?php echo t('gallery_list'); ?>
                </h3>
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Judul</th>
                            <th>Durasi</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wisata_data as $index => $wisata): ?>
                        <tr>
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td>
                                <?php if ($wisata['gambar']): ?>
                                    <img src="<?php echo $wisata['gambar']; ?>" class="thumb-img" 
                                         onerror="this.src='https://source.unsplash.com/random/80x60/?bali'">
                                <?php else: ?>
                                    <img src="https://source.unsplash.com/random/80x60/?bali" class="thumb-img">
                                <?php endif; ?>
                            </td>
                            <td><?php echo $wisata['judul']; ?></td>
                            <td><?php echo $wisata['durasi']; ?></td>
                            <td><?php echo date('d M Y', strtotime($wisata['tanggal_ditambahkan'])); ?></td>
                            <td>
                                <a href="crud_wisata.php?edit=<?php echo $wisata['id_wisata']; ?>" class="btn btn-primary">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <a href="crud_wisata.php?hapus=<?php echo $wisata['id_wisata']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('<?php echo addslashes(t('confirm_delete')); ?>')">
                                    <i class="fa fa-trash"></i> <?php echo t('delete'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php 
                $total_pages = (int)ceil($total_wisata_all / $per_page);
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

    <?php include 'footer.php'; ?>
    
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