<?php
session_start();
include 'koneksi.php';

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

// Proses registrasi
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validasi
    if (empty($nama) || empty($email) || empty($password)) {
        $error = "Semua field harus diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek apakah email sudah terdaftar
        $query_check = "SELECT id_user FROM user WHERE email = ?";
        $stmt_check = mysqli_prepare($koneksi, $query_check);
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) > 0) { 
          $error = "Email sudah terdaftar!"; 
        } else { 
          // Hash password 
          $hashed_password = password_hash($password, PASSWORD_DEFAULT); 
          // Simpan ke database 
          $query = "INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, 'user')"; 
          $stmt = mysqli_prepare($koneksi, $query); 
          mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $hashed_password); 
          if (mysqli_stmt_execute($stmt)) { 
            $user_id = mysqli_insert_id($koneksi); 
            // Auto login setelah registrasi 
            $_SESSION['user_id'] = $user_id; 
            $_SESSION['nama'] = $nama; 
            $_SESSION['email'] = $email; 
            $_SESSION['role'] = 'user'; 

            header("Location: dashboard.php"); 
            exit(); 
          } else { 
            $error = "Gagal membuat akun! Silakan coba lagi."; 
          } 
        } 
      } 
  } 
  ?> 
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar Akun | Kampung Jalak Bali</title>
  </head>
  <body>
    <header>
      <div>
        <div><h1>Kampung Jalak Bali</h1></div>
        <nav>
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#wisata">Wisata</a></li>
            <li><a href="login.php">Login</a></li>
          </ul>
        </nav>
      </div>
    </header>

    <section>
      <div>
        <div>
          <h2>Buat Akun Baru</h2>

          <?php if (!empty($error)): ?>
          <div style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 15px">
            <?php echo $error; ?>
          </div>
          <?php endif; ?>

          <form method="POST" action="">
            <div>
              <label for="nama">Nama Lengkap</label>
              <input type="text" id="nama" name="nama" placeholder="Nama Anda" required />
            </div>
            <div>
              <label for="email">Email</label>
              <input type="email" id="email" name="email" placeholder="email@example.com" required />
            </div>
            <div>
              <label for="password">Kata Sandi</label>
              <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required />
              <small>Password minimal 6 karakter</small>
            </div>
            <button type="submit">Daftar</button>
            <p>Sudah punya akun? <a href="login.php">Login</a></p>
          </form>
        </div>
      </div>
    </section>

    <footer>
      <div>
        <p>&copy; 2025 Kampung Jalak Bali</p>
      </div>
    </footer>
  </body>
</html>
<?php mysqli_close($koneksi); ?>
