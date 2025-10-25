<?php
session_start();
include 'koneksi.php';

// Ambil data wisata dari database
$query_wisata = "SELECT * FROM wisata ORDER BY tanggal_ditambahkan DESC";
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
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kampung Jalak Bali - Destinasi Wisata Edukasi di Bali</title>
  </head>
  <body>
    <!-- Header -->
    <header>
      <div>
        <div>
          <img src="uploads/Rancangan Logo.png" alt="Logo Kampung Jalak Bali" width="500" />
          <h1>Kampung Jalak Bali</h1>
        </div>
        <nav>
          <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="#tentang">Tentang</a></li>
            <li><a href="#wisata">Wisata</a></li>
            <li><a href="#galeri">Galeri</a></li>
            <li><a href="#kontak">Kontak</a></li>
            <?php if (isLoggedIn()): ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li>
              <a href="logout.php">Logout (<?php echo $_SESSION['nama']; ?>)</a>
            </li>
            <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
      <div class="hero-container">
        <div class="hero-content">
          <h1 class="hero-title">Selamat Datang di Kampung Jalak Bali</h1>
          <p class="hero-description">Destinasi wisata edukasi yang memukau di Pulau Dewata, menawarkan pengalaman unik tentang konservasi burung Jalak Bali dan budaya lokal.</p>
          <a href="#wisata" class="hero-button">Jelajahi Sekarang</a>
        </div>
      </div>
    </section>

    <!-- Tentang Section -->
    <section id="tentang" class="about-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Tentang Kampung Jalak Bali</h2>
        </div>

        <div class="about-content">
          <div class="content-main">
            <div class="text-content">
              <p class="lead-text">
                Kampoeng Jalak Bali adalah sebuah pusat konservasi ex-situ bagi Burung Jalak Bali, satwa endemik yang dilindungi, yang terletak di Banjar Tingkihkerep, Desa Tengkudak, Tabanan, Bali. Inisiatif ini merupakan perwujudan nyata
                dari kearifan lokal Bali, khususnya filosofi Tri Hita Karana yang menekankan keharmonisan antara manusia, Tuhan, dan alam.
              </p>

              <div class="content-section">
                <h3 class="content-title">Sejarah dan Latar Belakang</h3>
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
                      <h4 class="card-title">Visi</h4>
                    </div>
                    <p class="vision-text">Mewujudkan desa konservasi yang harmonis antara manusia, alam, dan budaya melalui pelestarian Jalak Bali sebagai warisan satwa endemik Pulau Bali.</p>
                  </div>

                  <div class="mission-card">
                    <div class="card-header">
                      <div class="icon-wrapper">
                        <i class="icon-checklist"></i>
                      </div>
                      <h4 class="card-title">Misi</h4>
                    </div>
                    <ul class="mission-list">
                      <li class="mission-item">
                        <span class="item-number">1</span>
                        <span class="item-text">Menyelenggarakan konservasi Jalak Bali berbasis partisipasi masyarakat.</span>
                      </li>
                      <li class="mission-item">
                        <span class="item-number">2</span>
                        <span class="item-text">Menguatkan peran adat dan budaya dalam menjaga kelestarian alam.</span>
                      </li>
                      <li class="mission-item">
                        <span class="item-number">3</span>
                        <span class="item-text">Meningkatkan kesadaran dan pendidikan lingkungan bagi warga dan generasi muda.</span>
                      </li>
                      <li class="mission-item">
                        <span class="item-number">4</span>
                        <span class="item-text">Mengembangkan potensi ekowisata berbasis konservasi dan budaya lokal.</span>
                      </li>
                      <li class="mission-item">
                        <span class="item-number">5</span>
                        <span class="item-text">Membangun kemitraan dengan lembaga konservasi, pemerintah, dan pihak swasta.</span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div class="structure-section">
              <h3 class="content-title">Struktur Kepengurusan</h3>
              <div class="structure-grid">
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
          <h2 class="section-title">Wisata Edukasi</h2>
          <p class="section-subtitle">Jelajahi pengalaman unik konservasi dan budaya di Kampung Jalak Bali</p>
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
                  <?php echo $wisata['durasi']; ?>
                </span>
              </div>
              <a href="detail_wisata.php?id=<?php echo $wisata['id_wisata']; ?>" class="card-button">
                Selengkapnya
                <i class="icon-arrow"></i>
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Galeri Section -->
    <section id="galeri" class="gallery-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Galeri</h2>
          <p class="section-subtitle">Momen-momen indah di Kampung Jalak Bali</p>
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
          <a href="galeri.php" class="gallery-button">Lihat Semua Galeri</a>
        </div>
      </div>
    </section>

    <!-- Modal untuk zoom image -->
    <div id="galleryModal" class="modal">
      <div class="modal-content">
        <span class="close-button">&times;</span>
        <img id="modalImage" src="" alt="" class="modal-image" />
        <h3 id="modalTitle" class="modal-title"></h3>
      </div>
    </div>

    <!-- Kontak Section -->
    <section id="kontak" class="contact-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Kontak Kami</h2>
          <p class="section-subtitle">Hubungi kami untuk informasi lebih lanjut tentang Kampung Jalak Bali</p>
        </div>

        <div class="contact-grid">
          <!-- Informasi Kontak -->
          <div class="contact-info">
            <h3 class="contact-title">Informasi Kontak</h3>
            <div class="contact-list">
              <div class="contact-item">
                <div class="contact-icon">
                  <i class="icon-location"></i>
                </div>
                <div class="contact-content">
                  <h4 class="item-title">Alamat</h4>
                  <p class="item-text">Desa Adat Tingkihkerep, Desa Tengkudak, Kecamatan Penebel, Kabupaten Tabanan, Bali</p>
                </div>
              </div>

              <div class="contact-item">
                <div class="contact-icon">
                  <i class="icon-phone"></i>
                </div>
                <div class="contact-content">
                  <h4 class="item-title">Telepon</h4>
                  <p class="item-text">I Wayan Yudi Artana (083862519604)</p>
                </div>
              </div>
            </div>

            <div class="social-links">
              <h4 class="social-title">Follow Kami</h4>
              <div class="social-icons">
                <a href="https://instagram.com/kampoengjalakbali/" target="_blank" class="social-link"> <i class="icon-instagram"></i>@kampoengjalakbali </a>
                <a href="#" class="social-link"> <i class="icon-facebook"></i>Kampoeng Jalak Bali </a>
                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=kampoengjalakbali@gmail.com" class="social-link"> <i class="icon-email"></i>kampoengjalakbali@gmail.com </a>
              </div>
            </div>
          </div>

          <!-- Form Kontak -->
          <div class="contact-form">
            <h3 class="contact-title">Kirim Pesan</h3>
            <form method="POST" action="proses_kontak.php" class="form">
              <div class="form-group">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" id="nama" name="nama" class="form-input" required />
              </div>

              <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" required />
              </div>

              <div class="form-group">
                <label for="subjek" class="form-label">Subjek</label>
                <input type="text" id="subjek" name="subjek" class="form-input" required />
              </div>

              <div class="form-group">
                <label for="pesan" class="form-label">Pesan</label>
                <textarea id="pesan" name="pesan" class="form-textarea" rows="5" required></textarea>
              </div>

              <button type="submit" class="form-button">
                Kirim Pesan
                <i class="icon-send"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <!-- Brand Section -->
          <div class="footer-section">
            <h3 class="footer-title">Kampung Jalak Bali</h3>
            <p class="footer-description">Destinasi wisata edukasi yang memukau di Pulau Dewata, menawarkan pengalaman unik tentang konservasi burung Jalak Bali dan budaya lokal.</p>
          </div>

          <!-- Quick Links -->
          <div class="footer-section">
            <h3 class="footer-title">Menu Cepat</h3>
            <ul class="footer-links">
              <li><a href="#home" class="footer-link">Home</a></li>
              <li><a href="#tentang" class="footer-link">Tentang</a></li>
              <li><a href="#wisata" class="footer-link">Wisata</a></li>
              <li><a href="#galeri" class="footer-link">Galeri</a></li>
              <li><a href="#kontak" class="footer-link">Kontak</a></li>
              <?php if (isLoggedIn()): ?>
              <li><a href="dashboard.php" class="footer-link">Dashboard</a></li>
              <?php else: ?>
              <li><a href="login.php" class="footer-link">Login</a></li>
              <?php endif; ?>
            </ul>
          </div>

          <!-- Social Media -->
          <div class="footer-section">
            <h3 class="footer-title">Ikuti Kami</h3>
            <div class="social-links">
              <a href="#" class="social-link" title="Facebook">
                <i class="icon-facebook"></i>
                <span>Facebook</span>
              </a>
              <a href="#" class="social-link" title="Instagram">
                <i class="icon-instagram"></i>
                <span>Instagram</span>
              </a>
            </div>
          </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
          <p class="copyright">&copy; 2025 Kampung Jalak Bali. Semua Hak Dilindungi.</p>
        </div>
      </div>
    </footer>
  </body>
</html>
<?php mysqli_close($koneksi); ?>
