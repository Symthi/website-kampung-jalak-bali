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
$pageTitle = t('products_title') . ' | Kampoeng Jalak Bali';
$currentPage = 'produk';

// Ambil data produk dengan pagination
$per_page = 5;
$page = isset($_GET['page_produk']) ? max(1, (int)$_GET['page_produk']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM produk");
$total_produk = mysqli_fetch_assoc($total_q)['cnt'];
$query_produk = "SELECT * FROM produk ORDER BY tanggal_ditambahkan DESC LIMIT $per_page OFFSET $offset";
$result_produk = mysqli_query($koneksi, $query_produk);
$produk_data = mysqli_fetch_all($result_produk, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo t('products_title'); ?> | Kampoeng Jalak Bali</title>
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head>
  <body>
<?php include __DIR__ . '/includes/header.php'; ?>

    <section class="content-section">
      <div class="container">
        <div class="page-header text-center">
          <h2><i class="fas fa-shopping-bag"></i> <?php echo t('products_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('products_subtitle'); ?></p>
        </div>

        <div class="product-section">
          <?php if (count($produk_data) > 0): ?>
          <div class="produk-grid">
            <?php foreach ($produk_data as $produk): 
              // Format pesan WhatsApp
              $whatsapp_message = urlencode(
                "Halo, saya ingin memesan produk:\n" .
                "📦 *" . $produk['nama'] . "*\n" .
                "💵 Harga: Rp " . number_format($produk['harga'], 0, ',', '.') . "\n" .
                "\n" .
                "Bisa info lebih detail dan cara pemesanan?"
              );
              $whatsapp_url = "https://wa.me/6283862519604?text=" . $whatsapp_message;
            ?>
            <div class="produk-card">
              <div class="produk-image">
                <img src="<?php echo $produk['gambar'] ? public_url($produk['gambar']) : 'https://source.unsplash.com/random/300x200/?merchandise'; ?>" 
                     alt="<?php echo $produk['nama']; ?>">
              </div>
              <div class="produk-content">
                <h3><?php echo $produk['nama']; ?></h3>
                <p class="produk-description"><?php echo $produk['deskripsi']; ?></p>
                <div class="price">
                  <i class="fas fa-tag"></i> Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?>
                </div>
                <div class="stock">
                  <i class="fas fa-box"></i> <?php echo t('stock'); ?>: 
                  <span class="stock-number"><?php echo $produk['stok']; ?></span>
                </div>
                
                <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="whatsapp-btn">
                  <i class="fab fa-whatsapp"></i> <?php echo t('book_now'); ?>
                </a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          
          <?php $total_pages = (int)ceil($total_produk / $per_page); if ($total_pages > 1): ?>
          <div class="pagination">
            <?php for ($p=1; $p<=$total_pages; $p++): ?>
              <?php if ($p == $page): ?>
                <span class="active"><?php echo $p; ?></span>
              <?php else: ?>
                <a href="?page_produk=<?php echo $p; ?>"><?php echo $p; ?></a>
              <?php endif; ?>
            <?php endfor; ?>
          </div>
          <?php endif; ?>
          
          <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-shopping-basket"></i>
            <p><?php echo t('no_data'); ?></p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
</html>
<?php mysqli_close($koneksi); ?>