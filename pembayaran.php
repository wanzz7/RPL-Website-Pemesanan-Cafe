<?php
session_start();
include 'db.php';

// Pastikan user sudah login
$userId = $_SESSION['user']['id'] ?? null;
$username = $_SESSION['user']['username'] ?? null;
if (!$userId || !$username) {
  header("Location: login.php");
  exit;
}

// Tangani aksi tambah/kurang qty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_keranjang'])) {
  $id_keranjang = (int)$_POST['id_keranjang'];
  $action = $_POST['action'];

  $result = $conn->query("SELECT quantity FROM keranjang WHERE id = $id_keranjang AND user_id = $userId");
  $item = $result->fetch_assoc();

  if ($item) {
    $qty = (int)$item['quantity'];
    if ($action === 'decrease') {
      if ($qty > 1) {
        $conn->query("UPDATE keranjang SET quantity = quantity - 1 WHERE id = $id_keranjang");
      } else {
        $conn->query("DELETE FROM keranjang WHERE id = $id_keranjang");
      }
    } elseif ($action === 'increase') {
      $conn->query("UPDATE keranjang SET quantity = quantity + 1 WHERE id = $id_keranjang");
    }
  }
  header("Location: pembayaran.php");
  exit;
}

// Ambil data keranjang
$query = $conn->query("SELECT * FROM keranjang WHERE user_id = $userId");
$items = [];
$total = 0;
while ($row = $query->fetch_assoc()) {
  $items[] = $row;
  $total += $row['harga'] * $row['quantity'];
}
$tax = $total * 0.10;
$grandTotal = $total + $tax;

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
  $metode = $conn->real_escape_string($_POST['metode']);
  $namaPemesan = $conn->real_escape_string($username);

  // Simpan ke tabel pesanan
  $conn->query("INSERT INTO pesanan (nama_pemesan, metode_bayar, tanggal_pesanan, status_pesanan) 
                VALUES ('$namaPemesan', '$metode', NOW(), 'diproses')");
  $id_pesanan = $conn->insert_id;

  // Simpan ke detail_pesanan
  foreach ($items as $item) {
    $id_menu = (int)$item['id_menu'];
    $nama = $conn->real_escape_string($item['nama_menu']);
    $harga = (int)$item['harga'];
    $qty = (int)$item['quantity'];
    $gambar = $conn->real_escape_string($item['gambar']);

    $conn->query("INSERT INTO detail_pesanan (id_pesanan, id_menu, nama_menu, harga, quantity, gambar) 
                  VALUES ($id_pesanan, $id_menu, '$nama', $harga, $qty, '$gambar')");
  }

  // Hapus keranjang
  $conn->query("DELETE FROM keranjang WHERE user_id = $userId");

  if ($metode === 'QRIS') {
    echo "<script>alert('Pesanan berhasil! Silakan scan QRIS.'); window.location.href='qris.php';</script>";
  } else {
    echo "<script>alert('Pesanan berhasil!'); window.location.href='statuspesanan.php';</script>";
  }
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pembayaran</title>
  <link rel="stylesheet" href="assets/pembayaran.css">
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

    <?php if (count($items) === 0): ?>
      <p style="text-align: center;">Keranjang kosong.</p>
    <?php else: ?>
      <div class="order-list">
        <?php foreach ($items as $item): ?>
          <div class="order-item">
  <img src="img/<?= htmlspecialchars($item['gambar']) ?>" alt="">
  <div class="order-details">
    <span><?= htmlspecialchars($item['nama_menu']) ?></span>
    <small>Rp.<?= number_format($item['harga'], 0, ',', '.') ?></small>
  </div>
  <div class="item-quantity">
    <form action="pembayaran.php" method="POST">
      <input name="id_keranjang" type="hidden" value="<?= $item['id']; ?>"> 
      <input name="action" type="hidden" value="decrease">
      <button type="submit">−</button>
    </form>

    <span><?= $item['quantity']; ?></span>

    <form action="pembayaran.php" method="POST">
      <input name="id_keranjang" type="hidden" value="<?= $item['id']; ?>"> 
      <input name="action" type="hidden" value="increase">
      <button type="submit">+</button>
    </form>
  </div>
</div>

        <?php endforeach; ?>
      </div>

      <form method="POST" onsubmit="return confirm('Lanjutkan ke pembayaran?')">
        <p><strong>Metode Pembayaran:</strong></p>
        <div class="payment-options">
          <input type="radio" id="cash" name="metode" value="Tunai" required>
          <label for="cash">Cash</label>

          <input type="radio" id="qris" name="metode" value="QRIS" required>
          <label for="qris">QRIS</label>
        </div>

        <div class="summary">
          <p><span>Sub Total :</span><span>Rp.<?= number_format($total, 0, ',', '.') ?></span></p>
          <p><span>Tax (10%) :</span><span>Rp.<?= number_format($tax, 0, ',', '.') ?></span></p>
          <p><span>Total :</span><span>Rp.<?= number_format($grandTotal, 0, ',', '.') ?></span></p>
        </div>

        <button type="submit" name="checkout">CONFIRM</button>
      </form>
    <?php endif; ?>
    </div>
  </div>
</body>
</html>
