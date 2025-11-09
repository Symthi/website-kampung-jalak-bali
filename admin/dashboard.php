<?php
session_start();
include __DIR__ . '/../config/koneksi.php';
include __DIR__ . '/../config/language.php';

// compute base URL (site root)
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Cek apakah user sudah login
if (!isLoggedIn()) {
  header("Location: {$base}/auth/login.php");
  exit();
}

// Ambil statistik
$user_nama = $_SESSION['nama'] ?? '';
$user_role = $_SESSION['role'] ?? '';

if (isAdmin()) {
  // Global stats for admin
  $query_wisata = "SELECT COUNT(*) as total FROM wisata";
  $query_komentar = "SELECT COUNT(*) as total FROM komentar";
  $query_pesan = "SELECT COUNT(*) as total FROM pesan";
  $query_user = "SELECT COUNT(*) as total FROM user";
  $query_produk = "SELECT COUNT(*) as total FROM produk";
  $query_informasi = "SELECT COUNT(*) as total FROM informasi";

  $total_wisata = mysqli_fetch_assoc(mysqli_query($koneksi, $query_wisata))['total'];
  $total_komentar = mysqli_fetch_assoc(mysqli_query($koneksi, $query_komentar))['total'];
  $total_pesan = mysqli_fetch_assoc(mysqli_query($koneksi, $query_pesan))['total'];
  $total_user = mysqli_fetch_assoc(mysqli_query($koneksi, $query_user))['total'];
  $total_produk = mysqli_fetch_assoc(mysqli_query($koneksi, $query_produk))['total'];
  $total_informasi = mysqli_fetch_assoc(mysqli_query($koneksi, $query_informasi))['total'];
} else {
  // Personal stats for regular user
  $uid = (int)($_SESSION['user_id'] ?? 0);
  // number of comments by this user
  $q_comments = "SELECT COUNT(*) as total FROM komentar WHERE id_user = ?";
  $stmt = mysqli_prepare($koneksi, $q_comments);
  mysqli_stmt_bind_param($stmt, "i", $uid);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $user_comments = ($res && ($row = mysqli_fetch_assoc($res))) ? $row['total'] : 0;

  // number of messages sent from this user's email (contact form)
  $user_email = $_SESSION['email'] ?? '';
  if (!empty($user_email)) {
    $q_msgs = "SELECT COUNT(*) as total FROM pesan WHERE email = ?";
    $stmt2 = mysqli_prepare($koneksi, $q_msgs);
    mysqli_stmt_bind_param($stmt2, "s", $user_email);
    mysqli_stmt_execute($stmt2);
    $res2 = mysqli_stmt_get_result($stmt2);
    $user_messages = ($res2 && ($r2 = mysqli_fetch_assoc($res2))) ? $r2['total'] : 0;
  } else {
    $user_messages = 0;
  }

  // fetch user registration date for display
  $q_user = "SELECT tanggal_daftar FROM user WHERE id_user = ? LIMIT 1";
  $stmt3 = mysqli_prepare($koneksi, $q_user);
  mysqli_stmt_bind_param($stmt3, "i", $uid);
  mysqli_stmt_execute($stmt3);
  $res3 = mysqli_stmt_get_result($stmt3);
  $user_registered = ($res3 && ($r3 = mysqli_fetch_assoc($res3))) ? $r3['tanggal_daftar'] : null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('dashboard'); ?> | Kampoeng Jalak Bali</title>
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <?php 
  $current_page = 'dashboard';
  include __DIR__ . '/../includes/header.php';
  ?>

    <section class="dashboard-section">
      <div class="admin-dashboard container">
        <?php if (isAdmin()): ?>
        <aside class="admin-sidebar" aria-label="Admin menu">
          <div class="sidebar-title"><i class="fa fa-cogs"></i> <?php echo t('admin_menu'); ?></div>
            <ul>
            <li><a href="<?php echo $base; ?>/admin/crud/crud_wisata.php"><i class="fa fa-map-marked-alt"></i> <?php echo t('manage_tourism'); ?></a></li>
            <li><a href="<?php echo $base; ?>/admin/crud/crud_informasi.php"><i class="fa fa-info-circle"></i> <?php echo t('manage_information'); ?></a></li>
            <li><a href="<?php echo $base; ?>/admin/crud/crud_komentar.php"><i class="fa fa-comments"></i> <?php echo t('manage_comments'); ?></a></li>
            <li><a href="<?php echo $base; ?>/admin/crud/crud_produk.php"><i class="fa fa-box"></i> <?php echo t('manage_products'); ?></a></li>
            <li><a href="<?php echo $base; ?>/admin/crud/crud_pesan.php"><i class="fa fa-envelope"></i> <?php echo t('manage_messages'); ?></a></li>
            <li><a href="<?php echo $base; ?>/admin/crud/crud_galeri.php"><i class="fa fa-images"></i> <?php echo t('manage_gallery'); ?></a></li>
            <li><a href="<?php echo $base; ?>/admin/crud/crud_user.php"><i class="fa fa-users"></i> <?php echo t('manage_users'); ?></a></li>
          </ul>
        </aside>
        <?php endif; ?>

        <main class="admin-main">
          <div class="dashboard-header">
            <h2><i class="fa fa-tachometer-alt icon"></i> <?php echo isAdmin() ? t('admin_dashboard') : t('user_dashboard'); ?></h2>
          </div>

          <div class="dashboard-welcome">
            <h3><i class="fa fa-user icon"></i> <?php echo t('welcome'); ?>, <?php echo $user_nama; ?>!</h3>
            <p><i class="fa fa-user-tag icon"></i> <?php echo t('role'); ?>: <?php echo $user_role; ?></p>
          </div>

          <div class="admin-stats-wrap">
            <div class="admin-stats">
              <?php if (isAdmin()): ?>
                <div class="dashboard-card">
                  <i class="fa fa-map-marked-alt"></i>
                  <div class="stat-title"><?php echo t('total_tourism') ?: 'Wisata'; ?></div>
                  <div class="stat-number"><?php echo $total_wisata; ?></div>
                </div>
                <div class="dashboard-card">
                  <i class="fa fa-comments"></i>
                  <div class="stat-title"><?php echo t('total_comments') ?: 'Komentar'; ?></div>
                  <div class="stat-number"><?php echo $total_komentar; ?></div>
                </div>
                <div class="dashboard-card">
                  <i class="fa fa-envelope"></i>
                  <div class="stat-title"><?php echo t('total_messages') ?: 'Pesan'; ?></div>
                  <div class="stat-number"><?php echo $total_pesan; ?></div>
                </div>
                <div class="dashboard-card">
                  <i class="fa fa-users"></i>
                  <div class="stat-title"><?php echo t('total_users') ?: 'User'; ?></div>
                  <div class="stat-number"><?php echo $total_user; ?></div>
                </div>
                <div class="dashboard-card">
                  <i class="fa fa-box"></i>
                  <div class="stat-title"><?php echo t('total_products') ?: 'Produk'; ?></div>
                  <div class="stat-number"><?php echo $total_produk; ?></div>
                </div>
                <div class="dashboard-card">
                  <i class="fa fa-info-circle"></i>
                  <div class="stat-title"><?php echo t('information') ?: 'Informasi'; ?></div>
                  <div class="stat-number"><?php echo $total_informasi; ?></div>
                </div>
              <?php else: ?>
                <div class="dashboard-card">
                  <i class="fa fa-comments"></i>
                  <div class="stat-title"><?php echo t('comments') ?: 'Komentar'; ?></div>
                  <div class="stat-number"><?php echo $user_comments ?? 0; ?></div>
                </div>
                <div class="dashboard-card">
                  <i class="fa fa-envelope"></i>
                  <div class="stat-title"><?php echo t('my_messages') ?: 'Pesan Saya'; ?></div>
                  <div class="stat-number"><?php echo $user_messages ?? 0; ?></div>
                </div>
                <div class="dashboard-card">
                  <i class="fa fa-calendar-check"></i>
                  <div class="stat-title"><?php echo t('member_since') ?: 'Bergabung Sejak'; ?></div>
                  <div class="stat-number"><?php echo $user_registered ? date('d M Y', strtotime($user_registered)) : '-'; ?></div>
                </div>
              <?php endif; ?>
            </div>
          </div>

        </main>
      </div>
    </section>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
    
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
