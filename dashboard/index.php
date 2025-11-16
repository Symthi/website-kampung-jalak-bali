<?php
session_start();
include __DIR__ . '/../config/koneksi.php';
include __DIR__ . '/../config/language.php';

// compute base URL (site root)
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Cek apakah user sudah login
if (!isLoggedIn()) {
  header("Location: {$base}/auth/login.php");
  exit();
}

// Ambil data user
$user_id = $_SESSION['user_id'] ?? 0;
$user_nama = $_SESSION['nama'] ?? '';
$user_role = $_SESSION['role'] ?? '';
$user_email = $_SESSION['email'] ?? '';

// Ambil statistik
$stats = array();
$earnings_data = array_fill(0, 12, 0);
$category_data = array();

if (isAdmin()) {
  // Global stats for admin - 7 data
  $query_wisata = "SELECT COUNT(*) as total FROM wisata";
  $query_komentar = "SELECT COUNT(*) as total FROM komentar";
  $query_pesan = "SELECT COUNT(*) as total FROM pesan WHERE dibaca = 0";
  $query_user = "SELECT COUNT(*) as total FROM user";
  $query_produk = "SELECT COUNT(*) as total FROM produk";
  $query_informasi = "SELECT COUNT(*) as total FROM informasi";
  $query_galeri = "SELECT COUNT(*) as total FROM galeri";

  $stats['wisata'] = mysqli_fetch_assoc(mysqli_query($koneksi, $query_wisata))['total'] ?? 0;
  $stats['komentar'] = mysqli_fetch_assoc(mysqli_query($koneksi, $query_komentar))['total'] ?? 0;
  $stats['pesan'] = mysqli_fetch_assoc(mysqli_query($koneksi, $query_pesan))['total'] ?? 0;
  $stats['user'] = mysqli_fetch_assoc(mysqli_query($koneksi, $query_user))['total'] ?? 0;
  $stats['produk'] = mysqli_fetch_assoc(mysqli_query($koneksi, $query_produk))['total'] ?? 0;
  $stats['informasi'] = mysqli_fetch_assoc(mysqli_query($koneksi, $query_informasi))['total'] ?? 0;
  $stats['galeri'] = mysqli_fetch_assoc(mysqli_query($koneksi, $query_galeri))['total'] ?? 0;

  // Activity overview - total aktivitas sistem per bulan (wisata, pesan, komentar, user) -- ADMIN
$query_activity = "SELECT 
    MONTH(tanggal) as bulan,
    COUNT(*) as jumlah
  FROM (
    -- Gabungkan semua aktivitas penting
    SELECT tanggal_ditambahkan as tanggal, 'wisata' as tipe FROM wisata
    UNION ALL
    SELECT tanggal, 'komentar' as tipe FROM komentar  
    UNION ALL
    SELECT tanggal, 'pesan' as tipe FROM pesan
    UNION ALL  
    SELECT tanggal_daftar as tanggal, 'user' as tipe FROM user
  ) AS aktivitas_sistem
  WHERE YEAR(tanggal) = YEAR(NOW())
  GROUP BY MONTH(tanggal)
  ORDER BY bulan
";
  $activity_result = mysqli_query($koneksi, $query_activity);
  $earnings_data = array_fill(0, 12, 0);
  while($row = mysqli_fetch_assoc($activity_result)) {
    $earnings_data[$row['bulan'] - 1] = $row['jumlah'];
  }

  // Data untuk chart 7 kategori
  $query_category_data = "
    SELECT 'Wisata' as label, COUNT(*) as value FROM wisata
    UNION ALL
    SELECT 'Komentar', COUNT(*) FROM komentar
    UNION ALL
    SELECT 'Pesan', COUNT(*) FROM pesan
    UNION ALL
    SELECT 'User', COUNT(*) FROM user
    UNION ALL
    SELECT 'Produk', COUNT(*) FROM produk
    UNION ALL
    SELECT 'Informasi', COUNT(*) FROM informasi
    UNION ALL
    SELECT 'Galeri', COUNT(*) FROM galeri
  ";
  $category_result = mysqli_query($koneksi, $query_category_data);
  while($row = mysqli_fetch_assoc($category_result)) {
    $category_data[] = $row;
  }
} else {
  // Personal stats for regular user
  $q_comments = "SELECT COUNT(*) as total FROM komentar WHERE id_user = ?";
  $stmt = mysqli_prepare($koneksi, $q_comments);
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $stats['comments'] = ($res && ($row = mysqli_fetch_assoc($res))) ? $row['total'] : 0;

  // number of messages sent from this user's email (contact form)
  if (!empty($user_email)) {
    $q_msgs = "SELECT COUNT(*) as total FROM pesan WHERE email = ?";
    $stmt2 = mysqli_prepare($koneksi, $q_msgs);
    mysqli_stmt_bind_param($stmt2, "s", $user_email);
    mysqli_stmt_execute($stmt2);
    $res2 = mysqli_stmt_get_result($stmt2);
    $stats['messages'] = ($res2 && ($r2 = mysqli_fetch_assoc($res2))) ? $r2['total'] : 0;
  } else {
    $stats['messages'] = 0;
  }

  // fetch user registration date
  $q_user = "SELECT tanggal_daftar FROM user WHERE id_user = ? LIMIT 1";
  $stmt3 = mysqli_prepare($koneksi, $q_user);
  mysqli_stmt_bind_param($stmt3, "i", $user_id);
  mysqli_stmt_execute($stmt3);
  $res3 = mysqli_stmt_get_result($stmt3);
  $stats['registered'] = ($res3 && ($r3 = mysqli_fetch_assoc($res3))) ? $r3['tanggal_daftar'] : null;

  // User activity chart (comments over months)
  $query_activity = "SELECT MONTH(tanggal) as bulan, COUNT(*) as jumlah FROM komentar 
                     WHERE id_user = ? AND YEAR(tanggal) = YEAR(NOW()) GROUP BY MONTH(tanggal) ORDER BY bulan";
  $stmt_activity = mysqli_prepare($koneksi, $query_activity);
  mysqli_stmt_bind_param($stmt_activity, "i", $user_id);
  mysqli_stmt_execute($stmt_activity);
  $activity_result = mysqli_stmt_get_result($stmt_activity);
  $earnings_data = array_fill(0, 12, 0);
  while($row = mysqli_fetch_assoc($activity_result)) {
    $earnings_data[$row['bulan'] - 1] = $row['jumlah'];
  }

  // User category data - ringkasan aktivitas user (Komentar + Pesan)
  // Fallback ke summary karena wisata tidak punya kategori
  $category_data = array(
    array('label' => 'Komentar', 'value' => $stats['comments']),
    array('label' => 'Pesan', 'value' => $stats['messages'])
  );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Dashboard Kampung Jalak Bali">
    <meta name="author" content="Kampung Jalak Bali">

    <title><?php echo isAdmin() ? 'Admin Dashboard' : 'User Dashboard'; ?> | Kampung Jalak Bali</title>

    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/responsive-custom.css" rel="stylesheet">
    <style>
      /* Theme colors sesuai website */
      :root {
        --brown: #4c3d19;
        --dark-green: #354024;
        --muted-green: #889063;
        --tan: #cfbb99;
        --cream: #e5d7c4;
        --text: #2d2a23;
        --muted-text: #6b6458;
        --font-heading: "Playfair Display", serif;
        --font-body: "Poppins", sans-serif;
      }

      body {
        font-family: var(--font-body);
        color: var(--text);
        background-color: var(--cream);
      }

      h1, h2, h3, h4, h5, h6 {
        font-family: var(--font-heading);
        color: var(--dark-green);
      }

      p {
        color: var(--brown);
      }

      a {
        color: var(--dark-green);
        transition: all 0.3s ease;
      }

      a:hover {
        color: var(--brown);
      }

      /* Override Bootstrap colors dengan theme */
      .bg-gradient-primary {
        background: linear-gradient(135deg, var(--brown) 0%, var(--dark-green) 100%) !important;
      }

      .sidebar-dark .sidebar-brand {
        background-color: var(--dark-green);
      }

      .sidebar-dark .nav-link {
        color: var(--cream);
        transition: all 0.3s ease;
        font-weight: 500;
      }

      .sidebar-dark .nav-link:hover {
        background-color: rgba(207, 187, 153, 0.15);
        border-left-color: var(--tan);
        color: var(--tan);
      }

      .sidebar-dark .nav-link.active {
        background-color: rgba(207, 187, 153, 0.2);
        border-left-color: var(--tan);
        color: var(--tan);
      }

      .card {
        border: none;
        box-shadow: 0 2px 8px rgba(76, 61, 25, 0.1);
        transition: all 0.3s ease;
        border-radius: 12px;
      }

      .card:hover {
        box-shadow: 0 4px 12px rgba(76, 61, 25, 0.15);
        transform: translateY(-2px);
      }

      .card-header {
        background-color: var(--cream) !important;
        border-bottom: 2px solid var(--tan);
      }

      .card-header h6 {
        color: var(--brown) !important;
        font-family: var(--font-heading);
        font-weight: 700;
      }

      /* Border colors dengan theme */
      .border-left-primary { border-left: 4px solid var(--brown) !important; }
      .border-left-success { border-left: 4px solid var(--dark-green) !important; }
      .border-left-info { border-left: 4px solid var(--tan) !important; }
      .border-left-warning { border-left: 4px solid #d4a574 !important; }

      /* Text color overrides */
      .text-primary { color: var(--brown) !important; }
      .text-success { color: var(--dark-green) !important; }
      .text-info { color: var(--tan) !important; }
      .text-gray-800 { color: var(--text) !important; }

      /* Stat cards styling */
      .stat-card {
        border-radius: 12px;
        transition: all 0.3s ease;
        background: white;
      }

      .stat-card:hover {
        transform: scale(1.02);
        box-shadow: 0 6px 16px rgba(76, 61, 25, 0.15) !important;
      }

      /* Chart containers */
      .chart-area {
        position: relative;
        height: 300px;
        overflow: hidden;
      }

      .chart-pie {
        position: relative;
        height: 300px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      #myPieChart {
        max-height: 280px !important;
        max-width: 100% !important;
      }

      #myAreaChart {
        max-height: 280px !important;
        max-width: 100% !important;
      }

      /* Topbar styling */
      .topbar {
        background-color: var(--cream) !important;
        border-bottom: 2px solid var(--tan);
      }

      .topbar .form-control {
        border-color: var(--tan);
        background-color: white;
        color: var(--text);
      }

      .topbar .form-control:focus {
        border-color: var(--brown);
        box-shadow: 0 0 0 0.2rem rgba(76, 61, 25, 0.25);
      }

      /* Chart colors */
      .chart-primary { color: var(--brown); }
      .chart-secondary { color: var(--dark-green); }
      .chart-accent { color: var(--tan); }

      /* Mobile sidebar fix */
      @media (max-width: 768px) {
        .sidebar {
          position: fixed;
          top: 56px;
          left: 0;
          width: 100%;
          height: calc(100vh - 56px);
          transform: translateX(-100%);
          transition: transform 0.3s ease-in-out;
          z-index: 999;
          background-color: var(--dark-green);
          overflow-y: auto;
        }

        .sidebar.show {
          transform: translateX(0);
          box-shadow: 2px 0 10px rgba(0,0,0,0.3);
        }

        .sidebar-brand {
          width: 100%;
        }

        .navbar-nav.sidebar {
          flex-direction: column;
        }

        #sidebarToggleTop {
          display: block !important;
          background: var(--brown);
          color: var(--cream);
          border: none;
          padding: 8px 12px;
          border-radius: 6px;
          cursor: pointer;
          font-size: 1.1rem;
          transition: all 0.3s ease;
        }

        #sidebarToggleTop:hover {
          background: var(--dark-green);
          transform: scale(1.05);
        }

        #sidebarToggleTop:focus {
          outline: none;
          box-shadow: 0 0 0 3px rgba(76, 61, 25, 0.3);
        }

        body.sidebar-toggled .sidebar {
          transform: translateX(0);
        }

        /* Backdrop untuk close sidebar */
        .sidebar-backdrop {
          display: none;
          position: fixed;
          top: 56px;
          left: 0;
          right: 0;
          bottom: 0;
          background: rgba(0, 0, 0, 0.3);
          z-index: 998;
        }

        .sidebar-backdrop.show {
          display: block;
        }
      }

      /* Responsive adjustments */
      @media (max-width: 576px) {
        .col-xl-3, .col-md-6 {
          flex: 0 0 100%;
          max-width: 100%;
        }

        h1, h2, h3 {
          font-size: 1.3rem;
        }

        .card {
          margin-bottom: 1rem;
        }
      }

      /* Scrollbar styling */
      ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
      }

      ::-webkit-scrollbar-track {
        background: var(--cream);
      }

      ::-webkit-scrollbar-thumb {
        background: var(--tan);
        border-radius: 4px;
      }

      ::-webkit-scrollbar-thumb:hover {
        background: var(--brown);
      }

      /* Scroll to Top Button */
      .scroll-to-top {
        position: fixed;
        right: 1rem;
        bottom: 1rem;
        display: none;
        width: 2.75rem;
        height: 2.75rem;
        text-align: center;
        background: linear-gradient(135deg, var(--brown), var(--dark-green));
        color: white;
        border-radius: 0.3rem;
        line-height: 2.75rem;
        font-size: 1rem;
        z-index: 1000;
        opacity: 0;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(76, 61, 25, 0.3);
      }

      .scroll-to-top:hover {
        opacity: 1;
        background: linear-gradient(135deg, var(--dark-green), var(--brown));
        box-shadow: 0 4px 12px rgba(76, 61, 25, 0.4);
        transform: scale(1.05);
      }

      .scroll-to-top.show {
        display: block;
        opacity: 0.8;
      }

      /* Alert styling */
      .alert {
        border-radius: 8px;
        border: none;
        font-family: var(--font-body);
      }

      .alert-primary {
        background-color: var(--brown);
        color: white;
        border-left: 4px solid var(--tan);
      }

      .alert-success {
        background-color: var(--dark-green);
        color: white;
        border-left: 4px solid var(--tan);
      }

      .alert-danger {
        background-color: #a94442;
        color: white;
        border-left: 4px solid var(--tan);
      }

      .alert-warning {
        background-color: #8a6d3b;
        color: white;
        border-left: 4px solid var(--tan);
      }

      .alert-info {
        background-color: #31708f;
        color: white;
        border-left: 4px solid var(--tan);
      }

      .alert-close {
        color: white;
        opacity: 0.8;
        transition: opacity 0.3s ease;
      }

      .alert-close:hover {
        opacity: 1;
      }
    </style>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo $base; ?>/dashboard/index.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-leaf"></i>
                </div>
                <div class="sidebar-brand-text mx-3">KJB </div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="<?php echo $base; ?>/dashboard/index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span><?php echo t('dashboard') ?: 'Dashboard'; ?></span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <?php if (isAdmin()): ?>
            <!-- Heading -->
            <div class="sidebar-heading">
                <?php echo t('management') ?: 'Manajemen'; ?>
            </div>

            <!-- Nav Item - Wisata -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $base; ?>/admin/crud/crud_wisata.php">
                    <i class="fas fa-fw fa-map-marked-alt"></i>
                    <span><?php echo t('manage_tourism') ?: 'Kelola Wisata'; ?></span></a>
            </li>

            <!-- Nav Item - Informasi -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $base; ?>/admin/crud/crud_informasi.php">
                    <i class="fas fa-fw fa-info-circle"></i>
                    <span><?php echo t('manage_information') ?: 'Kelola Informasi'; ?></span></a>
            </li>

            <!-- Nav Item - Produk -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $base; ?>/admin/crud/crud_produk.php">
                    <i class="fas fa-fw fa-box"></i>
                    <span><?php echo t('manage_products') ?: 'Kelola Produk'; ?></span></a>
            </li>

            <!-- Nav Item - Gallery -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $base; ?>/admin/crud/crud_galeri.php">
                    <i class="fas fa-fw fa-images"></i>
                    <span><?php echo t('manage_gallery') ?: 'Kelola Galeri'; ?></span></a>
            </li>

            <!-- Nav Item - Comments -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $base; ?>/admin/crud/crud_komentar.php">
                    <i class="fas fa-fw fa-comments"></i>
                    <span><?php echo t('manage_comments') ?: 'Kelola Komentar'; ?></span></a>
            </li>

            <!-- Nav Item - Messages -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $base; ?>/admin/crud/crud_pesan.php">
                    <i class="fas fa-fw fa-envelope"></i>
                    <span><?php echo t('manage_messages') ?: 'Kelola Pesan'; ?></span>
                    <?php if ($stats['pesan'] > 0): ?>
                    <span class="badge badge-danger badge-counter ml-2"><?php echo $stats['pesan']; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- Nav Item - Users -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $base; ?>/admin/crud/crud_user.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span><?php echo t('manage_users') ?: 'Kelola User'; ?></span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">
            <?php else: ?>
            <!-- Divider -->
            <hr class="sidebar-divider">
            <?php endif; ?>

            <!-- Heading -->
            <div class="sidebar-heading">
                <?php echo t('account') ?: 'Akun'; ?>
            </div>

            <!-- Nav Item - Profile -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $base; ?>/index.php">
                    <i class="fas fa-fw fa-home"></i>
                    <span><?php echo t('home') ?: 'Halaman Utama'; ?></span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($user_nama); ?></span>
                                <img class="img-profile rounded-circle" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_nama); ?>&background=random">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?php echo $base; ?>/index.php">
                                    <i class="fas fa-home fa-sm fa-fw mr-2 text-gray-400"></i>
                                    <?php echo t('home') ?: 'Halaman Utama'; ?>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?php echo $base; ?>/auth/logout.php" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    <?php echo t('logout') ?: 'Logout'; ?>
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <?php echo isAdmin() ? 'Dasbor Admin' : 'Dasbor Pengguna'; ?>
                        </h1>
                        <p class="text-gray-600 small">
                            Selamat datang, <strong><?php echo htmlspecialchars($user_nama); ?></strong>!
                        </p>
                    </div>

                    <!-- Content Row - Stats Cards -->
                    <div class="row">

                        <?php if (isAdmin()): ?>

                        <!-- Wisata Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Wisata</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $stats['wisata']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-map-marked-alt fa-2x" style="color: #4c3d19;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Komentar Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Komentar</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $stats['komentar']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-comments fa-2x" style="color: #354024;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pesan Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Pesan Baru</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $stats['pesan']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-envelope fa-2x" style="color: #cfbb99;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                User</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $stats['user']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x" style="color: #d4a574;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Produk Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Produk</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $stats['produk']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-box fa-2x" style="color: #4c3d19;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Informasi</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $stats['informasi']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-info-circle fa-2x" style="color: #354024;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Galeri Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Galeri</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $stats['galeri']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-images fa-2x" style="color: #cfbb99;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php else: ?>

                        <!-- User Comments Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Komentar Saya</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $stats['comments']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-comments fa-2x" style="color: #4c3d19;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Messages Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Pesan Saya</div>
                                            <div class="h5 mb-0 font-weight-bold"><?php echo $stats['messages']; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-envelope fa-2x" style="color: #354024;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Member Since Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2 stat-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Bergabung</div>
                                            <div class="h5 mb-0 font-weight-bold">
                                                <?php echo $stats['registered'] ? date('d M Y', strtotime($stats['registered'])) : '-'; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x" style="color: #cfbb99;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php endif; ?>
                    </div>

                    <!-- Content Row - Charts -->
                    <div class="row">

                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <!-- Card Header -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <?php echo isAdmin() ? 'Ikhtisar Aktivitas' : 'Aktivitas Saya'; ?>
                                    </h6>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body p-3">
                                    <div class="chart-area">
                                        <canvas id="myAreaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <!-- Card Header -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <?php echo isAdmin() ? 'Kategori Data' : 'Ringkasan'; ?>
                                    </h6>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body p-3">
                                    <div class="chart-pie">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Hak Cipta &copy; 2025 Kampung Jalak Bali. Semua Hak Dilindungi.</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Logout</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Apakah Anda yakin ingin logout?</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <a class="btn btn-primary" href="<?php echo $base; ?>/auth/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
        // Chart data from PHP
        const earningsData = <?php echo json_encode(array_values($earnings_data)); ?>;
        const categoryData = <?php echo json_encode($category_data); ?>;
        const isAdminUser = <?php echo isAdmin() ? 'true' : 'false'; ?>;
    </script>
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>

    <script src="js/script-index.js"></script>

</body>

</html>
<?php mysqli_close($koneksi); ?>
