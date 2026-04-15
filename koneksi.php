<?php
$hostname="localhost";
$username="root";
$password="";
$database="usk_kasir";
$koneksi=new mysqli($hostname,$username,$password,$database);
if (!$koneksi) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>

