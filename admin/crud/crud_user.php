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
    <title><?php echo t('manage_users'); ?> | Kampoeng Jalak Bali</title>
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body class="admin-page">
    <?php $current_page = 'admin'; include __DIR__ . '/../../includes/header.php'; ?>

    <section class="crud-section">
        <div class="container">
            <div class="page-header">
                <h2><i class="fas fa-users"></i> <?php echo t('manage_users'); ?></h2>
                <nav class="breadcrumb">
                    <a href="<?php echo $base; ?>/admin/dashboard.php">Dashboard</a> /
                    <span><?php echo t('manage_users'); ?></span>
                </nav>
            </div>

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
                <div class="stat-card bg-primary">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Total User</h3>
                        <p class="number"><?php echo $total_user; ?></p>
                    </div>
                </div>
                <div class="stat-card bg-success">
                    <div class="stat-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Admin</h3>
                        <p class="number"><?php echo $total_admin; ?></p>
                    </div>
                </div>
                <div class="stat-card bg-info">
                    <div class="stat-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stat-details">
                        <h3>User Regular</h3>
                        <p class="number"><?php echo $total_regular; ?></p>
                    </div>
                </div>
            </div>

            <!-- Form Tambah/Edit User -->
            <div class="crud-panel form-panel">
                <div class="panel-header">
                    <h3>
                        <i class="fas <?php echo $edit_data ? 'fa-user-edit' : 'fa-user-plus'; ?>"></i>
                        <?php echo $edit_data ? t('edit') : t('add'); ?> <?php echo t('user'); ?>
                    </h3>
                </div>
                <form method="POST" action="">
                    <?php if ($edit_data): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id_user']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user"></i> <?php echo t('name'); ?>:</label>
                        <input class="form-input" type="text" name="nama" value="<?php echo $edit_data['nama'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-envelope"></i> <?php echo t('email_address'); ?>:</label>
                        <input class="form-input" type="email" name="email" value="<?php echo $edit_data['email'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-lock"></i> <?php echo t('password'); ?>:</label>
                        <input class="form-input" type="password" name="password" <?php echo $edit_data ? 'placeholder="Kosongkan jika tidak ingin mengubah"' : 'required'; ?>>
                        <small class="form-text"><?php echo $edit_data ? 'Kosongkan password jika tidak ingin mengubah' : t('password_min_length'); ?></small>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user-tag"></i> <?php echo t('role'); ?>:</label>
                        <select class="form-input" name="role" required>
                            <option value="user" <?php echo ($edit_data['role'] ?? '') === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo ($edit_data['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <div class="form-actions" style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:6px;">
                        <?php if ($edit_data): ?>
                            <a href="crud_user.php" class="btn btn-warning btn-icon"><i class="fas fa-times"></i> <?php echo t('cancel'); ?></a>
                        <?php endif; ?>
                        <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary btn-icon">
                            <i class="fas <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                            <?php echo $edit_data ? t('update') : t('add'); ?> <?php echo t('user'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table User -->
            <div class="crud-panel table-panel">
                <div class="panel-header">
                    <h3>
                        <i class="fas fa-list"></i>
                        <?php echo t('user'); ?> <?php echo t('gallery_list'); ?>
                    </h3>
                </div>
                <table class="crud-table">
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
                                    <i class="fas <?php echo $user['role'] === 'admin' ? 'fa-user-shield' : 'fa-user'; ?>"></i>
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($user['tanggal_daftar'])); ?></td>
                            <td>
                                <a href="crud_user.php?edit=<?php echo $user['id_user']; ?>" class="btn btn-primary btn-icon btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                <?php if ($user['id_user'] != $_SESSION['user_id']): ?>
                                    <a href="crud_user.php?hapus=<?php echo $user['id_user']; ?>" 
                                       class="btn btn-danger btn-icon btn-sm" 
                                       onclick="return confirm('Yakin hapus user <?php echo $user['nama']; ?>?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                <?php else: ?>
                                    <span class="btn btn-secondary btn-icon btn-sm disabled">
                                        <i class="fas fa-trash"></i> Hapus
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php 
                $total_pages = (int)ceil($total_user_all / $per_page);
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
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script>
        $(document).ready(function() {
            // Toggle mobile menu
            $('.menu-toggle').click(function() {
                $('.nav-container').toggleClass('active');
            });

            // Close menu on window resize
            $(window).resize(function() {
                if ($(window).width() > 768) {
                    $('.nav-container').removeClass('active');
                }
            });
        });
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>