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
        // Tambah user
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];
        
        // Validasi password
        if (strlen($password) < 6) {
            $_SESSION['error_message'] = "Password minimal 6 karakter!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $hashed_password, $role);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "User berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan user! Email mungkin sudah digunakan.";
            }
        }
        
    } elseif (isset($_POST['edit'])) {
        // Edit user
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        
        // Jika password diisi, update password juga
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                $_SESSION['error_message'] = "Password minimal 6 karakter!";
            } else {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $query = "UPDATE user SET nama=?, email=?, password=?, role=? WHERE id_user=?";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, "ssssi", $nama, $email, $password, $role, $id);
            }
        } else {
            $query = "UPDATE user SET nama=?, email=?, role=? WHERE id_user=?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "sssi", $nama, $email, $role, $id);
        }
        
        if (empty($_SESSION['error_message'])) {
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "User berhasil diupdate!";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate user!";
            }
        }
    }
}

// Hapus user (tidak bisa hapus diri sendiri)
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    if ($id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Tidak bisa menghapus akun sendiri!";
    } else {
        $query = "DELETE FROM user WHERE id_user=?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "User berhasil dihapus!";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus user!";
        }
    }
    
    header("Location: crud_user.php");
    exit();
}

// Ambil data user dengan pagination
$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

// Hitung total
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM user");
$total_user_all = mysqli_fetch_assoc($total_q)['cnt'];

$query = "SELECT * FROM user ORDER BY tanggal_daftar DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$user_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM user WHERE id_user=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_data = mysqli_fetch_assoc($result);
}

// Hitung statistik
$total_user = $total_user_all;
$total_admin = 0;
$total_regular = 0;

foreach ($user_data as $user) {
    if ($user['role'] === 'admin') $total_admin++;
    else $total_regular++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('manage_users'); ?> | Kampung Jalak Bali</title>
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
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; }
        .badge-success { background: #28a745; color: white; }
        .badge-primary { background: #007bff; color: white; }
        .badge-secondary { background: #6c757d; color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { margin: 0 0 10px 0; font-size: 14px; color: #666; }
        .stat-card .number { font-size: 32px; font-weight: bold; margin: 0; }
        .current-user { background-color: #e7f3ff !important; }
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
                    <li><a href="crud_user.php"><?php echo t('manage_users'); ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section>
        <div>
            <h2><?php echo t('manage_users'); ?></h2>
            
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
                    <h3>Total User</h3>
                    <p class="number"><?php echo $total_user; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Admin</h3>
                    <p class="number"><?php echo $total_admin; ?></p>
                </div>
                <div class="stat-card">
                    <h3>User Regular</h3>
                    <p class="number"><?php echo $total_regular; ?></p>
                </div>
            </div>

            <!-- Form Tambah/Edit User -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3><?php echo $edit_data ? t('edit') : t('add'); ?> <?php echo t('user'); ?></h3>
                <form method="POST" action="">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id_user']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Nama Lengkap:</label>
                        <input type="text" name="nama" value="<?php echo $edit_data['nama'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?php echo $edit_data['email'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" <?php echo $edit_data ? 'placeholder="Kosongkan jika tidak ingin mengubah"' : 'required'; ?>>
                        <small><?php echo $edit_data ? 'Kosongkan password jika tidak ingin mengubah' : ''; ?></small>
                    </div>
                    
                    <div class="form-group">
                        <label>Role:</label>
                        <select name="role" required>
                            <option value="user" <?php echo ($edit_data['role'] ?? '') === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo ($edit_data['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                        <?php echo $edit_data ? t('update') : t('add'); ?> <?php echo t('user'); ?>
                    </button>
                    
                    <?php if ($edit_data): ?>
                        <a href="crud_user.php" class="btn btn-warning"><?php echo t('cancel'); ?></a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Daftar User -->
            <div>
                <h3><?php echo t('user'); ?> <?php echo t('gallery_list'); /* reuse gallery_list to mean list */ ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_data as $index => $user): ?>
                        <tr class="<?php echo $user['id_user'] == $_SESSION['user_id'] ? 'current-user' : ''; ?>">
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td>
                                <?php echo $user['nama']; ?>
                                <?php if ($user['id_user'] == $_SESSION['user_id']): ?>
                                    <span class="badge badge-primary">Anda</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $user['email']; ?></td>
                            <td>
                                <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-success' : 'badge-secondary'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($user['tanggal_daftar'])); ?></td>
                            <td>
                                <a href="crud_user.php?edit=<?php echo $user['id_user']; ?>" class="btn btn-primary">Edit</a>
                                
                                <?php if ($user['id_user'] != $_SESSION['user_id']): ?>
                                <a href="crud_user.php?hapus=<?php echo $user['id_user']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Yakin hapus user <?php echo $user['nama']; ?>?')">
                                    Hapus
                                </a>
                                <?php else: ?>
                                <span class="btn btn-secondary" style="opacity: 0.5;">Hapus</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php 
                $total_pages = (int)ceil($total_user_all / $per_page);
                if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p == $page): ?>
                            <span class="active"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $p; ?>"?><?php echo $p; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <div>
            <p>&copy; 2025 Kampung Jalak Bali | Kelola User</p>
        </div>
    </footer>
</body>
</html>
<?php mysqli_close($koneksi); ?>