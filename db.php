<?php
$conn = new mysqli("localhost", "root", "", "kopirpl");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);
?>
