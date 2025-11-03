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

$total_wisata = mysqli_fetch_assoc(mysqli_query($koneksi, $query_wisata))['total'];
$total_komentar = mysqli_fetch_assoc(mysqli_query($koneksi, $query_komentar))['total'];
$total_pesan = mysqli_fetch_assoc(mysqli_query($koneksi, $query_pesan))['total'];
$total_user = mysqli_fetch_assoc(mysqli_query($koneksi, $query_user))['total'];

$user_nama = $_SESSION['nama'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo t('dashboard'); ?> | Kampung Jalak Bali</title>
  </head>
  <body>
    <header>
      <div>
        <div><h1>Kampung Jalak Bali</h1></div>
        <nav>
          <ul>
            <li><a href="index.php"><?php echo t('home'); ?></a></li>
            <li><a href="index.php#wisata"><?php echo t('tourism'); ?></a></li>
            <li><a href="dashboard.php"><?php echo t('dashboard'); ?></a></li>
            <li>
              <a href="logout.php"><?php echo t('logout'); ?> (<?php echo $user_nama; ?>)</a>
            </li>
          </ul>
        </nav>
      </div>
    </header>

    <section>
      <div>
        <h2>
          <?php echo isAdmin() ? t('admin_dashboard') : t('user_dashboard'); ?>
        </h2>

        <div>
          <h3>
            <?php echo t('welcome'); ?>, <?php echo $user_nama; ?>!
          </h3>
          <p>
            <?php echo t('role'); ?>: <?php echo $user_role; ?>
          </p>
        </div>

        <!-- Statistik -->
        <div>
          <h3><?php echo t('statistics'); ?></h3>
          <div>
            <div>
              <h4><?php echo t('total_tourism'); ?></h4>
              <p><?php echo $total_wisata; ?></p>
            </div>
            <div>
              <h4><?php echo t('total_comments'); ?></h4>
              <p><?php echo $total_komentar; ?></p>
            </div>
            <div>
              <h4><?php echo t('total_messages'); ?></h4>
              <p><?php echo $total_pesan; ?></p>
            </div>
            <?php if (isAdmin()): ?>
            <div>
              <h4><?php echo t('total_users'); ?></h4>
              <p><?php echo $total_user; ?></p>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Menu Admin -->
        <?php if (isAdmin()): ?>
        <div>
          <h3><?php echo t('admin_menu'); ?></h3>
          <ul>
            <li><a href="crud_wisata.php"><?php echo t('manage_tourism'); ?></a></li>
            <li><a href="crud_komentar.php"><?php echo t('manage_comments'); ?></a></li>
            <li><a href="crud_pesan.php"><?php echo t('manage_messages'); ?></a></li>
            <li><a href="crud_galeri.php"><?php echo t('manage_gallery'); ?></a></li>
            <li><a href="crud_user.php"><?php echo t('manage_users'); ?></a></li>
            <li><a href="crud_informasi.php"><?php echo t('manage_information'); ?></a></li>
            <li><a href="crud_produk.php"><?php echo t('manage_products'); ?></a></li>
          </ul>
        </div>
        <?php endif; ?>

        <!-- Menu User -->
        <div>
          <h3><?php echo t('user_menu'); ?></h3>
          <ul>
            <li><a href="detail_wisata.php?id=1"><?php echo t('view_details'); ?> - Konservasi Jalak Bali</a></li>
            <li><a href="detail_wisata.php?id=2"><?php echo t('view_details'); ?> - Budaya Lokal</a></li>
            <li><a href="detail_wisata.php?id=3"><?php echo t('view_details'); ?> - Ekowisata</a></li>
          </ul>
        </div>
      </div>
    </section>

    <footer>
      <div>
        <p>
          &copy; 2025 Kampung Jalak Bali | Dashboard
          <?php echo isAdmin() ? 'Admin' : 'User'; ?>
        </p>
      </div>
    </footer>
  </body>
</html>
<?php mysqli_close($koneksi); ?>
