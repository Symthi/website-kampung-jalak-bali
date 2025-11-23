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


DROP TABLE wisata;
DROP TABLE komentar;

-- ========================================
-- TABEL: website_settings
-- Menyimpan pengaturan umum website
-- ========================================
CREATE TABLE IF NOT EXISTS website_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_website VARCHAR(150) NOT NULL DEFAULT 'Kampung Jalak Bali',
    deskripsi_website TEXT,
    logo VARCHAR(255),
    favicon VARCHAR(255),
    email_kontak VARCHAR(100),
    telepon VARCHAR(20),
    alamat TEXT,
    jam_kerja VARCHAR(100),
    link_whatsapp VARCHAR(255),
    link_facebook VARCHAR(255),
    link_instagram VARCHAR(255),
    link_youtube VARCHAR(255),
    link_tiktok VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- TABEL: menu_navbar
-- Menyimpan item menu di navbar website
-- ========================================
CREATE TABLE IF NOT EXISTS menu_navbar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    label VARCHAR(100) NOT NULL,
    url VARCHAR(255),
    icon VARCHAR(50),
    urutan INT DEFAULT 0,
    aktif TINYINT DEFAULT 1,
    tipe_menu ENUM('link_eksternal', 'halaman_internal', 'kategori') DEFAULT 'halaman_internal',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- TABEL: sidebar_menu
-- Menyimpan label dan item menu sidebar admin
-- ========================================
CREATE TABLE IF NOT EXISTS sidebar_menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    label VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    urutan INT DEFAULT 0,
    aktif TINYINT DEFAULT 1,
    page_id VARCHAR(50),
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- TABEL: halaman
-- Menyimpan halaman/section yang bisa dibuat oleh admin
-- ========================================
CREATE TABLE IF NOT EXISTS halaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    konten TEXT,
    gambar VARCHAR(255),
    meta_deskripsi VARCHAR(255),
    aktif TINYINT DEFAULT 1,
    urutan INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- TABEL: pengaturan_tampilan
-- Menyimpan pengaturan tampilan website
-- ========================================
CREATE TABLE IF NOT EXISTS pengaturan_tampilan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    warna_utama VARCHAR(7) DEFAULT '#354024',
    warna_sekunder VARCHAR(7) DEFAULT '#8BAC3F',
    tampilkan_breadcrumb TINYINT DEFAULT 1,
    tampilkan_search_bar TINYINT DEFAULT 1,
    tampilkan_footer_newsletter TINYINT DEFAULT 1,
    tampilkan_chat_widget TINYINT DEFAULT 0,
    items_per_page INT DEFAULT 6,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

alter table pengaturan_tampilan drop font_utama;

-- ========================================
-- TABEL: homepage_sections
-- Menyimpan section yang ditampilkan di homepage
-- ========================================
CREATE TABLE IF NOT EXISTS homepage_sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_section VARCHAR(100) NOT NULL,
    tipe ENUM('wisata', 'galeri', 'produk', 'informasi', 'testimoni', 'custom') DEFAULT 'custom',
    judul VARCHAR(150),
    deskripsi TEXT,
    urutan INT DEFAULT 0,
    aktif TINYINT DEFAULT 1,
    items_ditampilkan INT DEFAULT 6,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- Insert data default
-- ========================================
INSERT INTO website_settings (nama_website, deskripsi_website, alamat, telepon, email_kontak) VALUES 
('Kampung Jalak Bali', 'Destinasi Edukasi Wisata di Bali', 'Bali, Indonesia', '081234567890', 'info@kampungjalak.id');

INSERT INTO menu_navbar (label, url, urutan, aktif) VALUES 
('Beranda', '/', 1, 1),
('Wisata', 'index.php#wisata', 2, 1),
('Galeri', 'index.php#galeri', 3, 1),
('Produk', 'produk.php', 4, 1),
('Informasi', 'informasi.php', 5, 1),
('Hubungi Kami', 'index.php#kontak', 6, 1);

INSERT INTO sidebar_menu (label, page_id, urutan, aktif) VALUES 
('Manajemen Wisata', 'wisata', 1, 1),
('Manajemen Galeri', 'galeri', 2, 1),
('Manajemen Produk', 'produk', 3, 1),
('Manajemen Informasi', 'informasi', 4, 1),
('Manajemen Komentar', 'komentar', 5, 1),
('Manajemen Pesan', 'pesan', 6, 1),
('Manajemen User', 'user', 7, 1);

INSERT INTO pengaturan_tampilan (warna_utama, warna_sekunder) VALUES 
('#354024', '#8BAC3F');

INSERT INTO homepage_sections (nama_section, tipe, judul, urutan, aktif) VALUES 
('Wisata Terbaru', 'wisata', 'Wisata & Edukasi', 1, 1),
('Galeri', 'galeri', 'Galeri Kegiatan', 2, 1),
('Produk', 'produk', 'Produk Kami', 3, 1),
('Informasi', 'informasi', 'Informasi Terkini', 4, 1);