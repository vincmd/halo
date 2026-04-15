<?php
session_start();
require 'koneksi.php';
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_barang = $_POST["nama_barang"];
    $harga = $_POST["harga"];
    $stok = $_POST["stok"];

    $sql = "INSERT INTO barang (nama_barang, harga, stok) VALUES (?, ?, ?)";
    $koneksi->execute_query($sql, [$nama_barang, $harga, $stok]);
    if ($koneksi->affected_rows > 0) {
        echo "Data barang berhasil ditambahkan!";
    } else {
        echo "Gagal menambahkan data barang!";
    }
    } elseif (isset($_POST['aksi']) && $_POST['aksi'] == 'hapus') {
        $id = $_POST["id"];
        $nama_barang = $_POST["nama_barang"];
        $harga = $_POST["harga"];
        $stok = $_POST["stok"];

        $sql = "DELETE FROM barang WHERE id=?";
        $koneksi->execute_query($sql, [$id]);
        if ($koneksi->affected_rows > 0) {
            echo "Data barang berhasil dihapus!";
        } else {
            echo "Gagal menghapus data barang!";
        }
    } elseif (isset($_POST['aksi']) && $_POST['aksi'] == 'edit') {
        $id = $_POST["id"];
        $nama_barang = $_POST["nama_barang"];
        $harga = $_POST["harga"];
        $stok = $_POST["stok"];

        $sql = "UPDATE barang SET nama_barang=?, harga=?, stok=? WHERE id=?";
        $koneksi->execute_query($sql, [$nama_barang, $harga, $stok, $id]);
        if ($koneksi->affected_rows > 0) {
            echo "Data barang berhasil diupdate!";
        } else {
            echo "Gagal mengupdate data barang!";
        }
    } elseif (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $sql = "DELETE FROM barang WHERE id=?";
        $koneksi->execute_query($sql, [$id]);
        if ($koneksi->affected_rows > 0) {
            header("Location: pendataan_barang.php?success=delete");
            exit();
        } else {
            echo "Gagal menghapus data barang!";
        }
    }

$result = $koneksi->query("SELECT * FROM barang ORDER BY id DESC");

// Cek jika ada parameter success
if (isset($_GET['success'])) {
    $success = $_GET['success'];
    if ($success == 'add') {
        echo "Data barang berhasil ditambahkan!";
    } elseif ($success == 'delete') {
        echo "Data barang berhasil dihapus!";
    } elseif ($success == 'edit') {
        echo "Data barang berhasil diupdate!";
    }
}
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
                <a href="register.php">register</a>
            <a href="logout.php">logout</a>
            </div>
    </div>
                <div class="container" style="margin-top: 20px;">
        <h2>Daftar Barang</h2>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Form Tambah/Edit Barang -->
        <div class="card" style="margin-bottom: 30px;">
            <h3>Form Barang <?php echo isset($_GET['edit']) ? '(Edit)' : '(Tambah)'; ?></h3>
            <form action="pendataan_barang.php" method="POST" style="margin-top: 20px;">
                <?php if(isset($_GET['edit'])): 
                    $id = $_GET['edit'];
                    $stmt = $koneksi->prepare("SELECT * FROM barang WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $data = $res->fetch_assoc();
                ?>
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">
                <?php else: ?>
                <input type="hidden" name="aksi" value="tambah">
                <input type="hidden" name="id" value="">
                <?php endif; ?>
                
         <div class="form-group">
                    <label>Nama Barang</label>
                    <input type="text" name="nama_barang" class="form-control" maxlength="100" value="<?= isset($data) ? htmlspecialchars($data['nama_barang']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" class="form-control" min="1" value="<?= isset($data) ? $data['harga'] : '' ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 30px;">
                    <label>Stok</label>
                    <input type="number" name="stok" class="form-control" min="0" value="<?= isset($data) ? $data['stok'] : '' ?>" required>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-success">Simpan Data</button>
                    <a href="dashboard.php" class="btn">Batal</a>
                </div>
            </form>
        </div>
            <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td><?= $row['stok'] ?></td>
                        <td>
                            <a href="pendataan_barang.php?edit=<?= $row['id'] ?>">Edit</a>
                            
                          
                            <a href="pendataan_barang.php?delete=<?= $row['id'] ?>">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($result->num_rows == 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Belum ada data barang.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>