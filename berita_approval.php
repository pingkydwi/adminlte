<?php
include 'conf/config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['level']!='editor') {
    header('location: index.php');
    exit;
}
if (!isset($_GET['id']) || !isset($_GET['aksi'])) {
    header('location: berita_list.php');
    exit;
}
$id = intval($_GET['id']);
$aksi = $_GET['aksi'];
if ($aksi == 'publish') {
    $sql = "UPDATE berita SET status='published' WHERE id='$id' AND status='draft'";
} elseif ($aksi == 'reject') {
    $sql = "UPDATE berita SET status='rejected' WHERE id='$id' AND status='draft'";
}
if (isset($sql)) {
    mysqli_query($koneksi, $sql);
}
header('location: berita_list.php');
exit;
