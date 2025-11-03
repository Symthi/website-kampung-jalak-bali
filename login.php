<?php
session_start();
include 'koneksi.php';
include 'language.php';

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

// Proses login
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Cek user di database
    $query = "SELECT * FROM user WHERE email = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
        // Verifikasi password dengan password_verify
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo t('login_title'); ?> | Kampung Jalak Bali</title>
  </head>
  <body>
    <header>
      <div>
        <div><h1>Kampung Jalak Bali</h1></div>
        <nav>
          <ul>
            <li><a href="index.php"><?php echo t('home'); ?></a></li>
            <li><a href="index.php#wisata"><?php echo t('tourism'); ?></a></li>
            <li><a href="login.php"><?php echo t('login'); ?></a></li>
          </ul>
        </nav>
      </div>
    </header>

    <section>
      <div>
        <div>
          <h2><?php echo t('login_title'); ?></h2>

          <?php if (!empty($error)): ?>
          <div style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 15px">
            <?php echo $error; ?>
          </div>
          <?php endif; ?>

          <form method="POST" action="">
            <div>
              <label for="email"><?php echo t('email_address'); ?></label>
              <input type="email" id="email" name="email" placeholder="email@example.com" required />
            </div>
            <div>
              <label for="password"><?php echo t('password'); ?></label>
              <input type="password" id="password" name="password" placeholder="••••••" required />
            </div>
            <button type="submit"><?php echo t('login'); ?></button>
            <p><?php echo t('no_account'); ?> <a href="register.php"><?php echo t('register_here'); ?></a></p>
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
