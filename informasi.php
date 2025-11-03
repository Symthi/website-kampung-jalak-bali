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

// Ambil data informasi dengan pagination
$per_page = 5;
$page = isset($_GET['page_info']) ? max(1, (int)$_GET['page_info']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM informasi");
$total_informasi = mysqli_fetch_assoc($total_q)['cnt'];
$query = "SELECT * FROM informasi ORDER BY tanggal_dibuat DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$informasi_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo t('information_title'); ?> | Kampung Jalak Bali</title>
    <style>
      .info-card {
        border: 1px solid #ddd;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        background: white;
      }
      .info-card img {
        max-width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 15px;
      }
      .info-category {
        background: #1a6b3b;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        display: inline-block;
        margin-bottom: 10px;
      }
      .info-date {
        color: #666;
        font-size: 0.9em;
        margin-bottom: 10px;
      }
    </style>
  </head>
  <body>
    <!-- Language Switcher -->
    <div style="text-align: right; padding: 10px; background: #f8f9fa;">
      <a href="?lang=id" style="margin-right: 10px;">🇮🇩 Indonesia</a>
      <a href="?lang=en">🇬🇧 English</a>
    </div>

    <header>
      <div>
        <div><h1>Kampung Jalak Bali</h1></div>
        <nav>
          <ul>
            <li><a href="index.php"><?php echo t('home'); ?></a></li>
            <li><a href="index.php#wisata"><?php echo t('tourism'); ?></a></li>
            <li><a href="informasi.php"><?php echo t('information'); ?></a></li>
            <li><a href="galeri.php"><?php echo t('gallery'); ?></a></li>
            <li><a href="produk.php"><?php echo t('products'); ?></a></li>
            <?php if (isLoggedIn()): ?>
            <li><a href="dashboard.php"><?php echo t('dashboard'); ?></a></li>
            <li>
              <a href="logout.php"><?php echo t('logout'); ?> (<?php echo $_SESSION['nama']; ?>)</a>
            </li>
            <?php else: ?>
            <li><a href="login.php"><?php echo t('login'); ?></a></li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </header>

    <section>
      <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 40px;">
          <h2><?php echo t('information_title'); ?></h2>
          <p><?php echo t('information_subtitle'); ?></p>
        </div>

        <div>
          <?php if (count($informasi_data) > 0): ?>
            <?php foreach ($informasi_data as $informasi): ?>
            <div class="info-card">
              <?php if ($informasi['gambar']): ?>
              <img src="<?php echo $informasi['gambar']; ?>" 
                   alt="<?php echo $informasi['judul']; ?>"
                   onerror="this.src='https://source.unsplash.com/random/800x400/?article,news'">
              <?php endif; ?>
              
              <div class="info-category"><?php echo t($informasi['kategori']); ?></div>
              <h3 style="margin-top: 0;"><?php echo $informasi['judul']; ?></h3>
              <div class="info-date">
                <?php echo date('d F Y', strtotime($informasi['tanggal_dibuat'])); ?>
              </div>
              <div style="line-height: 1.6;">
                <?php echo nl2br($informasi['isi']); ?>
              </div>
            </div>
            <?php endforeach; ?>
            <?php $total_pages = (int)ceil($total_informasi / $per_page); if ($total_pages > 1): ?>
            <div style="display:flex; gap:8px; justify-content:center; margin:20px 0;">
              <?php for ($p=1; $p<=$total_pages; $p++): ?>
                <?php if ($p == $page): ?>
                  <span style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; background:#007bff; color:#fff;">&nbsp;<?php echo $p; ?>&nbsp;</span>
                <?php else: ?>
                  <a href="?page_info=<?php echo $p; ?>" style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; text-decoration:none; color:#333;">&nbsp;<?php echo $p; ?>&nbsp;</a>
                <?php endif; ?>
              <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div style="text-align: center; padding: 60px; background: #f8f9fa; border-radius: 12px;">
              <p style="font-size: 1.2em; color: #666;"><?php echo t('no_information'); ?></p>
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