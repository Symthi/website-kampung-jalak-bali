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

// Ambil semua data galeri
$query_galeri = "SELECT * FROM galeri ORDER BY tanggal_upload DESC";
$result_galeri = mysqli_query($koneksi, $query_galeri);
$galeri_data = mysqli_fetch_all($result_galeri, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Galeri Lengkap | Kampung Jalak Bali</title>
  </head>
  <body>
    <header>
      <div>
        <div><h1>Kampung Jalak Bali</h1></div>
        <nav>
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#wisata">Wisata</a></li>
            <li><a href="galeri.php">Galeri</a></li>
            <?php if (isLoggedIn()): ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li>
              <a href="logout.php">Logout (<?php echo $_SESSION['nama']; ?>)</a>
            </li>
            <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </header>

    <section>
      <div>
        <div>
          <h2>Galeri Kampung Jalak Bali</h2>
          <p>Kumpulan momen indah dan kegiatan di Kampung Jalak Bali</p>
        </div>

        <div>
          <?php if (count($galeri_data) >
          0): ?>
          <div>
            <?php foreach ($galeri_data as $galeri): ?>
            <div>
              <img src="<?php echo $galeri['gambar'] ?: 'https://source.unsplash.com/random/600x400/?bali'; ?>" alt="<?php echo $galeri['judul']; ?>" width="400" height="300" />
              <h3><?php echo $galeri['judul']; ?></h3>
              <?php if (!empty($galeri['deskripsi'])): ?>
              <p><?php echo $galeri['deskripsi']; ?></p>
              <?php endif; ?>
              <small
                >Upload:
                <?php echo date('d M Y', strtotime($galeri['tanggal_upload'])); ?></small
              >

              <?php if (isAdmin()): ?>
              <div>
                <a href="hapus_galeri.php?id=<?php echo $galeri['id_galeri']; ?>" onclick="return confirm('Yakin hapus foto?')">Hapus</a>
              </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div>
            <p>Belum ada foto di galeri.</p>
          </div>
          <?php endif; ?>
        </div>

        <div>
          <a href="index.php">Kembali ke Home</a>
        </div>
      </div>
    </section>

    <footer>
      <div>
        <p>&copy; 2025 Kampung Jalak Bali</p>
      </div>
    </footer>
  </body>
</html>
<?php mysqli_close($koneksi); ?>
