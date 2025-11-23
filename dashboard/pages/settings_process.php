<?php
// settings_process.php
// File untuk menangani semua proses pengaturan admin

// ========================================
// UPDATE GENERAL SETTINGS
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_general_settings'])) {
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
    
    $query = "UPDATE website_settings SET 
        nama_website = ?,
        deskripsi_website = ?,
        email_kontak = ?,
        telepon = ?,
        alamat = ?,
        jam_kerja = ?,
        link_whatsapp = ?,
        link_facebook = ?,
        link_instagram = ?,
        link_youtube = ?,
        link_tiktok = ?
        WHERE id = 1";
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sssssssssss", $nama_website, $deskripsi_website, $email_kontak, 
        $telepon, $alamat, $jam_kerja, $link_whatsapp, $link_facebook, $link_instagram, $link_youtube, $link_tiktok);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Pengaturan umum berhasil diperbarui!";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui pengaturan umum: " . mysqli_error($koneksi);
    }
    
    header("Location: ?page=settings&tab=general");
    exit();
}

// ========================================
// TAMBAH MENU NAVBAR
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_menu_navbar'])) {
    $label = trim($_POST['label'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $tipe_menu = $_POST['tipe_menu'] ?? 'halaman_internal';
    
    $query = "SELECT MAX(urutan) as max_urutan FROM menu_navbar";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    $urutan = ($row['max_urutan'] ?? 0) + 1;
    
    $query = "INSERT INTO menu_navbar (label, url, icon, urutan, tipe_menu) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sssii", $label, $url, $icon, $urutan, $tipe_menu);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Menu navbar berhasil ditambahkan!";
    } else {
        $_SESSION['error_message'] = "Gagal menambahkan menu navbar!";
    }
    
    header("Location: ?page=settings&tab=navbar");
    exit();
}

// ========================================
// UPDATE MENU NAVBAR
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_menu_navbar'])) {
    $id = (int)($_POST['id'] ?? 0);
    $label = trim($_POST['label'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $aktif = (int)($_POST['aktif'] ?? 0);
    $tipe_menu = $_POST['tipe_menu'] ?? 'halaman_internal';
    
    $query = "UPDATE menu_navbar SET label=?, url=?, icon=?, aktif=?, tipe_menu=? WHERE id=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sssiii", $label, $url, $icon, $aktif, $tipe_menu, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Menu navbar berhasil diperbarui!";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui menu navbar!";
    }
    
    header("Location: ?page=settings&tab=navbar");
    exit();
}

// ========================================
// HAPUS MENU NAVBAR
// ========================================
if (isset($_GET['delete_navbar'])) {
    $id = (int)($_GET['delete_navbar'] ?? 0);
    
    $query = "DELETE FROM menu_navbar WHERE id=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Menu navbar berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus menu navbar!";
    }
    
    header("Location: ?page=settings&tab=navbar");
    exit();
}

// ========================================
// UPDATE URUTAN MENU NAVBAR
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_urutan_navbar'])) {
    $urutan_data = $_POST['urutan'];
    
    foreach ($urutan_data as $id => $urutan) {
        $id = (int)$id;
        $urutan = (int)$urutan;
        
        $query = "UPDATE menu_navbar SET urutan=? WHERE id=?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ii", $urutan, $id);
        mysqli_stmt_execute($stmt);
    }
    
    $_SESSION['success_message'] = "Urutan menu berhasil diperbarui!";
    header("Location: ?page=settings&tab=navbar");
    exit();
}

// ========================================
// UPDATE SIDEBAR MENU
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_sidebar_menu'])) {
    $id = (int)($_POST['id'] ?? 0);
    $label = trim($_POST['label'] ?? '');
    $aktif = (int)($_POST['aktif'] ?? 0);
    
    $query = "UPDATE sidebar_menu SET label=?, aktif=? WHERE id=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sii", $label, $aktif, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Sidebar menu berhasil diperbarui!";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui sidebar menu!";
    }
    
    header("Location: ?page=settings&tab=sidebar");
    exit();
}

// ========================================
// UPDATE DISPLAY SETTINGS
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_display_settings'])) {
    $warna_utama = trim($_POST['warna_utama'] ?? '');
    $warna_sekunder = trim($_POST['warna_sekunder'] ?? '');
    $tampilkan_breadcrumb = isset($_POST['tampilkan_breadcrumb']) ? 1 : 0;
    $tampilkan_search_bar = isset($_POST['tampilkan_search_bar']) ? 1 : 0;
    $tampilkan_footer_newsletter = isset($_POST['tampilkan_footer_newsletter']) ? 1 : 0;
    $items_per_page = (int)($_POST['items_per_page'] ?? 6);
    
    $query = "UPDATE pengaturan_tampilan SET 
        warna_utama=?,
        warna_sekunder=?,
        tampilkan_breadcrumb=?,
        tampilkan_search_bar=?,
        tampilkan_footer_newsletter=?,
        items_per_page=?
        WHERE id = 1";
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssiiii", $warna_utama, $warna_sekunder, 
        $tampilkan_breadcrumb, $tampilkan_search_bar, $tampilkan_footer_newsletter, $items_per_page);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Pengaturan tampilan berhasil diperbarui!";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui pengaturan tampilan!";
    }
    
    header("Location: ?page=settings&tab=display");
    exit();
}

// ========================================
// UPDATE HOMEPAGE SECTIONS
// ========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_homepage_sections'])) {
    foreach ($_POST['section_aktif'] as $id => $aktif) {
        $id = (int)$id;
        $aktif = (int)$aktif;
        
        $query = "UPDATE homepage_sections SET aktif=? WHERE id=?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ii", $aktif, $id);
        mysqli_stmt_execute($stmt);
    }
    
    $_SESSION['success_message'] = "Pengaturan section homepage berhasil diperbarui!";
    header("Location: ?page=settings&tab=homepage");
    exit();
}

?>
