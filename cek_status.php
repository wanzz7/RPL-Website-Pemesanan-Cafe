<?php
session_start();
header('Content-Type: application/json');

include 'db.php';

$nama_pemesan = $_SESSION['user']['username'] ?? '';
if (!$nama_pemesan) {
  echo json_encode(['status' => 'unauthorized']);
  exit;
}

$stmt = $conn->prepare("SELECT status_pesanan FROM pesanan WHERE nama_pemesan = ? ORDER BY tanggal_pesanan DESC LIMIT 1");
$stmt->bind_param("s", $nama_pemesan);
$stmt->execute();
$result = $stmt->get_result();

$status = $result->fetch_assoc()['status_pesanan'] ?? 'tidak ada';

echo json_encode(['status' => $status]);
