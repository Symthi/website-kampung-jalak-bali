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

// Hapus komentar
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $query = "DELETE FROM komentar WHERE id_komentar=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Komentar berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus komentar!";
    }
    
    header("Location: crud_komentar.php");
    exit();
}

// Ambil data komentar dengan join ke user dan wisata + pagination
$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM komentar");
$total_komentar_all = mysqli_fetch_assoc($total_q)['cnt'];

$query = "SELECT k.*, u.nama as nama_user, u.email, w.judul as judul_wisata 
          FROM komentar k 
          JOIN user u ON k.id_user = u.id_user 
          JOIN wisata w ON k.id_wisata = w.id_wisata 
          ORDER BY k.tanggal DESC
          LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$komentar_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('manage_comments'); ?> | Kampoeng Jalak Bali</title>
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
            <h2 class="section-title">
                <i class="fa fa-comments"></i> <?php echo t('manage_comments'); ?>
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
                <i class="fa fa-chart-bar"></i> <?php echo t('comment_statistics'); ?>
            </h3>
            <div class="dashboard-stats">
                <div class="dashboard-card">
                    <i class="fa fa-comments"></i>
                    <div class="stat-title"><?php echo t('total_comments'); ?></div>
                    <div class="stat-number"><?php echo $total_komentar_all; ?></div>
                </div>
            </div>
        </div>

        <!-- Daftar Komentar -->
        <div class="crud-list">
            <h3 class="list-title">
                <i class="fa fa-list"></i> <?php echo t('comments'); ?>
            </h3>

          <?php if (empty($komentar_data)): ?>
            <p><?php echo t('no_comments'); ?></p>
          <?php else: ?>
          <table class="crud-table">
            <thead>
              <tr>
                <th width="50"><?php echo t('no'); ?></th>
                <th width="200"><?php echo t('user'); ?></th>
                <th width="200"><?php echo t('tourism'); ?></th>
                <th><?php echo t('comments'); ?></th>
                <th width="150"><?php echo t('date'); ?></th>
                <th width="150"><?php echo t('actions'); ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($komentar_data as $index =>
              $komentar): ?>
              <tr>
                <td><?php echo $offset + $index + 1; ?></td>
                <td>
                  <strong><?php echo $komentar['nama_user']; ?></strong><br />
                  <small><?php echo $komentar['email']; ?></small>
                </td>
                <td><?php echo $komentar['judul_wisata']; ?></td>
                <td><?php echo $komentar['isi']; ?></td>
                <td><?php echo date('d M Y H:i', strtotime($komentar['tanggal'])); ?></td>
                <td>
                  <a href="crud_komentar.php?hapus=<?php echo $komentar['id_komentar']; ?>" 
                     class="btn btn-danger" 
                     onclick="return confirm('<?php echo addslashes(t('confirm_delete')); ?>')">
                    <i class="fa fa-trash"></i> <?php echo t('delete'); ?>
                  </a>
                  <a href="detail_wisata.php?id=<?php echo $komentar['id_wisata']; ?>" 
                     class="btn btn-primary">
                    <i class="fa fa-eye"></i> <?php echo t('view_tour'); ?>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
          <?php 
          $total_pages = (int)ceil($total_komentar_all / $per_page);
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
