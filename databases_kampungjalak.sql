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
  keterangan VARCHAR(255),
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

DROP TABLE wisata;
DROP TABLE komentar;