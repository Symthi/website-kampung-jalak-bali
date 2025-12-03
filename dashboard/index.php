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

// Tentukan page mana yang akan ditampilkan
$current_page = isset($_GET['page']) ? basename($_GET['page']) : 'dashboard';
$action = isset($_GET['action']) ? basename($_GET['action']) : null;
$allowed_pages = ['dashboard', 'wisata', 'informasi', 'produk', 'galeri', 'komentar', 'pesan', 'user', 'pengaturan'];

// Validasi page
if (!in_array($current_page, $allowed_pages)) {
    $current_page = 'dashboard';
}

// Jika bukan admin dan coba akses halaman management, redirect ke dashboard
if (!isAdmin() && in_array($current_page, ['wisata', 'informasi', 'produk', 'galeri', 'komentar', 'pesan', 'user', 'pengaturan'])) {
    $current_page = 'dashboard';
}

// ===== PROSES FORM SUBMISSION SEBELUM OUTPUT HTML =====
// Jika ada POST request, proses di sini sebelum HTML dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['hapus']) || isset($_GET['baca']) || isset($_GET['delete']) || isset($_GET['delete_navbar'])) {
    // Include CRUD processing file untuk halaman saat ini
    if ($current_page === 'wisata' && isAdmin()) {
        include __DIR__ . '/pages/crud_wisata_process.php';
    } elseif ($current_page === 'informasi' && isAdmin()) {
        include __DIR__ . '/pages/crud_informasi_process.php';
    } elseif ($current_page === 'produk' && isAdmin()) {
        include __DIR__ . '/pages/crud_produk_process.php';
    } elseif ($current_page === 'galeri' && isAdmin()) {
        include __DIR__ . '/pages/crud_galeri_process.php';
    } elseif ($current_page === 'komentar' && isAdmin()) {
        include __DIR__ . '/pages/crud_komentar_process.php';
    } elseif ($current_page === 'pesan' && isAdmin()) {
        include __DIR__ . '/pages/crud_pesan_process.php';
    } elseif ($current_page === 'user' && isAdmin()) {
        include __DIR__ . '/pages/crud_user_process.php';
    } elseif ($current_page === 'pengaturan' && isAdmin()) {
        include __DIR__ . '/pages/crud_pengaturan_process.php';
    }
}

// Ambil statistik untuk dashboard
$stats = array();
$earnings_data = array_fill(0, 12, 0);
$category_data = array();

if (isAdmin()) {
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

  $query_activity = "SELECT 
    MONTH(tanggal) as bulan,
    COUNT(*) as jumlah
  FROM (
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
  $q_comments = "SELECT COUNT(*) as total FROM komentar WHERE id_user = ?";
  $stmt = mysqli_prepare($koneksi, $q_comments);
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $stats['comments'] = ($res && ($row = mysqli_fetch_assoc($res))) ? $row['total'] : 0;

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

  $q_user = "SELECT tanggal_daftar FROM user WHERE id_user = ? LIMIT 1";
  $stmt3 = mysqli_prepare($koneksi, $q_user);
  mysqli_stmt_bind_param($stmt3, "i", $user_id);
  mysqli_stmt_execute($stmt3);
  $res3 = mysqli_stmt_get_result($stmt3);
  $stats['registered'] = ($res3 && ($r3 = mysqli_fetch_assoc($res3))) ? $r3['tanggal_daftar'] : null;

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

    <title><?php echo isAdmin() ? 'Admin Dashboard' : 'User Dashboard'; ?> | <?php echo get_setting('site_title', 'Kampung Jalak Bali'); ?></title>

    <!-- ================================ -->
    <!-- CSS UTAMA - PENTING! -->
    <!-- ================================ -->
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" type="text/css">
    
    <!-- CSS UTAMA WEBSITE (PENTING - INI BUAT CRUD STYLING) -->
    <link href="<?php echo $base; ?>/assets/css/style.css" rel="stylesheet">

    <!-- Bootstrap (untuk layout dashboard template) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Dashboard Styles -->
    <link href="css/crud.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/responsive-custom.css" rel="stylesheet">

    <style>
    :root {
        --brown: #0186ab;
        --dark-green: #001b48;
        --muted-green: #02457a;
        --tan: #7ec8d9;
        --cream: #d6e8ee;
        --text: #2d2a23;
        --muted-text: #001b48;
        --white: #ffffff;
        --font-heading: "Playfair Display", serif;
        --font-body: "Poppins", sans-serif;
    }

    /* ============================================ */
    /* SIDEBAR STYLING - CLEAN & PROFESSIONAL */
    /* ============================================ */

    #wrapper {
        background-color: #f8f9fa;
    }

    /* Sidebar Background - Clean Hijau */
    .sidebar {
        background: linear-gradient(180deg, #ffffff 0%, #dce6f2 35%, #4c6785 65%, #001b48 100%);


        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.08);
    }

    /* Sidebar Brand - Simple & Clean */
    .sidebar-brand {
        background: #001b48;
        padding: 1.2rem 0;
        border-bottom: 1px solid #0186ab;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.7rem;
    }

    .sidebar-brand-icon {
        color: #e8f5e8;
        font-size: 1.5rem;
    }

    .sidebar-brand-text {
        color: #ffffff;
        font-weight: 600;
        font-size: 1.5rem;
        font-family: var(--font-body);
        margin: 0;
    }

    /* Sidebar Divider */
    .sidebar-divider {
        border-color: rgba(255, 255, 255, 0.2);
        margin: 0.5rem 1rem;
    }

    /* Sidebar Heading */
    .sidebar-heading {
        padding: 0.7rem 1rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #000 !important;
        margin-top: 0.5rem;
    }

    /* Nav Items - Clean & Simple */
    .nav-item {
        margin-bottom: 0.1rem;
    }

    .nav-link {
        padding: 0.8rem 1rem !important;
        color: #000 !important;
        font-weight: 500;
        font-family: var(--font-body);
        font-size: 0.9rem;
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }

    .nav-link i {
        width: 18px;
        text-align: center;
        color: #000000 !important;
        transition: color 0.2s ease;
    }

    .nav-link:hover {
        background-color: rgba(0, 0, 0, 0.08);
        border-left-color: #e8f5e8;
        color: #000000 !important;
    }

    .nav-link:hover i {
        color: #000000 !important;
    }

    .nav-link.active {
        background-color: rgba(0, 0, 0, 0.08);
        border-left-color: #e8f5e8;
        color: #000000 !important;
    }

    .nav-link.active i {
        color: # !important;
    }

    /* Badge Counter */
    .badge-counter {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        margin-left: auto;
        background: #dc3545;
        border-radius: 10px;
        color: white;
        font-weight: 500;
    }

    /* Sidebar Toggle Button */
    #sidebarToggle {
        background: #f8f9fa;
        color: #000000 ;
        border: 1px solid #000000;
    }

    #sidebarToggle:hover {
        background: #000000;
        color: #ffffff;
    }

    /* ============================================ */
    /* TOPBAR STYLING - CLEAN WHITE */
    /* ============================================ */

    .topbar {
        background-color: #ffffff;
        border-bottom: 1px solid #e0e0e0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        padding: 0.5rem 0;
    }

    .topbar .nav-link {
        padding: 0.5rem 1rem !important;
        border: none !important;
        display: flex !important;
        align-items: center;
    }

    .topbar .text-gray-600 {
        color: #001b48 !important;
        font-weight: 500;
        font-family: var(--font-body);
        font-size: 0.9rem;
    }

    /* Profile Image */
    .img-profile {
        width: 35px;
        height: 35px;
        border: 2px solid #001b48;
        object-fit: cover;
    }

    /* Dropdown Menu - Clean White */
    .dropdown-menu {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.3rem 0;
    }

    .dropdown-item {
        color: #333333;
        font-family: var(--font-body);
        font-size: 0.9rem;
        padding: 0.6rem 1rem;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #001b48;
    }

    .dropdown-item i {
        color: #6c757d;
        width: 16px;
        text-align: center;
        margin-right: 0.5rem;
    }

    .dropdown-divider {
        border-color: #e9ecef;
        margin: 0.3rem 0;
    }

    /* ============================================ */
    /* SCROLL TO TOP BUTTON - SIMPLE GREEN */
    /* ============================================ */

    .scroll-to-top {
        background: #001b48;
        color: #ffffff;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .scroll-to-top:hover {
        background: #02457a;
        transform: translateY(-2px);
    }

    /* ============================================ */
    /* FOOTER STYLING - CLEAN */
    /* ============================================ */

    .sticky-footer {
        background-color: #ffffff;
        border-top: 1px solid #e0e0e0;
        padding: 0.8rem 0;
    }

    .copyright {
        color: #6c757d;
        font-size: 0.8rem;
        font-weight: 400;
    }

    /* ============================================ */
    /* MODAL STYLING - CLEAN GREEN */
    /* ============================================ */

    .modal-header {
        background: #001b48;
        color: #ffffff;
        border-bottom: 1px solid #0186ab;
        padding: 1rem 1.2rem;
    }

    .modal-header .modal-title {
        font-family: var(--font-body);
        font-weight: 600;
        font-size: 1.1rem;
    }

    .modal-header .close {
        color: #ffffff;
        opacity: 0.8;
        transition: opacity 0.2s ease;
    }

    .modal-header .close:hover {
        opacity: 1;
    }

    .modal-body {
        color: #333333;
        font-family: var(--font-body);
        padding: 1.2rem;
    }

    .modal-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #e9ecef;
        padding: 0.8rem 1.2rem;
    }

    .modal-footer .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
        color: #ffffff;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .modal-footer .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }

    .modal-footer .btn-primary {
        background: #001b48;
        border: none;
        color: #ffffff;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .modal-footer .btn-primary:hover {
        background: #0186ab;
    }

    /* ============================================ */
    /* RESPONSIVE ADJUSTMENTS */
    /* ============================================ */

    @media (max-width: 768px) {
        .sidebar {
            background: #2d5a3d;
        }

        #sidebarToggleTop {
            background: #4a7c59;
            color: #ffffff;
            border: none;
            padding: 0.6rem 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        #sidebarToggleTop:hover {
            background: #3d6b4f;
        }
        
        .nav-link {
            padding: 0.7rem 0.9rem !important;
        }
    }

    /* ============================================ */
    /* CONTENT AREA */
    /* ============================================ */
    #content {
        padding: 0;
    }
    
    .container-fluid {
        padding: 0.8rem 1rem;
    }

    .second-row-container {
        justify-content: left;
    }
    
    #main-content {
        min-height: calc(100vh - 180px);
    }

    /* ============================================ */
    /* RESPONSIVE ADJUSTMENTS */
    /* ============================================ */

    @media (max-width: 768px) {
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.show {
            transform: translateX(0);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-backdrop.show {
            display: block;
        }

        #sidebarToggleTop {
            display: block !important;
            background: var(--brown);
            color: var(--cream);
            border: none;
            padding: 0.6rem 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        #sidebarToggleTop:hover {
            background: var(--dark-green);
            transform: scale(1.05);
        }

        #sidebarToggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: transparent;
            color: var(--cream);
            border: 2px solid var(--cream) !important;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        #sidebarToggle:hover {
            background: var(--cream);
            color: var(--brown);
        }

        #sidebarToggle::before {
            content: '\f00d';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }
        
        .container-fluid {
            padding: 0.5rem 0.8rem;
        }
        
        .topbar {
            padding: 0.3rem 0;
        }
        
        .topbar .text-gray-600 {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .sidebar-heading {
            padding: 0.6rem 1rem;
            font-size: 0.7rem;
        }

        .nav-link {
            padding: 0.7rem 1rem !important;
        }

        .sidebar-brand-text {
            font-size: 1rem;
        }

        .modal-header {
            padding: 0.8rem 1rem;
        }

        .modal-header .modal-title {
            font-size: 1rem;
        }
        
        .modal-body {
            padding: 1rem;
        }
        
        .modal-footer {
            padding: 0.7rem 1rem;
        }
    }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex align-items-center justify-content-center">
                <div class="sidebar-brand-icon">
                    <img src="<?php echo $base; ?>/uploads/Rancangan Logo.png" alt="Logo" style="height: 60px; width: auto;">
                </div>
                <div class="sidebar-brand-text mx-3"><?php echo get_setting('navbar_site_name', 'KJB'); ?></div>
            </a>

            <hr class="sidebar-divider">

            <?php if (isAdmin()): ?>
            <div class="sidebar-heading">
                <?php echo t('management') ?: 'Manajemen'; ?>
            </div>

            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'wisata' ? 'active' : ''; ?>" href="<?php echo $base; ?>/dashboard/index.php">
                    <i class="fas fa-chart-line"></i>
                    <span><?php echo t('dashboard') ?: 'Dashboard'; ?></span>
                </a>    
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'wisata' ? 'active' : ''; ?>" href="<?php echo $base; ?>/dashboard/index.php?page=wisata">
                    <i class="fas fa-fw fa-map-marked-alt"></i>
                    <span><?php echo t('manage_tourism') ?: 'Kelola Wisata'; ?></span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'informasi' ? 'active' : ''; ?>" href="<?php echo $base; ?>/dashboard/index.php?page=informasi">
                    <i class="fas fa-fw fa-info-circle"></i>
                    <span><?php echo t('manage_information') ?: 'Kelola Informasi'; ?></span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'produk' ? 'active' : ''; ?>" href="<?php echo $base; ?>/dashboard/index.php?page=produk">
                    <i class="fas fa-fw fa-box"></i>
                    <span><?php echo t('manage_products') ?: 'Kelola Produk'; ?></span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'galeri' ? 'active' : ''; ?>" href="<?php echo $base; ?>/dashboard/index.php?page=galeri">
                    <i class="fas fa-fw fa-images"></i>
                    <span><?php echo t('manage_gallery') ?: 'Kelola Galeri'; ?></span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'komentar' ? 'active' : ''; ?>" href="<?php echo $base; ?>/dashboard/index.php?page=komentar">
                    <i class="fas fa-fw fa-comments"></i>
                    <span><?php echo t('manage_comments') ?: 'Kelola Komentar'; ?></span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'pesan' ? 'active' : ''; ?>" href="<?php echo $base; ?>/dashboard/index.php?page=pesan">
                    <i class="fas fa-fw fa-envelope"></i>
                    <span><?php echo t('manage_messages') ?: 'Kelola Pesan'; ?></span>
                    <?php if ($stats['pesan'] > 0): ?>
                    <span class="badge badge-danger badge-counter ml-2"><?php echo $stats['pesan']; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'user' ? 'active' : ''; ?>" href="<?php echo $base; ?>/dashboard/index.php?page=user">
                    <i class="fas fa-fw fa-users"></i>
                    <span><?php echo t('manage_users') ?: 'Kelola User'; ?></span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">
                Pengaturan Sistem
            </div>

            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'pengaturan' ? 'active' : ''; ?>" 
                   href="<?php echo $base; ?>/dashboard/index.php?page=pengaturan">
                    <i class="fas fa-fw fa-cogs"></i>
                    <span>Pengaturan Website</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <?php else: ?>
            <hr class="sidebar-divider">
            <?php endif; ?>

            <!-- Sidebar Toggle (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-3 static-top shadow">

                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($user_nama); ?></span>
                                <img class="img-profile rounded-circle" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_nama); ?>&background=random">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?php echo $base; ?>/auth/logout.php" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    <?php echo t('logout') ?: 'Logout'; ?>
                                </a>
                            </div>
                        </li>
                    </ul>

                </nav>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <div id="main-content">
                        <?php
                        // Load content berdasarkan current_page dan action
                        if ($action === 'add' || $action === 'edit') {
                            // Load form pages
                            if ($current_page === 'galeri' && isAdmin()) {
                                include __DIR__ . '/pages/galeri_form.php';
                            } elseif ($current_page === 'wisata' && isAdmin()) {
                                include __DIR__ . '/pages/wisata_form.php';
                            } elseif ($current_page === 'informasi' && isAdmin()) {
                                include __DIR__ . '/pages/informasi_form.php';
                            } elseif ($current_page === 'produk' && isAdmin()) {
                                include __DIR__ . '/pages/produk_form.php';
                            } elseif ($current_page === 'user' && isAdmin()) {
                                include __DIR__ . '/pages/user_form.php';
                            } else {
                                include __DIR__ . '/pages/dashboard.php';
                            }
                        } else {
                            // Load list pages
                            if ($current_page === 'dashboard') {
                                include __DIR__ . '/pages/dashboard.php';
                            } elseif ($current_page === 'wisata' && isAdmin()) {
                                include __DIR__ . '/pages/crud_wisata.php';
                            } elseif ($current_page === 'informasi' && isAdmin()) {
                                include __DIR__ . '/pages/crud_informasi.php';
                            } elseif ($current_page === 'produk' && isAdmin()) {
                                include __DIR__ . '/pages/crud_produk.php';
                            } elseif ($current_page === 'galeri' && isAdmin()) {
                                include __DIR__ . '/pages/crud_galeri.php';
                            } elseif ($current_page === 'komentar' && isAdmin()) {
                                include __DIR__ . '/pages/crud_komentar.php';
                            } elseif ($current_page === 'pesan' && isAdmin()) {
                                include __DIR__ . '/pages/crud_pesan.php';
                            } elseif ($current_page === 'user' && isAdmin()) {
                                include __DIR__ . '/pages/crud_user.php';
                            } elseif ($current_page === 'pengaturan' && isAdmin()) {
                                include __DIR__ . '/pages/crud_pengaturan.php';
                            } else {
                                include __DIR__ . '/pages/dashboard.php';
                            }
                        }
                        ?>
                    </div>

                </div>

            </div>

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span><?php echo get_setting('footer_copyright', 'Hak Cipta &copy; 2025 Kampung Jalak Bali. Semua Hak Dilindungi.'); ?></span>
                    </div>
                </div>
            </footer>

        </div>

    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
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

    <!-- ================================ -->
    <!-- JAVASCRIPT LIBRARIES -->
    <!-- ================================ -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <script>
        const earningsData = <?php echo json_encode(array_values($earnings_data)); ?>;
        const categoryData = <?php echo json_encode($category_data); ?>;
        const isAdminUser = <?php echo isAdmin() ? 'true' : 'false'; ?>;
    </script>
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggleTop');
            const sidebar = document.getElementById('accordionSidebar');
            let backdrop = document.querySelector('.sidebar-backdrop');

            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.classList.add('sidebar-backdrop');
                document.body.appendChild(backdrop);
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const isShown = sidebar.classList.contains('show');
                    if (!isShown) {
                        sidebar.classList.add('show');
                        backdrop.classList.add('show');
                    } else {
                        sidebar.classList.remove('show');
                        backdrop.classList.remove('show');
                    }
                });
            }

            backdrop.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                }
            });

            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    const isClickOnSidebar = sidebar.contains(e.target);
                    const isClickOnToggle = sidebarToggle && sidebarToggle.contains(e.target);
                    if (!isClickOnSidebar && !isClickOnToggle && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                        backdrop.classList.remove('show');
                    }
                }
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                }
            });

            const navLinks = sidebar.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        setTimeout(() => {
                            sidebar.classList.remove('show');
                            backdrop.classList.remove('show');
                        }, 100);
                    }
                });
            });
        });
    </script>

</body>

</html>
<?php mysqli_close($koneksi); ?>