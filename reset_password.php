<?php
include 'conf/config.php';
$token = isset($_GET['token']) ? $_GET['token'] : '';
if (empty($token)) { die('Token tidak valid.'); }
// Proses reset password
if (isset($_POST['reset'])) {
    $password = trim($_POST['password']);
    $konfirmasi = trim($_POST['konfirmasi']);
    if (empty($password) || empty($konfirmasi)) {
        $error = "Semua field harus diisi.";
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        // Cari user dengan token
        $cek = mysqli_query($koneksi, "SELECT * FROM tb_users WHERE reset_token='$token'");
        if (mysqli_num_rows($cek) == 1) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($koneksi, "UPDATE tb_users SET password='$password_hash', reset_token=NULL WHERE reset_token='$token'");
            $success = "Password berhasil direset. Silakan <a href='index.php'>login</a>.";
        } else {
            $error = "Token tidak valid atau sudah digunakan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <b>Reset</b> Password
    </div>
    <div class="card">
        <div class="card-body login-card-body">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <?php if(!isset($success)): ?>
            <form action="" method="post">
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password Baru" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="konfirmasi" class="form-control" placeholder="Konfirmasi Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" name="reset" class="btn btn-primary btn-block">Reset Password</button>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>
