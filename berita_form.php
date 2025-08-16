<?php
include_once 'conf/config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['level'] !== 'wartawan') {
    header('Location: index.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// Fetch categories
$kategori = mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY nama_kategori");

// Edit news if ID is provided
$judul = $isi = $id_kategori = $gambar = '';
$edit = false;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $q = mysqli_query($koneksi, "SELECT * FROM berita WHERE id='$id' AND id_pengirim='$user_id'");
    if ($data = mysqli_fetch_assoc($q)) {
        $judul = $data['judul'];
        $isi = $data['isi'];
        $id_kategori = $data['id_kategori'];
        $gambar = $data['gambar'];
        $edit = true;
    }
}

// Process form submission
if (isset($_POST['simpan'])) {
    $judul = trim($_POST['judul']);
    $isi = trim($_POST['isi']);
    $id_kategori = intval($_POST['id_kategori']);
    $gambar_name = $gambar;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar_name = time() . '_' . rand(1000, 9999) . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'upload/' . $gambar_name);
    }
    if ($edit) {
        $sql = "UPDATE berita SET judul='$judul', isi='$isi', id_kategori='$id_kategori', gambar='$gambar_name' WHERE id='$id' AND id_pengirim='$user_id'";
    } else {
        $sql = "INSERT INTO berita (judul, isi, id_kategori, gambar, id_pengirim, status) VALUES ('$judul', '$isi', '$id_kategori', '$gambar_name', '$user_id', 'draft')";
    }
    if (mysqli_query($koneksi, $sql)) {
        header('Location: berita_list.php');
        exit;
    } else {
        $error = 'Gagal menyimpan berita.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit ? 'Edit' : 'Tambah' ?> Berita</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <style>
        :root {
            --primary: #FFC107; /* Deep yellow */
            --secondary: #FFECB3; /* Medium yellow */
            --accent: #FFF3CD; /* Light yellow */
            --bg-gradient: linear-gradient(to bottom, #FFF9E6 0%, #FFD54F 50%, #FFC107 100%); /* Vertical yellow gradient */
            --card-bg: rgba(255, 243, 205, 0.95); /* Semi-transparent light yellow */
            --shadow: 0 8px 24px rgba(255, 193, 7, 0.2); /* Yellow-tinted shadow */
            --text-light: #1C2526; /* Dark gray for readability */
            --text-muted: #6B5B00; /* Muted yellow-brown for secondary text */
            --glow: 0 0 12px rgba(255, 193, 7, 0.3); /* Yellow glow effect */
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            color: var(--text-light);
            font-family: 'Roboto', sans-serif;
            display: flex;
            flex-direction: column;
            padding: 1.5rem; /* Reduced padding */
        }

        .container-wrapper {
            width: 100%;
            max-width: 1100px; /* Wider than original */
            margin: 0 auto;
            flex-grow: 1;
        }

        .news-card {
            background: var(--card-bg);
            border-radius: 0.8rem; /* Smaller radius than original */
            box-shadow: var(--shadow);
            padding: 2rem; /* Slightly reduced padding */
            border: 1px solid var(--accent);
        }

        .card-header {
            text-align: center; /* Different: centered header */
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--accent);
            padding-bottom: 0.8rem;
        }

        .card-header h3 {
            color: var(--text-light);
            font-weight: 600;
            font-size: 1.8rem; /* Smaller font size */
            margin: 0;
        }

        .badge-status {
            background: var(--secondary);
            color: var(--text-light);
            font-size: 0.85rem;
            padding: 0.4rem 0.9rem;
            border-radius: 0.8rem;
            margin-left: 0.8rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 3fr 1fr; /* Different proportions */
            gap: 1.5rem; /* Smaller gap */
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            color: var(--text-light);
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        label[for*='kategori'], .kategori-label {
            color: var(--text-light) !important;
        }

        .form-control, select, textarea {
            background: #FFF9E6 !important; /* Very light yellow */
            border: 1px solid var(--accent) !important;
            border-radius: 0.5rem !important; /* Smaller radius */
            color: var(--text-light) !important;
            font-size: 0.95rem;
            padding: 0.8rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus, select:focus, textarea:focus {
            border-color: var(--primary) !important;
            box-shadow: var(--glow);
        }

        select, select option {
            background: #FFF9E6 !important;
            color: var(--text-light) !important;
        }

        textarea.form-control {
            min-height: 180px; /* Taller textarea */
            resize: vertical;
            line-height: 1.6;
        }

        .custom-file-input {
            border-radius: 0.5rem;
            cursor: pointer;
        }

        .custom-file-label {
            background: rgba(255, 236, 179, 0.1); /* Yellow-tinted transparent */
            border: 1px solid var(--accent);
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .custom-file-label::after {
            background: var(--primary);
            color: #fff;
            border-radius: 0 0.5rem 0.5rem 0;
            padding: 0.6rem 1rem;
        }

        .img-preview {
            max-width: 200px; /* Larger preview */
            margin-top: 0.6rem;
            border-radius: 0.5rem;
            border: 1px solid var(--accent);
            background: #FFF9E6;
            padding: 3px;
            transition: transform 0.2s;
        }

        .img-preview:hover {
            transform: scale(1.05);
        }

        .alert {
            background: #FFF9E6; /* Very light yellow for alert */
            border: 1px solid var(--accent);
            border-radius: 0.5rem;
            padding: 0.8rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: var(--text-light);
        }

        .form-text {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.3rem;
        }

        .button-group {
            display: flex;
            justify-content: center; /* Different: centered buttons */
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn-primary, .btn-secondary {
            background: var(--primary);
            border: none;
            border-radius: 0.5rem;
            padding: 0.8rem 1.8rem;
            font-weight: 600;
            color: #fff;
            font-size: 0.95rem;
            transition: background 0.2s, transform 0.2s;
        }

        .btn-secondary {
            background: var(--secondary);
            color: var(--text-light);
        }

        .btn-primary:hover {
            background: #FFA000; /* Darker yellow */
            transform: translateY(-2px);
        }

        .btn-secondary:hover {
            background: #FFD54F; /* Brighter yellow */
            transform: translateY(-2px);
        }

        .footer {
            background: var(--card-bg);
            border-top: 1px solid var(--accent);
            text-align: center;
            padding: 1rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            box-shadow: var(--shadow);
            margin-top: 1.5rem;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
            .news-card {
                padding: 1.5rem;
            }
            .card-header h3 {
                font-size: 1.5rem;
            }
            .form-control, select, textarea {
                font-size: 0.9rem;
                padding: 0.6rem;
            }
            .button-group {
                flex-direction: column;
                align-items: center;
            }
            .img-preview {
                max-width: 160px;
            }
            .footer {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
<div class="container-wrapper">
    <div class="news-card">
        <div class="card-header">
            <h3><i class="fas fa-edit mr-2"></i><?= $edit ? 'Edit' : 'Tambah' ?> Berita</h3>
            <span class="badge-status"><?= $edit ? 'Edit Mode' : 'Tambah Baru' ?></span>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>
            <form action="" method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Judul Berita</label>
                        <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($judul) ?>" required placeholder="Masukkan judul berita">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-tags"></i> Kategori</label>
                        <select name="id_kategori" id="id_kategori" class="form-control custom-select" required>
                            <option value="" disabled hidden <?= $id_kategori == '' ? 'selected' : '' ?>>Pilih Kategori</option>
                            <?php
                            if ($kategori instanceof mysqli_result && $kategori->num_rows > 0) mysqli_data_seek($kategori, 0);
                            while ($row = mysqli_fetch_assoc($kategori)): ?>
                                <option value="<?= $row['id'] ?>" <?= $id_kategori == $row['id'] ? 'selected' : '' ?>><?= htmlspecialchars($row['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div id="kategoriError" class="form-text text-danger" style="display:none;"><i class="fas fa-exclamation-triangle"></i> Silakan pilih kategori!</div>
                    </div>
                    <div class="form-group full-width">
                        <label><i class="fas fa-align-left"></i> Isi Berita</label>
                        <textarea name="isi" class="form-control" rows="5" required placeholder="Tulis isi berita di sini..."><?= htmlspecialchars($isi) ?></textarea>
                        <small class="form-text"><i class="fas fa-info-circle mr-1"></i>Gunakan bahasa yang jelas dan informatif.</small>
                    </div>
                    <div class="form-group full-width">
                        <label><i class="fas fa-image"></i> Gambar</label>
                        <?php if ($gambar): ?>
                            <div>
                                <img src="upload/<?= htmlspecialchars($gambar) ?>" class="img-preview">
                            </div>
                        <?php endif; ?>
                        <div class="custom-file">
                            <input type="file" name="gambar" class="custom-file-input" id="gambarInput">
                            <label class="custom-file-label" for="gambarInput">Pilih file gambar...</label>
                        </div>
                        <small class="form-text"><i class="fas fa-info-circle mr-1"></i>Format: jpg, png, max 2MB.</small>
                    </div>
                </div>
                <div class="button-group">
                    <button type="submit" name="simpan" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan</button>
                    <a href="berita_list.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
    <div class="footer">
        Today: 07:40 PM WIB, Sunday, June 15, 2025
    </div>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script>
$(function () {
    bsCustomFileInput.init();
    $('form').on('submit', function(e) {
        var kategori = $('#id_kategori').val();
        if (!kategori) {
            $('#kategoriError').show();
            $('#id_kategori').addClass('is-invalid');
            $('#id_kategori').focus();
            e.preventDefault();
            return false;
        } else {
            $('#kategoriError').hide();
            $('#id_kategori').removeClass('is-invalid');
        }
    });
    $('#id_kategori').on('change', function() {
        if ($(this).val()) {
            $('#kategoriError').hide();
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
</body>
</html>