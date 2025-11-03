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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo t('manage_comments'); ?> | Kampung Jalak Bali</title>
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
    <style>
      .pagination { display: flex; gap: 8px; margin-top: 15px; }
      .pagination a, .pagination span { padding: 6px 10px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; }
      .pagination .active { background: #007bff; color: #fff; border-color: #007bff; }
    </style>
  </head>
  <body>
    <header>
      <div>
        <div><h1>Kampung Jalak Bali</h1></div>
        <nav>
          <ul>
            <li><a href="index.php"><?php echo t('home'); ?></a></li>
            <li><a href="dashboard.php"><?php echo t('dashboard'); ?></a></li>
            <li><a href="crud_komentar.php"><?php echo t('manage_comments'); ?></a></li>
            <li><a href="logout.php"><?php echo t('logout'); ?></a></li>
          </ul>
        </nav>
      </div>
    </header>

    <section>
      <div>
  <h2><?php echo t('manage_comments'); ?></h2>

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
          <h3><?php echo t('comment_statistics'); ?></h3>
          <p>
            <?php echo t('total_comments'); ?>: <strong><?php echo $total_komentar_all; ?></strong>
          </p>
        </div>

        <!-- Daftar Komentar -->
        <div>
          <h3><?php echo t('comments'); ?></h3>

          <?php if (empty($komentar_data)): ?>
            <p><?php echo t('no_comments'); ?></p>
          <?php else: ?>
          <table>
            <thead>
              <tr>
                <th><?php echo t('no'); ?></th>
                <th><?php echo t('user'); ?></th>
                <th><?php echo t('tourism'); ?></th>
                <th><?php echo t('comments'); ?></th>
                <th><?php echo t('date'); ?></th>
                <th><?php echo t('actions'); ?></th>
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
                  <a href="crud_komentar.php?hapus=<?php echo $komentar['id_komentar']; ?>" class="btn btn-danger" onclick="return confirm('<?php echo addslashes(t('confirm_delete')); ?>')"> <?php echo t('delete'); ?> </a>
                  <a href="detail_wisata.php?id=<?php echo $komentar['id_wisata']; ?>" class="btn"> <?php echo t('view_tour'); ?> </a>
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

    <footer>
      <div>
        <p>&copy; 2025 Kampung Jalak Bali | <?php echo t('manage_comments'); ?></p>
      </div>
    </footer>
  </body>
</html>
<?php mysqli_close($koneksi); ?>
