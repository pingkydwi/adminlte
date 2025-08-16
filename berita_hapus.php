<?php
include 'conf/config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['level']!='wartawan') {
    header('location: index.php');
    exit;
}
if (!isset($_GET['id'])) {
    header('location: berita_list.php');
    exit;
}
$id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
// Hapus gambar jika ada
$q = mysqli_query($koneksi, "SELECT gambar FROM berita WHERE id='$id' AND id_pengirim='$user_id'");
if ($data = mysqli_fetch_assoc($q)) {
    if ($data['gambar'] && file_exists('upload/'.$data['gambar'])) {
        unlink('upload/'.$data['gambar']);
    }
    mysqli_query($koneksi, "DELETE FROM berita WHERE id='$id' AND id_pengirim='$user_id'");
}
header('location: berita_list.php');
exit;
