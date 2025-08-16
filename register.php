<?php
include 'conf/config.php';
// Proses form register
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    // Validasi sederhana
    if (empty($username) || empty($password) || empty($email) || empty($nama_lengkap)) {
        $error = "Semua field harus diisi.";
    } else {
        // Cek username/email sudah ada
        $cek = mysqli_query($koneksi, "SELECT * FROM tb_users WHERE username='$username' OR email='$email'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Username atau email sudah terdaftar.";
        } else {
            // Simpan user baru (level default wartawan)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $simpan = mysqli_query($koneksi, "INSERT INTO tb_users (username, password, email, nama_lengkap, level) VALUES ('$username', '$password_hash', '$email', '$nama_lengkap', 'wartawan')");
            if ($simpan) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Registrasi gagal. Silakan coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <style>
        :root {
            --primary: #FFC107; /* Deep yellow */
            --secondary: #FFECB3; /* Medium yellow */
            --accent: #FFF3CD; /* Light yellow */
            --bg-gradient: linear-gradient(90deg, #FFF9E6 0%, #FFD54F 50%, #FFC107 100%); /* 90-degree yellow gradient */
            --card-bg: rgba(255, 243, 205, 0.95); /* Semi-transparent light yellow */
            --shadow: 0 6px 20px rgba(255, 193, 7, 0.2); /* Yellow-tinted shadow */
            --text-light: #1C2526; /* Dark gray for readability */
            --text-muted: #6B5B00; /* Muted yellow-brown for secondary text */
            --glow: 0 0 10px rgba(255, 193, 7, 0.3); /* Yellow glow effect */
        }

        body {
            background: var(--bg-gradient);
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .register-box {
            background: var(--card-bg);
            border: 1px solid var(--accent);
            border-radius: 0.6rem; /* Smaller radius */
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 500px; /* Wider than original */
            margin: 1.5rem auto; /* Smaller margin */
            padding: 0;
        }

        .card-header {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            padding: 1rem; /* Smaller padding */
            border-radius: 0.6rem 0.6rem 0 0;
            text-align: center;
        }

        .register-logo {
            font-size: 1.6rem; /* Smaller font */
            color: var(--text-light);
            margin: 0;
            font-weight: 600;
        }

        .card-body {
            padding: 1.2rem; /* Smaller padding */
        }

        .form-group {
            margin-bottom: 1.5rem; /* Smaller margin */
            text-align: left;
        }

        .form-group label {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 0.4rem;
            display: block;
        }

        .form-group .form-control {
            background: #FFF9E6 !important; /* Very light yellow */
            border: 1px solid var(--accent);
            border-radius: 0.3rem; /* Smaller radius */
            color: var(--text-light);
            font-size: 0.9rem;
            padding: 0.6rem;
            width: 100%;
        }

        .form-group .form-control:focus {
            border-color: var(--primary);
            box-shadow: var(--glow);
        }

        .form-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.8rem; /* Smaller gap */
        }

        button[type="submit"], .btn-primary {
            background: var(--primary);
            border: none;
            color: #fff;
            padding: 0.6rem 1.8rem; /* Smaller padding */
            border-radius: 0.3rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            width: 100%;
        }

        button[type="submit"]:hover, .btn-primary:hover {
            background: #FFA000; /* Darker yellow */
            transform: translateY(-2px);
        }

        .links a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .links a:hover {
            color: #FFA000;
            text-decoration: underline;
        }

        .alert {
            border-radius: 0.3rem;
            padding: 0.7rem;
            margin-bottom: 1.2rem;
            text-align: left;
            font-size: 0.9rem;
        }

        .alert-danger {
            background: #FFF9E6; /* Very light yellow */
            border: 1px solid var(--accent);
            color: var(--text-light);
        }

        .alert-success {
            background: #FFF9E6; /* Very light yellow */
            border: 1px solid var(--accent);
            color: var(--text-light);
        }

        .footer {
            position: sticky;
            bottom: 0;
            width: 100%;
            background: var(--card-bg);
            border-top: 1px solid var(--accent);
            text-align: center;
            padding: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            box-shadow: var(--shadow);
        }

        @media (max-width: 576px) {
            .register-box {
                max-width: 95%;
                margin: 1rem auto;
            }
            .register-logo {
                font-size: 1.4rem;
            }
            .form-group .form-control {
                font-size: 0.85rem;
                padding: 0.5rem;
            }
            .alert {
                font-size: 0.85rem;
            }
            .footer {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-box">
        <div class="card-header">
            <div class="register-logo">
                <b>Register</b> User
            </div>
        </div>
        <div class="card-body register-card-body">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <form action="" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" placeholder="Nama Lengkap" required>
                </div>
                <div class="form-actions">
                    <button type="submit" name="register" class="btn btn-primary">Daftar</button>
                    <div class="links"><a href="index.php">Sudah punya akun? Login</a></div>
                </div>
            </form>
        </div>
    </div>
    <div class="footer">
        Today: 07:48 PM WIB, Sunday, June 15, 2025
    </div>
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>