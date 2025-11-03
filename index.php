<?php
session_start();
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
          <img src="uploads/Rancangan Logo.png" alt="Logo Kampung Jalak Bali" width="500" />
          <h1>Kampung Jalak Bali</h1>
        </div>
        <nav>
          <ul>
            <li><a href="#home"><?php echo t('home'); ?></a></li>
            <li><a href="#tentang"><?php echo t('about'); ?></a></li>
            <li><a href="#wisata"><?php echo t('tourism'); ?></a></li>
            <li><a href="#informasi"><?php echo t('information'); ?></a></li>
            <li><a href="#galeri"><?php echo t('gallery'); ?></a></li>
            <li><a href="#produk"><?php echo t('products'); ?></a></li>
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
                        <i class="icon-target"></i>
                      </div>
                      <h4 class="card-title"><?php echo t('vision'); ?></h4>
                    </div>
                    <p class="vision-text"><?php echo t('vision_text'); ?></p>
                  </div>

                  <div class="mission-card">
                    <div class="card-header">
                      <div class="icon-wrapper">
                        <i class="icon-checklist"></i>
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
                  <i class="icon-clock"></i>
                  <?php echo t('duration'); ?>: <?php echo $wisata['durasi']; ?>
                </span>
              </div>
              <a href="detail_wisata.php?id=<?php echo $wisata['id_wisata']; ?>" class="card-button">
                <?php echo t('view_details'); ?>
                <i class="icon-arrow"></i>
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

    <!-- Informasi Section -->
    <section id="informasi" class="informasi-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"><?php echo t('information_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('information_subtitle'); ?></p>
        </div>

        <div class="informasi-grid">
          <?php
          $query_informasi = "SELECT * FROM informasi ORDER BY tanggal_dibuat DESC LIMIT 3";
          $result_informasi = mysqli_query($koneksi, $query_informasi);
          $informasi_data = mysqli_fetch_all($result_informasi, MYSQLI_ASSOC);
          
          foreach ($informasi_data as $informasi):
          ?>
          <div class="informasi-card">
            <div class="card-content">
              <span class="card-category"><?php echo t($informasi['kategori']); ?></span>
              <h3 class="card-title"><?php echo $informasi['judul']; ?></h3>
              <p class="card-excerpt"><?php echo substr(strip_tags($informasi['isi']), 0, 150) . '...'; ?></p>
              <div class="card-meta">
                <span class="meta-date"><?php echo date('d M Y', strtotime($informasi['tanggal_dibuat'])); ?></span>
              </div>
              <a href="informasi.php" class="card-button"><?php echo t('read_more'); ?></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="informasi-actions">
          <a href="informasi.php" class="informasi-button"><?php echo t('see_all'); ?> <?php echo t('information'); ?></a>
        </div>
      </div>
    </section>

    <!-- Produk Section -->
    <section id="produk" class="produk-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title"><?php echo t('products_title'); ?></h2>
          <p class="section-subtitle"><?php echo t('products_subtitle'); ?></p>
        </div>

        <div class="produk-grid">
          <?php
          $query_produk = "SELECT * FROM produk ORDER BY tanggal_ditambahkan DESC LIMIT 3";
          $result_produk = mysqli_query($koneksi, $query_produk);
          $produk_data = mysqli_fetch_all($result_produk, MYSQLI_ASSOC);
          
          foreach ($produk_data as $produk):
          ?>
          <div class="produk-card">
            <div class="card-image">
              <img src="<?php echo $produk['gambar'] ?: 'https://source.unsplash.com/random/300x200/?merchandise'; ?>" alt="<?php echo $produk['nama']; ?>" />
            </div>
            <div class="card-content">
              <h3 class="card-title"><?php echo $produk['nama']; ?></h3>
              <p class="card-description"><?php echo $produk['deskripsi']; ?></p>
              <div class="card-meta">
                <span class="meta-price"><?php echo t('price'); ?>: Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></span>
                <span class="meta-stock"><?php echo t('stock'); ?>: <?php echo $produk['stok']; ?></span>
              </div>
              <button class="card-button">
                <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="card-button">
                  📱 <?php echo t('book_now'); ?>
                </a>
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="produk-actions">
          <a href="produk.php" class="produk-button"><?php echo t('see_all'); ?> <?php echo t('products'); ?></a>
        </div>
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
                  <button class="view-button" onclick="openModal('<?php echo $galeri['gambar']; ?>', '<?php echo $galeri['judul']; ?>')">
                    <i class="icon-zoom"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="gallery-actions">
          <a href="galeri.php" class="gallery-button"><?php echo t('view_gallery'); ?></a>
        </div>
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
                  <i class="icon-location"></i>
                </div>
                <div class="contact-content">
                  <h4 class="item-title"><?php echo t('address'); ?></h4>
                  <p class="item-text">Desa Adat Tingkihkerep, Desa Tengkudak, Kecamatan Penebel, Kabupaten Tabanan, Bali</p>
                </div>
              </div>

              <div class="contact-item">
                <div class="contact-icon">
                  <i class="icon-phone"></i>
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
                <a href="https://instagram.com/kampoengjalakbali/" target="_blank" class="social-link"> <i class="icon-instagram"></i>@kampoengjalakbali </a>
                <a href="#" class="social-link"> <i class="icon-facebook"></i>Kampoeng Jalak Bali </a>
                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=kampoengjalakbali@gmail.com" class="social-link"> <i class="icon-email"></i>kampoengjalakbali@gmail.com </a>
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
                <i class="icon-send"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Location Section -->
    <section id="location" style="padding: 40px 0;">
      <div>
        <div style="text-align: center; margin-bottom: 30px;">
          <h2><?php echo t('location_title'); ?></h2>
          <p><?php echo t('location_subtitle'); ?></p>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
          <div>
            <h3>Kampung Jalak Bali</h3>
            <p>Desa Adat Tingkihkerep, Desa Tengkudak, Kecamatan Penebel, Kabupaten Tabanan, Bali</p>
            
            <div>
              <div><strong>Telepon:</strong> I Wayan Yudi Artana (083862519604)</div>
              <div><strong>Email:</strong> kampoengjalakbali@gmail.com</div>
              <div><strong>Instagram:</strong> @kampoengjalakbali</div>
            </div>
          </div>
          
          <div>
            <iframe 
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3945.447676234625!2d115.09547427579436!3d-8.506537491489967!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd23ba60f56f36f%3A0x9ac1cda35155124c!2sDesa%20Tengkudak%2C%20Kec.%20Penebel%2C%20Kabupaten%20Tabanan%2C%20Bali!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid" 
              width="100%" 
              height="300" 
              style="border:0; border-radius: 8px;" 
              allowfullscreen="" 
              loading="lazy" 
              referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
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
            <img src="uploads/supporter/fnpf-logo.png" alt="FNPF" width="100" 
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
                onerror="this.src='https://via.placeholder.com/100x60/1a6b3b/ffffff?text=Pemprov+Bali'">
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
              <li><a href="#informasi" class="footer-link"><?php echo t('information'); ?></a></li>
              <li><a href="#galeri" class="footer-link"><?php echo t('gallery'); ?></a></li>
              <li><a href="#produk" class="footer-link"><?php echo t('products'); ?></a></li>
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