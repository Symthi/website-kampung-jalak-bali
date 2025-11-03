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
  <title><?php echo t('products_title'); ?> | Kampung Jalak Bali</title>
    <style>
      .produk-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin: 30px 0;
      }
      .produk-card {
        border: 1px solid #ddd;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        background: white;
        transition: transform 0.3s ease;
      }
      .produk-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      }
      .produk-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 15px;
      }
      .produk-card h3 {
        color: #1a6b3b;
        margin-bottom: 10px;
      }
      .produk-card .price {
        font-size: 1.3em;
        font-weight: bold;
        color: #1a6b3b;
        margin: 10px 0;
      }
      .produk-card .stock {
        color: #666;
        margin-bottom: 15px;
      }
      .whatsapp-btn {
        background: #25D366;
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        width: 100%;
        transition: background 0.3s ease;
      }
      .whatsapp-btn:hover {
        background: #128C7E;
      }
      .whatsapp-btn i {
        margin-right: 8px;
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
      <div>
        <div style="text-align: center; padding: 30px 0;">
          <h2><?php echo t('products_title'); ?></h2>
          <p><?php echo t('products_subtitle'); ?></p>
        </div>

        <div>
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
              <img src="<?php echo $produk['gambar'] ?: 'https://source.unsplash.com/random/300x200/?merchandise'; ?>" 
                   alt="<?php echo $produk['nama']; ?>">
              <h3><?php echo $produk['nama']; ?></h3>
              <p><?php echo $produk['deskripsi']; ?></p>
              <div class="price">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
              <div class="stock"><?php echo t('stock'); ?>: <?php echo $produk['stok']; ?></div>
              
              <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="whatsapp-btn">
                📱 <?php echo t('book_now'); ?>
              </a>
            </div>
            <?php endforeach; ?>
          </div>
          <?php $total_pages = (int)ceil($total_produk / $per_page); if ($total_pages > 1): ?>
          <div style="display:flex; gap:8px; justify-content:center; margin-top:15px;">
            <?php for ($p=1; $p<=$total_pages; $p++): ?>
              <?php if ($p == $page): ?>
                <span style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; background:#007bff; color:#fff;">&nbsp;<?php echo $p; ?>&nbsp;</span>
              <?php else: ?>
                <a href="?page_produk=<?php echo $p; ?>" style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; text-decoration:none; color:#333;">&nbsp;<?php echo $p; ?>&nbsp;</a>
              <?php endif; ?>
            <?php endfor; ?>
          </div>
          <?php endif; ?>
          <?php else: ?>
          <div style="text-align: center; padding: 40px;">
            <p><?php echo t('no_data'); ?></p>
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