<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>QRIS Pembayaran</title>
  <link rel="stylesheet" href="assets/qris.css">
</head>
<body>

<div class="qris-container">
  <h2>Scan & Bayar via QRIS</h2>
  <img src="img/qr.png" alt="Kode QRIS">
  <p>Gunakan aplikasi e-wallet seperti DANA, OVO, GoPay, dll untuk menyelesaikan pembayaran Anda.</p>

  <form action="statuspesanan.php" method="get" onsubmit="return confirm('Pastikan Anda telah menyelesaikan pembayaran!')">
    <button type="submit" class="btn-confirm">SAYA SUDAH BAYAR</button>
  </form>
</div>

</body>
</html>
