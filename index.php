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
$per_page_wisata = 4;
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
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  </head>
  <body>
  <?php 
  $current_page = 'home';
  include __DIR__ . '/includes/header.php';
  ?>
    <!-- Hero Section -->
    <section id="home" class="hero-section">
      <!-- Background Slider Container -->
      <div class="hero-slider-container">
        <div class="hero-slide active" style="background-image: url(uploads/hero3.jpg);">
          <div class="slide-overlay"></div>
        </div>
        <div class="hero-slide" style="background-image: url(uploads/hero2.jpg);">
          <div class="slide-overlay"></div>
        </div>
        <div class="hero-slide" style="background-image: url(uploads/hero1.jpg);">
          <div class="slide-overlay"></div>
        </div>
      </div>

      <!-- Hero Content -->
      <div class="hero-container">
        <div class="hero-content">
          <h1 class="hero-title"><?php echo t('hero_title'); ?></h1>
          <p class="hero-description"><?php echo t('hero_description'); ?></p>
          <a href="#wisata" class="hero-button">
            <?php echo t('explore_now'); ?>
            <i class="fas fa-arrow-down"></i>
          </a>
        </div>
      </div>

      <!-- Slider Controls -->
      <div class="hero-controls">
        <button class="hero-control-btn prev" onclick="prevSlide()">
          <i class="fas fa-chevron-left"></i>
        </button>
        <div class="hero-dots">
          <span class="dot active" onclick="currentSlide(0)"></span>
          <span class="dot" onclick="currentSlide(1)"></span>
          <span class="dot" onclick="currentSlide(2)"></span>
        </div>
        <button class="hero-control-btn next" onclick="nextSlide()">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </section>

    <script>
    // ============ HERO SLIDER FUNCTIONALITY ============
    let currentSlideIndex = 0;
    let autoPlayInterval;

    // Initialize auto-play
    document.addEventListener('DOMContentLoaded', function() {
      startAutoPlay();
    });

    function showSlide(index) {
      const slides = document.querySelectorAll('.hero-slide');
      const dots = document.querySelectorAll('.dot');

      // Normalize index
      if (index >= slides.length) {
        currentSlideIndex = 0;
      } else if (index < 0) {
        currentSlideIndex = slides.length - 1;
      } else {
        currentSlideIndex = index;
      }

      // Remove active class from all slides and dots
      slides.forEach(slide => slide.classList.remove('active'));
      dots.forEach(dot => dot.classList.remove('active'));

      // Add active class to current slide and dot
      slides[currentSlideIndex].classList.add('active');
      dots[currentSlideIndex].classList.add('active');
    }

    function nextSlide() {
      showSlide(currentSlideIndex + 1);
      resetAutoPlay();
    }

    function prevSlide() {
      showSlide(currentSlideIndex - 1);
      resetAutoPlay();
    }

    function currentSlide(index) {
      showSlide(index);
      resetAutoPlay();
    }

    function startAutoPlay() {
      autoPlayInterval = setInterval(() => {
        showSlide(currentSlideIndex + 1);
      }, 6000);
    }

    function resetAutoPlay() {
      clearInterval(autoPlayInterval);
      startAutoPlay();
    }

    // Pause auto-play on hover
    document.addEventListener('DOMContentLoaded', function() {
      const heroSection = document.querySelector('.hero-section');
      
      heroSection.addEventListener('mouseenter', () => {
        clearInterval(autoPlayInterval);
      });

      heroSection.addEventListener('mouseleave', () => {
        startAutoPlay();
      });
    });
    </script>

    <!-- Tentang Section -->
    <section id="tentang" class="about-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"><?php echo t('about_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('about_description'); ?></p>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs-container">
          <button class="tab-btn active" onclick="switchTab('vision-mission')">
            <i class="fas fa-bullseye"></i>
            <?php echo t('vision'); ?> & <?php echo t('mission'); ?>
          </button>
          <button class="tab-btn" onclick="switchTab('structure')">
            <i class="fas fa-sitemap"></i>
            <?php echo t('management_structure'); ?>
          </button>
          <button class="tab-btn" onclick="switchTab('history')">
            <i class="fas fa-history"></i>
            <?php echo t('history'); ?>
          </button>
        </div>

        <!-- TAB 1: Vision & Mission -->
        <div id="vision-mission" class="tab-content active">
          <div class="vision-mission-grid">
            <!-- Vision Card -->
            <div class="vision-mission-card">
              <div class="card-header">
                <div class="icon-wrapper">
                  <i class="fas fa-bullseye"></i>
                </div>
                <h3 class="card-title"><?php echo t('vision'); ?></h3>
              </div>
              <p class="card-text"><?php echo t('vision_text'); ?></p>
            </div>

            <!-- Mission Card -->
            <div class="vision-mission-card">
              <div class="card-header">
                <div class="icon-wrapper">
                  <i class="fas fa-tasks"></i>
                </div>
                <h3 class="card-title"><?php echo t('mission'); ?></h3>
              </div>
              <ul class="mission-list">
                <?php foreach (t('mission_items') as $index => $mission_item): ?>
                <li class="mission-item">
                  <span class="mission-number"><?php echo $index + 1; ?></span>
                  <span class="mission-text"><?php echo $mission_item; ?></span>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>

        <!-- TAB 2: Struktur Organisasi -->
        <div id="structure" class="tab-content">
          <div class="structure-grid">
            <!-- Advisor -->
            <div class="structure-item">
              <span class="structure-level"><?php echo t('advisor'); ?></span>
              <h4 class="structure-title"><?php echo t('advisor'); ?></h4>
              <div class="structure-members">
                <div class="member">
                  <div class="member-name">I KETUT SUARTANCA</div>
                  <div class="member-position"><?php echo t('position_village_chief'); ?></div>
                </div>
                <div class="member">
                  <div class="member-name">Drh. I MADE SUGIARTA</div>
                  <div class="member-position"><?php echo t('position_fnpf'); ?></div>
                </div>
              </div>
            </div>

            <!-- Responsible Party -->
            <div class="structure-item">
              <span class="structure-level"><?php echo t('responsible_party'); ?></span>
              <h4 class="structure-title"><?php echo t('responsible_party'); ?></h4>
              <div class="structure-members">
                <div class="member">
                  <div class="member-name"><?php echo t('position_adat_village'); ?></div>
                  <div class="member-position"><?php echo t('position_adat_village'); ?></div>
                </div>
              </div>
            </div>

            <!-- Chairperson -->
            <div class="structure-item">
              <span class="structure-level"><?php echo t('chairperson'); ?></span>
              <h4 class="structure-title"><?php echo t('chairperson'); ?></h4>
              <div class="structure-members">
                <div class="member">
                  <div class="member-name">I NYOMAN OKA TRIADI</div>
                  <div class="member-position"><?php echo t('position_adat_leader'); ?></div>
                </div>
              </div>
            </div>

            <!-- Secretary & Treasurer -->
            <div class="structure-item">
              <span class="structure-level"><?php echo t('secretary_and_treasurer'); ?></span>
              <h4 class="structure-title"><?php echo t('secretary_and_treasurer'); ?></h4>
              <div class="structure-members">
                <div class="member">
                  <div class="member-name">I MADE SUKARATA</div>
                  <div class="member-position"><?php echo t('position_secretary'); ?></div>
                </div>
                <div class="member">
                  <div class="member-name">NI PUTU DESY ANGGRAENI</div>
                  <div class="member-position"><?php echo t('position_treasurer'); ?></div>
                </div>
              </div>
            </div>

            <!-- Members - Guides -->
            <div class="structure-item">
              <span class="structure-level"><?php echo t('member'); ?></span>
              <h4 class="structure-title"><?php echo t('position_guide'); ?></h4>
              <div class="structure-members">
                <div class="member">
                  <div class="member-name">I WAYAN EDDYAS PRIHANTARA</div>
                  <div class="member-position"><?php echo t('position_guide'); ?></div>
                </div>
                <div class="member">
                  <div class="member-name">I KETUT MERTAJAYA</div>
                  <div class="member-position"><?php echo t('position_guide'); ?></div>
                </div>
                <div class="member">
                  <div class="member-name">I WAYAN SUDARMA</div>
                  <div class="member-position"><?php echo t('position_guide'); ?></div>
                </div>
              </div>
            </div>

            <!-- Members - Others -->
            <div class="structure-item">
              <span class="structure-level"><?php echo t('member'); ?></span>
              <h4 class="structure-title"><?php echo t('position_observer'); ?></h4>
              <div class="structure-members">
                <div class="member">
                  <div class="member-name">I WAYAN YUDI ARTANA</div>
                  <div class="member-position"><?php echo t('position_observer'); ?></div>
                </div>
                <div class="member">
                  <div class="member-name">NI WAYAN SUIKI</div>
                  <div class="member-position"><?php echo t('member'); ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 3: History -->
        <div id="history" class="tab-content">
          <div class="history-content">
            <div class="history-section">
              <h3 class="content-title"><?php echo t('history'); ?></h3>
              <p><?php echo t('history_paragraph1'); ?></p>
              <p><?php echo t('history_paragraph2'); ?></p>
            </div>
          </div>
        </div>
      </div>
    </section>


      <script>
      function switchTab(tabName) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.about-section .tab-content');
        tabContents.forEach(tab => {
          tab.classList.remove('active');
        });

        // Remove active class from all buttons
        const tabBtns = document.querySelectorAll('.about-section .tab-btn');
        tabBtns.forEach(btn => {
          btn.classList.remove('active');
        });

        // Show selected tab content
        document.getElementById(tabName).classList.add('active');

        // Add active class to clicked button
        event.target.closest('.tab-btn').classList.add('active');
      }
      </script>

    <!-- Wisata Section -->
    <section id="wisata" class="wisata-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"><?php echo t('tourism_sec'); ?></h2>
          <p class="section-subtitle"><?php echo t('tourism_subtitle'); ?></p>
        </div>

        <div class="wisata-grid">
          <?php if (empty($wisata_data)): ?>
            <p style="grid-column: 1 / -1; text-align: center; color: var(--muted-text);">
              <?php echo t('no_tourism'); ?>
            </p>
          <?php else: ?>
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
                    <?php echo t('time'); ?>: <strong><?php echo ucfirst($wisata['waktu']); ?></strong> (<?php echo date('H:i', strtotime($wisata['jam'])); ?>)
                  </span>
                </div>
                <a href="detail_wisata.php?id=<?php echo $wisata['id_wisata']; ?>" class="card-button">
                  <?php echo t('view_details'); ?>
                  <i class="fas fa-arrow-right"></i>
                </a>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
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
          
          if (empty($galeri_data)): ?>
            <p style="grid-column: 1 / -1; text-align: center; color: var(--muted-text);">
              <?php echo t('no_gallery_images'); ?>
            </p>
          <?php else: ?>
            <?php foreach ($galeri_data as $galeri): ?>
            <div class="gallery-item">
              <div class="image-container">
                <img src="<?php echo $galeri['gambar'] ? public_url($galeri['gambar']) : 'https://source.unsplash.com/random/600x400/?bali'; ?>" alt="<?php echo $galeri['judul']; ?>" class="gallery-image" />
                <div class="image-overlay">
                  <div class="overlay-content">
                    <h4 class="image-title"><?php echo $galeri['judul']; ?></h4>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
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


      </div>
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
                <a href="https://instagram.com/kampoengjalakbali/" target="_blank" class="social-link"> 
                  <i class="fab fa-instagram"></i>@kampoengjalakbali 
                </a>
                <a href="#" class="social-link"> 
                  <i class="fab fa-facebook-f"></i>Kampoeng Jalak Bali 
                </a>
                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=kampoengjalakbali@gmail.com" class="social-link"> 
                  <i class="fas fa-envelope"></i>kampoengjalakbali@gmail.com 
                </a>
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
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3945.447676234625!2d115.09547427579436!3d-8.506537491489967!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd23ba60f56f36f%3A0x9ac1cda35155124c!2sDesa%20Tengkudak%2C%20Kec.%20Penebel%2C%20Kabupaten%20Tabanan%2C%20Bali!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid" 
              allowfullscreen="" 
              loading="lazy" 
              referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>
    </section>

  <?php include __DIR__ . '/includes/footer.php'; ?>
  
  <script>
  // Smooth scroll dengan offset yang lebih besar untuk navbar
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      
      const targetId = this.getAttribute('href');
      if (targetId === '#') return;
      
      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        const headerHeight = document.querySelector('.header').offsetHeight;
        // PERBAIKAN: Offset diperbesar dari 10px menjadi 80px
        const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight - 40;
        
        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });
        
        // Update URL hash tanpa trigger scroll ulang
        history.pushState(null, null, targetId);
      }
    });
  });

  // Handle URL hash on page load dengan offset yang sama
  window.addEventListener('load', function() {
    if (window.location.hash) {
      const targetElement = document.querySelector(window.location.hash);
      if (targetElement) {
        setTimeout(() => {
          const headerHeight = document.querySelector('.header').offsetHeight;
          // PERBAIKAN: Offset diperbesar dari 10px menjadi 80px
          const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight - 40;
          
          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });
        }, 100);
      }
    }
  });

  // Tambahan: Handle resize untuk update offset jika navbar berubah ukuran
  let resizeTimer;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
      // Refresh scroll position jika ada hash di URL
      if (window.location.hash) {
        const targetElement = document.querySelector(window.location.hash);
        if (targetElement) {
          const headerHeight = document.querySelector('.header').offsetHeight;
          const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight - 80;
          
          window.scrollTo({
            top: targetPosition,
            behavior: 'auto'
          });
        }
      }
    }, 250);
  });
  </script>
  </body>
</html>
<?php mysqli_close($koneksi); ?>