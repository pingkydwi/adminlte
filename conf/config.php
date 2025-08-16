<?php
// Konfigurasi koneksi MySQL XAMPP
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'adminlte';

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Mengecek koneksi
if (!$koneksi) {
    die('Koneksi ke database gagal: ' . mysqli_connect_error());
}
?>