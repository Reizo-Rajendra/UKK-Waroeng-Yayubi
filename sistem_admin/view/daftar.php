<?php
require_once __DIR__ . '/../database/koneksi.php';
require_once __DIR__ . '/../controller/user.controller.php';

$userController = new UserController($conn);
$userController->redirectIfLoggedIn();

$message = "";
$messageType = ""; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $result = $userController->handleRegister($username, $email, $password );
    
    if ($result === "Registration successful! Silakan login.") {
        $message = $result;
        $messageType = "success";
    } else {
        $message = $result;
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta charset="utf-8" />
  <title>Daftar – Dapoer Yayubi</title>
  <link rel="stylesheet" href="../global.css">
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <main class="login">
    <!-- Main card container -->
    <section class="login-card" aria-label="Halaman daftar">

      <!-- Left panel: Register form -->
      <div class="login-form-panel">
        <div class="login-box">

        <!-- Logo -->
        <div class="login-logo-wrapper">
          <img class="login-logo" src="../img/logo.png" alt="Logo Dapoer Yayubi" />
        </div>

        <!-- Heading -->
        <h1 class="login-title">Daftar</h1>
        
        <!-- Register Form -->
        <form class="login-form" action="" method="post">
          <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>" style="color: <?php echo $messageType === 'success' ? 'green' : 'red'; ?>; margin-bottom: 1rem;">
              <?php echo $message; ?>
            </div>
          <?php endif; ?>

          <!-- Username Field -->
            <div class="form-group">
            <label class="form-label" for="username">Username</label>
             <input
               class="form-input"
               type="text"
               id="username"
               name="nama"
               placeholder="masukan nama lengkap"
               autocomplete="username"
               required
            />
          </div>


          <!-- Email Field -->
          <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input
              class="form-input"
              type="email"
              id="email"
              name="email"
              placeholder="username@gmail.com"
              autocomplete="email"
              required
            />
          </div>

          <!-- Password Field -->
          <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-password-wrapper">
              <input
                class="form-input form-input--password"
                type="password"
                id="password"
                name="password"
                placeholder="Password"
                autocomplete="new-password"
                required
              />
              <button
                type="button"
                class="password-toggle"
                aria-label="Tampilkan / sembunyikan password"
              >
                <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn-masuk">Daftar</button>
          <p class="register-prompt">
            Sudah punya akun? <a href="index.php" class="register-link">Masuk disini</a>
          </p>
        </form>
      </div>
      </div>

      <!-- Right panel: Decorative food image -->
      <div class="login-image-panel" aria-hidden="true">
        <img
          class="login-food-image"
          src="../img/tumpeng.png"
          alt="tumpeng"
        />
      </div>
    </section>
  </main>

  <script>
    // Password toggle
    const toggleBtn = document.querySelector('.password-toggle');
    const passwordInput = document.getElementById('password');
    if (toggleBtn && passwordInput) {
      toggleBtn.addEventListener('click', () => {
        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        toggleBtn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
      });
    }
  </script>
</body>
</html>