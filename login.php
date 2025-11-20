<?php
session_start();
include 'db.php';

// Register
if (isset($_POST['register'])) {
  $username = $conn->real_escape_string($_POST['username']);
  $password = $_POST['password'];

  $check = $conn->query("SELECT * FROM users WHERE username='$username'");
  if ($check->num_rows > 0) {
    $error = "Username sudah digunakan";
  } else {
    $conn->query("INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'user')");
    echo "<script>alert('Registrasi berhasil! Silakan login.');</script>";
  }
}

// Login
if (isset($_POST['login'])) {
  $username = $conn->real_escape_string($_POST['username']);
  $password = $_POST['password'];

  $query = $conn->query("SELECT * FROM users WHERE username='$username'");
  if ($query->num_rows > 0) {
    $user = $query->fetch_assoc();
    if ($password === $user['password']) {
      $_SESSION['user'] = $user;
      if ($user['role'] === 'admin') {
        header("Location: dashboardstaff.php");
      } else {
        header("Location: indexhot.php");
      }
      exit;
    } else {
      $error = "Password salah";
    }
  } else {
    $error = "Akun tidak ditemukan";
  }
} 
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Login/Daftar</title>
  <link rel="stylesheet" href="assets/login.css">
</head>
<body>

  <?php if (isset($error)): ?>
    <script>alert('<?= $error ?>');</script>
  <?php endif; ?>

  <div class="tab-container">
    <button class="tab active" id="daftarTab" onclick="showForm('daftar')">Daftar</button>
    <button class="tab" id="loginTab" onclick="showForm('login')">Login</button>
  </div>

  <div class="form-container" id="formContent">
    <form method="POST">
      <h2>Daftar</h2>
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <p>Sudah punya akun? <a href="#" onclick="showForm('login')">Login di sini</a></p>
      <button type="submit" name="register" class="form-button">DAFTAR</button>
    </form>
  </div>

  <script>
    function showForm(type) {
      const daftarTab = document.getElementById('daftarTab');
      const loginTab = document.getElementById('loginTab');
      const formContent = document.getElementById('formContent');

      if (type === 'login') {
        daftarTab.classList.remove('active');
        loginTab.classList.add('active');
        formContent.innerHTML = `
          <form method="POST">
            <h2>Login</h2>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <p>Belum punya akun? <a href="#" onclick="showForm('daftar')">Daftar di sini</a></p>
            <button type="submit" name="login" class="form-button">LOGIN</button>
          </form>
        `;
      } else {
        daftarTab.classList.add('active');
        loginTab.classList.remove('active');
        formContent.innerHTML = `
          <form method="POST">
            <h2>Daftar</h2>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <p>Sudah punya akun? <a href="#" onclick="showForm('login')">Login di sini</a></p>
            <button type="submit" name="register" class="form-button">DAFTAR</button>
          </form>
        `;
      }
    }
  </script>

</body>
</html>
