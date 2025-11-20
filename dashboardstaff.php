<?php
session_start();
include 'db.php';

// Cek login staff
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Update status pesanan
if (isset($_GET['update_id'])) {
    $id = intval($_GET['update_id']);
    $q = $conn->query("SELECT status_pesanan FROM pesanan WHERE id_pesanan = $id");
    if ($q->num_rows > 0) {
        $status = $q->fetch_assoc()['status_pesanan'];
        $next = $status === 'diproses' ? 'siap ambil' : ($status === 'siap ambil' ? 'selesai' : 'selesai');
        $conn->query("UPDATE pesanan SET status_pesanan = '$next' WHERE id_pesanan = $id");
        header("Location: dashboardstaff.php");
        exit;
    }
}


$query = "
SELECT 
    p.id_pesanan,
    p.nama_pemesan,
    p.tanggal_pesanan,
    p.status_pesanan,
    GROUP_CONCAT(CONCAT(d.nama_menu, ' x', d.quantity) SEPARATOR ', ') AS daftar_menu
FROM pesanan p
JOIN detail_pesanan d ON p.id_pesanan = d.id_pesanan
GROUP BY p.id_pesanan
ORDER BY p.id_pesanan DESC";
$data = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Staff</title>
  <link rel="stylesheet" href="assets/dashboardstaff.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<a href="login.php" class="logout-btn" onclick="return confirmLogout()" title="Logout">
      <i class="fas fa-sign-out-alt"></i>
    </a>

<div class="dashboard">
  <h2>☕<br>COFFEE<br>DASHBOARD STAFF</h2>
  <p>Welcome, Staff</p>

  <table>
    <tr>
      <th>No</th>
      <th>Nama</th>
      <th>Menu</th>
      <th>Tgl</th>
      <th>Status</th>
      <th>Update</th>
    </tr>
    <?php $no = 1; ?>
    <?php while ($row = $data->fetch_assoc()): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['nama_pemesan']) ?></td>
        <td><?= htmlspecialchars($row['daftar_menu']) ?></td>
        <td><?= date('d-m', strtotime($row['tanggal_pesanan'])) ?></td>
        <td class="status-<?= str_replace(' ', '-', strtolower($row['status_pesanan'])) ?>">
          <?= ucfirst($row['status_pesanan']) ?>
        </td>
        <td>
          <?php if ($row['status_pesanan'] !== 'selesai'): ?>
            <a href="?update_id=<?= $row['id_pesanan'] ?>" class="update-btn">🔁</a>
          <?php else: ?>
            ✅
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

</body>
</html>
