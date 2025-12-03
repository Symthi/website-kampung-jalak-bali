<?php
session_start();

// Switch language
if (isset($_GET['lang'])) {
  $_SESSION['language'] = ($_GET['lang'] == 'en') ? 'en' : 'id';
  header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
  exit();
}

include __DIR__ . '/config/koneksi.php';
include __DIR__ . '/config/language.php';

// compute base URL (site root) dynamically, e.g. /uas
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// helper to produce public URLs for uploaded files
function public_url($path) {
  global $base;
  if (empty($path)) return '';
  if (preg_match('#^https?://#i', $path) || strpos($path, '/') === 0) return $path;
  return $base . '/' . ltrim($path, '/');
}

// Create mitra table if doesn't exist
$create_mitra = "CREATE TABLE IF NOT EXISTS mitra (
    id_mitra INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    gambar VARCHAR(500) NOT NULL,
    link_partner VARCHAR(500),
    urutan INT DEFAULT 0,
    aktif TINYINT DEFAULT 1,
    tanggal_ditambahkan TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($koneksi, $create_mitra);

// Ambil data wisata dari database dengan pagination (section wisata)
$per_page_wisata = 3;
$page_wisata = isset($_GET['p_w']) ? max(1, (int)$_GET['p_w']) : 1;
$offset_wisata = ($page_wisata - 1) * $per_page_wisata;
$total_wisata_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM wisata");
$total_wisata_home = mysqli_fetch_assoc($total_wisata_q)['cnt'];
$query_wisata = "SELECT * FROM wisata ORDER BY tanggal_ditambahkan DESC LIMIT $per_page_wisata OFFSET $offset_wisata";
$result_wisata = mysqli_query($koneksi, $query_wisata);
$wisata_data = mysqli_fetch_all($result_wisata, MYSQLI_ASSOC);

// Ambil data mitra dari database
$query_mitra = "SELECT * FROM mitra WHERE aktif = 1 ORDER BY urutan ASC, tanggal_ditambahkan DESC";
$result_mitra = mysqli_query($koneksi, $query_mitra);
$mitra_data = mysqli_fetch_all($result_mitra, MYSQLI_ASSOC);

// Ambil 5 logo dari database
$logo_1 = get_setting('logo_1', '');
$logo_2 = get_setting('logo_2', '');
$logo_3 = get_setting('logo_3', '');
$logo_4 = get_setting('logo_4', '');
$logo_5 = get_setting('logo_5', '');

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
    <title><?php echo t('site_title'); ?> - <?php echo t('tourism_subtitle'); ?></title>
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
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
        <?php
        // Ambil hero background dari pengaturan
        $hero_bg_1 = get_setting('hero_background_1', 'uploads/hero1.jpg');
        $hero_bg_2 = get_setting('hero_background_2', 'uploads/hero2.jpg'); 
        $hero_bg_3 = get_setting('hero_background_3', 'uploads/hero3.jpg');
        ?>
        <div class="hero-slide active" style="background-image: url(<?php echo public_url($hero_bg_1); ?>);">
          <div class="slide-overlay"></div>
        </div>
        <div class="hero-slide" style="background-image: url(<?php echo public_url($hero_bg_2); ?>);">
          <div class="slide-overlay"></div>
        </div>
        <div class="hero-slide" style="background-image: url(<?php echo public_url($hero_bg_3); ?>);">
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
    <section id="tentang" class="about-section-slide">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"><?php echo t('about_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('about_description'); ?></p>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs-slide">
          <button class="tab-btn-slide active" data-tab="history">
            <i class="fas fa-history"></i>
            <?php echo t('history'); ?>
          </button>
          <button class="tab-btn-slide" data-tab="background">
            <i class="fas fa-scroll"></i>
            <?php echo t('background'); ?>
          <button class="tab-btn-slide" data-tab="vision-mission">
            <i class="fas fa-bullseye"></i>
            <?php echo t('vision'); ?> & <?php echo t('mission'); ?>
          </button>
          <button class="tab-btn-slide" data-tab="structure">
            <i class="fas fa-sitemap"></i>
            <?php echo t('management_structure'); ?>
          </button>
        </div>

        <!-- History Card -->
        <div class="card-slide-container animate pop" id="history-card">
          <div class="overlay-slide">
            <div class="overlay-content-slide animate slide-left delay-2">
              <h1 class="overlay-title animate slide-left delay-4">Sejarah</h1>
              <p class="overlay-subtitle animate slide-left delay-5">Melestarikan budaya bukan hanya menjaga masa lalu, tapi juga membangun masa depan bagi penerus</p>
              <p class="overlay-subtitle animate slide-left delay-5">Warisan Budaya Bali</p>
            </div>
            <div class="image-content-slide animate slide delay-5" 
                style="background-image: url('<?php echo $base; ?>/uploads/history-image.jpg')"></div>
            <div class="dots-slide animate">
              <div class="dot-slide animate slide-up delay-6"></div>
              <div class="dot-slide animate slide-up delay-7"></div>
              <div class="dot-slide animate slide-up delay-8"></div>
            </div>
          </div>
          <div class="text-content-slide">
            <h2 class="text-title"><i class="fas fa-landmark"></i> <?php echo t('history'); ?></h2>
            <p class="text-paragraph"><?php echo t('history_paragraph1'); ?></p>
            <p class="text-paragraph"><?php echo t('history_paragraph2'); ?></p>
          </div>
        </div>

        <!-- Background Card (Hidden by default) -->
        <div class="card-slide-container animate pop" id="background-card" style="display: none;">
          <div class="overlay-slide">
            <div class="overlay-content-slide animate slide-left delay-2">
              <h1 class="overlay-title animate slide-left delay-4"><?php echo t('background'); ?></h1>
              <p class="overlay-subtitle animate slide-left delay-5"><?php echo get_setting('background_subtitle', 'Konservasi Jalak Bali berlandaskan Tri Hita Karana menjadi fondasi pelestarian budaya, lingkungan, dan ekonomi masyarakat.'); ?></p>
              <p class="overlay-subtitle animate slide-left delay-5"><?php echo get_setting('background_tagline', 'landasan Filosofi'); ?></p>
            </div>
            <?php $bg_image = get_setting('background_image', 'uploads/background-image.jpeg'); ?>
            <div class="image-content-slide animate slide delay-5" 
                style="background-image: url('<?php echo public_url($bg_image); ?>')"></div>
            <div class="dots-slide animate">
              <div class="dot-slide animate slide-up delay-6"></div>
              <div class="dot-slide animate slide-up delay-7"></div>
              <div class="dot-slide animate slide-up delay-8"></div>
            </div>
          </div>
          <div class="text-content-slide">
            <h2 class="text-title"><i class="fas fa-scroll"></i> <?php echo t('background'); ?></h2>
            <p class="text-paragraph"><?php echo get_setting('background_paragraph1', t('background_paragraph1')); ?></p>
            <p class="text-paragraph"><?php echo get_setting('background_paragraph2', t('background_paragraph2')); ?></p>
          </div>
        </div>

        <!-- Visi Misi Card (Hidden by default) -->
        <div class="card-slide-container animate pop" id="vision-mission-card" style="display: none;">
          <div class="overlay-slide">
            <div class="overlay-content-slide animate slide-left delay-2">
              <h1 class="overlay-title animate slide-left delay-4">Visi & Misi</h1>
              <p class="overlay-subtitle animate slide-left delay-5">Kami tidak hanya menjaga tradisi, tetapi juga menciptakan inovasi yang relevan dengan zaman tanpa meninggalkan akar budaya</p>
              <p class="overlay-subtitle animate slide-left delay-5">Pedoman Kami</p>
            </div>
            <div class="image-content-slide animate slide delay-5" 
                style="background-image: url('<?php echo $base; ?>/uploads/vision-image.jpg')"></div>
            <div class="dots-slide animate">
              <div class="dot-slide animate slide-up delay-6"></div>
              <div class="dot-slide animate slide-up delay-7"></div>
              <div class="dot-slide animate slide-up delay-8"></div>
            </div>
          </div>
          <div class="text-content-slide">
            <h2 class="text-title"><i class="fas fa-bullseye"></i> Visi & Misi</h2>
            
            <h3 class="text-title" style="font-size: 1.4rem; margin-top: 1rem;"><i class="fas fa-eye"></i> <?php echo t('vision'); ?></h3>
            <p class="text-paragraph"><?php echo t('vision_text'); ?></p>
            
            <h3 class="text-title" style="font-size: 1.4rem; margin-top: 1.5rem;"><i class="fas fa-target"></i> <?php echo t('mission'); ?></h3>
            <ul style="color: var(--muted-text); line-height: 1.7; padding-left: 1.5rem;">
              <?php foreach (get_mission_items() as $index => $mission_item): ?>
              <li style="margin-bottom: 0.8rem;"><?php echo $mission_item; ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <!-- Structure Card (Hidden by default) -->
        <div class="card-slide-container animate pop" id="structure-card" style="display: none;">
          <div class="overlay-slide">
            <div class="overlay-content-slide animate slide-left delay-2">
              <h1 class="overlay-title animate slide-left delay-4">Struktur</h1>
              <p class="overlay-subtitle animate slide-left delay-5">Struktur organisasi kami berlandaskan “menyama braya”, menumbuhkan kebersamaan dalam keberagaman, menghargai setiap suara</p>
              <p class="overlay-subtitle animate slide-left delay-5">Organisasi Kami</p>
            </div>
            <div class="image-content-slide animate slide delay-5" 
                style="background-image: url('<?php echo $base; ?>/uploads/structure-image.jpg')"></div>
            <div class="dots-slide animate">
              <div class="dot-slide animate slide-up delay-6"></div>
              <div class="dot-slide animate slide-up delay-7"></div>
              <div class="dot-slide animate slide-up delay-8"></div>
            </div>
          </div>
          <div class="text-content-slide structure-content-slide">
            <h2 class="text-title"><i class="fas fa-sitemap"></i> Struktur Organisasi</h2>
            
            <div class="structure-grid-slide">
              <!-- Advisor -->
              <div class="structure-item-slide">
                <h3 class="structure-title-slide"><i class="fas fa-users"></i> <?php echo t('advisor'); ?></h3>
                <div class="structure-members-slide">
                  <?php 
                  $advisor_names = get_structure_names('advisor_names');
                  $advisor_positions = get_structure_names('advisor_positions');
                  for ($i = 0; $i < count($advisor_names); $i++): 
                  ?>
                  <div class="member-slide">
                    <div class="member-avatar">
                      <?php echo substr($advisor_names[$i], 0, 1); ?>
                    </div>
                    <div class="member-info">
                      <div class="member-name-slide"><?php echo $advisor_names[$i]; ?></div>
                      <div class="member-position-slide"><?php echo $advisor_positions[$i]; ?></div>
                    </div>
                  </div>
                  <?php endfor; ?>
                </div>
              </div>

              <!-- Responsible Party -->
              <div class="structure-item-slide">
                <h3 class="structure-title-slide"><i class="fas fa-user-tie"></i> <?php echo t('responsible_party'); ?></h3>
                <div class="structure-members-slide">
                  <div class="member-slide">
                    <div class="member-avatar">
                      <?php echo substr(t('position_adat_village'), 0, 1); ?>
                    </div>
                    <div class="member-info">
                      <div class="member-name-slide"><?php echo t('position_adat_village'); ?></div>
                      <div class="member-position-slide"><?php echo t('position_adat_village'); ?></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Chairperson -->
              <div class="structure-item-slide">
                <h3 class="structure-title-slide"><i class="fas fa-crown"></i> <?php echo t('chairperson'); ?></h3>
                <div class="structure-members-slide">
                  <div class="member-slide">
                    <div class="member-avatar">
                      <?php echo substr(get_setting('chairperson_name', 'I NYOMAN OKA TRIADI'), 0, 1); ?>
                    </div>
                    <div class="member-info">
                      <div class="member-name-slide"><?php echo get_setting('chairperson_name', 'I NYOMAN OKA TRIADI'); ?></div>
                      <div class="member-position-slide"><?php echo t('position_adat_leader'); ?></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Secretary & Treasurer -->
              <div class="structure-item-slide">
                <h3 class="structure-title-slide"><i class="fas fa-users-cog"></i> <?php echo t('secretary_and_treasurer'); ?></h3>
                <div class="structure-members-slide">
                  <div class="member-slide">
                    <div class="member-avatar">
                      <?php echo substr(get_setting('secretary_name', 'I MADE SUKARATA'), 0, 1); ?>
                    </div>
                    <div class="member-info">
                      <div class="member-name-slide"><?php echo get_setting('secretary_name', 'I MADE SUKARATA'); ?></div>
                      <div class="member-position-slide"><?php echo t('position_secretary'); ?></div>
                    </div>
                  </div>
                  <div class="member-slide">
                    <div class="member-avatar">
                      <?php echo substr(get_setting('treasurer_name', 'NI PUTU DESY ANGGRAENI'), 0, 1); ?>
                    </div>
                    <div class="member-info">
                      <div class="member-name-slide"><?php echo get_setting('treasurer_name', 'NI PUTU DESY ANGGRAENI'); ?></div>
                      <div class="member-position-slide"><?php echo t('position_treasurer'); ?></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Members - Guides -->
              <div class="structure-item-slide">
                <h3 class="structure-title-slide"><i class="fas fa-map-signs"></i> <?php echo t('position_guide'); ?></h3>
                <div class="structure-members-slide">
                  <?php 
                  $guide_names = get_structure_names('guide_names');
                  foreach ($guide_names as $guide_name): 
                  ?>
                  <div class="member-slide">
                    <div class="member-avatar">
                      <?php echo substr($guide_name, 0, 1); ?>
                    </div>
                    <div class="member-info">
                      <div class="member-name-slide"><?php echo $guide_name; ?></div>
                      <div class="member-position-slide"><?php echo t('position_guide'); ?></div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- Members - Others -->
              <div class="structure-item-slide">
                <span class="structure-level-slide"><?php echo t('member'); ?></span>
                <h3 class="structure-title-slide"><i class="fas fa-binoculars"></i> <?php echo t('position_observer'); ?></h3>
                <div class="structure-members-slide">
                  <?php 
                  $observer_names = get_structure_names('observer_names');
                  foreach ($observer_names as $observer_name): 
                  ?>
                  <div class="member-slide">
                    <div class="member-avatar">
                      <?php echo substr($observer_name, 0, 1); ?>
                    </div>
                    <div class="member-info">
                      <div class="member-name-slide"><?php echo $observer_name; ?></div>
                      <div class="member-position-slide"><?php echo t('position_observer'); ?></div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <script>
    // JavaScript untuk tab functionality
    document.addEventListener('DOMContentLoaded', function() {
      const tabButtons = document.querySelectorAll('.tab-btn-slide');
      const cards = {
        'history': document.getElementById('history-card'),
        'background': document.getElementById('background-card'),
        'vision-mission': document.getElementById('vision-mission-card'),
        'structure': document.getElementById('structure-card')
      };

      // Tab button click
      tabButtons.forEach(button => {
        button.addEventListener('click', function() {
          const tabName = this.getAttribute('data-tab');
          
          // Remove active class from all buttons
          tabButtons.forEach(btn => btn.classList.remove('active'));
          // Add active class to clicked button
          this.classList.add('active');
          
          // Hide all cards
          Object.values(cards).forEach(card => {
            if (card) card.style.display = 'none';
          });
          
          // Show selected card
          if (cards[tabName]) {
            cards[tabName].style.display = 'flex';
          }
        });
      });
      
      // Set default active tab
      document.querySelector('.tab-btn-slide.active').click();
    });
    </script>

    <!-- Wisata Section -->
    <section id="wisata" class="wisata-section-new">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"><?php echo t('tourism_sec'); ?></h2>
          <p class="section-subtitle"><?php echo t('tourism_subtitle'); ?></p>
        </div>

        <div class="wisata-grid-new">
          <?php if (empty($wisata_data)): ?>
            <div style="grid-column: 1 / -1; text-align: center; color: var(--muted-text); padding: 40px;">
              <i class="fas fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 1rem;"></i>
              <p><?php echo t('no_tourism'); ?></p>
            </div>
          <?php else: ?>
            <?php foreach ($wisata_data as $wisata): ?>
            <article class="wisata-card-new">
              <img
                class="card__background"
                src="<?php echo $wisata['gambar'] ? public_url($wisata['gambar']) : 'https://source.unsplash.com/random/600x400/?bali'; ?>"
                alt="<?php echo htmlspecialchars($wisata['judul']); ?>"
              />
              <div class="card__content">
                <h2 class="card__title"><?php echo htmlspecialchars($wisata['judul']); ?></h2>
                
                <!-- ELEMEN YANG MUNCUL SAAT HOVER -->
                <div class="card__hover-content">
                  <div class="card__meta-grid">
                    <div class="card__meta-item">
                      <span class="card__meta-label">Waktu</span>
                      <span class="card__meta-value">
                        <i class="fas fa-calendar-day"></i>
                        <?php echo ucfirst($wisata['waktu']); ?>
                      </span>
                    </div>
                    <div class="card__meta-item">
                      <span class="card__meta-label">Jam</span>
                      <span class="card__meta-value">
                        <i class="fas fa-clock"></i>
                        <?php echo date('H:i', strtotime($wisata['jam'])); ?>
                      </span>
                    </div>
                  </div>
                  <a href="detail_wisata.php?id=<?php echo $wisata['id_wisata']; ?>" class="card__button">
                    <?php echo t('view_details'); ?>
                    <i class="fas fa-arrow-right"></i>
                  </a>
                </div>
              </div>
            </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php $total_pages_w = (int)ceil($total_wisata_home / $per_page_wisata); if ($total_pages_w > 1): ?>
        <div class="wisata-pagination">
          <?php if ($page_wisata > 1): ?>
            <a href="?p_w=<?php echo ($page_wisata - 1); ?>#wisata" class="page-nav" title="Previous">
              <i class="fas fa-chevron-left"></i> Prev
            </a>
          <?php endif; ?>

          <div class="page-numbers">
            <?php for ($p = 1; $p <= $total_pages_w; $p++): ?>
              <?php if ($p == $page_wisata): ?>
                <span class="active"><?php echo $p; ?></span>
              <?php else: ?>
                <a href="?p_w=<?php echo $p; ?>#wisata"><?php echo $p; ?></a>
              <?php endif; ?>
            <?php endfor; ?>
          </div>

          <?php if ($page_wisata < $total_pages_w): ?>
            <a href="?p_w=<?php echo ($page_wisata + 1); ?>#wisata" class="page-nav" title="Next">
              Next <i class="fas fa-chevron-right"></i>
            </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Mitra/Partners Section -->
    <section id="mitra" class="partners-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"></i> Mitra Kami</h2>
          <p class="section-subtitle">Dukungan dari Pemerintahan dan Yayasan</p>
        </div>
        <div class="partners-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 20px; justify-items: center; align-items: center;">
          <?php if (!empty($mitra_data)): ?>
            <?php foreach ($mitra_data as $mitra): ?>
              <div class="partners-card" style="width: 100%; max-width: 200px; display: flex; align-items: center; justify-content: center;">
                <a href="<?php echo !empty($mitra['link_partner']) ? htmlspecialchars($mitra['link_partner']) : '#'; ?>" 
                   <?php echo !empty($mitra['link_partner']) ? 'target="_blank" rel="noopener"' : ''; ?>
                   style="display: block; width: 100%; height: 100%;">
                  <img src="<?php echo public_url($mitra['gambar']); ?>" 
                       alt="<?php echo htmlspecialchars($mitra['nama']); ?>" 
                       style="width: 100%; height: auto; object-fit: contain; padding: 15px; max-height: 120px;" />
                </a>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; color: var(--muted-text); padding: 40px;">
              <i class="fas fa-handshake" style="font-size: 3rem; margin-bottom: 1rem;"></i>
              <p>Belum ada data mitra yang tersedia</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Galeri Section -->
    <section id="galeri" class="gallery-section-new">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"><?php echo t('gallery_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('gallery_subtitle'); ?></p>
        </div>

        <div id="image-gallery">
          <div class="gallery-row">
            <?php
            // Pagination untuk galeri
            $per_page_galeri = 4;
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
              <div style="grid-column: 1 / -1; text-align: center; color: rgba(255,255,255,0.7); padding: 40px;">
                <i class="fas fa-images" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p><?php echo t('no_gallery_images'); ?></p>
              </div>
            <?php else: ?>
              <?php foreach ($galeri_data as $galeri): ?>
              <div class="image">
                <div class="img-wrapper">
                  <a href="<?php echo $galeri['gambar'] ? public_url($galeri['gambar']) : 'https://source.unsplash.com/random/600x400/?bali'; ?>">
                    <img src="<?php echo $galeri['gambar'] ? public_url($galeri['gambar']) : 'https://source.unsplash.com/random/600x400/?bali'; ?>" 
                        alt="<?php echo htmlspecialchars($galeri['judul']); ?>" 
                        class="img-responsive" />
                  </a>
                  <div class="img-overlay">
                    <i class="fas fa-plus-circle" aria-hidden="true"></i>
                  </div>

                  <div class="img-overlay-content">
                    <h4 class="image-title"><?php echo htmlspecialchars($galeri['judul']); ?></h4>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <?php 
        $total_pages_g = (int)ceil($total_galeri / $per_page_galeri); 
        if ($total_pages_g > 1): 
        ?>
        <div class="gallery-pagination">
          <?php if ($page_galeri > 1): ?>
            <a href="?p_g=<?php echo ($page_galeri - 1); ?>#galeri" class="page-nav" title="Previous">
              <i class="fas fa-chevron-left"></i> Prev
            </a>
          <?php endif; ?>

          <div class="page-numbers">
            <?php for ($p = 1; $p <= $total_pages_g; $p++): ?>
              <?php if ($p == $page_galeri): ?>
                <span class="active"><?php echo $p; ?></span>
              <?php else: ?>
                <a href="?p_g=<?php echo $p; ?>#galeri"><?php echo $p; ?></a>
              <?php endif; ?>
            <?php endfor; ?>
          </div>

          <?php if ($page_galeri < $total_pages_g): ?>
            <a href="?p_g=<?php echo ($page_galeri + 1); ?>#galeri" class="page-nav" title="Next">
              Next <i class="fas fa-chevron-right"></i>
            </a>
          <?php endif; ?>
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
          <div>
            <h3 class="contact-title"><?php echo t('contact_info'); ?></h3>
            <div class="contact-list">
            <div class="contact-item">
            <div class="contact-icon">
            <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="contact-content">
            <h4 class="item-title"><?php echo t('address'); ?></h4>
            <p class="item-text"><?php echo get_setting('address', 'Desa Adat Tingkihkerep, Desa Tengkudak, Kecamatan Penebel, Kabupaten Tabanan, Bali'); ?></p>
            </div>
            </div>
            
            <div class="contact-item">
            <div class="contact-icon">
            <i class="fab fa-whatsapp"></i>
            </div>
            <div class="contact-content">
            <h4 class="item-title"><?php echo t('phone'); ?></h4>
            <?php $wa = preg_replace('/\D+/', '', get_setting('contact_phone', '083862519604')); ?>
            <p class="item-text">
            <?php echo get_setting('contact_person', 'I Wayan Yudi Artana'); ?> (<?php echo get_setting('contact_phone', '083862519604'); ?>)
            <br>
            <a class="contact-whatsapp" href="https://wa.me/<?php echo (strpos($wa,'62')===0?$wa:'62'.$wa); ?>" target="_blank" rel="noopener">
            <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
            </p>
            </div>
            </div>
            
            <div class="contact-item">
            <div class="contact-icon">
            <i class="fas fa-clock"></i>
            </div>
            <div class="contact-content">
            <h4 class="item-title"><?php echo t('operational_hours'); ?></h4>
            <p class="item-text"><?php echo t('everyday'); ?>: 08:00 - 17:00 WITA</p>
            <p class="item-text" style="margin-top: 0.3rem; font-size: 0.85rem; color: var(--teal-blue);">
            <i class="fas fa-info-circle"></i> <?php echo t('open_daily'); ?>
            </p>
            </div>
            </div>
            </div>
          </div>

          <div class="social-links">
            <h4 class="social-title">Ikuti Kami</h4>
            <div class="social-grid">
              <!-- Instagram & Facebook in one row -->
              <div class="social-row double">
                <a href="<?php echo get_setting('social_instagram', 'https://instagram.com/kampoengjalakbali/'); ?>" target="_blank" class="social-link"> 
                  <i class="fab fa-instagram"></i>@kampoengjalakbali
                </a>
                <a href="<?php echo get_setting('social_facebook', '#'); ?>" class="social-link"> 
                  <i class="fab fa-facebook-f"></i>Kampoeng Jalak Bali
                </a>
              </div>
              <!-- Email in separate row -->
              <div class="social-row single">
                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo get_setting('contact_email', 'kampoengjalakbali@gmail.com'); ?>" class="social-link"> 
                  <i class="fas fa-envelope"></i><?php echo get_setting('contact_email', 'kampoengjalakbali@gmail.com'); ?> 
                </a>
              </div>
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

            <!-- Cloudflare DI DALAM FORM -->
            <div class="turnstile-container">
              <div class="cf-turnstile" 
                  data-sitekey="0x4AAAAAACDNwsHDioXb3RuZ" 
                  data-theme="light"></div>
            </div>

            <button type="submit" class="form-button">
              <?php echo t('send_message'); ?>
              <i class="fas fa-paper-plane"></i>
            </button>
          </form>
        </div>
      </div>

      <!-- Map Full Width -->
      <div class="map-wrapper-full">
        <iframe 
          class="map-iframe-full"
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3946.745252189239!2d115.11536599999998!3d-8.4266598!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd225006bc21dfd%3A0x485a7470e57deebc!2sKampoeng%20Jalak%20Bali!5e0!3m2!1sid!2sid!4v1764225479471!5m2!1sid!2sid" 
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

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
  $(document).ready(function() {
    // Inisialisasi lightbox hanya sekali
    if ($('#overlay').length === 0) {
      var $overlay = $('<div id="overlay"></div>');
      var $image = $("<img>");
      var $prevButton = $('<div id="prevButton"><i class="fas fa-chevron-left"></i></div>');
      var $nextButton = $('<div id="nextButton"><i class="fas fa-chevron-right"></i></div>');
      var $exitButton = $('<div id="exitButton"><i class="fas fa-times"></i></div>');

      $overlay.append($image).prepend($prevButton).append($nextButton).append($exitButton);
      $("body").append($overlay);
    }

    // Sembunyikan overlay di awal
    $('#overlay').hide();

    // Hover effect
    $(".img-wrapper").hover(
      function() {
        $(this).find(".img-overlay").stop().animate({opacity: 1}, 600);
      }, function() {
        $(this).find(".img-overlay").stop().animate({opacity: 0}, 600);
      }
    );

    // Variabel untuk navigasi
    var currentImageIndex = 0;
    var totalImages = $('.image').length;

    // Fungsi untuk menampilkan gambar di lightbox
    function showImage(index) {
      var $targetImage = $('.image').eq(index).find('a').attr('href');
      $('#overlay img').attr('src', $targetImage);
      currentImageIndex = index;
    }

    // Klik overlay gambar
    $(".img-overlay").click(function(event) {
      event.preventDefault();
      event.stopPropagation();
      
      var imageLocation = $(this).prev().attr("href");
      if (imageLocation) {
        // Cari index gambar yang diklik
        currentImageIndex = $(this).closest('.image').index();
        showImage(currentImageIndex);
        $('#overlay').fadeIn("slow");
        $("body").css("overflow", "hidden");
      }
    });

    // Next image
    $('#nextButton').click(function(event) {
      event.stopPropagation();
      currentImageIndex = (currentImageIndex + 1) % totalImages;
      showImage(currentImageIndex);
    });

    // Previous image
    $('#prevButton').click(function(event) {
      event.stopPropagation();
      currentImageIndex = (currentImageIndex - 1 + totalImages) % totalImages;
      showImage(currentImageIndex);
    });

    // Exit lightbox
    $('#exitButton').click(function(event) {
      event.stopPropagation();
      $('#overlay').fadeOut("slow");
      $("body").css("overflow", "auto");
    });

    // Klik background untuk close
    $('#overlay').click(function(event) {
      if (event.target === this) {
        $(this).fadeOut("slow");
        $("body").css("overflow", "auto");
      }
    });

    // ESC key untuk close
    $(document).keyup(function(e) {
      if (e.keyCode === 27) {
        $('#overlay').fadeOut("slow");
        $("body").css("overflow", "auto");
      }
      // Panah kiri/kanan untuk navigasi
      else if (e.keyCode === 37) { // Left arrow
        $('#prevButton').click();
      }
      else if (e.keyCode === 39) { // Right arrow
        $('#nextButton').click();
      }
    });
  });
  </script>
  </body>
</html>
<?php 
// HAPUS INI: mysqli_close($koneksi); 
?>