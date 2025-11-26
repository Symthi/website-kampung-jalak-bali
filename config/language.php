<?php
// config/language.php - Update dengan database

// Load language strings from database
function load_language_strings($language) {
    global $koneksi;
    
    $strings = array();
    $query = "SELECT string_key, terjemahan FROM language_strings WHERE bahasa = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $language);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $strings[$row['string_key']] = $row['terjemahan'];
    }
    
    return $strings;
}

// Load settings from database
function load_settings() {
    global $koneksi;
    
    $settings = array();
    $query = "SELECT kunci, nilai FROM pengaturan";
    $result = mysqli_query($koneksi, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[$row['kunci']] = $row['nilai'];
    }
    
    return $settings;
}

// Load both language strings and settings
$settings = load_settings();

// Set default language if not set
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'id';
}

$current_language = $_SESSION['language'];
$lang[$current_language] = load_language_strings($current_language);


/**
 * Translate function to get language strings
 */
/**
 * Hardcoded strings as fallback
 */
function get_hardcoded_strings() {
    return [
        'id' => [
            'management' => 'Manajemen',
            'manage_tourism' => 'Kelola Wisata',
            'manage_information' => 'Kelola Informasi', 
            'manage_products' => 'Kelola Produk',
            'manage_gallery' => 'Kelola Galeri',
            'manage_comments' => 'Kelola Komentar',
            'manage_messages' => 'Kelola Pesan',
            'manage_users' => 'Kelola User',
            'home' => 'Beranda',
            'about' => 'Tentang',
            'tourism' => 'Wisata',
            'information' => 'Informasi',
            'gallery' => 'Galeri',
            'products' => 'Produk',
            'supporter' => 'Pendukung',
            'contact' => 'Kontak',
            'login' => 'Masuk',
            'logout' => 'Keluar',
            'dashboard' => 'Dashboard',
            'welcome' => 'Selamat Datang',
            'read_more' => 'Baca Selengkapnya',
            'see_all' => 'Lihat Semua',
            'send_message' => 'Kirim Pesan',
            'view_details' => 'Lihat Detail',
            'book_now' => 'Pesan Sekarang',
            'back' => 'Kembali',
            'next' => 'Selanjutnya',
            'previous' => 'Sebelumnya',
            'out_of_stock' => 'Stok Habis',
            'site_title' => 'Kampoeng Jalak Bali',
            
            // ====== HERO SECTION ======
            'hero_title' => 'Selamat Datang di Kampoeng Jalak Bali',
            'hero_description' => 'Destinasi wisata edukasi yang memukau di Pulau Dewata, menawarkan pengalaman unik tentang konservasi burung Jalak Bali dan budaya lokal.',
            'explore_now' => 'Jelajahi Sekarang',
            
            // ====== ABOUT SECTION ======
            'about_title' => 'Tentang Kampoeng Jalak Bali',
            'about_description' => 'Kampoeng Jalak Bali adalah sebuah pusat konservasi ex-situ bagi Burung Jalak Bali, satwa endemik yang dilindungi, yang terletak di Banjar Tingkihkerep, Desa Tengkudak, Tabanan, Bali.',
            'history' => 'Sejarah dan Latar Belakang',
            'history_paragraph1' => 'Program konservasi ini dimulai pada April 2024 oleh Yayasan Friends of Nature, People and Forests (FNPF) dengan melepasliarkan 60 ekor Jalak Bali. Lokasi Desa Tengkudak dipilih setelah melalui kajian habitat oleh akademisi Universitas Udayana dan didukung kuat oleh budaya masyarakat setempat.',
            'history_paragraph2' => 'Masyarakat adat Tingkihkerep telah lama melestarikan satwa melalui Awig-Awig dan Perarem (hukum adat) yang melarang perburuan, didasari oleh keyakinan akan keberadaan "Pelingsih Wewalungan" sebagai stana dewa pelindung satwa. Hal ini menjadikan Kampoeng Jalak Bali sebagai contoh sukses konservasi berbasis kearifan lokal dan resmi diresmikan oleh Bupati Tabanan pada Juni 2024.',
            'vision' => 'Visi',
            'mission' => 'Misi',
            'vision_text' => 'Mewujudkan desa konservasi yang harmonis antara manusia, alam, dan budaya melalui pelestarian Jalak Bali sebagai warisan satwa endemik Pulau Bali.',
            'mission_items' => 'Menyelenggarakan konservasi Jalak Bali berbasis partisipasi masyarakat.,Menguatkan peran adat dan budaya dalam menjaga kelestarian alam.,Meningkatkan kesadaran dan pendidikan lingkungan bagi warga dan generasi muda.,Mengembangkan potensi ekowisata berbasis konservasi dan budaya lokal.,Membangun kemitraan dengan lembaga konservasi, pemerintah, dan pihak swasta.',
            
            // ====== ORGANIZATIONAL ======
            'management_structure' => 'Struktur Kepengurusan',
            'management_structure_subtitle' => 'Susunan pengurus dan peran mereka di Kampoeng Jalak Bali',
            'advisor' => 'Pembina',
            'responsible_party' => 'Penanggungjawab',
            'chairperson' => 'Ketua',
            'secretary_and_treasurer' => 'Sekretaris & Bendahara',
            'guide' => 'Pemandu',
            'observer' => 'Pengamat',
            'member' => 'Anggota',
            'secretary' => 'Sekretaris',
            'treasurer' => 'Bendahara',
            'position_village_chief' => 'Perbekel Desa Tengkudak',
            'position_fnpf' => 'FNPF',
            'position_adat_village' => 'Desa Adat Tingkihkerep',
            'position_adat_leader' => 'Bandes Adat Tingkihkerep',
            'position_secretary' => 'Sekretaris',
            'position_treasurer' => 'Bendahara',
            'position_guide' => 'Pemandu',
            'position_observer' => 'Pengamat',
            
            // ====== TOURISM SECTION ======
            'tourism_sec' => 'Wisata Edukasi',
            'tourism_subtitle' => 'Jelajahi pengalaman unik konservasi dan budaya di Kampoeng Jalak Bali',
            'duration' => 'Durasi',
            'location' => 'Lokasi',
            'time' => 'Waktu',
            'no_tourism' => 'Belum ada data wisata',
            
            // ====== GALLERY SECTION ======
            'gallery_title' => 'Galeri',
            'gallery_subtitle' => 'Momen-momen indah di Kampoeng Jalak Bali',
            'no_gallery_images' => 'Belum ada gambar di galeri',
            
            // ====== PRODUCTS SECTION ======
            'products_title' => 'Produk & Merchandise',
            'products_subtitle' => 'Dukung konservasi Jalak Bali dengan membeli produk kami',
            'merchandise_available' => 'Tersedia di lokasi Kampoeng Jalak Bali',
            'merchandise_about_title' => 'Tentang Merchandise Kami',
            'merchandise_about_text1' => 'Semua merchandise di Kampoeng Jalak Bali adalah pilihan spesial yang dirancang untuk mendukung konservasi Jalak Bali. Setiap pembelian Anda berkontribusi langsung pada program pelestarian burung Jalak Bali yang terancam punah.',
            'interested_visit_us' => 'Tertarik? Kunjungi kami sekarang juga!',
            'visit_us' => 'Kunjungi Kami',
            'for_more_info' => 'Untuk informasi lebih lanjut',
            'no_data' => 'Tidak ada data',
            
            // ====== INFORMATION SECTION ======
            'information_title' => 'Informasi Terbaru',
            'information_subtitle' => 'Update terbaru seputar Kampoeng Jalak Bali',
            'no_information' => 'Belum ada informasi yang tersedia',
            
            // ====== CONTACT SECTION ======
            'contact_title' => 'Kontak Kami',
            'contact_subtitle' => 'Hubungi kami untuk informasi lebih lanjut tentang Kampoeng Jalak Bali',
            'contact_info' => 'Informasi Kontak',
            'address' => 'Alamat',
            'phone' => 'Telepon',
            'email' => 'Email',
            'subject' => 'Subjek',
            'message' => 'Pesan',
            'follow_us' => 'Ikuti Kami',
            'full_name' => 'Nama Lengkap',
            'location_title' => 'Lokasi Kami',
            'location_subtitle' => 'Kunjungi Kampoeng Jalak Bali di Tabanan, Bali',
            
            // ====== COMMENTS ======
            'comments' => 'Komentar',
            'write_comment' => 'Tulis Komentar',
            'post_comment' => 'Kirim Komentar',
            'no_comments_yet' => 'Belum ada komentar',
            'login_to_comment' => 'Silakan login untuk menulis komentar',
            'confirm_delete' => 'Yakin ingin menghapus?',
            'delete' => 'Hapus',
            
            // ====== FOOTER ======
            'quick_links' => 'Menu Cepat',
            'rights_reserved' => 'Semua Hak Dilindungi',
            'footer_description' => 'Website resmi Kampoeng Jalak Bali untuk promosi wisata, produk, dan informasi desa.',
            
            // ====== AUTH ======
            'password' => 'Kata Sandi',
            'confirm_password' => 'Konfirmasi Kata Sandi',
            'remember_me' => 'Ingat Saya',
            'forgot_password' => 'Lupa Kata Sandi?',
            'no_account' => 'Belum punya akun?',
            'have_account' => 'Sudah punya akun?',
            'register_here' => 'Daftar di sini',
            'login_here' => 'Masuk di sini',
            'register' => 'Daftar',
            
            // ====== CRUD ======
            'add' => 'Tambah',
            'edit' => 'Ubah',
            'update' => 'Perbarui',
            'save' => 'Simpan',
            'cancel' => 'Batal',
            'search' => 'Cari',
            'actions' => 'Aksi',
            'title' => 'Judul',
            'description' => 'Deskripsi',
            'content' => 'Konten',
            'category' => 'Kategori',
            'price' => 'Harga',
            'stock' => 'Stok',
            'image' => 'Gambar',
            'date' => 'Tanggal',
            'name' => 'Nama',
            'role' => 'Peran',
            'user' => 'Pengguna',
            'email_already_exists' => 'Email sudah terdaftar!',
            'wrong_password' => 'Password salah!',
            'email_not_found' => 'Email tidak ditemukan!',
            'all_fields_required' => 'Semua field wajib diisi!',
            'account_created_failed' => 'Gagal membuat akun! Silakan coba lagi.',
            'settings_updated' => 'Pengaturan berhasil diperbarui!',
            'translation_updated' => 'Terjemahan berhasil diperbarui!',
            'language_added' => 'String bahasa berhasil ditambahkan!',
            'language_deleted' => 'String bahasa berhasil dihapus!',
            'settings_management' => 'Pengaturan Website Lengkap',
            'general_settings' => 'Pengaturan Umum',
            'language_management' => 'Kelola Bahasa',
            'add_language_string' => 'Tambah String Bahasa Baru',
            'string_key' => 'Key String',
            'language' => 'Bahasa',
            'select' => 'Pilih',
            'category' => 'Kategori',
            'translation' => 'Terjemahan',
            'enter_translation' => 'Masukkan terjemahan',
            'contact_email' => 'Email',
            'contact_phone' => 'Telepon', 
            'footer_copyright' => 'Hak Cipta &copy; 2025 Kampoeng Jalak Bali. Semua Hak Dilindungi.',
            // Tambahkan key lainnya yang missing...
        ],
        'en' => [
            'management' => 'Management',
            'manage_tourism' => 'Manage Tourism',
            'manage_information' => 'Manage Information',
            'manage_products' => 'Manage Products', 
            'manage_gallery' => 'Manage Gallery',
            'manage_comments' => 'Manage Comments',
            'manage_messages' => 'Manage Messages',
            'manage_users' => 'Manage Users',
            // ====== GENERAL ======
            'home' => 'Home',
            'about' => 'About',
            'tourism' => 'Tourism',
            'information' => 'Information',
            'gallery' => 'Gallery',
            'products' => 'Products',
            'supporter' => 'Supporter',
            'contact' => 'Contact',
            'login' => 'Login',
            'logout' => 'Logout',
            'dashboard' => 'Dashboard',
            'welcome' => 'Welcome',
            'read_more' => 'Read More',
            'see_all' => 'See All',
            'send_message' => 'Send Message',
            'view_details' => 'View Details',
            'book_now' => 'Book Now',
            'back' => 'Back',
            'next' => 'Next',
            'previous' => 'Previous',
            'out_of_stock' => 'Out of Stock',
            'site_title' => 'Kampoeng Jalak Bali',
            
            // ====== HERO SECTION ======
            'hero_title' => 'Welcome to Kampoeng Jalak Bali',
            'hero_description' => 'A stunning educational tourism destination in the Island of Gods, offering unique experiences about Bali Starling conservation and local culture.',
            'explore_now' => 'Explore Now',
            
            // ====== ABOUT SECTION ======
            'about_title' => 'About Kampoeng Jalak Bali',
            'about_description' => 'Kampoeng Jalak Bali is an ex-situ conservation center for the Bali Starling, a protected endemic species, located in Banjar Tingkihkerep, Tengkudak Village, Tabanan, Bali.',
            'history' => 'History and Background',
            'history_paragraph1' => 'The conservation program began in April 2024 by the Friends of Nature, People and Forests (FNPF) foundation, releasing 60 Bali Starlings. The Tengkudak Village location was selected after habitat studies by academics from Udayana University and is strongly supported by the local community culture.',
            'history_paragraph2' => 'The Tingkihkerep customary community has long protected wildlife through traditional regulations (Awig-Awig and Perarem) that prohibit hunting, grounded in the belief of the presence of the "Pelingsih Wewalungan" as a guardian spirit. This makes Kampoeng Jalak Bali a successful example of community-based conservation and was officially inaugurated by the Regent of Tabanan in June 2024.',
            'vision' => 'Vision',
            'mission' => 'Mission',
            'vision_text' => 'To realize a conservation village that harmonizes humans, nature, and culture through the preservation of Bali Starling as the endemic wildlife heritage of Bali Island.',
            'mission_items' => 'Organize Bali Starling conservation based on community participation.,Strengthen the role of customs and culture in maintaining environmental sustainability.,Increase environmental awareness and education for residents and the younger generation.,Develop ecotourism potential based on conservation and local culture.,Build partnerships with conservation institutions, government, and private parties.',
            
            // ====== ORGANIZATIONAL ======
            'management_structure' => 'Organizational Structure',
            'management_structure_subtitle' => 'Composition of management and their roles at Kampoeng Jalak Bali',
            'advisor' => 'Advisor',
            'responsible_party' => 'Responsible Party',
            'chairperson' => 'Chairperson',
            'secretary_and_treasurer' => 'Secretary & Treasurer',
            'guide' => 'Guide',
            'observer' => 'Observer',
            'member' => 'Member',
            'secretary' => 'Secretary',
            'treasurer' => 'Treasurer',
            'position_village_chief' => 'Village Chief (Perbekel)',
            'position_fnpf' => 'FNPF',
            'position_adat_village' => 'Customary Village Adat Tingkihkerep',
            'position_adat_leader' => 'Customary Leader (Bandes Adat)',
            'position_secretary' => 'Secretary',
            'position_treasurer' => 'Treasurer',
            'position_guide' => 'Guide',
            'position_observer' => 'Observer',
            
            // ====== TOURISM SECTION ======
            'tourism_sec' => 'Educational Tourism',
            'tourism_subtitle' => 'Explore unique conservation and cultural experiences at Kampoeng Jalak Bali',
            'duration' => 'Duration',
            'location' => 'Location',
            'time' => 'Time',
            'no_tourism' => 'No tourism data available',
            
            // ====== GALLERY SECTION ======
            'gallery_title' => 'Gallery',
            'gallery_subtitle' => 'Beautiful moments at Kampoeng Jalak Bali',
            'no_gallery_images' => 'No images in the gallery yet',
            
            // ====== PRODUCTS SECTION ======
            'products_title' => 'Products & Merchandise',
            'products_subtitle' => 'Support Bali Starling conservation by purchasing our products',
            'merchandise_available' => 'Available at Kampoeng Jalak Bali location',
            'merchandise_about_title' => 'About Our Merchandise',
            'merchandise_about_text1' => 'All merchandise at Kampoeng Jalak Bali are special selections designed to support Bali Starling conservation. Every purchase you make contributes directly to the conservation program for this endangered Bali Starling bird.',
            'interested_visit_us' => 'Interested? Visit us now!',
            'visit_us' => 'Visit Us',
            'for_more_info' => 'For more information',
            'no_data' => 'No data available',
            
            // ====== INFORMATION SECTION ======
            'information_title' => 'Latest Information',
            'information_subtitle' => 'Latest updates about Kampoeng Jalak Bali',
            'no_information' => 'No information available',
            
            // ====== CONTACT SECTION ======
            'contact_title' => 'Contact Us',
            'contact_subtitle' => 'Contact us for more information about Kampoeng Jalak Bali',
            'contact_info' => 'Contact Information',
            'address' => 'Address',
            'phone' => 'Phone',
            'email' => 'Email',
            'subject' => 'Subject',
            'message' => 'Message',
            'follow_us' => 'Follow Us',
            'full_name' => 'Full Name',
            'location_title' => 'Our Location',
            'location_subtitle' => 'Visit Kampoeng Jalak Bali in Tabanan, Bali',
            
            // ====== COMMENTS ======
            'comments' => 'Comments',
            'write_comment' => 'Write Comment',
            'post_comment' => 'Post Comment',
            'no_comments_yet' => 'No comments yet',
            'login_to_comment' => 'Please login to write a comment',
            'confirm_delete' => 'Are you sure you want to delete?',
            'delete' => 'Delete',
            
            // ====== FOOTER ======
            'quick_links' => 'Quick Links',
            'rights_reserved' => 'All Rights Reserved',
            'footer_description' => 'Official website of Kampoeng Jalak Bali for tourism promotion, products, and village information.',
            
            // ====== AUTH ======
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
            'remember_me' => 'Remember Me',
            'forgot_password' => 'Forgot Password?',
            'no_account' => 'Don\'t have an account?',
            'have_account' => 'Already have an account?',
            'register_here' => 'Register here',
            'login_here' => 'Login here',
            'register' => 'Register',
            
            // ====== CRUD ======
            'add' => 'Add',
            'edit' => 'Edit',
            'update' => 'Update',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'search' => 'Search',
            'actions' => 'Actions',
            'title' => 'Title',
            'description' => 'Description',
            'content' => 'Content',
            'category' => 'Category',
            'price' => 'Price',
            'stock' => 'Stock',
            'image' => 'Image',
            'date' => 'Date',
            'name' => 'Name',
            'role' => 'Role',
            'user' => 'User',
            'email_already_exists' => 'Email already registered!',
            'wrong_password' => 'Wrong password!',
            'email_not_found' => 'Email not found!',
            'all_fields_required' => 'All fields are required!',
            'account_created_failed' => 'Failed to create account! Please try again.',
            'settings_updated' => 'Settings updated successfully!',
            'translation_updated' => 'Translation updated successfully!',
            'language_added' => 'Language string added successfully!',
            'language_deleted' => 'Language string deleted successfully!',
            'settings_management' => 'Website Settings',
            'general_settings' => 'General Settings',
            'language_management' => 'Language Management',
            'add_language_string' => 'Add New Language String',
            'string_key' => 'String Key',
            'language' => 'Language',
            'select' => 'Select',
            'category' => 'Category',
            'translation' => 'Translation',
            'enter_translation' => 'Enter translation',
            // Tambahkan di bagian hardcoded strings
            // ... untuk English
            'contact_email' => 'Email',
            'contact_phone' => 'Phone',
            'footer_copyright' => 'Copyright &copy; 2025 Kampoeng Jalak Bali. All Rights Reserved.'
            // ... dan seterusnya
        ]
    ];
}

function t($key) {
    global $lang, $koneksi;
    
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = 'id';
    }

    $language = $_SESSION['language'];
    
    // 1. Cek di loaded language data dulu (dari database)
    if (isset($lang[$language][$key])) {
        return $lang[$language][$key];
    }
    
    // 2. Query database langsung untuk key yang missing
    $query = "SELECT terjemahan FROM language_strings WHERE string_key = ? AND bahasa = ? LIMIT 1";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ss", $key, $language);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Cache the result untuk penggunaan berikutnya
        $lang[$language][$key] = $row['terjemahan'];
        return $row['terjemahan'];
    }
    
    // 3. Fallback ke bahasa Indonesia di database
    if ($language !== 'id') {
        $query = "SELECT terjemahan FROM language_strings WHERE string_key = ? AND bahasa = 'id' LIMIT 1";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "s", $key);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Cache the result
            $lang[$language][$key] = $row['terjemahan'];
            return $row['terjemahan'];
        }
    }
    
    // 4. Fallback ke hardcoded strings di language.php
    $hardcoded_strings = get_hardcoded_strings();
    if (isset($hardcoded_strings[$language][$key])) {
        return $hardcoded_strings[$language][$key];
    }
    
    // 5. Fallback ke hardcoded Indonesian
    if ($language !== 'id' && isset($hardcoded_strings['id'][$key])) {
        return $hardcoded_strings['id'][$key];
    }
    
    // 6. Return key sendiri jika tidak ditemukan
    return $key;
}

/**
 * Get setting value
 */
function get_setting($key, $default = '') {
    global $settings;
    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * Get mission items as array
 */
function get_mission_items() {
    $mission_string = t('mission_items');
    return explode(',', $mission_string);
}

/**
 * Get structure names as array
 */
function get_structure_names($key) {
    $names_string = get_setting($key, '');
    return explode(',', $names_string);
}
?>