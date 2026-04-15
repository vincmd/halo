<?php
session_start();
require 'koneksi.php';
    if ($_SERVER["REQUEST_METHOD"] == "POST") { 
        $username = $_POST["username"]; 
        $password = md5($_POST["password"]); 
        $sql = "SELECT id,username,password FROM users where username=?";
        $row = $koneksi->execute_query($sql, [$username])->fetch_assoc();
        if ($password === $row['password']) {
        $_SESSION["id"] = $row["id"];
        $_SESSION["username"] = $row["username"];
        header('location:dashboard.php');
        } else {
        echo "password salah atau username tidak ditemukan!";
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
    <div class="container1">  
    <h1>Form Login</h1>
    <form action="" method="POST">
        <div>
        <label for="username">Masukkan username anda</label><br>
        <input type="text" id="username" name="username" placeholder="Username"required><br>
        </div>
        <div>
        <label for="password">Masukkan password anda</label><br>
        <input type="password" id="password" name="password" placeholder="Password" required><br>
        </div>
    <button type="submit">Login</button>
    </form>
    </div>
</body>
</html>