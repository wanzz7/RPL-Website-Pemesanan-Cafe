    <?php
    include 'db.php';

    $data = json_decode(file_get_contents("php://input"), true);

    $userId = $_SESSION['user']['id'] ?? null;
    if (!$userId) {
      http_response_code(401);
      echo "Unauthorized";
      exit;
    }


    $id_menu = (int)($data['id_menu'] ?? 0);
    if (!$id_menu) {
      http_response_code(400);
      echo "ID menu tidak valid";
      exit;
    }

    
    $menuResult = $conn->query("SELECT * FROM menu WHERE id_menu = $id_menu");
    $menu = $menuResult->fetch_assoc();
    if (!$menu) {
      http_response_code(404);
      echo "Menu tidak ditemukan";
      exit;
    }

    $nama_menu = $conn->real_escape_string($menu['nama_menu']);
    $harga = (int)$menu['harga'];
    $gambar = $conn->real_escape_string($menu['gambar']);


    $check = $conn->query("SELECT * FROM keranjang WHERE user_id = $userId AND id_menu = $id_menu");
    if ($check->num_rows > 0) {
      $conn->query("UPDATE keranjang SET quantity = quantity + 1 WHERE user_id = $userId AND id_menu = $id_menu");
    } else {
      $conn->query("INSERT INTO keranjang (user_id, id_menu, nama_menu, harga, gambar, quantity) 
                    VALUES ($userId, $id_menu, '$nama_menu', $harga, '$gambar', 1)");
    }

    echo "Item berhasil ditambahkan ke keranjang";
    ?>
