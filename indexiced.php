<?php
session_start();
include 'db.php';

$query = $conn->query("SELECT * FROM menu WHERE kategori = 'iced' AND aktif = 1");
$menuList = [];
while ($row = $query->fetch_assoc()) {
  $menuList[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Coffee Menu</title>
  <link rel="stylesheet" href="assets/index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <div class="container">

    <a href="login.php" class="logout-btn" onclick="return confirmLogout()" title="Logout">
      <i class="fas fa-sign-out-alt"></i>
    </a>


    <aside class="sidebar">
      <a href="indexhot.php">-Hot Coffee</a>
      <a href="indexiced.php">-Iced Coffee</a>
      <a href="indexothers.php">-Others</a>
      <a href="statuspesanan.php">-Status Pesanan</a>
    </aside>


    <main class="content">
      <div class="top-bar">
        <p>Welcome, <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Guest') ?></p>
      </div>

      <h1>COFFEE</h1>

      <div class="menu-grid">
        <?php foreach ($menuList as $menu): ?>
          <div class="menu-item">
            <img src="img/<?= htmlspecialchars($menu['gambar']) ?>" alt="<?= htmlspecialchars($menu['nama_menu']) ?>">
            <h3><?= htmlspecialchars($menu['nama_menu']) ?></h3>
            <p><?= htmlspecialchars($menu['deskripsi']) ?></p>
            <div class="price-add">
              <span>Rp.<?= number_format($menu['harga'], 0, ',', '.') ?></span>
              <button 
                class="add-btn"
                data-id_menu="<?= $menu['id_menu'] ?>"
              >+</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>

  <script>
    function confirmLogout() {
      return confirm("Yakin ingin logout?");
    }

    const addButtons = document.querySelectorAll('.add-btn');

    addButtons.forEach(button => {
      button.addEventListener('click', () => {
        const id_menu = parseInt(button.getAttribute('data-id_menu'));

        fetch('keranjang.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ id_menu })
        })
        .then(response => {
          if (response.ok) {
            window.location.href = "pembayaran.php";
          } else if (response.status === 401) {
            alert("Silakan login terlebih dahulu.");
          } else {
            alert("Gagal menambahkan ke keranjang.");
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
      });
    });
  </script>
</body>
</html>
