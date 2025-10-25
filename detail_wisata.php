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

$wisata_id = $_GET['id'] ?? 1;

// Ambil data wisata
$query_wisata = "SELECT * FROM wisata WHERE id_wisata = ?";
$stmt = mysqli_prepare($koneksi, $query_wisata);
mysqli_stmt_bind_param($stmt, "i", $wisata_id);
mysqli_stmt_execute($stmt);
$result_wisata = mysqli_stmt_get_result($stmt);
$wisata = mysqli_fetch_assoc($result_wisata);

if (!$wisata) {
    header("Location: index.php");
    exit();
}

// Ambil komentar untuk wisata ini
$query_komentar = "SELECT k.*, u.nama FROM komentar k 
                   JOIN user u ON k.id_user = u.id_user 
                   WHERE k.id_wisata = ? 
                   ORDER BY k.tanggal DESC";
$stmt_komentar = mysqli_prepare($koneksi, $query_komentar);
mysqli_stmt_bind_param($stmt_komentar, "i", $wisata_id);
mysqli_stmt_execute($stmt_komentar);
$result_komentar = mysqli_stmt_get_result($stmt_komentar);
$komentar_data = mysqli_fetch_all($result_komentar, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $wisata['judul']; ?> | Kampung Jalak Bali</title>
  </head>
  <body>
    <header>
      <div>
        <div><h1>Kampung Jalak Bali</h1></div>
        <nav>
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#wisata">Wisata</a></li>
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
          <img src="<?php echo $wisata['gambar'] ?: 'https://source.unsplash.com/random/900x400/?bali'; ?>" alt="<?php echo $wisata['judul']; ?>" />
          <h2><?php echo $wisata['judul']; ?></h2>
          <p>
            <strong>Durasi:</strong>
            <?php echo $wisata['durasi']; ?>
          </p>
          <p><?php echo $wisata['deskripsi']; ?></p>
        </div>

        <div>
          <h3>Komentar Pengunjung</h3>

          <!-- Form Komentar -->
          <div>
            <?php if (isLoggedIn()): ?>
            <form method="POST" action="proses_komentar.php">
              <input type="hidden" name="wisata_id" value="<?php echo $wisata_id; ?>" />
              <div>
                <label for="komentar"
                  >Tulis Komentar (Login sebagai
                  <?php echo $_SESSION['nama']; ?>)</label
                >
                <textarea id="komentar" name="komentar" placeholder="Bagikan pengalaman Anda..." required></textarea>
              </div>
              <button type="submit">Kirim Komentar</button>
            </form>
            <?php else: ?>
            <p>Silakan <a href="login.php">login</a> untuk menulis komentar.</p>
            <?php endif; ?>
          </div>

          <div>
            <?php foreach ($komentar_data as $komentar): ?>
            <div>
              <div>
                <span
                  ><strong><?php echo $komentar['nama']; ?></strong></span
                >
                <span><?php echo date('d M Y H:i', strtotime($komentar['tanggal'])); ?></span>
              </div>
              <p><?php echo $komentar['isi']; ?></p>
              <?php if (isAdmin() || (isLoggedIn() && $_SESSION['user_id'] == $komentar['id_user'])): ?>
              <div>
                <a href="hapus_komentar.php?id=<?php echo $komentar['id_komentar']; ?>&wisata_id=<?php echo $wisata_id; ?>" onclick="return confirm('Yakin hapus komentar?')">Hapus</a>
              </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
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
