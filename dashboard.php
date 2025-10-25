<?php
session_start();
include 'koneksi.php';

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
    <title>Dashboard | Kampung Jalak Bali</title>
  </head>
  <body>
    <header>
      <div>
        <div><h1>Kampung Jalak Bali</h1></div>
        <nav>
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#wisata">Wisata</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li>
              <a href="logout.php">Logout (<?php echo $user_nama; ?>)</a>
            </li>
          </ul>
        </nav>
      </div>
    </header>

    <section>
      <div>
        <h2>
          Dashboard
          <?php echo isAdmin() ? 'Admin' : 'User'; ?>
        </h2>

        <div>
          <h3>
            Selamat datang,
            <?php echo $user_nama; ?>!
          </h3>
          <p>
            Anda login sebagai:
            <?php echo $user_role; ?>
          </p>
        </div>

        <!-- Statistik -->
        <div>
          <h3>Statistik</h3>
          <div>
            <div>
              <h4>Total Wisata</h4>
              <p><?php echo $total_wisata; ?></p>
            </div>
            <div>
              <h4>Total Komentar</h4>
              <p><?php echo $total_komentar; ?></p>
            </div>
            <div>
              <h4>Total Pesan</h4>
              <p><?php echo $total_pesan; ?></p>
            </div>
            <?php if (isAdmin()): ?>
            <div>
              <h4>Total User</h4>
              <p><?php echo $total_user; ?></p>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Menu Admin -->
        <?php if (isAdmin()): ?>
        <div>
          <h3>Menu Admin</h3>
          <ul>
            <li><a href="crud_wisata.php">Kelola Wisata</a></li>
            <li><a href="crud_komentar.php">Kelola Komentar</a></li>
            <li><a href="crud_pesan.php">Kelola Pesan</a></li>
            <li><a href="crud_galeri.php">Kelola Galeri</a></li>
            <li><a href="crud_user.php">Kelola User</a></li>
          </ul>
        </div>
        <?php endif; ?>

        <!-- Menu User -->
        <div>
          <h3>Menu</h3>
          <ul>
            <li><a href="detail_wisata.php?id=1">Lihat Konservasi Jalak Bali</a></li>
            <li><a href="detail_wisata.php?id=2">Lihat Budaya Lokal</a></li>
            <li><a href="detail_wisata.php?id=3">Lihat Ekowisata</a></li>
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
