<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}


// Inisialisasi keranjang
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Proses keranjang
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_to_cart'])) {
        $id_barang = $_POST['barang_id'];
        $qty = (int)$_POST['qty'];

        // Ambil info barang
        $stmt = $koneksi->prepare("SELECT nama_barang, harga, stok FROM barang WHERE id = ?");
        $stmt->bind_param("i", $id_barang);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($b = $res->fetch_assoc()) {
            if ($b['stok'] >= $qty) {
                // Cek if exist in cart
                $found = false;
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] == $id_barang) {
                        if ($item['qty'] + $qty <= $b['stok']) {
                            $item['qty'] += $qty;
                            $item['subtotal'] = $item['qty'] * $item['harga'];
                        }
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $_SESSION['cart'][] = [
                        'id' => $id_barang,
                        'nama' => $b['nama_barang'],
                        'harga' => $b['harga'],
                        'qty' => $qty,
                        'subtotal' => $qty * $b['harga']
                    ];
                }
            } else {
                $error = "Stok tidak mencukupi!";
            }
        }
    } elseif (isset($_POST['remove_item'])) {
        $key = $_POST['cart_key'];
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex
    } elseif (isset($_POST['checkout'])) {
        if (!empty($_SESSION['cart'])) {
            $id = $_SESSION['id'];
            $total_harga = array_sum(array_column($_SESSION['cart'], 'subtotal'));

            // Insert penjualan
            $stmt = $koneksi->prepare("INSERT INTO penjualan (user_id, total_harga) VALUES (?, ?)");
            $stmt->bind_param("id", $id, $total_harga);
            $stmt->execute();
            $penjualan_id = $koneksi->insert_id;

            // Insert detail dan update stok
            foreach ($_SESSION['cart'] as $item) {
                // Detail
                $stmt_detail = $koneksi->prepare("INSERT INTO detail_penjualan (penjualan_id, barang_id, jumlah, subtotal) VALUES (?, ?, ?, ?)");
                $stmt_detail->bind_param("iiid", $penjualan_id, $item['id'], $item['qty'], $item['subtotal']);
                $stmt_detail->execute();

                // Update stok
                $koneksi->query("UPDATE barang SET stok = stok - {$item['qty']} WHERE id = {$item['id']}");
            }

            // Kosongkan keranjang
            $_SESSION['cart'] = [];
            $success = "Transaksi berhasil! Kembalian: Rp " . number_format($_POST['bayar'] - $total_harga, 0, ',', '.');
        }
    }
}

$barang_list = $koneksi->query("SELECT id, nama_barang, harga, stok FROM barang WHERE stok > 0");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Kasir - Aplikasi Kasir</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function checkBayar() {
            var total = <?= empty($_SESSION['cart']) ? 0 : array_sum(array_column($_SESSION['cart'], 'subtotal')) ?>;
            var bayar = document.getElementById("bayar").value;
            if (bayar < total) {
                alert("Uang bayar kurang dari total belanja!");
                return false;
            }
            return confirm("Proses transaksi ini?");
        }
    </script>
</head>
<body>
   
        <a href="dashboard.php">kasir</a>   
            <a href="pendataan_barang.php">Pendataan barang</a>
            <a href="penjualan_barang.php">Penjualan barang</a>
                <a href="register.php">register</a>
            <a href="logout.php">logout</a>
    <div class="container" style="margin-top: 20px; margin-bottom: 20px;">
        <h2>Transaksi Penjualan</h2>
           

        <?php if(isset($error)): ?>
            <div class="alert alert-danger" style="margin-top: 20px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if(isset($success)): ?>
            <div class="alert alert-success" style="margin-top: 20px;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <div>
            <!-- Keranjang Belanja -->
            <div class="card">
                <h3>Keranjang</h3>
                <table style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grand_total = 0;
                            foreach($_SESSION['cart'] as $k => $item): 
                                $grand_total += $item['subtotal'];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nama']) ?></td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td><?= $item['qty'] ?></td>
                                <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                <td>
                                    <form action="penjualan_barang.php" method="POST">
                                        <input type="hidden" name="remove_item" value="1">
                                        <input type="hidden" name="cart_key" value="<?= $k ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">X</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($_SESSION['cart'])): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Keranjang masih kosong</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
            </div>

            <!-- Panel Checkout & Add Item -->
            <div>
                <div class="card" style="margin-bottom: 20px;">
                    <h3>Pilih Barang</h3>
                    <form action="penjualan_barang.php" method="POST" style="margin-top: 20px;">
                        <input type="hidden" name="add_to_cart" value="1">
                        <div class="form-group">
                            <label>Barang</label>
                            <select name="barang_id" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Barang --</option>
                                <?php while($b = $barang_list->fetch_assoc()): ?>
                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nama_barang']) ?> - Rp <?= number_format($b['harga'], 0, ',', '.') ?> (Stok: <?= $b['stok'] ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 30px;">
                            <label>Jumlah (Qty)</label>
                            <input type="number" name="qty" class="form-control" value="1" min="1" required>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; display: block;">Tambah ke Keranjang</button>
                    </form>
                </div>

                <div class="card" style="background: #f9f9f9;">
                    <h3>Checkout</h3>
                    <div style="font-size: 20px; font-weight: 700; margin: 15px 0; color: #007bff;">
                        Total: Rp <?= number_format($grand_total, 0, ',', '.') ?>
                    </div>
                    
                    <form action="penjualan_barang.php" method="POST" onsubmit="return checkBayar()">
                        <input type="hidden" name="checkout" value="1">
                        <div class="form-group">
                            <label>Nominal Bayar (Rp)</label>
                            <input type="number" name="bayar" id="bayar" class="form-control" min="<?= $grand_total ?>" required <?= empty($_SESSION['cart']) ? 'disabled' : '' ?>>
                        </div>
                        <button type="submit" class="btn btn-success" style="width: 100%; display: block;" <?= empty($_SESSION['cart']) ? 'disabled' : '' ?>>Bayar & Selesaikan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
