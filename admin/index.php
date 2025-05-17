<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <!-- Favicon -->
  <link rel="icon" href="assets/images/churchlogo.png" type="image/x-icon" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Chango Friends Church</title>
  <link rel="stylesheet" href="../assets/css/styles.css"/>
  <link rel="stylesheet" href="../assets/css/about.css"/>
  <link rel="stylesheet" href="../assets/css/register.css"/>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <style>
    :root {
      --black-color: hsl(216, 73%, 19%);
      --black-color-light: hsl(216, 60%, 30%);
      --black-color-lighten: hsl(216, 40%, 50%);
      --white-color: hsl(36, 100%, 99%);
      --body-color: hsl(36, 100%, 98%);
      --accent-color: #FF5733;
      --blue-accent: var(--black-color);
      --shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
    }
    .header {
      background: var(--black-color);
      padding: 1rem 0;
      text-align: center;
      height: 70px;
    }
    .form-container {
      width: 90%;
      max-width: 800px;
      margin: 2rem auto;
      background: var(--white-color);
      border-radius: 15px;
      border: 1px solid var(--black-color-light);
      overflow: hidden;
    }
    .form--container{
      width: 90%;
      max-width: 450px;
      margin: 2rem auto;
      background: var(--white-color);
      overflow: hidden;
    }
    .form-group {
      display: flex;
      flex-direction: column;
      margin: 1rem 0;
    }
    .form-group label {
      font-size: 1.2rem;
      color: var(--black-color);
      margin-bottom: 5px;
      align-items: center;
      display: block;
    }
    .form-group input {
      padding: 10px;
      font-size: 1rem;
      border: 1px solid var(--black-color-light);
      border-radius: 5px;
      transition: var(--transition);
    }
    .form-group input:focus {
      border-color: var(--accent-color);
      outline: none;
    }
    .form-group {
      width: 90%;
      max-width: 800px;
      margin: 2rem auto;
      background: var(--white-color);
      border-radius: 15px;      
      overflow: hidden;
    }
    .form-description {
      font-size: 1.2rem;
      color: var(--accent-color);
      margin-bottom: 20px;
      text-align: center;
    }
    .btn{
      background-color: var(--accent-color);
      color: var(--white-color);
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: var(--transition);
      text-align: center;
      align-items: center;
      display: block;
      margin-left: auto;
      margin-right: auto;
      justify-content: center;
      font-size: 1.2rem;
      font-weight: 600;
      text-decoration: none;
      margin-top: 20px;
    }
    .btn:hover {
      background-color: #e64a2e;
      transform: translateY(-2px);
    }
    .login-form label {
      text-align: center;
      display: block;
      width: 100%;
      margin-bottom: 8px;
    }
    .chango-copyright {
      text-align: center;
      padding: 1rem 0;
      background: var(--black-color);
      color: var(--white-color);
      font-size: 0.9rem;
    }
    .alert {
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      text-align: center;
    }
    .alert-danger {
      background-color: #ffe6e6;
      color: #cc0000;
      border: 1px solid #ffcccc;
    }
    .logo-section {
      text-align: center;
      padding: 1.5rem 0;
    }
    .church--name {
      color: var(--white-color);
      margin-bottom: 0.5rem;
    }
    .church--tagline {
      color: var(--accent-color);
      font-style: italic;
    }
  </style>
</head>
<body>
  
  
  <?php
    // Start the session at the beginning of the file
    session_start();
    
    // If user is already logged in, redirect to dashboard
    if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
      header("Location: dashboard.php");
      exit();
    }
  ?>
  
  <div class="form-container">
    <div class="logo-section">
      <h1 class="church--name">Chango Friends Church</h1>
      <p class="church--tagline">Growing in Faith, Serving with Love</p>
    </div>

    <div class="form--container">
      <h2>Admin Login Panel</h2>
      <p class="form-description">Please enter your credentials to access the admin panel.</p>
      
      <?php
        // Display error message if there is one
        if (isset($_SESSION['login_error'])) {
          echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
          unset($_SESSION['login_error']);
        }
      ?>
      
      <form action="login.php" method="POST" class="login-form">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required />
        </div>
        <div class="form-group" style="position: relative;">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required />
          <i id="togglePassword" class="ri-eye-line" style="position: absolute; right: 10px; top: 38px; cursor: pointer; font-size: 1.2rem; color: var(--accent-color);"></i>
        </div>
        <button type="submit" class="btn">Login</button>
      </form>
    </div>
  </div>

  <!-- Copyright -->
  <div class="chango-copyright">
    &copy; 2025 Chango Friends Church. All rights reserved.
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Show/hide password toggle
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');

      togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('ri-eye-line');
        this.classList.toggle('ri-eye-off-line');
      });

      // Form validation
      const loginForm = document.querySelector('.login-form');
      loginForm.addEventListener('submit', function (e) {
        const username = document.getElementById('username').value.trim();
        const password = passwordInput.value.trim();
        if (!username || !password) {
          e.preventDefault();
          alert('Please enter both username and password.');
        }
      });
    });
  </script>
</body>
</html>