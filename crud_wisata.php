<?php
session_start();
include 'koneksi.php';

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
        $biaya = $_POST['biaya'];
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

// Ambil data wisata
$query = "SELECT * FROM wisata ORDER BY tanggal_ditambahkan DESC";
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
    <title>Kelola Wisata | Kampung Jalak Bali</title>
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
</head>
<body>
    <header>
        <div>
            <div><h1>Kampung Jalak Bali</h1></div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="crud_wisata.php">Kelola Wisata</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section>
        <div>
            <h2>Kelola Data Wisata</h2>
            
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
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3><?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Wisata</h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id_wisata']; ?>">
                        <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Judul Wisata:</label>
                        <input type="text" name="judul" value="<?php echo $edit_data['judul'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi:</label>
                        <textarea name="deskripsi" rows="5" required><?php echo $edit_data['deskripsi'] ?? ''; ?></textarea>
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
                    
                    <div class="form-group">
                        <label>Durasi:</label>
                        <input type="text" name="durasi" value="<?php echo $edit_data['durasi'] ?? ''; ?>" required>
                    </div>
                    
                    <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                        <?php echo $edit_data ? 'Update' : 'Tambah'; ?> Wisata
                    </button>
                    
                    <?php if ($edit_data): ?>
                        <a href="crud_wisata.php" class="btn btn-warning">Batal</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Daftar Wisata -->
            <div>
                <h3>Daftar Wisata</h3>
                <table>
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
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <?php if ($wisata['gambar']): ?>
                                    <img src="<?php echo $wisata['gambar']; ?>" style="max-width: 80px; max-height: 60px; object-fit: cover;" 
                                         onerror="this.src='https://source.unsplash.com/random/80x60/?bali'">
                                <?php else: ?>
                                    <img src="https://source.unsplash.com/random/80x60/?bali" style="max-width: 80px; max-height: 60px; object-fit: cover;">
                                <?php endif; ?>
                            </td>
                            <td><?php echo $wisata['judul']; ?></td>
                            <td><?php echo $wisata['durasi']; ?></td>
                            <td><?php echo date('d M Y', strtotime($wisata['tanggal_ditambahkan'])); ?></td>
                            <td>
                                <a href="crud_wisata.php?edit=<?php echo $wisata['id_wisata']; ?>" class="btn btn-primary">Edit</a>
                                <a href="crud_wisata.php?hapus=<?php echo $wisata['id_wisata']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Yakin hapus wisata <?php echo $wisata['judul']; ?>?')">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <footer>
        <div>
            <p>&copy; 2025 Kampung Jalak Bali | Kelola Wisata</p>
        </div>
    </footer>
</body>
</html>
<?php mysqli_close($koneksi); ?>