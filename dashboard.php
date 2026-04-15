<?php
session_start();
require 'koneksi.php';
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION["role"];
$username = $_SESSION["username"];

$total_barang = $koneksi->query("SELECT COUNT(id) AS total FROM barang")->fetch_assoc()['total'];
$total_penjualan = $koneksi->query("SELECT COUNT(id) AS total FROM penjualan")->fetch_assoc()['total'];
$pendapatan = $koneksi->query("SELECT SUM(total_harga) AS total FROM penjualan")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>
<body>
    <div class="navbar">
        <a href="dashboard.php">kasir</a>   
            <a href="pendataan_barang.php">Pendataan barang</a>
            <a href="penjualan_barang.php">Penjualan barang</a>
            <?php if($role === 'admin'): ?>
                <a href="register.php">register</a>
            <?php endif; ?>
            <a href="logout.php">logout</a>
            </div>
    </div>
    
    <h3>Selamat datang, <?php echo $username; ?>!</h3>
    <div class="grid">
            <div class="stat-card">
                <h3>Total Barang</h3>
                <div class="value"><?= number_format($total_barang, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Transaksi</h3>
                <div class="value"><?= number_format($total_penjualan, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Pendapatan</h3>
                <div class="value">Rp <?= number_format($pendapatan, 0, ',', '.') ?></div>
            </div>
        </div>
        
</body>
</html>