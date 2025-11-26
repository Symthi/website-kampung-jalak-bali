<?php
// dashboard/pages/crud_pengaturan_process.php

// Proses form submission untuk pengaturan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_pengaturan'])) {
        
        // Handle file upload untuk hero background
        $upload_dir = __DIR__ . '/../../uploads/hero/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Process hero background 1
        if (!empty($_FILES['hero_bg_1']['name'])) {
            $filename = 'hero1_' . time() . '.' . pathinfo($_FILES['hero_bg_1']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['hero_bg_1']['tmp_name'], $upload_dir . $filename)) {
                $_POST['pengaturan']['hero_background_1'] = 'uploads/hero/' . $filename;
            }
        }
        
        // Process hero background 2
        if (!empty($_FILES['hero_bg_2']['name'])) {
            $filename = 'hero2_' . time() . '.' . pathinfo($_FILES['hero_bg_2']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['hero_bg_2']['tmp_name'], $upload_dir . $filename)) {
                $_POST['pengaturan']['hero_background_2'] = 'uploads/hero/' . $filename;
            }
        }
        
        // Process hero background 3
        if (!empty($_FILES['hero_bg_3']['name'])) {
            $filename = 'hero3_' . time() . '.' . pathinfo($_FILES['hero_bg_3']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['hero_bg_3']['tmp_name'], $upload_dir . $filename)) {
                $_POST['pengaturan']['hero_background_3'] = 'uploads/hero/' . $filename;
            }
        }
        
        // Update semua pengaturan
        foreach ($_POST['pengaturan'] as $kunci => $nilai) {
            $query = "UPDATE pengaturan SET nilai = ? WHERE kunci = ?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ss", $nilai, $kunci);
            mysqli_stmt_execute($stmt);
        }
        $_SESSION['success_msg'] = "Pengaturan berhasil diperbarui!";
    }
    
    if (isset($_POST['update_bahasa'])) {
        foreach ($_POST['bahasa'] as $id => $terjemahan) {
            $query = "UPDATE language_strings SET terjemahan = ? WHERE id_string = ?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "si", $terjemahan, $id);
            mysqli_stmt_execute($stmt);
        }
        $_SESSION['success_msg'] = "Terjemahan berhasil diperbarui!";
    }
    
    if (isset($_POST['tambah_bahasa'])) {
        $string_key = mysqli_real_escape_string($koneksi, $_POST['string_key']);
        $bahasa = mysqli_real_escape_string($koneksi, $_POST['bahasa']);
        $terjemahan = mysqli_real_escape_string($koneksi, $_POST['terjemahan']);
        $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
        
        $query = "INSERT INTO language_strings (string_key, bahasa, terjemahan, kategori)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    terjemahan = VALUES(terjemahan),
                    kategori = VALUES(kategori)";

        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $string_key, $bahasa, $terjemahan, $kategori);
        mysqli_stmt_execute($stmt);

        $_SESSION['success_msg'] = "String bahasa berhasil ditambahkan atau diperbarui!";

    }
    
    // Redirect untuk menghindari resubmission
    header("Location: {$base}/dashboard/index.php?page=pengaturan");
    exit();
}

// Proses hapus string bahasa
if (isset($_GET['hapus_bahasa'])) {
    $id = (int)$_GET['hapus_bahasa'];
    $query = "DELETE FROM language_strings WHERE id_string = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $_SESSION['success_msg'] = "String bahasa berhasil dihapus!";
    
    header("Location: {$base}/dashboard/index.php?page=pengaturan");
    exit();
}
?>