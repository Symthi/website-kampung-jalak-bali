<?php
session_start();

// Switch language
if (isset($_GET['lang'])) {
  $_SESSION['language'] = ($_GET['lang'] == 'en') ? 'en' : 'id';
  header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
  exit();
}

include __DIR__ . '/config/koneksi.php';
include __DIR__ . '/config/language.php'; // Include language file

// compute base URL (site root) dynamically, e.g. /uas
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// helper to produce public URLs for uploaded files
function public_url($path) {
  global $base;
  if (empty($path)) return '';
  if (preg_match('#^https?://#i', $path) || strpos($path, '/') === 0) return $path;
  return $base . '/' . ltrim($path, '/');
}

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
    <title>Kampoeng Jalak Bali - <?php echo t('tourism_subtitle'); ?></title>
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  </head>
  <body>
    <?php 
    $current_page = 'home';
    include __DIR__ . '/includes/header.php';
    ?>
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
          <!-- Deskripsi dan Sejarah -->
          <div class="text-content">
            <p class="lead-text">
              <?php echo t('about_description'); ?>
            </p>
            <div class="history-section">
              <h3 class="content-title"><?php echo t('history'); ?></h3>
              <p><?php echo t('history_paragraph1'); ?></p>
              <p><?php echo t('history_paragraph2'); ?></p>
            </div>
          </div>

          <!-- Cards Section -->
          <div class="cards-section">
            <!-- Vision & Mission Card -->
            <div class="vision-mission-card">
              <h3 class="content-title">Visi & Misi</h3>
              <div class="vision-mission-slider">
                <div class="slider-content" id="visionMissionSlider">
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
                <div class="slider-nav">
                  <button class="slider-btn active" onclick="showVisionMission(0)">Visi</button>
                  <button class="slider-btn" onclick="showVisionMission(1)">Misi</button>
                </div>
              </div>
            </div>

            <!-- Structure Card -->
            <div class="structure-card">
              <h3 class="content-title"><?php echo t('management_structure'); ?></h3>
              <div class="structure-slider">
                <div class="structure-slide" id="structureSlider">
                  <div class="structure-item">
                    <h5 class="structure-title">PEMBINA</h5>
                    <ul class="structure-list">
                      <li>I KETUT SUARTANCA <span>(Perbekel Desa Tengkudak)</span></li>
                      <li>Drh. I MADE SUGIARTA <span>(FNPF)</span></li>
                    </ul>
                  </div>

                  <div class="structure-item">
                    <h5 class="structure-title">PENANGGUNGJAWAB</h5>
                    <ul class="structure-list">
                      <li>DESA ADAT TINGKIHKEREP</li>
                    </ul>
                  </div>

                  <div class="structure-item">
                    <h5 class="structure-title">KETUA</h5>
                    <ul class="structure-list">
                      <li>I NYOMAN OKA TRIADI <span>(Bandes Adat Tingkihkerep)</span></li>
                    </ul>
                  </div>

                  <div class="structure-item">
                    <h5 class="structure-title">SEKRETARIS & BENDAHARA</h5>
                    <ul class="structure-list">
                      <li>I MADE SUKARATA <span>(Sekretaris)</span></li>
                      <li>NI PUTU DESY ANGGRAENI <span>(Bendahara)</span></li>
                    </ul>
                  </div>

                  <div class="structure-item">
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
                <div class="structure-dots" id="structureDots"></div>
              </div>
            </div>
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
                <img src="<?php echo $wisata['gambar'] ? public_url($wisata['gambar']) : 'https://source.unsplash.com/random/600x400/?bali'; ?>" width="600" height="400" alt="<?php echo $wisata['judul']; ?>" class="wj-wisata-img" />
                <div class="location-badge">
                  <i class="fas fa-map-marker-alt"></i>
                  <?php echo $wisata['lokasi'] ?? 'Bali'; ?>
                </div>
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
        <div class="pagination">
          <?php for ($p=1; $p<=$total_pages_w; $p++): ?>
            <?php if ($p == $page_wisata): ?>
              <span class="active"><?php echo $p; ?></span>
            <?php else: ?>
              <a href="?p_w=<?php echo $p; ?>#wisata"><?php echo $p; ?></a>
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
          // Pagination untuk galeri
          $per_page_galeri = 5;
          $page_galeri = isset($_GET['p_g']) ? max(1, (int)$_GET['p_g']) : 1;
          $offset_galeri = ($page_galeri - 1) * $per_page_galeri;
          
          // Hitung total data galeri
          $total_galeri_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM galeri");
          $total_galeri = mysqli_fetch_assoc($total_galeri_q)['cnt'];
          
          // Query dengan pagination
          $query_galeri = "SELECT * FROM galeri ORDER BY tanggal_upload DESC LIMIT $per_page_galeri OFFSET $offset_galeri";
          $result_galeri = mysqli_query($koneksi, $query_galeri);
          $galeri_data = mysqli_fetch_all($result_galeri, MYSQLI_ASSOC);
          
          foreach ($galeri_data as $galeri):
          ?>
          <div class="gallery-item">
            <div class="image-container">
              <img src="<?php echo $galeri['gambar'] ? public_url($galeri['gambar']) : 'https://source.unsplash.com/random/600x400/?bali'; ?>" alt="<?php echo $galeri['judul']; ?>" class="gallery-image" />
              <div class="image-overlay">
                <div class="overlay-content">
                  <h4 class="image-title"><?php echo $galeri['judul']; ?></h4>
                  <button class="view-button" onclick="openGalleryDetail({
                      title: '<?php echo htmlspecialchars($galeri['judul'], ENT_QUOTES); ?>',
                      src: '<?php echo htmlspecialchars($galeri['gambar'] ? public_url($galeri['gambar']) : '', ENT_QUOTES); ?>',
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

        <?php 
        // Tampilkan pagination untuk galeri jika ada lebih dari 1 halaman
        $total_pages_g = (int)ceil($total_galeri / $per_page_galeri);
        if ($total_pages_g > 1):
        ?>
        <div class="pagination">
          <?php for ($p=1; $p<=$total_pages_g; $p++): ?>
            <?php if ($p == $page_galeri): ?>
              <span class="active"><?php echo $p; ?></span>
            <?php else: ?>
              <a href="?p_g=<?php echo $p; ?>#galeri"><?php echo $p; ?></a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
        <?php endif; ?>

        <!-- Modal Detail Galeri -->
        <div id="gallery-modal">
          <div class="modal-content">
            <div class="modal-header">
              <h3 id="gm-title" class="modal-title"></h3>
              <button onclick="closeGalleryDetail()" class="modal-close">✕</button>
            </div>
            <div class="modal-grid">
              <div>
                <img id="gm-image" src="" alt="" />
              </div>
              <div>
                <div class="muted-text">
                  <span><?php echo t('uploaded_on') ?: 'Diunggah pada'; ?> </span><span id="gm-date"></span>
                </div>
                <h4>Deskripsi</h4>
                <div id="gm-desc" class="modal-desc"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
        <script>
          const NO_DESCRIPTION_TEXT = "<?php echo t('no_description'); ?>";
        </script>
    </section>

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
                <a href="https://web.facebook.com/kampoeng.jalak.bali" target="_blank" class="social-link"> <i class="fab fa-facebook-f"></i>Kampoeng Jalak Bali </a>
                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=kampoengjalakbali@gmail.com" target="_blank" class="social-link"> <i class="fas fa-envelope"></i>kampoengjalakbali@gmail.com </a>
              </div>
            </div>
          </div>

          <!-- Form Kontak -->
          <div class="contact-form">
            <h3 class="contact-title"><?php echo t('send_message'); ?></h3>
            <form method="POST" action="<?php echo $base; ?>/processes/proses_kontak.php" class="form">
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
        <div class="map-wrapper">
          <h3 class="center"><?php echo t('location_title'); ?></h3>
          <p class="center"><?php echo t('location_subtitle'); ?></p>
          <iframe 
            class="map-iframe"
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3946.745252189239!2d115.11536599999998!3d-8.4266598!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd225006bc21dfd%3A0x485a7470e57deebc!2sKampoeng%20Jalak%20Bali!5e0!3m2!1sid!2sid!4v1763306878994!5m2!1sid!2sid"
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
  </body>
</html>
<?php mysqli_close($koneksi); ?>