<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// Set default language if not set
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'id';
}

// ensure $base is available (site root) when header is included from pages
if (!isset($base)) {
  $base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
}

// Tangani pergantian bahasa jika parameter lang ada
if (isset($_GET['lang'])) {
  $_SESSION['language'] = ($_GET['lang'] === 'en') ? 'en' : 'id';
  // Refresh language cache
  if (function_exists('load_language_strings')) {
      global $lang, $koneksi;
      $lang[$_SESSION['language']] = load_language_strings($_SESSION['language']);
  }
  // Redirect ke halaman sebelumnya tanpa parameter lang agar URL bersih
  $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : strtok($_SERVER['REQUEST_URI'], '?');
  header('Location: ' . $redirect);
  exit();
}

// Sertakan file bahasa sekali saja
include_once __DIR__ . '/../config/language.php';

// Fungsi untuk get navbar text dengan fallback
function get_navbar_text($key) {
    // Coba ambil dari pengaturan database
    $setting = get_setting($key, '');
    if (!empty($setting)) {
        return $setting;
    }
    
    // Fallback ke terjemahan bahasa
    return t($key);
}
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.0.0/css/flag-icons.min.css">
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
        <img src="<?php echo $base . '/' . get_setting('navbar_logo', 'uploads/Rancangan Logo.png'); ?>" 
             alt="Logo <?php echo get_setting('site_title', 'Kampoeng Jalak Bali'); ?>" width="50px" />
        <h1><?php echo get_setting('navbar_site_name', 'KJB'); ?></h1>
      </a>
    </div>
    <?php
      // aktifkan menu berdasarkan halaman
      $currentPath = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
      $isHome = ($currentPath === '' || $currentPath === 'index.php');
      $isInformasi = ($currentPath === 'informasi.php');
      $isProduk = ($currentPath === 'produk.php');
    ?>
    <nav class="navbar">
      <ul>
        <li><a href="<?php echo $base; ?>/index.php#tentang" data-section="#tentang"><i class="fa fa-info-circle icon"></i> <?php echo t('about'); ?></a></li>
        <li><a href="<?php echo $base; ?>/index.php#mitra" data-section="#mitra"><i class="fa fa-handshake icon"></i> <?php echo t('partners'); ?></a></li>
        <li><a href="<?php echo $base; ?>/index.php#wisata" data-section="#wisata"><i class="fa fa-map-marked-alt icon"></i> <?php echo t('tourism'); ?></a></li>
        <li><a href="<?php echo $base; ?>/index.php#galeri" data-section="#galeri"><i class="fa fa-image icon"></i> <?php echo t('gallery'); ?></a></li>
        <li><a href="<?php echo $base; ?>/informasi.php" class="<?php echo $isInformasi ? 'active' : ''; ?>"><i class="fa fa-info-circle icon"></i> <?php echo t('information'); ?></a></li>
        <li><a href="<?php echo $base; ?>/produk.php" class="<?php echo $isProduk ? 'active' : ''; ?>"><i class="fa fa-box icon"></i> <?php echo t('products'); ?></a></li>
        <li><a href="<?php echo $base; ?>/index.php#kontak" data-section="#kontak"><i class="fa fa-envelope icon"></i> <?php echo t('contact'); ?></a></li>
      </ul>
    </nav>
    <!-- Language selector (flags) -->
    <div class="lang-switcher" id="langSwitcher">
      <button class="lang-btn" id="langBtn" aria-haspopup="true" aria-expanded="false" title="Ganti Bahasa">
        <span class="lang-flag" id="currentFlag">
            <span class="fi fi-<?php echo (($_SESSION['language'] ?? 'id') === 'en') ? 'gb' : 'id'; ?>"></span>
        </span>
        <i class="fa fa-caret-down" aria-hidden="true"></i>
      </button>
      <ul class="lang-menu" id="langMenu" role="menu" aria-labelledby="langBtn">
        <li role="none"><a role="menuitem" href="#" data-lang="id">
            <span class="fi fi-id"></span> Indonesia
        </a></li>
        <li role="none"><a role="menuitem" href="#" data-lang="en">
            <span class="fi fi-gb"></span> English
        </a></li>
      </ul>
    </div>

    <script>
      (function(){
        const btn = document.getElementById('langBtn');
        const menu = document.getElementById('langMenu');
        const currentFlag = document.getElementById('currentFlag');
        const active = ("<?php echo $_SESSION['language'] ?? 'id'; ?>").toLowerCase();

        // Close menu on outside click
        document.addEventListener('click', function(e){
          if (!btn.contains(e.target) && !menu.contains(e.target)) {
            menu.style.display = 'none';
            btn.setAttribute('aria-expanded', 'false');
          }
        });

        btn.addEventListener('click', function(e){
          e.preventDefault();
          const showing = menu.style.display === 'block';
          menu.style.display = showing ? 'none' : 'block';
          btn.setAttribute('aria-expanded', String(!showing));
        });

        // Handle selection
        menu.querySelectorAll('a[data-lang]').forEach(function(a){
          a.addEventListener('click', function(ev){
            ev.preventDefault();
            const lang = a.getAttribute('data-lang');
            // Update bendera saat ganti bahasa
            currentFlag.innerHTML = '<span class="fi fi-' + (lang === 'en' ? 'gb' : 'id') + '"></span>';
            const url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            // navigate to same page with lang param; header.php will set session and redirect cleanly
            window.location.href = url.toString();
          });
        });
      })();
    </script>

</header>