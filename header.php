<?php
// header.php - Konsisten untuk semua halaman
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
      <img src="uploads/Rancangan Logo.png" alt="Logo Kampoeng Jalak Bali" width="50px" />
      <h1>Kampoeng Jalak Bali</h1>
    </div>
    <nav class="navbar">
      <ul>
        <li><a href="index.php"><i class="fa fa-home icon"></i> Beranda</a></li>
        <li><a href="index.php#wisata"><i class="fa fa-map-marked-alt icon"></i> Wisata</a></li>
        <li><a href="informasi.php"><i class="fa fa-info-circle icon"></i> Informasi</a></li>
        <li><a href="produk.php"><i class="fa fa-box icon"></i> Produk</a></li>
        <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
          <li><a href="dashboard.php"><i class="fa fa-tachometer-alt icon"></i> Dashboard</a></li>
          <li><a href="logout.php"><i class="fa fa-sign-out-alt icon"></i> Logout</a></li>
        <?php else: ?>
          <li><a href="login.php"><i class="fa fa-sign-in-alt icon"></i> Login</a></li>
          <li><a href="register.php"><i class="fa fa-user-plus icon"></i> Register</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <!-- Language Toggle -->
    <div class="lang-toggle">
    <input type="checkbox" id="lang-switch" hidden>
    <label for="lang-switch" class="lang-label" title="Ganti Bahasa">
        <i class="fa-solid fa-globe"></i>
    </label>
    </div>

    <script>
    // Ambil bahasa aktif dari PHP
    const currentLang = "<?php echo $_GET['lang'] ?? ($_SESSION['language'] ?? 'id'); ?>";
    const langSwitch = document.getElementById('lang-switch');

    // Jika bahasa aktif adalah EN → toggle aktif
    langSwitch.checked = (currentLang === 'en');

    // Saat klik, ubah URL ke ?lang=en atau ?lang=id
    langSwitch.addEventListener('change', () => {
        const newLang = langSwitch.checked ? 'en' : 'id';
        const url = new URL(window.location.href);
        url.searchParams.set('lang', newLang);
        window.location.href = url.toString();
    });
    </script>

</header>
