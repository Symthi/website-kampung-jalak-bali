<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// ensure $base is available (site root) when header is included from pages
if (!isset($base)) {
  $base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
}

// Tangani pergantian bahasa jika parameter lang ada
if (isset($_GET['lang'])) {
  $_SESSION['language'] = ($_GET['lang'] === 'en') ? 'en' : 'id';
  // Redirect ke halaman sebelumnya tanpa parameter lang agar URL bersih
  $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : strtok($_SERVER['REQUEST_URI'], '?');
  header('Location: ' . $redirect);
  exit();
}

// Sertakan file bahasa sekali saja
include_once __DIR__ . '/../config/language.php';
?>
<header class="header">
  <div class="header-container container">
    <!--bars-->
    <input type="checkbox" id="menu-toggler" class="menu-toggle">
    <label for="menu-toggler" class="hamburger">
      <i class="fa-solid fa-bars"></i>
    </label>
    <!--logo-->
    <div class="logo-title">
      <a href="<?php echo $base; ?>/index.php">
        <img src="<?php echo $base; ?>/uploads/Rancangan Logo.png" alt="Logo Kampoeng Jalak Bali" width="50px" />
        <h1>KJB</h1>
      </a>
    </div>
    <nav class="navbar">
      <ul>
  <li><a href="<?php echo $base; ?>/index.php#tentang"><i class="fa fa-info-circle icon"></i> <?php echo t('about'); ?></a></li>
  <li><a href="<?php echo $base; ?>/index.php#wisata"><i class="fa fa-map-marked-alt icon"></i> <?php echo t('tourism'); ?></a></li>
  <li><a href="<?php echo $base; ?>/index.php#galeri"><i class="fa fa-image icon"></i> <?php echo t('gallery'); ?></a></li>
  <li><a href="<?php echo $base; ?>/index.php#kontak"><i class="fa fa-envelope icon"></i> <?php echo t('contact'); ?></a></li>
  <li><a href="<?php echo $base; ?>/informasi.php"><i class="fa fa-info-circle icon"></i> <?php echo t('information'); ?></a></li>
  <li><a href="<?php echo $base; ?>/produk.php"><i class="fa fa-box icon"></i> <?php echo t('products'); ?></a></li>
        <?php
        // Determine logged-in state: prefer site function if exists, otherwise use session fallback
        $loggedIn = false;
        if (function_exists('isLoggedIn')) {
          try {
            $loggedIn = isLoggedIn();
          } catch (Throwable $e) {
            $loggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
          }
        } else {
          $loggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
        }

        if ($loggedIn): ?>
              <li><a href="<?php echo $base; ?>/dashboard/index.php"><i class="fa fa-tachometer-alt icon"></i> <?php echo t('dashboard'); ?></a></li>
              <li><a href="<?php echo $base; ?>/auth/logout.php"><i class="fa fa-sign-out-alt icon"></i> <?php echo t('logout'); ?></a></li>
            <?php else: ?>
              <li><a href="<?php echo $base; ?>/auth/login.php"><i class="fa fa-sign-in-alt icon"></i> <?php echo t('login'); ?></a></li>
            <?php endif; ?>
      </ul>
    </nav>
    <!-- Language selector (flags) -->
    <div class="lang-switcher" id="langSwitcher">
      <button class="lang-btn" id="langBtn" aria-haspopup="true" aria-expanded="false" title="Ganti Bahasa">
        <span class="lang-flag" id="currentFlag"><?php echo (($_SESSION['language'] ?? 'id') === 'en') ? '🇬🇧' : '🇮🇩'; ?></span>
        <span class="lang-code" id="currentLangCode"><?php echo strtoupper($_SESSION['language'] ?? 'id'); ?></span>
        <i class="fa fa-caret-down" aria-hidden="true"></i>
      </button>
      <ul class="lang-menu" id="langMenu" role="menu" aria-labelledby="langBtn">
        <li role="none"><a role="menuitem" href="#" data-lang="id">🇮🇩 Indonesia</a></li>
        <li role="none"><a role="menuitem" href="#" data-lang="en">🇬🇧 English</a></li>
      </ul>
    </div>
    <script src='../assets/js/script.js'></script>
</header>
