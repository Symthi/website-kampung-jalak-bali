<?php
session_start();
include __DIR__ . '/../config/koneksi.php';
include __DIR__ . '/../config/language.php';

// compute base URL (site root)
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Jika sudah login, redirect ke dashboard baru
if (isLoggedIn()) {
  header("Location: {$base}/dashboard/index.php");
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

            // Redirect ke dashboard baru (bukan yang lama)
            header("Location: {$base}/dashboard/index.php");
            exit();
        } else {
            $error = t('wrong_password');
        }
    } else {
        $error = t('email_not_found');
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo ($_SESSION['language'] ?? 'id'); ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo t('login'); ?> | <?php echo get_setting('site_title', 'Kampoeng Jalak Bali'); ?></title>
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/styles.css">
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/pages.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head>
  <body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <section class="auth-section">
      <div class="auth-card">
        <h2><i class="fas fa-sign-in-alt"></i> <?php echo t('login'); ?></h2>
        <?php if (!empty($error)): ?>
        <div class="alert-error">
          <?php echo $error; ?>
        </div>
        <?php endif; ?>
        <form method="POST" action="">
          <div>
            <label for="email"><i class="fas fa-envelope"></i> <?php echo t('email'); ?></label>
            <input type="email" id="email" name="email" placeholder="email@example.com" required />
          </div>
          <div>
            <label for="password"><i class="fas fa-lock"></i> <?php echo t('password'); ?></label>
            <input type="password" id="password" name="password" placeholder="••••••" required />
          </div>
          <button type="submit"><i class="fas fa-sign-in-alt"></i> <?php echo t('login'); ?></button>
        </form>
        <div class="auth-link">
          <?php echo t('no_account'); ?> <a href="<?php echo $base; ?>/auth/register.php"><?php echo t('register_here'); ?></a>
        </div>
      </div>
    </section>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  </body>
</html>
<?php mysqli_close($koneksi); ?>
