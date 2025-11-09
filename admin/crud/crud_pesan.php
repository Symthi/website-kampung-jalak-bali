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

// Hapus pesan
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $query = "DELETE FROM pesan WHERE id_pesan=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Pesan berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus pesan!";
    }
    
    header("Location: crud_pesan.php");
    exit();
}

// Tandai sebagai sudah dibaca
if (isset($_GET['baca'])) {
    $id = $_GET['baca'];
    $query = "UPDATE pesan SET dibaca = 1 WHERE id_pesan = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    
    header("Location: crud_pesan.php");
    exit();
}

// Ambil data pesan dengan pagination
$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM pesan");
$total_pesan_all = mysqli_fetch_assoc($total_q)['cnt'];
$query = "SELECT * FROM pesan ORDER BY tanggal DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$pesan_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Hitung statistik
$total_pesan = count($pesan_data);
$belum_dibaca = 0;

foreach ($pesan_data as $pesan) {
    if (!$pesan['dibaca']) $belum_dibaca++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('manage_messages'); ?> | Kampoeng Jalak Bali</title>
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="admin-page">
    <?php
    // gunakan header pusat agar konsisten
    $current_page = 'admin';
    include __DIR__ . '/../../includes/header.php';
    ?>

    <section class="crud-section">
        <div class="container">
            <h2 class="section-title">
                <i class="fa fa-envelope"></i> <?php echo t('manage_messages'); ?>
            </h2>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert-error">
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
                    <i class="fa fa-envelope-open"></i>
                    <div class="stat-title"><?php echo t('total_messages'); ?></div>
                    <div class="stat-number"><?php echo $total_pesan_all; ?></div>
                </div>
                <div class="dashboard-card">
                    <i class="fa fa-envelope"></i>
                    <div class="stat-title"><?php echo t('unread'); ?></div>
                    <div class="stat-number"><?php echo $belum_dibaca; ?></div>
                </div>
            </div>
        </div>

        <!-- Daftar Pesan -->
        <div class="crud-list">
            <h3 class="list-title">
                <i class="fa fa-list"></i> <?php echo t('contact_info'); ?>
            </h3>

            <?php if (empty($pesan_data)): ?>
                <p><?php echo t('no_data'); ?></p>
            <?php else: ?>
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th width="200">Pengirim</th>
                            <th width="150">Subjek</th>
                            <th>Pesan</th>
                            <th width="120">Tanggal</th>
                            <th width="100">Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pesan_data as $index => $pesan): ?>
                        <tr>
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td>
                                <strong><?php echo $pesan['nama']; ?></strong><br>
                                <small><?php echo $pesan['email']; ?></small>
                            </td>
                            <td><?php echo $pesan['subjek']; ?></td>
                            <td><?php echo substr($pesan['isi'], 0, 100) . '...'; ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($pesan['tanggal'])); ?></td>
                            <td>
                                <?php if (!$pesan['dibaca']): ?>
                                    <span class="status-badge status-active">Baru</span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">Sudah Dibaca</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="crud_pesan.php?baca=<?php echo $pesan['id_pesan']; ?>" class="btn btn-primary">
                                    <i class="fa fa-eye"></i> Baca
                                </a>
                                <a href="crud_pesan.php?hapus=<?php echo $pesan['id_pesan']; ?>" 
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
                $total_pages = (int)ceil($total_pesan_all / $per_page);
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
            <?php endif; ?>
        </div>
    </section>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    
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