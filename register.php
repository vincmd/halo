<?php
session_start();
require 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = md5($_POST["password"]);
    $role = $_POST["role"];

    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $koneksi->execute_query($sql, [$username, $password, $role]);
    if ($koneksi->affected_rows > 0) {
        echo "Registrasi berhasil!";
    } else {
        echo "Registrasi gagal!";
    }
  
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="" method="POST">
        <label for="username">Masukkan username anda</label><br>
        <input type="text" id="username" name="username" placeholder="Username" required><br>
        <label for="password">Masukkan password anda</label><br>
        <input type="password" id="password" name="password" placeholder="Password" required><br>
        <label for="role">Pilih role anda</label><br>
        <select name="role" id="role" class="form-control" required>
            <option value="kasir">Kasir</option>
            <option value="admin">admin</option>
            </select>
    <button type="submit">Register</button><br>
     <a href="dashboard.php">kembali</a>
</body>

</html>