<?php
session_start();
include __DIR__ . '/config/koneksi.php';
include __DIR__ . '/config/language.php';

// compute base URL (site root)
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

function public_url($path) {
    global $base;
    if (empty($path)) return '';
    if (preg_match('#^https?://#i', $path) || strpos($path, '/') === 0) return $path;
    return $base . '/' . ltrim($path, '/');
}

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
$pageTitle = t('information_title') . ' | ' . get_setting('site_title', 'Kampoeng Jalak Bali');
$currentPage = 'informasi';

// Ambil data informasi dengan pagination
$per_page = 1;
$page = isset($_GET['page_info']) ? max(1, (int)$_GET['page_info']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM informasi");
$total_informasi = mysqli_fetch_assoc($total_q)['cnt'];
$query = "SELECT * FROM informasi ORDER BY tanggal_dibuat DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$informasi_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?php echo ($_SESSION['language'] ?? 'id'); ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/pages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head>
  <body>
<?php include __DIR__ . '/includes/header.php'; ?>

    <section class="content-section">
      <div class="container">
        <div class="page-header text-center">
          <h2><i class="fas fa-info-circle"></i> <?php echo t('information_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('information_subtitle'); ?></p>
        </div>

        <!-- Ganti bagian info-grid dengan ini -->
        <div class="info-grid-new">
          <?php if (count($informasi_data) > 0): ?>
            <?php foreach ($informasi_data as $informasi): ?>
            <div class="info-wrap animate pop">
              <div class="info-overlay">
                <div class="info-overlay-content animate slide-left delay-2">
                  <div class="info-category-new animate slide-left delay-3">
                    <i class="fas fa-tag"></i> <?php echo t($informasi['kategori']); ?>
                  </div>
                  <h1 class="info-title-new animate slide-left delay-4"><?php echo $informasi['judul']; ?></h1>
                  <div class="info-date-new animate slide-left delay-5">
                    <i class="fas fa-calendar-alt"></i> <?php echo date('d F Y', strtotime($informasi['tanggal_dibuat'])); ?>
                  </div>
                </div>
                <div class="info-image-content animate slide delay-5" 
                    style="background-image: url('<?php echo $informasi['gambar'] ? public_url($informasi['gambar']) : 'https://source.unsplash.com/random/800x600/?article,news'; ?>')">
                </div>
                <div class="info-dots animate">
                  <div class="info-dot animate slide-up delay-1"></div>
                  <div class="info-dot animate slide-up delay-2"></div>
                  <div class="info-dot animate slide-up delay-3"></div>
                </div>
              </div>
              <div class="info-text">
                <div class="info-content-new">
                  <?php echo nl2br($informasi['isi']); ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
            
            <?php $total_pages = (int)ceil($total_informasi / $per_page); if ($total_pages > 1): ?>
            <div class="pagination-new">
              <?php if ($page > 1): ?>
                <a href="?page_info=<?php echo ($page - 1); ?>" class="page-nav" title="Previous">
                  <i class="fas fa-chevron-left"></i> Prev
                </a>
              <?php endif; ?>

              <div class="page-numbers">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                  <?php if ($p == $page): ?>
                    <span class="active"><?php echo $p; ?></span>
                  <?php else: ?>
                    <a href="?page_info=<?php echo $p; ?>"><?php echo $p; ?></a>
                  <?php endif; ?>
                <?php endfor; ?>
              </div>

              <?php if ($page < $total_pages): ?>
                <a href="?page_info=<?php echo ($page + 1); ?>" class="page-nav" title="Next">
                  Next <i class="fas fa-chevron-right"></i>
                </a>
              <?php endif; ?>
            </div>
            <?php endif; ?>
            
          <?php else: ?>
            <div class="empty-state-new">
              <i class="fas fa-info-circle"></i>
              <p><?php echo t('no_information'); ?></p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
</html>
<?php mysqli_close($koneksi); ?>