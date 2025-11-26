<?php
// footer.php - Konsisten untuk semua halaman
if (!isset($base)) {
  $base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
}

// Include language file
if (!function_exists('t')) {
  include_once __DIR__ . '/../config/language.php';
}
?>
<footer class="footer">
  <div class="container">
    <div class="footer-content">
      <div class="footer-section">
        <h3><?php echo t('site_title'); ?></h3>
        <p class="footer-description"><?php echo t('footer_description'); ?></p>
      </div>
      <div class="footer-section">
        <h3><?php echo t('quick_links'); ?></h3>
        <ul class="footer-links">
          <li><a class="footer-link" href="<?php echo $base; ?>/index.php"><?php echo t('home'); ?></a></li>
          <li><a class="footer-link" href="<?php echo $base; ?>/informasi.php"><?php echo t('information'); ?></a></li>
          <li><a class="footer-link" href="<?php echo $base; ?>/produk.php"><?php echo t('products'); ?></a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h3><?php echo t('contact'); ?></h3>
        <div class="footer-contact">
          <p><i class="fa fa-envelope icon"></i> 
            <a class="footer-link" href="https://mail.google.com/mail/?view=cm&fs=1&to=kampoengjalakbali@gmail.com">
              kampoengjalakbali@gmail.com
            </a>
          </p>
          <p>
            <i class="fa fa-phone icon"></i>
            <a class="footer-link" href="https://wa.me/6283862519604">
              083862519604
            </a>
          </p>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p class="copyright"><?php echo t('footer_copyright'); ?></p>
    </div>
  </div>
</footer>