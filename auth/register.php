<?php
session_start();
include __DIR__ . '/../config/koneksi.php';
include __DIR__ . '/../config/language.php';

// compute base URL (site root)
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($nama && $email && $password) {
        // Cek apakah email sudah terdaftar
        $query_check = "SELECT * FROM user WHERE email = ?";
        $stmt_check = mysqli_prepare($koneksi, $query_check);
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) > 0) {
            $error = t('email_already_exists');
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan ke database
            $query = "INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, 'user')";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                // Auto login setelah register
                $_SESSION['user_id'] = mysqli_insert_id($koneksi);
                $_SESSION['nama'] = $nama;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'user';

                // Redirect ke dashboard baru (bukan yang lama)
                header("Location: {$base}/dashboard/index.php");
                exit();
            } else {
                $error = t('account_created_failed');
            }
        }
    } else {
        $error = t('all_fields_required');
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo ($_SESSION['language'] ?? 'id'); ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo t('register'); ?> | <?php echo get_setting('site_title', 'Kampoeng Jalak Bali'); ?></title>
  <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/styles.css" />
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/pages.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head>
  <body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

    <section class="auth-section">
      <div class="auth-card">
        <h2><i class="fas fa-user-plus"></i> <?php echo t('register'); ?></h2>

        <?php if (!empty($error)): ?>
        <div class="alert-error">
          <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
          <div>
            <label for="nama"><i class="fas fa-user"></i> <?php echo t('full_name'); ?></label>
            <input type="text" id="nama" name="nama" required />
          </div>

          <div>
            <label for="email"><i class="fas fa-envelope"></i> <?php echo t('email'); ?></label>
            <input type="email" id="email" name="email" required />
          </div>

          <div>
            <label for="password"><i class="fas fa-lock"></i> <?php echo t('password'); ?></label>
            <input type="password" id="password" name="password" required />
          </div>

          <button type="submit"><i class="fas fa-user-plus"></i> <?php echo t('register'); ?></button>
        </form>

        <div class="auth-link">
          <?php echo t('have_account'); ?> <a href="<?php echo $base; ?>/auth/login.php"><?php echo t('login_here'); ?></a>
        </div>
      </div>
    </section>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  </body>
</html>
<?php mysqli_close($koneksi); ?>