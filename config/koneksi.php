<?php
// config/koneksi.php

// Start session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "kampungjalak";

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset untuk support multilingual
mysqli_set_charset($koneksi, "utf8mb4");

// Buat folder uploads jika belum ada
$folders = [
    'uploads', 
    'uploads/wisata', 
    'uploads/galeri', 
    'uploads/produk', 
    'uploads/informasi',
    'uploads/hero'
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
}

// Set timezone
date_default_timezone_set('Asia/Makassar');

// Fungsi untuk mendapatkan base URL
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $base = dirname($script);
    
    // Remove trailing slashes
    $base = rtrim($base, '/\\');
    
    return $protocol . "://" . $host . $base;
}

// Fungsi untuk sanitize input
function sanitize_input($data) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, trim($data));
}

// Fungsi untuk handle file upload
function handle_file_upload($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp']) {
    $errors = [];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Check file size (max 5MB)
        if ($file['size'] > 5000000) {
            $errors[] = "File terlalu besar. Maksimal 5MB.";
        }
        
        // Check file type
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            $errors[] = "Hanya file JPG, JPEG, PNG, GIF, dan WebP yang diizinkan.";
        }
        
        if (empty($errors)) {
            // Generate unique filename
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $target_file = $target_dir . '/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                return $filename;
            } else {
                $errors[] = "Terjadi kesalahan saat mengupload file.";
            }
        }
    } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = "Error upload file: " . $file['error'];
    }
    
    return ['errors' => $errors];
}

// Fungsi untuk delete file
function delete_file($filename, $directory) {
    if (!empty($filename) && file_exists($directory . '/' . $filename)) {
        return unlink($directory . '/' . $filename);
    }
    return false;
}

// Fungsi untuk log activity (untuk admin)
function log_activity($user_id, $action, $description = '') {
    global $koneksi;
    
    $query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    mysqli_stmt_bind_param($stmt, "issss", $user_id, $action, $description, $ip_address, $user_agent);
    mysqli_stmt_execute($stmt);
}

// Fungsi untuk check jika user adalah admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk check jika user sudah login
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi untuk redirect dengan pesan
function redirect_with_message($url, $type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: $url");
    exit();
}

// Fungsi untuk menampilkan flash message
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $message['type'];
        $text = $message['message'];
        
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$text}
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
              </div>";
        
        unset($_SESSION['flash_message']);
    }
}

// Auto-create necessary tables if they don't exist
function check_and_create_tables() {
    global $koneksi;
    
    // Table: pengaturan
    $query = "CREATE TABLE IF NOT EXISTS pengaturan (
        id_pengaturan INT AUTO_INCREMENT PRIMARY KEY,
        kunci VARCHAR(100) NOT NULL UNIQUE,
        nilai TEXT,
        kategori VARCHAR(50) DEFAULT 'umum',
        deskripsi TEXT,
        tanggal_diubah TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    mysqli_query($koneksi, $query);
    
    // Table: language_strings
    $query = "CREATE TABLE IF NOT EXISTS language_strings (
        id_string INT AUTO_INCREMENT PRIMARY KEY,
        string_key VARCHAR(255) NOT NULL,
        bahasa VARCHAR(10) NOT NULL DEFAULT 'id',
        terjemahan TEXT NOT NULL,
        kategori VARCHAR(50) DEFAULT 'general',
        tanggal_dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        tanggal_diubah TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_key_lang (string_key, bahasa)
    )";
    mysqli_query($koneksi, $query);
    
    // Insert default settings if table is empty
    $check_settings = "SELECT COUNT(*) as count FROM pengaturan";
    $result = mysqli_query($koneksi, $check_settings);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] == 0) {
        $default_settings = [
            // Umum
            ['site_title', 'Kampoeng Jalak Bali', 'umum', 'Judul website'],
            ['site_description', 'Destinasi wisata edukasi yang memukau di Pulau Dewata', 'umum', 'Deskripsi website'],
            
            // Kontak
            ['contact_email', 'kampoengjalakbali@gmail.com', 'kontak', 'Email kontak utama'],
            ['contact_phone', '083862519604', 'kontak', 'Nomor telepon kontak'],
            ['contact_person', 'I Wayan Yudi Artana', 'kontak', 'Nama kontak person'],
            ['address', 'Desa Adat Tingkihkerep, Desa Tengkudak, Kecamatan Penebel, Kabupaten Tabanan, Bali', 'kontak', 'Alamat lengkap'],
            
            // Sosial Media
            ['social_instagram', 'https://instagram.com/kampoengjalakbali/', 'sosial', 'Link Instagram'],
            ['social_facebook', '#', 'sosial', 'Link Facebook'],
            
            // Hero Section
            ['hero_title', 'Selamat Datang di Kampoeng Jalak Bali', 'hero', 'Judul hero section'],
            ['hero_description', 'Destinasi wisata edukasi yang memukau di Pulau Dewata, menawarkan pengalaman unik tentang konservasi burung Jalak Bali dan budaya lokal.', 'hero', 'Deskripsi hero section'],
            ['hero_button_text', 'Jelajahi Sekarang', 'hero', 'Teks tombol hero'],
            
            // About Section
            ['about_title', 'Tentang Kampoeng Jalak Bali', 'about', 'Judul section tentang'],
            ['about_description', 'Kampoeng Jalak Bali adalah sebuah pusat konservasi ex-situ bagi Burung Jalak Bali, satwa endemik yang dilindungi, yang terletak di Banjar Tingkihkerep, Desa Tengkudak, Tabanan, Bali.', 'about', 'Deskripsi tentang'],
            ['vision_text', 'Mewujudkan desa konservasi yang harmonis antara manusia, alam, dan budaya melalui pelestarian Jalak Bali sebagai warisan satwa endemik Pulau Bali.', 'about', 'Teks visi'],
            ['history_paragraph1', 'Program konservasi ini dimulai pada April 2024 oleh Yayasan Friends of Nature, People and Forests (FNPF) dengan melepasliarkan 60 ekor Jalak Bali. Lokasi Desa Tengkudak dipilih setelah melalui kajian habitat oleh akademisi Universitas Udayana dan didukung kuat oleh budaya masyarakat setempat.', 'about', 'Sejarah paragraf 1'],
            ['history_paragraph2', 'Masyarakat adat Tingkihkerep telah lama melestarikan satwa melalui Awig-Awig dan Perarem (hukum adat) yang melarang perburuan, didasari oleh keyakinan akan keberadaan "Pelingsih Wewalungan" sebagai stana dewa pelindung satwa. Hal ini menjadikan Kampoeng Jalak Bali sebagai contoh sukses konservasi berbasis kearifan lokal dan resmi diresmikan oleh Bupati Tabanan pada Juni 2024.', 'about', 'Sejarah paragraf 2'],
            
            // Struktur Organisasi
            ['advisor_names', 'I KETUT SUARTANCA,Drh. I MADE SUGIARTA', 'struktur', 'Nama-nama pembina'],
            ['advisor_positions', 'Perbekel Desa Tengkudak,FNPF', 'struktur', 'Jabatan pembina'],
            ['chairperson_name', 'I NYOMAN OKA TRIADI', 'struktur', 'Nama ketua'],
            ['chairperson_position', 'Bandes Adat Tingkihkerep', 'struktur', 'Jabatan ketua'],
            ['secretary_name', 'I MADE SUKARATA', 'struktur', 'Nama sekretaris'],
            ['treasurer_name', 'NI PUTU DESY ANGGRAENI', 'struktur', 'Nama bendahara'],
            ['guide_names', 'I WAYAN EDDYAS PRIHANTARA,I KETUT MERTAJAYA,I WAYAN SUDARMA', 'struktur', 'Nama-nama pemandu'],
            ['observer_names', 'I WAYAN YUDI ARTANA,NI WAYAN SUIKI', 'struktur', 'Nama-nama pengamat'],
            
            // Wisata Section
            ['wisata_title', 'Wisata Edukasi', 'wisata', 'Judul section wisata'],
            ['wisata_subtitle', 'Jelajahi pengalaman unik konservasi dan budaya di Kampoeng Jalak Bali', 'wisata', 'Subjudul wisata'],
            
            // Galeri Section
            ['gallery_title', 'Galeri', 'galeri', 'Judul section galeri'],
            ['gallery_subtitle', 'Momen-momen indah di Kampoeng Jalak Bali', 'galeri', 'Subjudul galeri'],
            
            // Produk Section
            ['products_title', 'Produk & Merchandise', 'produk', 'Judul section produk'],
            ['products_subtitle', 'Dukung konservasi Jalak Bali dengan membeli produk kami', 'produk', 'Subjudul produk'],
            ['merchandise_about_title', 'Tentang Merchandise Kami', 'produk', 'Judul tentang merchandise'],
            ['merchandise_about_text1', 'Semua merchandise di Kampoeng Jalak Bali adalah pilihan spesial yang dirancang untuk mendukung konservasi Jalak Bali. Setiap pembelian Anda berkontribusi langsung pada program pelestarian burung Jalak Bali yang terancam punah.', 'produk', 'Teks tentang merchandise 1'],
            
            // Informasi Section
            ['information_title', 'Informasi Terbaru', 'informasi', 'Judul section informasi'],
            ['information_subtitle', 'Update terbaru seputar Kampoeng Jalak Bali', 'informasi', 'Subjudul informasi'],
            
            // Footer
            ['footer_description', 'Website resmi Kampoeng Jalak Bali untuk promosi wisata, produk, dan informasi desa.', 'footer', 'Deskripsi footer'],
            ['footer_copyright', 'Hak Cipta &copy; 2025 Kampoeng Jalak Bali. Semua Hak Dilindungi.', 'footer', 'Teks copyright'],
            
            // Navbar
            ['navbar_logo', 'uploads/Rancangan Logo.png', 'navbar', 'Path logo navbar'],
            ['navbar_site_name', 'KJB', 'navbar', 'Nama singkat website'],
            
            // Theme
            ['primary_color', '#4c3d19', 'theme', 'Warna utama theme'],
            ['secondary_color', '#354024', 'theme', 'Warna sekunder theme'],
            ['accent_color', '#cfbb99', 'theme', 'Warna aksen theme']
        ];
        
        foreach ($default_settings as $setting) {
            $query = "INSERT INTO pengaturan (kunci, nilai, kategori, deskripsi) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $setting[0], $setting[1], $setting[2], $setting[3]);
            mysqli_stmt_execute($stmt);
        }
    }
    
    // Insert default language strings if table is empty
    $check_language = "SELECT COUNT(*) as count FROM language_strings";
    $result = mysqli_query($koneksi, $check_language);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] == 0) {
        $default_language = [
            // Indonesian
            ['home', 'id', 'Beranda', 'general'],
            ['about', 'id', 'Tentang', 'general'],
            ['tourism', 'id', 'Wisata', 'general'],
            ['information', 'id', 'Informasi', 'general'],
            ['gallery', 'id', 'Galeri', 'general'],
            ['products', 'id', 'Produk', 'general'],
            ['contact', 'id', 'Kontak', 'general'],
            ['login', 'id', 'Masuk', 'general'],
            ['logout', 'id', 'Keluar', 'general'],
            ['hero_title', 'id', 'Selamat Datang di Kampoeng Jalak Bali', 'hero'],
            ['hero_description', 'id', 'Destinasi wisata edukasi yang memukau di Pulau Dewata, menawarkan pengalaman unik tentang konservasi burung Jalak Bali dan budaya lokal.', 'hero'],
            ['explore_now', 'id', 'Jelajahi Sekarang', 'hero'],
            
            // English
            ['home', 'en', 'Home', 'general'],
            ['about', 'en', 'About', 'general'],
            ['tourism', 'en', 'Tourism', 'general'],
            ['information', 'en', 'Information', 'general'],
            ['gallery', 'en', 'Gallery', 'general'],
            ['products', 'en', 'Products', 'general'],
            ['contact', 'en', 'Contact', 'general'],
            ['login', 'en', 'Login', 'general'],
            ['logout', 'en', 'Logout', 'general'],
            ['hero_title', 'en', 'Welcome to Kampoeng Jalak Bali', 'hero'],
            ['hero_description', 'en', 'A stunning educational tourism destination in the Island of Gods, offering unique experiences about Bali Starling conservation and local culture.', 'hero'],
            ['explore_now', 'en', 'Explore Now', 'hero']
        ];
        
        foreach ($default_language as $lang) {
            $query = "INSERT INTO language_strings (string_key, bahasa, terjemahan, kategori) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $lang[0], $lang[1], $lang[2], $lang[3]);
            mysqli_stmt_execute($stmt);
        }
    }
}

// Panggil fungsi untuk check dan create tables
// check_and_create_tables();

// // Close connection function (optional, for explicit closing)
// function close_database_connection() {
//     global $koneksi;
//     if ($koneksi) {
//         mysqli_close($koneksi);
//     }
// }

// // Register shutdown function to close connection
// register_shutdown_function('close_database_connection');
?>