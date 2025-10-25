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

// Ambil data pesan
$query = "SELECT * FROM pesan ORDER BY tanggal DESC";
$result = mysqli_query($koneksi, $query);
$pesan_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Hitung statistik
$total_pesan = count($pesan_data);
$belum_dibaca = 0;
$sudah_dibalas = 0;

foreach ($pesan_data as $pesan) {
    if (!$pesan['dibaca']) $belum_dibaca++;
    if ($pesan['dibalas']) $sudah_dibalas++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesan | Kampung Jalak Bali</title>
</head>
<body>
    <header>
        <h1>Kampung Jalak Bali</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="crud_pesan.php">Kelola Pesan</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <section>
        <h2>Kelola Pesan Pengunjung</h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb;">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb;">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistik -->
        <div>
            <h3>Statistik Pesan</h3>
            <div>
                <div>
                    <h4>Total Pesan</h4>
                    <p><?php echo $total_pesan; ?></p>
                </div>
                <div>
                    <h4>Belum Dibaca</h4>
                    <p><?php echo $belum_dibaca; ?></p>
                </div>
                <div>
                    <h4>Sudah Dibalas</h4>
                    <p><?php echo $sudah_dibalas; ?></p>
                </div>
            </div>
        </div>

        <!-- Daftar Pesan -->
        <div>
            <h3>Daftar Pesan</h3>

            <?php if (empty($pesan_data)): ?>
                <p>Belum ada pesan.</p>
            <?php else: ?>
                <table border="1">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pengirim</th>
                            <th>Subjek</th>
                            <th>Pesan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pesan_data as $index => $pesan): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <strong><?php echo $pesan['nama']; ?></strong><br>
                                <small><?php echo $pesan['email']; ?></small>
                            </td>
                            <td><?php echo $pesan['subjek']; ?></td>
                            <td><?php echo substr($pesan['isi'], 0, 100) . '...'; ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($pesan['tanggal'])); ?></td>
                            <td>
                                <?php if (!$pesan['dibaca']): ?>
                                    <span style="background: #ffc107; color: black; padding: 4px 8px; border-radius: 12px;">Baru</span>
                                <?php else: ?>
                                    <span style="background: #6c757d; color: white; padding: 4px 8px; border-radius: 12px;">Sudah Dibaca</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="crud_pesan.php?baca=<?php echo $pesan['id_pesan']; ?>">Baca</a>
                                <a href="crud_pesan.php?hapus=<?php echo $pesan['id_pesan']; ?>" onclick="return confirm('Yakin hapus pesan ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 Kampung Jalak Bali | Kelola Pesan</p>
    </footer>
</body>
</html>
<?php mysqli_close($koneksi); ?>