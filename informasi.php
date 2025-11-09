<?php
session_start();
include 'koneksi.php';
include 'language.php';

// Security
define('ALLOWED', true);

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Set page info
$pageTitle = t('information_title') . ' | Kampoeng Jalak Bali';
$currentPage = 'informasi';

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
    <title><?php echo t('register_title'); ?> | Kampoeng Jalak Bali</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head>
  <body>
<?php include 'header.php'; ?>

    <section class="content-section">
      <div class="container">
        <div class="page-header text-center">
          <h2><i class="fas fa-info-circle"></i> <?php echo t('information_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('information_subtitle'); ?></p>
        </div>

        <div class="info-grid">
          <?php if (count($informasi_data) > 0): ?>
            <?php foreach ($informasi_data as $informasi): ?>
            <div class="info-card">
              <div class="info-image">
                <?php if ($informasi['gambar']): ?>
                <img src="<?php echo $informasi['gambar']; ?>" 
                     alt="<?php echo $informasi['judul']; ?>"
                     onerror="this.src='https://source.unsplash.com/random/800x400/?article,news'">
                <?php endif; ?>
              </div>
              
              <div class="info-content">
                <div class="info-category">
                  <i class="fas fa-tag"></i> <?php echo t($informasi['kategori']); ?>
                </div>
                <h3><?php echo $informasi['judul']; ?></h3>
                <div class="info-date">
                  <i class="fas fa-calendar-alt"></i> <?php echo date('d F Y', strtotime($informasi['tanggal_dibuat'])); ?>
                </div>
                <div class="info-text">
                  <?php echo nl2br($informasi['isi']); ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
            
            <?php $total_pages = (int)ceil($total_informasi / $per_page); if ($total_pages > 1): ?>
            <div class="pagination">
              <?php for ($p=1; $p<=$total_pages; $p++): ?>
                <?php if ($p == $page): ?>
                  <span class="active"><?php echo $p; ?></span>
                <?php else: ?>
                  <a href="?page_info=<?php echo $p; ?>"><?php echo $p; ?></a>
                <?php endif; ?>
              <?php endfor; ?>
            </div>
            <?php endif; ?>
            
          <?php else: ?>
            <div class="empty-state">
              <i class="fas fa-info-circle"></i>
              <p><?php echo t('no_information'); ?></p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

<?php include 'footer.php'; ?>
  </body>
</html>
<?php mysqli_close($koneksi); ?>