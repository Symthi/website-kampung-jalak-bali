<?php
session_start();

// Switch language
if (isset($_GET['lang'])) {
    $_SESSION['language'] = ($_GET['lang'] == 'en') ? 'en' : 'id';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

include 'koneksi.php';
include 'language.php'; // Include language file

// Ambil data wisata dari database dengan pagination (section wisata)
$per_page_wisata = 5;
$page_wisata = isset($_GET['p_w']) ? max(1, (int)$_GET['p_w']) : 1;
$offset_wisata = ($page_wisata - 1) * $per_page_wisata;
$total_wisata_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM wisata");
$total_wisata_home = mysqli_fetch_assoc($total_wisata_q)['cnt'];
$query_wisata = "SELECT * FROM wisata ORDER BY tanggal_ditambahkan DESC LIMIT $per_page_wisata OFFSET $offset_wisata";
$result_wisata = mysqli_query($koneksi, $query_wisata);
$wisata_data = mysqli_fetch_all($result_wisata, MYSQLI_ASSOC);

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi cek role admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kampung Jalak Bali - <?php echo t('tourism_subtitle'); ?></title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <!-- Language Switcher -->
    <div style="text-align: right; padding: 10px; background: #f8f9fa;">
      <a href="?lang=id" style="margin-right: 10px;">🇮🇩 Indonesia</a>
      <a href="?lang=en">🇬🇧 English</a>
    </div>

    <!-- Header -->
    <header>
      <div>
        <div>
          <img src="uploads/Rancangan Logo.png" alt="Logo Kampung Jalak Bali" width="100px"/>
          <h1>Kampung Jalak Bali</h1>
        </div>
        <nav>
          <ul>
            <li><a href="#home"><?php echo t('home'); ?></a></li>
            <li><a href="#tentang"><?php echo t('about'); ?></a></li>
            <li><a href="#wisata"><?php echo t('tourism'); ?></a></li>
            <li><a href="informasi.php"><?php echo t('information'); ?></a></li>
            <li><a href="#galeri"><?php echo t('gallery'); ?></a></li>
            <li><a href="produk.php"><?php echo t('products'); ?></a></li>
            <li><a href="#kontak"><?php echo t('contact'); ?></a></li>
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

    <!-- Hero Section -->
    <section id="home" class="hero-section">
      <div class="hero-container">
        <div class="hero-content">
          <h1 class="hero-title"><?php echo t('hero_title'); ?></h1>
          <p class="hero-description"><?php echo t('hero_description'); ?></p>
          <a href="#wisata" class="hero-button"><?php echo t('explore_now'); ?></a>
        </div>
      </div>
    </section>

    <!-- Tentang Section -->
    <section id="tentang" class="about-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"><?php echo t('about_title'); ?></h2>
        </div>

        <div class="about-content">
          <div class="content-main">
            <div class="text-content">
              <p class="lead-text">
                <?php echo t('about_description'); ?>
              </p>

              <div class="content-section">
                <h3 class="content-title"><?php echo t('history'); ?></h3>
                <p>
                  Program konservasi ini dimulai pada April 2024 oleh Yayasan Friends of Nature, People and Forests (FNPF) dengan melepasliarkan 60 ekor Jalak Bali. Lokasi Desa Tengkudak dipilih setelah melalui kajian habitat oleh akademisi
                  Universitas Udayana dan didukung kuat oleh budaya masyarakat setempat.
                </p>
                <p>
                  Masyarakat adat Tingkihkerep telah lama melestarikan satwa melalui Awig-Awig dan Perarem (hukum adat) yang melarang perburuan, didasari oleh keyakinan akan keberadaan "Pelingsih Wewalungan" sebagai stana dewa pelindung
                  satwa. Hal ini menjadikan Kampoeng Jalak Bali sebagai contoh sukses konservasi berbasis kearifan lokal dan resmi diresmikan oleh Bupati Tabanan pada Juni 2024.
                </p>
              </div>

              <div class="content-section">
                <div class="vision-mission-grid">
                  <div class="vision-card">
                    <div class="card-header">
                      <div class="icon-wrapper">
                        <i class="fas fa-bullseye"></i>
                      </div>
                      <h4 class="card-title"><?php echo t('vision'); ?></h4>
                    </div>
                    <p class="vision-text"><?php echo t('vision_text'); ?></p>
                  </div>

                  <div class="mission-card">
                    <div class="card-header">
                      <div class="icon-wrapper">
                        <i class="fas fa-tasks"></i>
                      </div>
                      <h4 class="card-title"><?php echo t('mission'); ?></h4>
                    </div>
                    <ul class="mission-list">
                      <?php foreach (t('mission_items') as $index => $mission_item): ?>
                      <li class="mission-item">
                        <span class="item-number"><?php echo $index + 1; ?></span>
                        <span class="item-text"><?php echo $mission_item; ?></span>
                      </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- Structure Section tetap sama karena konten spesifik -->
            <div class="structure-section">
              <h3 class="content-title"><?php echo t('management_structure'); ?></h3>
              <div class="structure-grid">
                <!-- ... struktur tetap ... -->
                 <div class="structure-card">
                  <h5 class="structure-title">PEMBINA</h5>
                  <ul class="structure-list">
                    <li>I KETUT SUARTANCA <span>(Perbekel Desa Tengkudak)</span></li>
                    <li>Drh. I MADE SUGIARTA <span>(FNPF)</span></li>
                  </ul>
                </div>

                <div class="structure-card">
                  <h5 class="structure-title">PENANGGUNGJAWAB</h5>
                  <ul class="structure-list">
                    <li>DESA ADAT TINGKIHKEREP</li>
                  </ul>
                </div>

                <div class="structure-card">
                  <h5 class="structure-title">KETUA</h5>
                  <ul class="structure-list">
                    <li>I NYOMAN OKA TRIADI <span>(Bandes Adat Tingkihkerep)</span></li>
                  </ul>
                </div>

                <div class="structure-card">
                  <h5 class="structure-title">SEKRETARIS</h5>
                  <ul class="structure-list">
                    <li>I MADE SUKARATA</li>
                  </ul>
                </div>

                <div class="structure-card">
                  <h5 class="structure-title">BENDAHARA</h5>
                  <ul class="structure-list">
                    <li>NI PUTU DESY ANGGRAENI</li>
                  </ul>
                </div>

                <div class="structure-card full-width">
                  <h5 class="structure-title">ANGGOTA</h5>
                  <ul class="structure-list">
                    <li>I WAYAN EDDYAS PRIHANTARA <span>(Pemandu)</span></li>
                    <li>I KETUT MERTAJAYA <span>(Pemandu)</span></li>
                    <li>I WAYAN SUDARMA <span>(Pemandu)</span></li>
                    <li>I WAYAN YUDI ARTANA <span>(Pengamat)</span></li>
                    <li>NI WAYAN SUIKI</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div class="content-image">
            <img src="https://source.unsplash.com/random/600x400/?bali,village" alt="Kampung Jalak Bali" class="featured-image" />
          </div>
        </div>
      </div>
    </section>

    <!-- Wisata Section -->
    <section id="wisata" class="wisata-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"><?php echo t('tourism_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('tourism_subtitle'); ?></p>
        </div>

        <div class="wisata-grid">
          <?php foreach ($wisata_data as $wisata): ?>
          <div class="wisata-card">
            <div class="card-image">
              <img src="<?php echo $wisata['gambar'] ?: 'https://source.unsplash.com/random/600x400/?bali'; ?>" width="600" height="400" alt="<?php echo $wisata['judul']; ?>" class="wisata-image" />
            </div>
            <div class="card-content">
              <h3 class="card-title"><?php echo $wisata['judul']; ?></h3>
              <div class="card-meta">
                <span class="meta-item">
                  <i class="fas fa-clock"></i>
                  <?php echo t('duration'); ?>: <?php echo $wisata['durasi']; ?>
                </span>
              </div>
              <a href="detail_wisata.php?id=<?php echo $wisata['id_wisata']; ?>" class="card-button">
                <?php echo t('view_details'); ?>
                <i class="fas fa-arrow-right"></i>
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php $total_pages_w = (int)ceil($total_wisata_home / $per_page_wisata); if ($total_pages_w > 1): ?>
        <div style="display:flex; gap:8px; justify-content:center; margin-top:15px;">
          <?php for ($p=1; $p<=$total_pages_w; $p++): ?>
            <?php if ($p == $page_wisata): ?>
              <span style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; background:#007bff; color:#fff;">&nbsp;<?php echo $p; ?>&nbsp;</span>
            <?php else: ?>
              <a href="?p_w=<?php echo $p; ?>#wisata" style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; text-decoration:none; color:#333;">&nbsp;<?php echo $p; ?>&nbsp;</a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Galeri Section -->
    <section id="galeri" class="gallery-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"><?php echo t('gallery_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('gallery_subtitle'); ?></p>
        </div>

        <div class="gallery-grid">
          <?php
          $query_galeri = "SELECT * FROM galeri ORDER BY tanggal_upload DESC LIMIT 6";
          $result_galeri = mysqli_query($koneksi, $query_galeri);
          $galeri_data = mysqli_fetch_all($result_galeri, MYSQLI_ASSOC);
          
          foreach ($galeri_data as $galeri):
          ?>
          <div class="gallery-item">
            <div class="image-container">
              <img src="<?php echo $galeri['gambar'] ?: 'https://source.unsplash.com/random/600x400/?bali'; ?>" alt="<?php echo $galeri['judul']; ?>" class="gallery-image" />
              <div class="image-overlay">
                <div class="overlay-content">
                  <h4 class="image-title"><?php echo $galeri['judul']; ?></h4>
                  <button class="view-button" onclick="openGalleryDetail({
                      title: '<?php echo htmlspecialchars($galeri['judul'], ENT_QUOTES); ?>',
                      src: '<?php echo htmlspecialchars($galeri['gambar'], ENT_QUOTES); ?>',
                      desc: `<?php echo htmlspecialchars($galeri['keterangan'] ?? '', ENT_QUOTES); ?>`,
                      date: '<?php echo htmlspecialchars(date('d M Y', strtotime($galeri['tanggal_upload'] ?? $galeri['tanggal'] ?? 'now')), ENT_QUOTES); ?>'
                    })">
                    <?php echo t('view_details'); ?>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Modal Detail Galeri -->
        <div id="gallery-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:1000; align-items:center; justify-content:center; padding:20px;">
          <div style="background:#fff; max-width:900px; width:100%; border-radius:8px; overflow:hidden;">
            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid #eee;">
              <h3 id="gm-title" style="margin:0; font-size:18px;"></h3>
              <button onclick="closeGalleryDetail()" style="border:none; background:#eee; padding:6px 10px; border-radius:4px; cursor:pointer;">✕</button>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; padding:16px;">
              <div>
                <img id="gm-image" src="" alt="" style="width:100%; height:auto; border-radius:6px;" />
              </div>
              <div>
                <div style="color:#777; font-size:12px; margin-bottom:6px;">
                  <span><?php echo t('uploaded_on') ?: 'Diunggah pada'; ?> </span><span id="gm-date"></span>
                </div>
                <h4 style="margin:0 0 8px;">Deskripsi</h4>
                <div id="gm-desc" style="white-space:pre-wrap; color:#444;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <script>
      function openGalleryDetail(data){
        var m = document.getElementById('gallery-modal');
        document.getElementById('gm-title').textContent = data.title || '';
        var img = document.getElementById('gm-image');
        img.src = data.src || '';
        img.alt = data.title || '';
        var descEl = document.getElementById('gm-desc');
        var raw = (data.desc || '').trim();
        // Render deskripsi sebagai HTML sederhana (sudah di-escape server-side) agar bisa ada pemformatan dasar
        descEl.textContent = '';
        if(raw){
          descEl.innerHTML = raw;
        } else {
          descEl.textContent = '<?php echo t('no_description'); ?>';
        }
        document.getElementById('gm-date').textContent = data.date || '';
        m.style.display = 'flex';
      }
      function closeGalleryDetail(){
        var m = document.getElementById('gallery-modal');
        m.style.display = 'none';
      }
      // Close on backdrop click
      document.addEventListener('click', function(e){
        var m = document.getElementById('gallery-modal');
        if(!m || m.style.display==='none') return;
        if(e.target === m) closeGalleryDetail();
      });
      // ESC to close
      document.addEventListener('keydown', function(e){
        if(e.key === 'Escape') closeGalleryDetail();
      });
    </script>

    <!-- Kontak Section -->
    <section id="kontak" class="contact-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"><?php echo t('contact_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('contact_subtitle'); ?></p>
        </div>

        <div class="contact-grid">
          <!-- Informasi Kontak -->
          <div class="contact-info">
            <h3 class="contact-title"><?php echo t('contact_info'); ?></h3>
            <div class="contact-list">
              <div class="contact-item">
                <div class="contact-icon">
                  <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="contact-content">
                  <h4 class="item-title"><?php echo t('address'); ?></h4>
                  <p class="item-text">Desa Adat Tingkihkerep, Desa Tengkudak, Kecamatan Penebel, Kabupaten Tabanan, Bali</p>
                </div>
              </div>

              <div class="contact-item">
                <div class="contact-icon">
                  <i class="fas fa-phone"></i>
                </div>
                <div class="contact-content">
                  <h4 class="item-title"><?php echo t('phone'); ?></h4>
                  <p class="item-text">I Wayan Yudi Artana (083862519604)</p>
                </div>
              </div>
            </div>

            <div class="social-links">
              <h4 class="social-title"><?php echo t('follow_us'); ?></h4>
              <div class="social-icons">
                <a href="https://instagram.com/kampoengjalakbali/" target="_blank" class="social-link"> <i class="fab fa-instagram"></i>@kampoengjalakbali </a>
                <a href="#" class="social-link"> <i class="fab fa-facebook-f"></i>Kampoeng Jalak Bali </a>
                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=kampoengjalakbali@gmail.com" class="social-link"> <i class="fas fa-envelope"></i>kampoengjalakbali@gmail.com </a>
              </div>
            </div>
          </div>

          <!-- Form Kontak -->
          <div class="contact-form">
            <h3 class="contact-title"><?php echo t('send_message'); ?></h3>
            <form method="POST" action="proses_kontak.php" class="form">
              <div class="form-group">
                <label for="nama" class="form-label"><?php echo t('full_name'); ?></label>
                <input type="text" id="nama" name="nama" class="form-input" required />
              </div>

              <div class="form-group">
                <label for="email" class="form-label"><?php echo t('email'); ?></label>
                <input type="email" id="email" name="email" class="form-input" required />
              </div>

              <div class="form-group">
                <label for="subjek" class="form-label"><?php echo t('subject'); ?></label>
                <input type="text" id="subjek" name="subjek" class="form-input" required />
              </div>

              <div class="form-group">
                <label for="pesan" class="form-label"><?php echo t('message'); ?></label>
                <textarea id="pesan" name="pesan" class="form-textarea" rows="5" required></textarea>
              </div>

              <button type="submit" class="form-button">
                <?php echo t('send_message'); ?>
                <i class="fas fa-paper-plane"></i>
              </button>
            </form>
          </div>
        </div>

        <!-- Map inside Contact Section -->
        <div style="margin-top: 30px;">
          <h3 style="text-align:center; margin-bottom:10px;"><?php echo t('location_title'); ?></h3>
          <p style="text-align:center; margin-bottom:20px;"><?php echo t('location_subtitle'); ?></p>
          <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3945.447676234625!2d115.09547427579436!3d-8.506537491489967!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd23ba60f56f36f%3A0x9ac1cda35155124c!2sDesa%20Tengkudak%2C%20Kec.%20Penebel%2C%20Kabupaten%20Tabanan%2C%20Bali!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid" 
            width="100%" 
            height="350" 
            style="border:0; border-radius: 8px;" 
            allowfullscreen="" 
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        
        <!-- Supporter Logos Section -->
        <div style="text-align: center; padding: 30px 0; border-bottom: 1px solid #ddd; margin-bottom: 30px;">
          <h3 style="margin-bottom: 20px;"><?php echo t('supporter_title'); ?></h3>
          <div style="display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 30px;">
            <!-- <img src="uploads/supporter/fnpf-logo.png" alt="FNPF" width="100" 
                onerror="this.src='https://via.placeholder.com/100x60/1a6b3b/ffffff?text=FNPF'">
            <img src="uploads/supporter/unud-logo.png" alt="Universitas Udayana" width="100"
                onerror="this.src='https://via.placeholder.com/100x60/1a6b3b/ffffff?text=Udayana'">
            <img src="uploads/supporter/pemkab-tabanan.png" alt="Pemkab Tabanan" width="100"
                onerror="this.src='https://via.placeholder.com/100x60/1a6b3b/ffffff?text=Tabanan'">
            <img src="uploads/supporter/desa-tengkudak.png" alt="Desa Tengkudak" width="100"
                onerror="this.src='https://via.placeholder.com/100x60/1a6b3b/ffffff?text=Tengkudak'">
            <img src="uploads/supporter/desa-adat.png" alt="Desa Adat" width="100"
                onerror="this.src='https://via.placeholder.com/100x60/1a6b3b/ffffff?text=Desa+Adat'">
            <img src="uploads/supporter/bali-government.png" alt="Pemprov Bali" width="100"
                onerror="this.src='https://via.placeholder.com/100x60/1a6b3b/ffffff?text=Pemprov+Bali'"> -->
          </div>
        </div>

        <div class="footer-content">
          <!-- Brand Section -->
          <div class="footer-section">
            <h3 class="footer-title">Kampung Jalak Bali</h3>
            <p class="footer-description"><?php echo t('hero_description'); ?></p>
          </div>

          <!-- Quick Links -->
          <div class="footer-section">
            <h3 class="footer-title"><?php echo t('quick_links'); ?></h3>
            <ul class="footer-links">
              <li><a href="#home" class="footer-link"><?php echo t('home'); ?></a></li>
              <li><a href="#tentang" class="footer-link"><?php echo t('about'); ?></a></li>
              <li><a href="#wisata" class="footer-link"><?php echo t('tourism'); ?></a></li>
              <li><a href="informasi.php" class="footer-link"><?php echo t('information'); ?></a></li>
              <li><a href="#galeri" class="footer-link"><?php echo t('gallery'); ?></a></li>
              <li><a href="produk.php" class="footer-link"><?php echo t('products'); ?></a></li>
              <li><a href="#kontak" class="footer-link"><?php echo t('contact'); ?></a></li>
            </ul>
          </div>

          <!-- Contact Info -->
          <div class="footer-section">
            <h3 class="footer-title"><?php echo t('contact'); ?></h3>
            <div class="footer-contact">
              <p>📞 083862519604</p>
              <p>📧 kampoengjalakbali@gmail.com</p>
              <p>📷 @kampoengjalakbali</p>
            </div>
          </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
          <p class="copyright">&copy; 2025 Kampung Jalak Bali. <?php echo t('rights_reserved'); ?></p>
        </div>
      </div>
    </footer>
  </body>
</html>
<?php mysqli_close($koneksi); ?>