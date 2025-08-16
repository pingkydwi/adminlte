<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include ('config.php');
session_start();

// Validasi input
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header('location:../index.php?login=failed');
    exit;
}
$username = trim($_POST['username']);
$password = trim($_POST['password']);

// Cari user berdasarkan username
$query = mysqli_query($koneksi, "SELECT * FROM tb_users WHERE username= '$username'");
if ($user = mysqli_fetch_assoc($query)) {
    // Verifikasi password hash
    if (password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['level'] = $user['level'];
        header('location:../app/index.php');
        exit;
    }
}
header('location:../index.php?login=failed');
exit;

?>