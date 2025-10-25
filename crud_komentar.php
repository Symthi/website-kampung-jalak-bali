<?php
session_start();
include 'koneksi.php';

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

// Ambil data komentar dengan join ke user dan wisata
$query = "SELECT k.*, u.nama as nama_user, u.email, w.judul as judul_wisata 
          FROM komentar k 
          JOIN user u ON k.id_user = u.id_user 
          JOIN wisata w ON k.id_wisata = w.id_wisata 
          ORDER BY k.tanggal DESC";
$result = mysqli_query($koneksi, $query);
$komentar_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kelola Komentar | Kampung Jalak Bali</title>
    <style>
      table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
      }
      th,
      td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
      }
      th {
        background-color: #f2f2f2;
      }
      .btn {
        padding: 8px 16px;
        margin: 5px;
        text-decoration: none;
        border-radius: 4px;
      }
      .btn-danger {
        background: #dc3545;
        color: white;
      }
      .btn-danger:hover {
        background: #c82333;
      }
      .alert {
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
      }
      .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
      }
      .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
      }
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
            <li><a href="crud_komentar.php">Kelola Komentar</a></li>
            <li><a href="logout.php">Logout</a></li>
          </ul>
        </nav>
      </div>
    </header>

    <section>
      <div>
        <h2>Kelola Komentar Pengunjung</h2>

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
        <div>
          <h3>Statistik Komentar</h3>
          <p>
            Total Komentar: <strong><?php echo count($komentar_data); ?></strong>
          </p>
        </div>

        <!-- Daftar Komentar -->
        <div>
          <h3>Daftar Semua Komentar</h3>

          <?php if (empty($komentar_data)): ?>
          <p>Belum ada komentar.</p>
          <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>User</th>
                <th>Wisata</th>
                <th>Komentar</th>
                <th>Tanggal</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($komentar_data as $index =>
              $komentar): ?>
              <tr>
                <td><?php echo $index + 1; ?></td>
                <td>
                  <strong><?php echo $komentar['nama_user']; ?></strong><br />
                  <small><?php echo $komentar['email']; ?></small>
                </td>
                <td><?php echo $komentar['judul_wisata']; ?></td>
                <td><?php echo $komentar['isi']; ?></td>
                <td><?php echo date('d M Y H:i', strtotime($komentar['tanggal'])); ?></td>
                <td>
                  <a href="crud_komentar.php?hapus=<?php echo $komentar['id_komentar']; ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus komentar ini?')"> Hapus </a>
                  <a href="detail_wisata.php?id=<?php echo $komentar['id_wisata']; ?>" class="btn"> Lihat Wisata </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <footer>
      <div>
        <p>&copy; 2025 Kampung Jalak Bali | Kelola Komentar</p>
      </div>
    </footer>
  </body>
</html>
<?php mysqli_close($koneksi); ?>
