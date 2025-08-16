<?php
// Endpoint login sosial (Google, Github, Microsoft)
include 'hybridauth.php';
session_start();

use Hybridauth\Hybridauth;

$provider = isset($_GET['provider']) ? $_GET['provider'] : '';
if (!$provider) {
    die('Provider tidak valid.');
}
try {
    $hybridauth = new Hybridauth($config);
    $adapter = $hybridauth->authenticate($provider);
    $userProfile = $adapter->getUserProfile();
    // Ambil data user dari provider
    $email = $userProfile->email;
    $name = $userProfile->displayName;
    $username = $userProfile->identifier;
    // Cek user di database
    include 'conf/config.php';
    $cek = mysqli_query($koneksi, "SELECT * FROM tb_users WHERE email='$email'");
    if ($user = mysqli_fetch_assoc($cek)) {
        // Login user lama
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['level'] = $user['level'];
    } else {
        // Registrasi user baru (level default: wartawan)
        $password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        mysqli_query($koneksi, "INSERT INTO tb_users (username, password, email, nama_lengkap, level) VALUES ('$username', '$password', '$email', '$name', 'wartawan')");
        $id = mysqli_insert_id($koneksi);
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        $_SESSION['level'] = 'wartawan';
    }
    header('location: app');
    exit;
} catch(Exception $e) {
    echo 'Gagal login sosial: ' . $e->getMessage();
}
