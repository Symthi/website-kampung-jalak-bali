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

// Ambil semua data galeri dengan pagination
$per_page = 5;
$page = isset($_GET['page_galeri']) ? max(1, (int)$_GET['page_galeri']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM galeri");
$total_galeri = mysqli_fetch_assoc($total_q)['cnt'];
$query_galeri = "SELECT * FROM galeri ORDER BY tanggal_upload DESC LIMIT $per_page OFFSET $offset";
$result_galeri = mysqli_query($koneksi, $query_galeri);
$galeri_data = mysqli_fetch_all($result_galeri, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo t('gallery_title'); ?> | Kampung Jalak Bali</title>
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
          <h2><?php echo t('gallery_title'); ?></h2>
          <p><?php echo t('gallery_subtitle'); ?></p>
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
                <a href="hapus_galeri.php?id=<?php echo $galeri['id_galeri']; ?>" onclick="return confirm('<?php echo addslashes(t('confirm_delete')); ?>')"><?php echo t('delete'); ?></a>
              </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php $total_pages = (int)ceil($total_galeri / $per_page); if ($total_pages > 1): ?>
          <div style="display:flex; gap:8px; justify-content:center; margin-top:15px;">
            <?php for ($p=1; $p<=$total_pages; $p++): ?>
              <?php if ($p == $page): ?>
                <span style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; background:#007bff; color:#fff;">&nbsp;<?php echo $p; ?>&nbsp;</span>
              <?php else: ?>
                <a href="?page_galeri=<?php echo $p; ?>" style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; text-decoration:none; color:#333;">&nbsp;<?php echo $p; ?>&nbsp;</a>
              <?php endif; ?>
            <?php endfor; ?>
          </div>
          <?php endif; ?>
          <?php else: ?>
          <div>
            <p><?php echo t('no_gallery_images'); ?></p>
          </div>
          <?php endif; ?>
        </div>

        <div>
          <a href="index.php"><?php echo t('back_to_home'); ?></a>
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
