<?php
// dashboard/pages/settings_process.php
// PROSES PENGATURAN WEBSITE - FIXED & ERROR FREE

if (!isset($_SESSION)) {
    session_start();
}

if (!isAdmin()) {
    exit();
}

// ========================================
// SAVE GENERAL SETTINGS
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_general'])) {
    $nama_website = trim($_POST['nama_website'] ?? '');
    $deskripsi_website = trim($_POST['deskripsi_website'] ?? '');
    $email_kontak = trim($_POST['email_kontak'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $jam_kerja = trim($_POST['jam_kerja'] ?? '');
    $link_whatsapp = trim($_POST['link_whatsapp'] ?? '');
    $link_facebook = trim($_POST['link_facebook'] ?? '');
    $link_instagram = trim($_POST['link_instagram'] ?? '');
    $link_youtube = trim($_POST['link_youtube'] ?? '');
    $link_tiktok = trim($_POST['link_tiktok'] ?? '');

    if (empty($nama_website)) {
        $_SESSION['error_message'] = "Nama website harus diisi!";
    } else {
        $query = "UPDATE website_settings SET nama_website=?, deskripsi_website=?, email_kontak=?, telepon=?, alamat=?, jam_kerja=?, link_whatsapp=?, link_facebook=?, link_instagram=?, link_youtube=?, link_tiktok=? WHERE id=1";
        
        $stmt = mysqli_prepare($koneksi, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssssssss", $nama_website, $deskripsi_website, $email_kontak, $telepon, $alamat, $jam_kerja, $link_whatsapp, $link_facebook, $link_instagram, $link_youtube, $link_tiktok);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Pengaturan umum berhasil disimpan!";
            } else {
                $_SESSION['error_message'] = "Gagal menyimpan: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: ?page=settings&tab=general");
    exit();
}

// ========================================
// ADD NAVBAR MENU
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_navbar'])) {
    $label = trim($_POST['navbar_label'] ?? '');
    $url = trim($_POST['navbar_url'] ?? '');
    $icon = trim($_POST['navbar_icon'] ?? 'fa-link');
    $tipe = trim($_POST['navbar_tipe'] ?? 'halaman_internal');

    if (empty($label) || empty($url)) {
        $_SESSION['error_message'] = "Label dan URL harus diisi!";
    } else {
        $result = mysqli_query($koneksi, "SELECT MAX(urutan) as max_urutan FROM menu_navbar");
        $row = mysqli_fetch_assoc($result);
        $urutan = ($row['max_urutan'] ?? 0) + 1;

        $query = "INSERT INTO menu_navbar (label, url, icon, urutan, tipe_menu, aktif) VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = mysqli_prepare($koneksi, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssss", $label, $url, $icon, $urutan, $tipe);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Menu navbar berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan menu!";
            }
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: ?page=settings&tab=navbar");
    exit();
}

// ========================================
// UPDATE NAVBAR MENU STATUS
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['menu_id'])) {
    $menu_id = (int)($_POST['menu_id'] ?? 0);
    $aktif = (int)($_POST['menu_aktif'] ?? 0);

    if ($menu_id > 0) {
        $query = "UPDATE menu_navbar SET aktif=? WHERE id=?";
        $stmt = mysqli_prepare($koneksi, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $aktif, $menu_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: ?page=settings&tab=navbar");
    exit();
}

// ========================================
// DELETE NAVBAR MENU
// ========================================
if (isset($_GET['delete_menu'])) {
    $menu_id = (int)($_GET['delete_menu'] ?? 0);

    if ($menu_id > 0) {
        $query = "DELETE FROM menu_navbar WHERE id=?";
        $stmt = mysqli_prepare($koneksi, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $menu_id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Menu berhasil dihapus!";
            }
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: ?page=settings&tab=navbar");
    exit();
}

// ========================================
// SAVE SIDEBAR MENU
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_sidebar'])) {
    $sidebar_id = $_POST['sidebar_id'] ?? [];
    $sidebar_label = $_POST['sidebar_label'] ?? [];
    $sidebar_aktif = $_POST['sidebar_aktif'] ?? [];

    foreach ($sidebar_id as $key => $id) {
        $id = (int)$id;
        $label = trim($sidebar_label[$key] ?? '');
        $aktif = (int)($sidebar_aktif[$key] ?? 0);

        if (!empty($label) && $id > 0) {
            $query = "UPDATE sidebar_menu SET label=?, aktif=? WHERE id=?";
            $stmt = mysqli_prepare($koneksi, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sii", $label, $aktif, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }

    $_SESSION['success_message'] = "Menu sidebar berhasil disimpan!";
    header("Location: ?page=settings&tab=sidebar");
    exit();
}

// ========================================
// SAVE DISPLAY SETTINGS - FIXED TYPE SPECIFIER
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_display'])) {
    $warna_utama = trim($_POST['warna_utama'] ?? '#354024');
    $warna_sekunder = trim($_POST['warna_sekunder'] ?? '#cfbb99');
    $tampilkan_breadcrumb = isset($_POST['tampilkan_breadcrumb']) ? 1 : 0;
    $tampilkan_search_bar = isset($_POST['tampilkan_search_bar']) ? 1 : 0;
    $tampilkan_footer_newsletter = isset($_POST['tampilkan_footer_newsletter']) ? 1 : 0;
    $items_per_page = (int)($_POST['items_per_page'] ?? 6);

    if ($items_per_page < 1) $items_per_page = 6;
    if ($items_per_page > 50) $items_per_page = 50;

    // FIX: Change 'u' to 'i' - items_per_page is INT not UNSIGNED INT
    $query = "UPDATE pengaturan_tampilan SET warna_utama=?, warna_sekunder=?, tampilkan_breadcrumb=?, tampilkan_search_bar=?, tampilkan_footer_newsletter=?, items_per_page=? WHERE id=1";

    $stmt = mysqli_prepare($koneksi, $query);

    if ($stmt) {
        // FIXED: ssiiuu changed to ssiiii (all parameters are strings or ints)
        mysqli_stmt_bind_param($stmt, "ssiiii", $warna_utama, $warna_sekunder, $tampilkan_breadcrumb, $tampilkan_search_bar, $tampilkan_footer_newsletter, $items_per_page);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Pengaturan tampilan berhasil disimpan!";
        } else {
            $_SESSION['error_message'] = "Gagal menyimpan pengaturan tampilan!";
        }
        mysqli_stmt_close($stmt);
    }

    header("Location: ?page=settings&tab=display");
    exit();
}

// ========================================
// SAVE HOMEPAGE SECTIONS
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_homepage'])) {
    $sections_result = mysqli_query($koneksi, "SELECT id FROM homepage_sections");
    
    while ($section = mysqli_fetch_assoc($sections_result)) {
        $section_id = $section['id'];
        $aktif = isset($_POST['section_aktif'][$section_id]) ? 1 : 0;

        $query = "UPDATE homepage_sections SET aktif=? WHERE id=?";
        $stmt = mysqli_prepare($koneksi, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $aktif, $section_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    $_SESSION['success_message'] = "Homepage settings berhasil disimpan!";
    header("Location: ?page=settings&tab=homepage");
    exit();
}

// ========================================
// SAVE KONTAK SETTINGS
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_kontak'])) {
    $email_kontak = trim($_POST['email_kontak'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $jam_kerja = trim($_POST['jam_kerja'] ?? '');
    $link_whatsapp = trim($_POST['link_whatsapp'] ?? '');

    if (empty($email_kontak) || empty($telepon) || empty($alamat)) {
        $_SESSION['error_message'] = "Email, telepon, dan alamat harus diisi!";
    } else {
        $query = "UPDATE website_settings SET email_kontak=?, telepon=?, alamat=?, jam_kerja=?, link_whatsapp=? WHERE id=1";

        $stmt = mysqli_prepare($koneksi, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssss", $email_kontak, $telepon, $alamat, $jam_kerja, $link_whatsapp);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Informasi kontak berhasil disimpan!";
            } else {
                $_SESSION['error_message'] = "Gagal menyimpan informasi kontak!";
            }
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: ?page=settings&tab=kontak");
    exit();
}

?>