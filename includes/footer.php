<?php
// footer.php - Konsisten untuk semua halaman
// ensure $base is available when footer included
if (!isset($base)) {
  $base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
}
?>
<footer class="footer">
  <div class="container">
    <div class="footer-content">
      <div class="footer-section">
        <h3>Kampoeng Jalak Bali</h3>
        <p class="footer-description">Website resmi Kampoeng Jalak Bali untuk promosi wisata, produk, dan informasi desa.</p>
      </div>
      <div class="footer-section">
        <h3>Menu</h3>
        <ul class="footer-links">
          <li><a class="footer-link" href="<?php echo $base; ?>/index.php">Beranda</a></li>
          <li><a class="footer-link" href="<?php echo $base; ?>/informasi.php">Informasi</a></li>
          <li><a class="footer-link" href="<?php echo $base; ?>/galeri.php">Galeri</a></li>
          <li><a class="footer-link" href="<?php echo $base; ?>/produk.php">Produk</a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h3>Kontak</h3>
        <div class="footer-contact">
          <p><i class="fa fa-envelope icon"></i> kampoengjalakbali@gmail.com</p>
          <p><i class="fa fa-phone icon"></i> 083862519604</p>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p class="copyright">&copy; 2025 Kampoeng Jalak Bali. All rights reserved.</p>
    </div>
  </div>
</footer>
