<?php
// panggil koneksi database dan controller user
require_once __DIR__ . '/../database/koneksi.php';
require_once __DIR__ . '/../controller/user.controller.php';

$userController = new UserController($conn);
// kalau user sudah login, langsung diarahkan ke halamannya
$userController->redirectIfLoggedIn();

$message = "";
// jika form login dikirim via method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // cek email dan password lewat controller
    $result = $userController->handleLogin($email, $password);
    if ($result === true) {
        // jika role admin, arahkan ke dashboard admin
        if ($_SESSION['role'] === 'penjual' || $_SESSION['role'] === 'admin') {
            header("Location: ../view/admin/dashboard.php");
            exit();
        } else {
            // jika role pembeli, arahkan ke beranda
            header("Location: ../../Home page/home.php");
            exit();
        }
    } else {
        // tampilkan pesan error jika login gagal
        $message = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta charset="utf-8" />
  <title>Login</title>
  <link rel="stylesheet" href="../global.css">
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <main class="login">

        <!-- Main card container -->
        <section class="login-card" aria-label="Login page">

        <!-- Left panel: Login form -->
        <div class="login-form-panel">
            <div class="login-box">
          
        <!-- Logo -->
        <div class="login-logo-wrapper">
          <img class="login-logo" src="../img/logo.png" alt="logo" />
        </div>

        <!-- Heading -->
        <h1 class="login-title">Login</h1>

        <!-- Login Form -->
        <form class="login-form" action="" method="post" novalidate>
        <?php if ($message): ?>
            <div class="alert error" style="color: red; margin-bottom: 1rem;"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Email Field -->
        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input class="form-input" type="email" id="email" name="email" placeholder="username@gmail.com" autocomplete="email" required />
        </div>


        <!-- Password Field -->
        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="input-password-wrapper">
            <input class="form-input form-input--password" type="password" id="password" name="password" placeholder="Password" autocomplete="current-password" required />
            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
              <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-masuk">Masuk</button>

        <!-- Register Link -->
        <p class="register-prompt">
          Belum punya akun? <a href="daftar.php" class="register-link">Daftar disini</a>
        </p>
        </form>
        </div>
        </div>
        
        <!-- Right panel: Decorative food image -->
        <div class="login-image-panel" aria-hidden="true">
          <img class="login-food-image" src="../img/tumpeng.png" alt="" />
        </div>
     </section>
    </main>
  </body>
</html>