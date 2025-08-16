<?php
include 'conf/config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['level']!='admin') {
    header('location: index.php');
    exit;
}
// Tambah kategori
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama_kategori']);
    if (!empty($nama)) {
        mysqli_query($koneksi, "INSERT INTO tb_kategori (nama_kategori) VALUES ('$nama')");
    }
}
// Edit kategori
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $nama = trim($_POST['nama_kategori']);
    if (!empty($nama)) {
        mysqli_query($koneksi, "UPDATE tb_kategori SET nama_kategori='$nama' WHERE id='$id'");
    }
}
// Hapus kategori
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM tb_kategori WHERE id='$id'");
}
$kategori = mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY nama_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Kategori</title>
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Daftar Kategori</h2>
    <form action="" method="post" class="form-inline mb-3">
        <input type="text" name="nama_kategori" class="form-control mr-2" placeholder="Nama Kategori" required>
        <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
    </form>
    <table class="table table-bordered">
        <thead><tr><th>Nama Kategori</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($kategori)): ?>
            <tr>
                <form action="" method="post" style="display:inline-block;">
                <td>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="text" name="nama_kategori" value="<?= htmlspecialchars($row['nama_kategori']) ?>" class="form-control" required>
                </td>
                <td>
                    <button type="submit" name="edit" class="btn btn-warning btn-sm">Edit</button>
                    <a href="?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kategori?')">Hapus</a>
                </td>
                </form>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
