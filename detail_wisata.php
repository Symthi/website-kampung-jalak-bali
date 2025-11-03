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

// Ambil komentar untuk wisata ini dengan pagination
$per_page_k = 5;
$page_k = isset($_GET['page_komen']) ? max(1, (int)$_GET['page_komen']) : 1;
$offset_k = ($page_k - 1) * $per_page_k;

// Hitung total komentar per wisata
$qcnt = "SELECT COUNT(*) as cnt FROM komentar WHERE id_wisata = ?";
$stcnt = mysqli_prepare($koneksi, $qcnt);
mysqli_stmt_bind_param($stcnt, "i", $wisata_id);
mysqli_stmt_execute($stcnt);
$rescnt = mysqli_stmt_get_result($stcnt);
$total_komen = mysqli_fetch_assoc($rescnt)['cnt'];

$query_komentar = "SELECT k.*, u.nama FROM komentar k 
                   JOIN user u ON k.id_user = u.id_user 
                   WHERE k.id_wisata = ? 
                   ORDER BY k.tanggal DESC
                   LIMIT $per_page_k OFFSET $offset_k";
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
            <strong><?php echo t('duration'); ?>:</strong>
            <?php echo $wisata['durasi']; ?>
          </p>
          <p><?php echo $wisata['deskripsi']; ?></p>
        </div>

        <div>
          <h3><?php echo t('comments'); ?></h3>

          <!-- Form Komentar -->
          <div>
            <?php if (isLoggedIn()): ?>
            <form method="POST" action="proses_komentar.php">
              <input type="hidden" name="wisata_id" value="<?php echo $wisata_id; ?>" />
              <div>
                <label for="komentar"><?php echo t('write_comment'); ?> (<?php echo t('login') ? '' : ''; ?><?php echo $_SESSION['nama']; ?>)</label>
                <textarea id="komentar" name="komentar" placeholder="<?php echo t('write_comment'); ?>" required></textarea>
              </div>
              <button type="submit"><?php echo t('post_comment'); ?></button>
            </form>
            <?php else: ?>
            <p><?php echo t('login_to_comment'); ?> <a href="login.php"><?php echo t('login'); ?></a></p>
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
                <a href="hapus_komentar.php?id=<?php echo $komentar['id_komentar']; ?>&wisata_id=<?php echo $wisata_id; ?>" onclick="return confirm('<?php echo addslashes(t('confirm_delete')); ?>')"><?php echo t('delete'); ?></a>
              </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php $total_pages_k = (int)ceil($total_komen / $per_page_k); if ($total_pages_k > 1): ?>
          <div style="display:flex; gap:8px; justify-content:center; margin-top:15px;">
            <?php for ($p=1; $p<=$total_pages_k; $p++): ?>
              <?php if ($p == $page_k): ?>
                <span style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; background:#007bff; color:#fff;">&nbsp;<?php echo $p; ?>&nbsp;</span>
              <?php else: ?>
                <a href="?id=<?php echo (int)$wisata_id; ?>&page_komen=<?php echo $p; ?>#comments" style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; text-decoration:none; color:#333;">&nbsp;<?php echo $p; ?>&nbsp;</a>
              <?php endif; ?>
            <?php endfor; ?>
          </div>
          <?php endif; ?>
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
