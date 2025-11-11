<?php
session_start();
include 'koneksi.php';
include 'language.php';

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Ambil statistik
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

$user_nama = $_SESSION['nama'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('dashboard'); ?> | Kampoeng Jalak Bali</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php 
    $current_page = 'dashboard';
    include 'header.php';
    ?>

    <section class="dashboard-section">
      <div class="dashboard-header">
        <h2><i class="fa fa-tachometer-alt icon"></i> <?php echo isAdmin() ? t('admin_dashboard') : t('user_dashboard'); ?></h2>
      </div>
      <div class="dashboard-welcome">
        <h3><i class="fa fa-user icon"></i> <?php echo t('welcome'); ?>, <?php echo $user_nama; ?>!</h3>
        <p><i class="fa fa-user-tag icon"></i> <?php echo t('role'); ?>: <?php echo $user_role; ?></p>
      </div>
      <!-- Statistik -->
      <div class="dashboard-stats">
        <div class="dashboard-card">
          <i class="fa fa-map-marked-alt"></i>
          <div class="stat-title">Wisata</div>
          <div class="stat-number"><?php echo $total_wisata; ?></div>
        </div>
        <div class="dashboard-card">
          <i class="fa fa-comments"></i>
          <div class="stat-title">Komentar</div>
          <div class="stat-number"><?php echo $total_komentar; ?></div>
        </div>
        <div class="dashboard-card">
          <i class="fa fa-envelope"></i>
          <div class="stat-title">Pesan</div>
          <div class="stat-number"><?php echo $total_pesan; ?></div>
        </div>
        <div class="dashboard-card">
          <i class="fa fa-users"></i>
          <div class="stat-title">User</div>
          <div class="stat-number"><?php echo $total_user; ?></div>
        </div>
        <div class="dashboard-card">
          <i class="fa fa-box"></i>
          <div class="stat-title">Produk</div>
          <div class="stat-number"><?php echo $total_produk; ?></div>
        </div>
        <div class="dashboard-card">
          <i class="fa fa-info-circle"></i>
          <div class="stat-title">Informasi</div>
          <div class="stat-number"><?php echo $total_informasi; ?></div>
        </div>
      </div>
      <!-- Menu Admin -->
      <?php if (isAdmin()): ?>
      <div class="dashboard-menu">
        <h3><i class="fa fa-cogs icon"></i> <?php echo t('admin_menu'); ?></h3>
        <ul>
          <li><a href="crud_wisata.php"><i class="fa fa-map-marked-alt"></i> <?php echo t('manage_tourism'); ?></a></li>
          <li><a href="crud_informasi.php"><i class="fa fa-info-circle icon"></i> <?php echo t('manage_information'); ?></a></li>
          <li><a href="crud_komentar.php"><i class="fa fa-comments"></i> <?php echo t('manage_comments'); ?></a></li>
          <li><a href="crud_produk.php"><i class="fa fa-box icon"></i> <?php echo t('manage_products'); ?></a></li>        
          <li><a href="crud_pesan.php"><i class="fa fa-envelope"></i> <?php echo t('manage_messages'); ?></a></li>
          <li><a href="crud_galeri.php"><i class="fa fa-images"></i> <?php echo t('manage_gallery'); ?></a></li>
          <li><a href="crud_user.php"><i class="fa fa-users"></i> <?php echo t('manage_users'); ?></a></li>

        </ul>
      </div>
      <?php endif; ?>
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
