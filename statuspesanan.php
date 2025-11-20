<?php
session_start();
include 'db.php';

$nama_pemesan = $_SESSION['user']['username'] ?? null;
if (!$nama_pemesan) {
  header("Location: login.php");
  exit;
}


$query = "
  SELECT p.status_pesanan, dp.nama_menu, dp.harga, dp.gambar, dp.quantity
  FROM pesanan p
  JOIN detail_pesanan dp ON dp.id_pesanan = p.id_pesanan
  WHERE p.nama_pemesan = ?
  ORDER BY p.tanggal_pesanan DESC
  LIMIT 10
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $nama_pemesan);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$status = '';
while ($row = $result->fetch_assoc()) {
  $items[] = $row;
  $status = $row['status_pesanan'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Status Pesanan</title>
  <link rel="stylesheet" href="assets/status.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="wrapper">
  <aside class="sidebar">
    <a href="indexhot.php">-Hot Coffee</a>
    <a href="indexiced.php">-Iced Coffee</a>
    <a href="indexothers.php">-Others</a>
    <a href="statuspesanan.php">-Status Pesanan</a>
  </aside>

<div class="container">
  <h1>COFFEE</h1>

  <a href="login.php" class="logout-btn" onclick="return confirmLogout()" title="Logout">
    <i class="fas fa-sign-out-alt"></i>
  </a>

  <?php if (count($items) === 0): ?>
    <p style="text-align: center;">Belum ada pesanan.</p>
  <?php else: ?>
    <div class="order-list">
      <?php foreach ($items as $item): ?>
        <div class="order-item">
          <img src="img/<?= htmlspecialchars($item['gambar']) ?>" alt="">
          <div class="order-details">
            <span><?= htmlspecialchars($item['nama_menu']) ?></span>
            <span class="quantity">x<?= (int)$item['quantity']; ?></span>
            <small>Rp.<?= number_format($item['harga'], 0, ',', '.') ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <p>Pesanan Atas Nama:</p>
    <input type="text" value="<?= htmlspecialchars($nama_pemesan) ?>" readonly>

    <p>Status:</p>
    <?php
    $status = strtolower(trim($status)); // normalisasi
    if ($status === 'diproses'): ?>
      <div class="status-box status-inprogress">
        <span class="status-icon">⏳</span>
        In Progress - Pesanan sedang dibuat!
      </div>
    <?php elseif ($status === 'siap' || $status === 'siap ambil'): ?>
      <div class="popup show" id="popup">
        <h4>✅ Pesanan Siap!</h4>
        <p>Silakan ambil di kasir</p>
        <button onclick="document.getElementById('popup').style.display='none'">Oke</button>
      </div>
      <div class="status-box status-done">
        <span class="status-icon">✅</span>
        Done - Pesanan siap diambil!
      </div>
    <?php else: ?>
      <p>Status tidak diketahui: <?= htmlspecialchars($status) ?></p>
    <?php endif; ?>
  <?php endif; ?>
</div>


<script>
function updateStatusBox(status) {
  const box = document.querySelector('.status-box');
  const popup = document.getElementById('popup');

  if (status === 'siap' || status === 'siap ambil') {
    if (popup) popup.style.display = 'block';
    if (box) {
      box.className = 'status-box status-done';
      box.innerHTML = '<span class="status-icon">✅</span> Done - Pesanan siap diambil!';
    }
  } else if (status === 'diproses') {
    if (box) {
      box.className = 'status-box status-inprogress';
      box.innerHTML = '<span class="status-icon">⏳</span> In Progress - Pesanan sedang dibuat!';
    }
  }
}

// Polling AJAX tiap 10 detik
setInterval(() => {
  fetch('cek_status.php')
    .then(res => res.json())
    .then(data => {
      if (data.status) {
        updateStatusBox(data.status.toLowerCase().trim());
      }
    });
}, 10000); // setiap 10 detik
</script>

</body>
</html>
