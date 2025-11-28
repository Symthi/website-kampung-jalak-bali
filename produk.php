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
$pageTitle = t('products_title') . ' | ' . get_setting('site_title', 'Kampoeng Jalak Bali');
$currentPage = 'produk';

// Ambil data produk dengan pagination
$per_page = 3;
$page = isset($_GET['page_produk']) ? max(1, (int)$_GET['page_produk']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM produk");
$total_produk = mysqli_fetch_assoc($total_q)['cnt'];
$query_produk = "SELECT * FROM produk ORDER BY tanggal_ditambahkan DESC LIMIT $per_page OFFSET $offset";
$result_produk = mysqli_query($koneksi, $query_produk);
$produk_data = mysqli_fetch_all($result_produk, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?php echo ($_SESSION['language'] ?? 'id'); ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/styles.css" />
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/pages.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head>
  <body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <section class="content-section bg-light">
      <div class="container">

        <!-- Page Header -->
        <div class="page-header text-center">
          <h2><i class="fas fa-gift"></i> <?php echo t('products_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('products_subtitle'); ?></p>
        </div>

        <!-- Merchandise Section -->
        <div class="merchandise-section">
          <?php if (count($produk_data) > 0): ?>
            
            <!-- Product Grid -->
            <div class="merchandise-grid">
              <?php foreach ($produk_data as $produk): ?>
                <div class="merchandise-card">
                  
                  <!-- Product Image -->
                  <div class="merchandise-image">
                    <img 
                      src="<?php echo $produk['gambar'] ? public_url($produk['gambar']) : 'https://source.unsplash.com/random/400x300/?souvenir'; ?>" 
                      alt="<?php echo htmlspecialchars($produk['nama']); ?>" 
                      class="merchandise-img"
                      loading="lazy">
                    
                    <!-- Hover Overlay -->
                    <div class="merchandise-overlay">
                      <div class="overlay-text">
                        <h4><?php echo htmlspecialchars($produk['nama']); ?></h4>
                      </div>
                    </div>
                  </div>

                  <!-- Product Content -->
                  <div class="merchandise-content">
                    <h3><?php echo htmlspecialchars($produk['nama']); ?></h3>
                    <p class="merchandise-desc"><?php echo htmlspecialchars($produk['deskripsi']); ?></p>
                    
                    <!-- Price & Stock Info -->
                    <div class="merchandise-price-stock">
                      <div class="price-item">
                        <span class="label"><i class="fas fa-tag"></i> <?php echo t('price'); ?></span>
                        <span class="value">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></span>
                      </div>
                      <div class="stock-item">
                        <span class="label"><i class="fas fa-boxes"></i> <?php echo t('stock'); ?></span>
                        <span class="value <?php echo $produk['stok'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                          <?php echo $produk['stok'] > 0 ? $produk['stok'] . ' pcs' : t('out_of_stock'); ?>
                        </span>
                      </div>
                    </div>
                    
                    <!-- Product Footer -->
                    <div class="merchandise-footer">
                      <p class="merchandise-info">
                        <i class="fas fa-location-dot"></i>
                        <?php echo t('merchandise_available'); ?>
                      </p>
                      <a href="<?php echo $base; ?>/index.php#kontak" class="btn btn-primary btn-block">
                        <i class="fas fa-map-location-dot"></i> <?php echo t('visit_us'); ?>
                      </a>
                    </div>
                  </div>
                  
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php $total_pages = (int)ceil($total_produk / $per_page); if ($total_pages > 1): ?>
            <div class="pagination">
              <?php if ($page > 1): ?>
                <a href="?page_produk=1" class="page-nav" title="First Page">
                  <i class="fas fa-chevron-left"></i>
                </a>
              <?php endif; ?>

              <?php 
              $start = max(1, $page - 2);
              $end = min($total_pages, $page + 2);
              
              for ($p = $start; $p <= $end; $p++): 
              ?>
                <?php if ($p == $page): ?>
                  <span class="active"><?php echo $p; ?></span>
                <?php else: ?>
                  <a href="?page_produk=<?php echo $p; ?>"><?php echo $p; ?></a>
                <?php endif; ?>
              <?php endfor; ?>

              <?php if ($page < $total_pages): ?>
                <a href="?page_produk=<?php echo $total_pages; ?>" class="page-nav" title="Last Page">
                  <i class="fas fa-chevron-right"></i>
                </a>
              <?php endif; ?>
            </div>
            <?php endif; ?>

          <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
              <i class="fas fa-box-open"></i>
              <p><?php echo t('no_data'); ?></p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Info Section -->
        <div class="merchandise-info-section">
          <h3><i class="fas fa-info-circle"></i> <?php echo t('merchandise_about_title'); ?></h3>
          <p><?php echo t('merchandise_about_text1'); ?></p>
          <p>
            <?php echo t('interested_visit_us'); ?> 
            <strong><a href="<?php echo $base; ?>/index.php#kontak"><?php echo t('visit_us'); ?></a></strong> 
            <?php echo t('for_more_info'); ?>.
          </p>
        </div>

      </div>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
</html>
<?php mysqli_close($koneksi); ?>