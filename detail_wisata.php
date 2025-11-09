<?php
session_start();
include 'koneksi.php';
include 'language.php';

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
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo t('register_title'); ?> | Kampoeng Jalak Bali</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head>
  <body>
<?php 
// Set page info
$pageTitle = $wisata['judul'] . ' | Kampoeng Jalak Bali';
$currentPage = 'wisata';

include 'header.php'; ?>

    <section class="content-section bg-light">
      <div class="container">
        <div class="wisata-detail">
          <!-- Breadcrumb -->
          <div class="breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <span class="separator">/</span>
            <a href="index.php#wisata"><i class="fas fa-map-marked-alt"></i> Wisata</a>
            <span class="separator">/</span>
            <span class="current"><?php echo $wisata['judul']; ?></span>
          </div>

          <!-- Main Content -->
          <div class="detail-card">
            <div class="wj-wisata-hero">
              <img src="<?php echo $wisata['gambar'] ?: 'https://source.unsplash.com/random/900x400/?bali'; ?>" 
                   alt="<?php echo $wisata['judul']; ?>" 
                   class="wj-featured-image" />
            </div>
            
            <div class="wisata-info">
              <h2 class="detail-title"><?php echo $wisata['judul']; ?></h2>
              <div class="wisata-meta">
                <span class="duration">
                  <i class="fas fa-clock"></i>
                  <strong><?php echo t('duration'); ?>:</strong>
                  <?php echo $wisata['durasi']; ?>
                </span>
                <span class="location">
                  <i class="fas fa-map-marker-alt"></i>
                  Kampoeng Jalak Bali
                </span>
              </div>
              <div class="wisata-description">
                <p><?php echo nl2br($wisata['deskripsi']); ?></p>
              </div>
            </div>
          </div>

          <!-- Comments Section -->
          <div class="comments-section" id="comments">
            <div class="section-header">
              <h3 class="section-title">
                <i class="fas fa-comments"></i> 
                <?php echo t('comments'); ?>
                <span class="comment-count">(<?php echo count($komentar_data); ?>)</span>
              </h3>
            </div>

            <!-- Comment Form -->
            <div class="comment-form-card">
              <?php if (isLoggedIn()): ?>
              <form method="POST" action="proses_komentar.php" class="comment-form">
                <input type="hidden" name="wisata_id" value="<?php echo $wisata_id; ?>" />
                <div class="form-group">
                  <label for="komentar">
                    <i class="fas fa-comment"></i>
                    <?php echo t('write_comment'); ?> 
                    <span class="user-name">
                      <i class="fas fa-user-circle"></i>
                      <?php echo $_SESSION['nama']; ?>
                    </span>
                  </label>
                  <textarea id="komentar" 
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
              <div class="login-prompt">
                <i class="fas fa-lock"></i>
                <p><?php echo t('login_to_comment'); ?></p>
                <a href="login.php" class="btn btn-primary">
                  <i class="fas fa-sign-in-alt"></i> <?php echo t('login'); ?>
                </a>
              </div>
              <?php endif; ?>
            </div>

            <!-- Comments List -->
            <div class="comments-list">
              <?php if (empty($komentar_data)): ?>
                <div class="no-comments">
                  <i class="fas fa-comments"></i>
                  <p>Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                </div>
              <?php else: ?>
                <?php foreach ($komentar_data as $komentar): ?>
                <div class="comment-card">
                  <div class="comment-header">
                    <div class="comment-user">
                      <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                      </div>
                      <div class="user-info">
                        <strong class="user-name"><?php echo $komentar['nama']; ?></strong>
                        <span class="comment-date">
                          <i class="fas fa-calendar-alt"></i>
                          <?php echo date('d M Y H:i', strtotime($komentar['tanggal'])); ?>
                        </span>
                      </div>
                    </div>
                    <?php if (isAdmin() || (isLoggedIn() && $_SESSION['user_id'] == $komentar['id_user'])): ?>
                    <div class="comment-actions">
                      <a href="hapus_komentar.php?id=<?php echo $komentar['id_komentar']; ?>&wisata_id=<?php echo $wisata_id; ?>" 
                         class="btn btn-danger btn-sm"
                         onclick="return confirm('<?php echo addslashes(t('confirm_delete')); ?>')">
                        <i class="fas fa-trash"></i> <?php echo t('delete'); ?>
                      </a>
                    </div>
                    <?php endif; ?>
                  </div>
                  <div class="comment-content">
                    <p><?php echo nl2br($komentar['isi']); ?></p>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
              
              <?php $total_pages_k = (int)ceil($total_komen / $per_page_k); if ($total_pages_k > 1): ?>
              <div class="pagination">
                <?php if ($page_k > 1): ?>
                  <a href="?id=<?php echo (int)$wisata_id; ?>&page_komen=<?php echo ($page_k - 1); ?>#comments" class="page-nav prev">
                    <i class="fas fa-chevron-left"></i> Previous
                  </a>
                <?php endif; ?>

                <div class="page-numbers">
                  <?php for ($p = 1; $p <= $total_pages_k; $p++): ?>
                    <?php if ($p == $page_k): ?>
                      <span class="active"><?php echo $p; ?></span>
                    <?php else: ?>
                      <a href="?id=<?php echo (int)$wisata_id; ?>&page_komen=<?php echo $p; ?>#comments"><?php echo $p; ?></a>
                    <?php endif; ?>
                  <?php endfor; ?>
                </div>

                <?php if ($page_k < $total_pages_k): ?>
                  <a href="?id=<?php echo (int)$wisata_id; ?>&page_komen=<?php echo ($page_k + 1); ?>#comments" class="page-nav next">
                    Next <i class="fas fa-chevron-right"></i>
                  </a>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

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

<?php include 'footer.php'; ?>
  </body>
</html>
<?php mysqli_close($koneksi); ?>
