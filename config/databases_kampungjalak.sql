CREATE DATABASE kampungjalak;
USE kampungjalak;

-- --------------------------------------------------------
-- TABEL: user (data admin & user)
-- --------------------------------------------------------
CREATE TABLE user (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') DEFAULT 'user',
  tanggal_daftar DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO user (nama, email, password, role) VALUES 
('Admin Jalak Bali', 'kampoengjalakbali@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- --------------------------------------------------------
-- TABEL: wisata (data kegiatan / destinasi edukasi)
-- --------------------------------------------------------
CREATE TABLE wisata (
  id_wisata INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(150) NOT NULL,
  deskripsi TEXT NOT NULL,
  gambar VARCHAR(255) DEFAULT NULL,
  durasi VARCHAR(50),
  tanggal_ditambahkan DATETIME DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE wisata
  DROP COLUMN durasi,
  ADD COLUMN waktu ENUM('pagi', 'siang', 'malam') AFTER gambar,
  ADD COLUMN jam TIME AFTER waktu;

-- --------------------------------------------------------
-- TABEL: komentar (komentar user pada tiap wisata)
-- --------------------------------------------------------
CREATE TABLE komentar (
  id_komentar INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  id_wisata INT NOT NULL,
  isi TEXT NOT NULL,
  tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_wisata) REFERENCES wisata(id_wisata) ON DELETE CASCADE
);

-- --------------------------------------------------------
-- TABEL: galeri (kumpulan foto kegiatan)
-- --------------------------------------------------------
CREATE TABLE galeri (
  id_galeri INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(100),
  gambar VARCHAR(255) NOT NULL,
  tanggal_upload DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- TABEL: pesan (form pesan dari halaman kontak)
-- --------------------------------------------------------
CREATE TABLE pesan (
  id_pesan INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  subjek VARCHAR(100),
  isi TEXT NOT NULL,
  dibaca TINYINT DEFAULT 0,
  tanggal DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabel informasi
CREATE TABLE informasi (
    id_informasi INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    gambar VARCHAR(500),
    kategori ENUM('berita', 'artikel', 'pengumuman', 'event') NOT NULL,
    tanggal_dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel produk
CREATE TABLE produk (
    id_produk INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    stok INT NOT NULL,
    gambar VARCHAR(500),
    tanggal_ditambahkan TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

show tables;

DROP TABLE pengaturan_tampilan;
DROP TABLE homepage_sections;

-- Hapus tabel lama jika ada
DROP TABLE IF EXISTS pengaturan_tampilan;
DROP TABLE IF EXISTS homepage_sections;
DROP TABLE IF EXISTS pengaturan;
DROP TABLE IF EXISTS language_strings;

-- Tabel untuk semua pengaturan website
CREATE TABLE pengaturan (
  id_pengaturan INT AUTO_INCREMENT PRIMARY KEY,
  kunci VARCHAR(100) NOT NULL UNIQUE,
  nilai TEXT,
  kategori VARCHAR(50) DEFAULT 'umum',
  deskripsi TEXT,
  tanggal_diubah TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk bahasa dan terjemahan
CREATE TABLE language_strings (
  id_string INT AUTO_INCREMENT PRIMARY KEY,
  string_key VARCHAR(255) NOT NULL,
  bahasa VARCHAR(10) NOT NULL DEFAULT 'id',
  terjemahan TEXT NOT NULL,
  kategori VARCHAR(50) DEFAULT 'general',
  tanggal_dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  tanggal_diubah TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_key_lang (string_key, bahasa)
);

-- Insert default settings untuk SEMUA bagian website
INSERT INTO pengaturan (kunci, nilai, kategori, deskripsi) VALUES 
-- Umum
('site_title', 'Kampoeng Jalak Bali', 'umum', 'Judul website'),
('site_description', 'Destinasi wisata edukasi yang memukau di Pulau Dewata', 'umum', 'Deskripsi website'),

-- Kontak
('contact_email', 'kampoengjalakbali@gmail.com', 'kontak', 'Email kontak utama'),
('contact_phone', '083862519604', 'kontak', 'Nomor telepon kontak'),
('contact_person', 'I Wayan Yudi Artana', 'kontak', 'Nama kontak person'),
('address', 'Desa Adat Tingkihkerep, Desa Tengkudak, Kecamatan Penebel, Kabupaten Tabanan, Bali', 'kontak', 'Alamat lengkap'),

-- Sosial Media
('social_instagram', 'https://instagram.com/kampoengjalakbali/', 'sosial', 'Link Instagram'),
('social_facebook', 'https://web.facebook.com/kampoeng.jalak.bali', 'sosial', 'Link Facebook'),

-- Hero Section
('hero_title', 'Selamat Datang di Kampoeng Jalak Bali', 'hero', 'Judul hero section'),
('hero_description', 'Kunjungi destinasi wisata edukasi yang memukau di Pulau Dewata. Saksikan langsung keberhasilan program pelestarian satwa endemik Bali yang didukung penuh oleh filosofi hidup masyarakat setempat.', 'hero', 'Deskripsi hero section'),
('hero_button_text', 'Jelajahi Sekarang', 'hero', 'Teks tombol hero'),

-- About Section
('about_title', 'Harmoni Konservasi Jalak Bali dan Kearifan Tri Hita Karana', 'about', 'Judul section tentang'),
('about_description', 'Kampoeng Jalak Bali adalah label untuk Banjar Dinas/Desa Adat Tingkihkerep, di Desa Tengkudak, Tabanan, yang didedikasikan sebagai lokasi utama pelepasliaran dan perlindungan Burung Jalak Bali, satwa endemik asli Pulau Bali. Program konservasi Ex-Situ yang dimulai sejak April 2024 ini diinisiasi oleh Yayasan FNPF dengan dukungan akademisi dan Pemerintah Daerah. Kami mengimplementasikan pelestarian satwa liar berbasis partisipasi masyarakat yang berakar kuat pada filosofi Tri Hita Karana dan Awig-Awig Desa Adat.', 'about', 'Deskripsi tentang'),
('vision_text', 'Mewujudkan desa konservasi yang harmonis antara manusia, alam, dan budaya melalui pelestarian Jalak Bali sebagai warisan satwa endemik Pulau Bali.', 'about', 'Teks visi'),
('history_paragraph1', 'Program konservasi ini dimulai pada April 2024 oleh Yayasan Friends of Nature, People and Forests (FNPF) dengan melepasliarkan 60 ekor Jalak Bali. Lokasi Desa Tengkudak dipilih setelah melalui kajian habitat oleh akademisi Universitas Udayana dan didukung kuat oleh budaya masyarakat setempat.', 'about', 'Sejarah paragraf 1'),
('history_paragraph2', 'Masyarakat adat Tingkihkerep telah lama melestarikan satwa melalui Awig-Awig dan Perarem (hukum adat) yang melarang perburuan, didasari oleh keyakinan akan keberadaan "Pelingsih Wewalungan" sebagai stana dewa pelindung satwa. Hal ini menjadikan Kampoeng Jalak Bali sebagai contoh sukses konservasi berbasis kearifan lokal dan resmi diresmikan oleh Bupati Tabanan pada Juni 2024.', 'about', 'Sejarah paragraf 2'),
('background_paragraph1', 'Kampoeng Jalak Bali adalah nama label untuk Banjar Dinas/Desa Adat Tingkihkerep, di Desa Tengkudak, Kecamatan Penebel, Kabupaten Tabanan. Wilayah ini, yang merupakan Banjar kecil di ujung barat Desa Tengkudak , dijadikan lokasi utama untuk kegiatan pelepasliaran dan perlindungan Burung Jalak Bali , spesies endemik asli Pulau Bali yang dilindungi di Indonesia. Penduduk Banjar/Desa Adat Tingkihkerep memiliki kesadaran untuk melaksanakan pelestarian dan perlindungan satwa di wilayah mereka. Kesadaran ini diperkuat dengan adanya Pelinggih Wewalungan yang diyakini sebagai stana (tempat suci) Dewa Satwa/Binatang.', 'about', 'latar Belakang paragraf 1'),
('background_paragraph2', 'Fondasi Budaya Lokal: Masyarakat Bali memiliki hari pemuliaan khusus, yaitu Tumpek Kandang sebagai hari pemuliaan binatang/satwa, dan Tumpek Uduh/Bubuh sebagai hari pemuliaan tumbuhan. Pelestarian tumbuhan dan satwa adalah satu kesatuan yang tak terpisahkan dalam konsep Tri Hita Karana. Tri Hita Karana adalah filosofi hidup masyarakat Bali yang berarti tiga penyebab kebahagiaan, yaitu hubungan harmonis antara manusia dengan Tuhan (Parahyangan), manusia dengan sesama (Pawongan), dan manusia dengan lingkungan (Palemahan). Pengamalan konsep Tri Hita Karana ini dilakukan melalui Awig-Awig dan Perarem Desa Adat yang secara tegas melarang segala bentuk aktivitas perburuan, penangkapan, atau perdagangan satwa liar di wilayah desa.', 'about', 'Background paragraf 2'),

-- Struktur Organisasi
('advisor_names', 'I KETUT SUARTANCA,Drh. I MADE SUGIARTA', 'struktur', 'Nama-nama pembina'),
('advisor_positions', 'Perbekel Desa Tengkudak,FNPF', 'struktur', 'Jabatan pembina'),
('chairperson_name', 'I NYOMAN OKA TRIDADI', 'struktur', 'Nama ketua'),
('chairperson_position', 'Bendesa Adat Tingkihkerep', 'struktur', 'Jabatan ketua'),
('secretary_name', 'I MADE SUKARATA', 'struktur', 'Nama sekretaris'),
('treasurer_name', 'NI PUTU DESY ANGGRAENI', 'struktur', 'Nama bendahara'),
('guide_names', 'I WAYAN EDDYAS PRIHANTARA,I KETUT MERTAJAYA,I WAYAN SUDARMA', 'struktur', 'Nama-nama pemandu'),
('observer_names', 'I WAYAN YUDI ARTANA,NI WAYAN SUIKI', 'struktur', 'Nama-nama pengamat'),

-- Wisata Section
('wisata_title', 'Wisata Edukasi', 'wisata', 'Judul section wisata'),
('wisata_subtitle', 'Jelajahi pengalaman unik konservasi dan budaya di Kampoeng Jalak Bali', 'wisata', 'Subjudul wisata'),

-- Galeri Section
('gallery_title', 'Galeri', 'galeri', 'Judul section galeri'),
('gallery_subtitle', 'Momen-momen indah di Kampoeng Jalak Bali', 'galeri', 'Subjudul galeri'),

-- Produk Section
('products_title', 'Produk & Merchandise', 'produk', 'Judul section produk'),
('products_subtitle', 'Dukung konservasi Jalak Bali dengan membeli produk kami', 'produk', 'Subjudul produk'),
('merchandise_about_title', 'Tentang Merchandise Kami', 'produk', 'Judul tentang merchandise'),
('merchandise_about_text1', 'Semua merchandise di Kampoeng Jalak Bali adalah pilihan spesial yang dirancang untuk mendukung konservasi Jalak Bali. Setiap pembelian Anda berkontribusi langsung pada program pelestarian burung Jalak Bali yang terancam punah.', 'produk', 'Teks tentang merchandise 1'),

-- Informasi Section
('information_title', 'Informasi Terbaru', 'informasi', 'Judul section informasi'),
('information_subtitle', 'Update terbaru seputar Kampoeng Jalak Bali', 'informasi', 'Subjudul informasi'),

-- Footer
('footer_description', 'Website resmi Kampoeng Jalak Bali untuk promosi wisata, produk, dan informasi desa.', 'footer', 'Deskripsi footer'),
('footer_copyright', 'Hak Cipta &copy; 2025 Kampoeng Jalak Bali. Semua Hak Dilindungi.', 'footer', 'Teks copyright'),

-- Navbar
('navbar_logo', 'uploads/Rancangan Logo.png', 'navbar', 'Path logo navbar'),
('navbar_site_name', 'KJB', 'navbar', 'Nama singkat website'),

-- Theme
('primary_color', '#4c3d19', 'theme', 'Warna utama theme'),
('secondary_color', '#354024', 'theme', 'Warna sekunder theme'),
('accent_color', '#cfbb99', 'theme', 'Warna aksen theme');

INSERT IGNORE INTO pengaturan (kunci, nilai, kategori, deskripsi) VALUES
('navbar_about', 'Tentang', 'navbar', 'Nama menu About di navbar'),
('navbar_tourism', 'Wisata', 'navbar', 'Nama menu Tourism di navbar'),
('navbar_partners', 'Mitra', 'navbar', 'Nama menu Mitra di navbar'),
('navbar_gallery', 'Galeri', 'navbar', 'Nama menu Gallery di navbar'),
('navbar_information', 'Informasi', 'navbar', 'Nama menu Information di navbar'),
('navbar_products', 'Produk', 'navbar', 'Nama menu Products di navbar'),
('navbar_contact', 'Kontak', 'navbar', 'Nama menu Contact di navbar'),

-- Tambah pengaturan untuk hero background
('hero_background_1', 'uploads/hero1.jpg', 'hero', 'Hero background image 1'),
('hero_background_2', 'uploads/hero2.jpg', 'hero', 'Hero background image 2'),
('hero_background_3', 'uploads/hero3.jpg', 'hero', 'Hero background image 3');

-- ========================================
-- INSERT DEFAULT LANGUAGE STRINGS - INDONESIAN
-- ========================================
INSERT IGNORE INTO language_strings (string_key, bahasa, terjemahan, kategori) VALUES
-- General Menu
('home', 'id', 'Beranda', 'general'),
('about', 'id', 'Tentang', 'general'),
('partners', 'id', 'Mitra', 'general'),
('tourism', 'id', 'Wisata', 'general'),
('information', 'id', 'Informasi', 'general'),
('gallery', 'id', 'Galeri', 'general'),
('products', 'id', 'Produk', 'general'),
('contact', 'id', 'Kontak', 'general'),
('login', 'id', 'Masuk', 'general'),
('logout', 'id', 'Keluar', 'general'),
('dashboard', 'id', 'Dashboard', 'general'),
('welcome', 'id', 'Selamat Datang', 'general'),
('read_more', 'id', 'Baca Selengkapnya', 'general'),
('see_all', 'id', 'Lihat Semua', 'general'),
('send_message', 'id', 'Kirim Pesan', 'general'),
('view_details', 'id', 'Lihat Detail', 'general'),
('book_now', 'id', 'Pesan Sekarang', 'general'),
('back', 'id', 'Kembali', 'general'),
('next', 'id', 'Selanjutnya', 'general'),
('previous', 'id', 'Sebelumnya', 'general'),
('out_of_stock', 'id', 'Stok Habis', 'general'),

-- Auth
('register', 'id', 'Daftar', 'auth'),
('email', 'id', 'Email', 'auth'),
('full_name', 'id', 'Nama Lengkap', 'auth'),
('password', 'id', 'Kata Sandi', 'auth'),
('confirm_password', 'id', 'Konfirmasi Kata Sandi', 'auth'),
('remember_me', 'id', 'Ingat Saya', 'auth'),
('no_account', 'id', 'Belum punya akun?', 'auth'),
('have_account', 'id', 'Sudah punya akun?', 'auth'),
('register_here', 'id', 'Daftar di sini', 'auth'),
('login_here', 'id', 'Masuk di sini', 'auth'),

-- CRUD
('add', 'id', 'Tambah', 'crud'),
('edit', 'id', 'Ubah', 'crud'),
('update', 'id', 'Perbarui', 'crud'),
('save', 'id', 'Simpan', 'crud'),
('cancel', 'id', 'Batal', 'crud'),
('delete', 'id', 'Hapus', 'crud'),
('search', 'id', 'Cari', 'crud'),
('actions', 'id', 'Aksi', 'crud'),
('title', 'id', 'Judul', 'crud'),
('description', 'id', 'Deskripsi', 'crud'),
('content', 'id', 'Konten', 'crud'),
('category', 'id', 'Kategori', 'crud'),
('price', 'id', 'Harga', 'crud'),
('stock', 'id', 'Stok', 'crud'),
('image', 'id', 'Gambar', 'crud'),
('date', 'id', 'Tanggal', 'crud'),
('name', 'id', 'Nama', 'crud'),
('role', 'id', 'Peran', 'crud'),

-- Contact & Footer
('quick_links', 'id', 'Menu Cepat', 'footer'),
('rights_reserved', 'id', 'Semua Hak Dilindungi', 'footer'),
('footer_description', 'id', 'Website resmi Kampoeng Jalak Bali untuk promosi wisata, produk, dan informasi desa.', 'footer'),
('contact_info', 'id', 'Informasi Kontak', 'contact'),
('address', 'id', 'Alamat', 'contact'),
('phone', 'id', 'Telepon', 'contact'),
('follow_us', 'id', 'Ikuti Kami', 'contact'),

-- Comments
('comments', 'id', 'Komentar', 'comments'),
('write_comment', 'id', 'Tulis Komentar', 'comments'),
('post_comment', 'id', 'Kirim Komentar', 'comments'),
('no_comments_yet', 'id', 'Belum ada komentar', 'comments'),
('login_to_comment', 'id', 'Silakan login untuk menulis komentar', 'comments'),
('confirm_delete', 'id', 'Yakin ingin menghapus?', 'comments'),

-- Dashboard/Admin
('settings_management', 'id', 'Pengaturan Website Lengkap', 'admin'),
('general_settings', 'id', 'Pengaturan Umum', 'admin'),
('language_management', 'id', 'Kelola Bahasa', 'admin'),
('add_language_string', 'id', 'Tambah String Bahasa Baru', 'admin'),
('string_key', 'id', 'Key String', 'admin'),
('language', 'id', 'Bahasa', 'admin'),
('select', 'id', 'Pilih', 'admin'),
('translation', 'id', 'Terjemahan', 'admin'),
('enter_translation', 'id', 'Masukkan terjemahan', 'admin'),

-- Error Messages
('email_already_exists', 'id', 'Email sudah terdaftar!', 'error'),
('wrong_password', 'id', 'Password salah!', 'error'),
('email_not_found', 'id', 'Email tidak ditemukan!', 'error'),
('all_fields_required', 'id', 'Semua field wajib diisi!', 'error'),
('account_created_failed', 'id', 'Gagal membuat akun! Silakan coba lagi.', 'error'),

-- Success Messages
('settings_updated', 'id', 'Pengaturan berhasil diperbarui!', 'success'),
('translation_updated', 'id', 'Terjemahan berhasil diperbarui!', 'success'),
('language_added', 'id', 'String bahasa berhasil ditambahkan!', 'success'),
('language_deleted', 'id', 'String bahasa berhasil dihapus!', 'success'),

-- Misc
('no_data', 'id', 'Tidak ada data', 'general'),
('no_tourism', 'id', 'Belum ada data wisata', 'general'),
('no_gallery_images', 'id', 'Belum ada gambar di galeri', 'general'),
('no_information', 'id', 'Belum ada informasi yang tersedia', 'general');

-- ========================================
-- INSERT DEFAULT LANGUAGE STRINGS - ENGLISH
-- ========================================
INSERT IGNORE INTO language_strings (string_key, bahasa, terjemahan, kategori) VALUES
-- General Menu
('home', 'en', 'Home', 'general'),
('about', 'en', 'About', 'general'),
('partners', 'en', 'Partners', 'general'),
('tourism', 'en', 'Tourism', 'general'),
('information', 'en', 'Information', 'general'),
('gallery', 'en', 'Gallery', 'general'),
('products', 'en', 'Products', 'general'),
('contact', 'en', 'Contact', 'general'),
('login', 'en', 'Login', 'general'),
('logout', 'en', 'Logout', 'general'),
('dashboard', 'en', 'Dashboard', 'general'),
('welcome', 'en', 'Welcome', 'general'),
('read_more', 'en', 'Read More', 'general'),
('see_all', 'en', 'See All', 'general'),
('send_message', 'en', 'Send Message', 'general'),
('view_details', 'en', 'View Details', 'general'),
('book_now', 'en', 'Book Now', 'general'),
('back', 'en', 'Back', 'general'),
('next', 'en', 'Next', 'general'),
('previous', 'en', 'Previous', 'general'),
('out_of_stock', 'en', 'Out of Stock', 'general'),

-- Auth
('register', 'en', 'Register', 'auth'),
('email', 'en', 'Email', 'auth'),
('full_name', 'en', 'Full Name', 'auth'),
('password', 'en', 'Password', 'auth'),
('confirm_password', 'en', 'Confirm Password', 'auth'),
('remember_me', 'en', 'Remember Me', 'auth'),
('no_account', 'en', 'Don\'t have an account?', 'auth'),
('have_account', 'en', 'Already have an account?', 'auth'),
('register_here', 'en', 'Register here', 'auth'),
('login_here', 'en', 'Login here', 'auth'),

-- CRUD
('add', 'en', 'Add', 'crud'),
('edit', 'en', 'Edit', 'crud'),
('update', 'en', 'Update', 'crud'),
('save', 'en', 'Save', 'crud'),
('cancel', 'en', 'Cancel', 'crud'),
('delete', 'en', 'Delete', 'crud'),
('search', 'en', 'Search', 'crud'),
('actions', 'en', 'Actions', 'crud'),
('title', 'en', 'Title', 'crud'),
('description', 'en', 'Description', 'crud'),
('content', 'en', 'Content', 'crud'),
('category', 'en', 'Category', 'crud'),
('price', 'en', 'Price', 'crud'),
('stock', 'en', 'Stock', 'crud'),
('image', 'en', 'Image', 'crud'),
('date', 'en', 'Date', 'crud'),
('name', 'en', 'Name', 'crud'),
('role', 'en', 'Role', 'crud'),

-- Contact & Footer
('quick_links', 'en', 'Quick Links', 'footer'),
('rights_reserved', 'en', 'All Rights Reserved', 'footer'),
('footer_description', 'en', 'Official website of Kampoeng Jalak Bali for tourism promotion, products, and village information.', 'footer'),
('contact_info', 'en', 'Contact Information', 'contact'),
('address', 'en', 'Address', 'contact'),
('phone', 'en', 'Phone', 'contact'),
('follow_us', 'en', 'Follow Us', 'contact'),

-- Comments
('comments', 'en', 'Comments', 'comments'),
('write_comment', 'en', 'Write Comment', 'comments'),
('post_comment', 'en', 'Post Comment', 'comments'),
('no_comments_yet', 'en', 'No comments yet', 'comments'),
('login_to_comment', 'en', 'Please login to write a comment', 'comments'),
('confirm_delete', 'en', 'Are you sure you want to delete?', 'comments'),

-- Dashboard/Admin
('settings_management', 'en', 'Website Settings', 'admin'),
('general_settings', 'en', 'General Settings', 'admin'),
('language_management', 'en', 'Language Management', 'admin'),
('add_language_string', 'en', 'Add New Language String', 'admin'),
('string_key', 'en', 'String Key', 'admin'),
('language', 'en', 'Language', 'admin'),
('select', 'en', 'Select', 'admin'),
('translation', 'en', 'Translation', 'admin'),
('enter_translation', 'en', 'Enter translation', 'admin'),

-- Error Messages
('email_already_exists', 'en', 'Email already registered!', 'error'),
('wrong_password', 'en', 'Wrong password!', 'error'),
('email_not_found', 'en', 'Email not found!', 'error'),
('all_fields_required', 'en', 'All fields are required!', 'error'),
('account_created_failed', 'en', 'Failed to create account! Please try again.', 'error'),

-- Success Messages
('settings_updated', 'en', 'Settings updated successfully!', 'success'),
('translation_updated', 'en', 'Translation updated successfully!', 'success'),
('language_added', 'en', 'Language string added successfully!', 'success'),
('language_deleted', 'en', 'Language string deleted successfully!', 'success'),

-- Misc
('no_data', 'en', 'No data available', 'general'),
('no_tourism', 'en', 'No tourism data available', 'general'),
('no_gallery_images', 'en', 'No images in the gallery yet', 'general'),
('no_information', 'en', 'No information available', 'general');