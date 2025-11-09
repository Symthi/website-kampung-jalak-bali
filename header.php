<?php
// header.php - Konsisten untuk semua halaman
// Pastikan session aktif dan bahasa tersedia untuk seluruh halaman
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
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
include_once __DIR__ . '/language.php';
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
      <a href="index.php">
        <img src="uploads/Rancangan Logo.png" alt="Logo Kampoeng Jalak Bali" width="50px" />
        <h1>KJB</h1>
      </a>
    </div>
    <nav class="navbar">
      <ul>
        <li><a href="index.php#tentang"><i class="fa fa-info-circle icon"></i> <?php echo t('about'); ?></a></li>
        <li><a href="index.php#wisata"><i class="fa fa-map-marked-alt icon"></i> <?php echo t('tourism'); ?></a></li>
        <li><a href="index.php#galeri"><i class="fa fa-image icon"></i> <?php echo t('gallery'); ?></a></li>
        <li><a href="index.php#kontak"><i class="fa fa-envelope icon"></i> <?php echo t('contact'); ?></a></li>
        <li><a href="informasi.php"><i class="fa fa-info-circle icon"></i> <?php echo t('information'); ?></a></li>
        <li><a href="produk.php"><i class="fa fa-box icon"></i> <?php echo t('products'); ?></a></li>
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
              <li><a href="dashboard.php"><i class="fa fa-tachometer-alt icon"></i> <?php echo t('dashboard'); ?></a></li>
              <li><a href="logout.php"><i class="fa fa-sign-out-alt icon"></i> <?php echo t('logout'); ?></a></li>
            <?php else: ?>
              <li><a href="login.php"><i class="fa fa-sign-in-alt icon"></i> <?php echo t('login'); ?></a></li>
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

    <script>
      (function(){
        const btn = document.getElementById('langBtn');
        const menu = document.getElementById('langMenu');
        const currentFlag = document.getElementById('currentFlag');
        const currentLangCode = document.getElementById('currentLangCode');
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
            const url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            // navigate to same page with lang param; header.php will set session and redirect cleanly
            window.location.href = url.toString();
          });
        });
      })();
    </script>

</header>
