<?php
session_start();
include __DIR__ . '/../config/koneksi.php';
include __DIR__ . '/../config/language.php';

// compute base URL (site root)
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $email = mysqli_real_escape_string($koneksi, $_POST['email'] ?? '');
    $subjek = mysqli_real_escape_string($koneksi, $_POST['subjek'] ?? '');
    $pesan = mysqli_real_escape_string($koneksi, $_POST['pesan'] ?? '');
    
    // Validasi
    if (empty($nama) || empty($email) || empty($subjek) || empty($pesan)) {
        $_SESSION['error_message'] = "Semua field harus diisi!";
        header("Location: {$base}/index.php#kontak");
        exit();
    }
    
    // Simpan ke database
    $query = "INSERT INTO pesan (nama, email, subjek, isi) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $subjek, $pesan);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_message'] = "Pesan Anda berhasil dikirim!";
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.";
    }
    
    header("Location: {$base}/index.php#kontak");
    exit();
} else {
    header("Location: {$base}/index.php");
    exit();
}

$secret_key = '0x4AAAAAACDNwtHcL_Na2isDtD6HYcJUQoE'; 

$turnstile_token = $_POST['cf-turnstile-response'] ?? '';

if (empty($turnstile_token)) {
    header("Location: index.php?status=error&message=Verifikasi keamanan gagal: Token tidak ditemukan.");
    exit; 
}

$data = [
    'secret' => $secret_key,
    'response' => $turnstile_token
];

$ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result['success'] !== true) {
    $error_message = "Verifikasi keamanan gagal/Security Verification failed: " . implode(", ", $result['error-codes'] ?? ['Unknown Error']);
    header("Location: index.php?status=error&message=" . urlencode($error_message));
    exit;
}

header("Location: index.php?status=success&message=Pesan Anda berhasil terkirim!/Your message has been sent successfully!");
exit;
?>