<?php
session_start();
include __DIR__ . '/config/koneksi.php';
include __DIR__ . '/config/language.php';

// compute base URL (site root)
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

function public_url($path) {
  global $base;
  if (empty($path)) return '';
  if (preg_match('#^https?://#i', $path) || strpos($path, '/') === 0) return $path;
  return $base . '/' . ltrim($path, '/');
}

// Security
define('ALLOWED', true);

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

$wisata_id = $_GET['id'] ?? 1;

// Ambil data wisata
$query_wisata = "SELECT * FROM wisata WHERE id_wisata = ?";
$stmt = mysqli_prepare($koneksi, $query_wisata);
mysqli_stmt_bind_param($stmt, "i", $wisata_id);
mysqli_stmt_execute($stmt);
$result_wisata = mysqli_stmt_get_result($stmt);
$wisata = mysqli_fetch_assoc($result_wisata);

if (!$wisata) {
    header("Location: index.php");
    exit();
}

// Ambil komentar untuk wisata ini dengan pagination
$per_page_k = 5;
$page_k = isset($_GET['page_komen']) ? max(1, (int)$_GET['page_komen']) : 1;
$offset_k = ($page_k - 1) * $per_page_k;

// Hitung total komentar per wisata
$qcnt = "SELECT COUNT(*) as cnt FROM komentar WHERE id_wisata = ?";
$stcnt = mysqli_prepare($koneksi, $qcnt);
mysqli_stmt_bind_param($stcnt, "i", $wisata_id);
mysqli_stmt_execute($stcnt);
$rescnt = mysqli_stmt_get_result($stcnt);
$total_komen = mysqli_fetch_assoc($rescnt)['cnt'];

$query_komentar = "SELECT k.*, u.nama FROM komentar k 
                   JOIN user u ON k.id_user = u.id_user 
                   WHERE k.id_wisata = ? 
                   ORDER BY k.tanggal DESC
                   LIMIT $per_page_k OFFSET $offset_k";
$stmt_komentar = mysqli_prepare($koneksi, $query_komentar);
mysqli_stmt_bind_param($stmt_komentar, "i", $wisata_id);
mysqli_stmt_execute($stmt_komentar);
$result_komentar = mysqli_stmt_get_result($stmt_komentar);
$komentar_data = mysqli_fetch_all($result_komentar, MYSQLI_ASSOC);

// Set page info
$pageTitle = $wisata['judul'] . ' | ' . get_setting('site_title', 'Kampoeng Jalak Bali');
$currentPage = 'wisata';
?>

<!DOCTYPE html>
<html lang="<?php echo ($_SESSION['language'] ?? 'id'); ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($wisata['judul']); ?> | <?php echo get_setting('site_title', 'Kampoeng Jalak Bali'); ?></title>
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/pages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head>
  <body>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <section class="content-section bg-light">
      <div class="container">
        
        <!-- Ganti bagian setelah breadcrumb dengan kode berikut -->

        <div class="wisata-detail-new">
  
        <div class="breadcrumb-new">
          <a href="<?php echo $base; ?>/index.php" title="Home">
            <i class="fas fa-home"></i> <?php echo t('home'); ?>
          </a>
          <span class="separator">/</span>
          <a href="<?php echo $base; ?>/index.php#wisata" title="Wisata">
            <i class="fas fa-map-marked-alt"></i> <?php echo t('tourism'); ?>
          </a>
          <span class="separator">/</span>
          <span class="current" title="<?php echo htmlspecialchars($wisata['judul']); ?>">
            <?php echo htmlspecialchars($wisata['judul']); ?>
          </span>
        </div>

        <div class="wisata-container">
          
          <div class="wisata-content">
            <div>
              <h1><?php echo htmlspecialchars($wisata['judul']); ?></h1>
              
              <div class="wisata-description-new">
                  <?php echo nl2br(htmlspecialchars($wisata['deskripsi'])); ?>
              </div>

              <!-- Dekorasi Ikon Matahari untuk Meminimalkan Ruang Kosong -->
              <div class="wisata-decoration">
                  <i class="fas fa-sun"></i>
</div>

            </div>
            
            <div class="wisata-control">
              <a href="<?php echo $base; ?>/index.php#kontak" class="wisata-btn">
                <span class="location-icon"><i class="fas fa-map-location-dot"></i></span>
                <span class="visit-text"><?php echo t('visit_us'); ?></span>
              </a>
            </div>
          </div>
          
          <div class="wisata-image-container">
            <img 
              src="<?php echo $wisata['gambar'] ? public_url($wisata['gambar']) : 'https://source.unsplash.com/random/900x600/?bali'; ?>" 
              alt="<?php echo htmlspecialchars($wisata['judul']); ?>" 
              loading="lazy" />
            
            <div class="wisata-info-overlay">
              <h2>Informasi Wisata</h2>
              <ul>
                <li><strong>Waktu:</strong> <span><?php echo ucfirst($wisata['waktu']); ?></span></li>
                <li><strong>Jam:</strong> <span><?php echo date('H:i', strtotime($wisata['jam'])); ?></span></li>
              </ul>
            </div>
          </div>
        </div>

          <!-- Comments Section -->
          <div class="comments-section" id="comments">
            
            <!-- Section Header -->
            <div class="section-header">
              <h3 class="section-title">
                <i class="fas fa-comments"></i> 
                <?php echo t('comments'); ?>
                <span class="comment-count"><?php echo count($komentar_data); ?></span>
              </h3>
            </div>

            <!-- Comment Form Card -->
            <div class="comment-form-card">
              <?php if (isLoggedIn()): ?>
                
                <!-- Form for Logged In Users -->
                <form method="POST" action="<?php echo $base; ?>/processes/proses_komentar.php" class="comment-form">
                  <input type="hidden" name="wisata_id" value="<?php echo (int)$wisata_id; ?>" />
                  
                  <div class="form-group">
                    <label for="komentar">
                      <span>
                        <i class="fas fa-comment"></i>
                        <?php echo t('write_comment'); ?>
                      </span>
                      <span class="user-name">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['nama']); ?>
                      </span>
                    </label>
                    <textarea 
                      id="komentar" 
                      name="komentar" 
                      class="form-control" 
                      placeholder="<?php echo t('write_comment'); ?>" 
                      rows="4"
                      required></textarea>
                  </div>
                  
                  <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> <?php echo t('post_comment'); ?>
                  </button>
                </form>
                
              <?php else: ?>
                
                <!-- Login Prompt for Guests -->
                <div class="login-prompt">
                  <i class="fas fa-lock"></i>
                  <p><?php echo t('login_to_comment'); ?></p>
                  <a href="<?php echo $base; ?>/auth/login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> <?php echo t('login'); ?>
                  </a>
                </div>
                
              <?php endif; ?>
            </div>

            <!-- Comments List -->
            <div class="comments-list">
              
              <?php if (empty($komentar_data)): ?>
                
                <!-- Empty Comments State -->
                <div class="no-comments">
                  <i class="fas fa-comment-slash"></i>
                  <p><?php echo t('no_comments_yet'); ?></p>
                </div>
                
              <?php else: ?>
                
                <!-- Comments Iteration -->
                <?php foreach ($komentar_data as $komentar): ?>
                  <div class="comment-card">
                    
                    <!-- Comment Header -->
                    <div class="comment-header">
                      <div class="comment-user">
                        <div class="user-avatar">
                          <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="user-info">
                          <strong class="user-name">
                            <?php echo htmlspecialchars($komentar['nama']); ?>
                          </strong>
                          <span class="comment-date">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('d M Y H:i', strtotime($komentar['tanggal'])); ?>
                          </span>
                        </div>
                      </div>
                      
                      <!-- Comment Actions -->
                      <?php if (isAdmin() || (isLoggedIn() && $_SESSION['user_id'] == $komentar['id_user'])): ?>
                        <div class="comment-actions">
                          <a 
                            href="<?php echo $base; ?>/admin/proses/hapus_komentar.php?id=<?php echo (int)$komentar['id_komentar']; ?>&wisata_id=<?php echo (int)$wisata_id; ?>" 
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('<?php echo addslashes(t('confirm_delete')); ?>')">
                            <i class="fas fa-trash"></i> <?php echo t('delete'); ?>
                          </a>
                        </div>
                      <?php endif; ?>
                    </div>
                    
                    <!-- Comment Content -->
                    <div class="comment-content">
                      <?php echo nl2br(htmlspecialchars($komentar['isi'])); ?>
                    </div>
                  </div>
                <?php endforeach; ?>
                
              <?php endif; ?>
              
              <!-- Pagination -->
              <?php $total_pages_k = (int)ceil($total_komen / $per_page_k); if ($total_pages_k > 1): ?>
                <div class="pagination">
                  
                  <!-- Previous Button -->
                  <?php if ($page_k > 1): ?>
                    <a 
                      href="?id=<?php echo (int)$wisata_id; ?>&page_komen=<?php echo ($page_k - 1); ?>#comments" 
                      class="page-nav"
                      title="<?php echo t('previous'); ?>">
                      <i class="fas fa-chevron-left"></i> <?php echo t('previous'); ?>
                    </a>
                  <?php endif; ?>

                  <!-- Page Numbers -->
                  <div class="page-numbers">
                    <?php for ($p = 1; $p <= $total_pages_k; $p++): ?>
                      <?php if ($p == $page_k): ?>
                        <span class="active" title="Current Page"><?php echo $p; ?></span>
                      <?php else: ?>
                        <a 
                          href="?id=<?php echo (int)$wisata_id; ?>&page_komen=<?php echo $p; ?>#comments"
                          title="Go to Page <?php echo $p; ?>">
                          <?php echo $p; ?>
                        </a>
                      <?php endif; ?>
                    <?php endfor; ?>
                  </div>

                  <!-- Next Button -->
                  <?php if ($page_k < $total_pages_k): ?>
                    <a 
                      href="?id=<?php echo (int)$wisata_id; ?>&page_komen=<?php echo ($page_k + 1); ?>#comments" 
                      class="page-nav"
                      title="<?php echo t('next'); ?>">
                      <?php echo t('next'); ?> <i class="fas fa-chevron-right"></i>
                    </a>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
      // Image zoom effect
      $('.wj-featured-image').click(function() {
        $(this).toggleClass('zoomed');
      });

      // Smooth scroll to comments
      $('a[href="#comments"]').click(function(e) {
        e.preventDefault();
        $('html, body').animate({
          scrollTop: $($(this).attr('href')).offset().top - 100
        }, 500);
      });

      // Auto-resize textarea
      $('textarea').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
      });

      // Toggle mobile menu
      $('.menu-toggle').click(function() {
        $('.main-nav').toggleClass('active');
        $(this).find('i').toggleClass('fa-bars fa-times');
      });

      // Close menu on window resize
      $(window).resize(function() {
        if ($(window).width() > 768) {
          $('.main-nav').removeClass('active');
          $('.menu-toggle i').removeClass('fa-times').addClass('fa-bars');
        }
      });
    });
    </script>

    <?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
</html>

<?php mysqli_close($koneksi); ?>