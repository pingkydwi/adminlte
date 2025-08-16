<?php
include 'conf/config.php';
session_start();

// Fetch categories for filter
$kategori = mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY nama_kategori");

// Handle search
$search_results = [];
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_kategori = isset($_GET['kategori']) ? intval($_GET['kategori']) : 0;
$search_performed = ($search_keyword !== '' || $search_kategori > 0);
if ($search_performed) {
    $where = [];
    if ($search_keyword !== '') {
        $escaped = mysqli_real_escape_string($koneksi, $search_keyword);
        $where[] = "(b.judul LIKE '%$escaped%' OR b.isi LIKE '%$escaped%')";
    }
    if ($search_kategori > 0) {
        $where[] = "b.id_kategori = $search_kategori";
    }
    $where[] = "b.status = 'publish'";
    $where_sql = implode(' AND ', $where);
    $q_search = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, u.username FROM berita b LEFT JOIN tb_kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_pengirim=u.id WHERE $where_sql ORDER BY b.created_at DESC LIMIT 12");
    while ($row = mysqli_fetch_assoc($q_search)) {
        $search_results[] = $row;
    }
}

if (!isset($_GET['id'])) {
    header('Location: berita_list.php');
    exit;
}
$id = intval($_GET['id']);
$sql_detail = "SELECT b.*, k.nama_kategori, u.username FROM berita b LEFT JOIN tb_kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_pengirim=u.id WHERE b.id='$id'";
$q = mysqli_query($koneksi, $sql_detail);
if (!$q) {
    echo '<div class="alert alert-danger">Query error: ' . mysqli_error($koneksi) . '</div>';
    echo '<pre>' . htmlspecialchars($sql_detail) . '</pre>';
    exit;
}
if (!$data = mysqli_fetch_assoc($q)) {
    $cek = mysqli_query($koneksi, "SELECT * FROM berita WHERE id='$id'");
    if (mysqli_num_rows($cek) == 0) {
        echo '<div class="alert alert-danger">Data berita dengan id = ' . $id . ' tidak ada di tabel berita.</div>';
    } else {
        echo '<div class="alert alert-danger">Data berita ditemukan, tapi join kategori/user gagal. Cek data kategori dan user terkait.</div>';
    }
    echo '<pre>' . htmlspecialchars($sql_detail) . '</pre>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Berita</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <style>
        :root {
            --primary: #FFC107; /* Deep yellow */
            --secondary: #FFECB3; /* Medium yellow */
            --accent: #FFF3CD; /* Light yellow */
            --bg-gradient: linear-gradient(120deg, #FFF9E6 0%, #FFD54F 50%, #FFC107 100%); /* 120-degree yellow gradient */
            --card-bg: rgba(255, 243, 205, 0.95); /* Semi-transparent light yellow */
            --shadow: 0 8px 24px rgba(255, 193, 7, 0.2); /* Yellow-tinted shadow */
            --text-light: #1C2526; /* Dark gray for readability */
            --text-muted: #6B5B00; /* Muted yellow-brown for secondary text */
            --glow: 0 0 12px rgba(255, 193, 7, 0.3); /* Yellow glow effect */
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            font-family: 'Roboto', sans-serif;
            color: var(--text-light);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .search-header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--accent);
            padding: 1rem; /* Smaller padding */
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: flex-start; /* Different: left-aligned */
        }

        .search-toggle {
            background: var(--primary);
            border: none;
            border-radius: 0.5rem; /* Smaller radius */
            padding: 0.6rem 1.3rem;
            color: #fff;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .search-toggle:hover {
            background: #FFA000; /* Darker yellow */
        }

        .search-form {
            display: none;
            max-width: 1100px; /* Wider */
            margin: 1rem auto;
            background: var(--card-bg);
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: var(--shadow);
        }

        .search-form.active {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-form .form-control {
            background: #FFF9E6 !important; /* Very light yellow */
            border: 1px solid var(--accent);
            color: var(--text-light);
            border-radius: 0.5rem;
            padding: 0.6rem;
            font-size: 0.85rem;
        }

        .search-form select.form-control {
            background: #FFF9E6 !important;
            color: var(--text-light);
        }

        .search-form .form-control:focus {
            border-color: var(--primary);
            box-shadow: var(--glow);
        }

        .search-form .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 0.5rem;
            padding: 0.6rem 1.3rem;
            font-size: 0.85rem;
        }

        .search-form .btn-primary:hover {
            background: #FFA000;
        }

        .container-wrapper {
            max-width: 1200px; /* Wider than original */
            margin: 2rem auto; /* Smaller margin */
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 4fr 1fr; /* Different proportion */
            gap: 1.5rem; /* Smaller gap */
        }

        .glass-card {
            background: var(--card-bg);
            border-radius: 0.8rem; /* Smaller radius */
            box-shadow: var(--shadow);
            border: 1px solid var(--accent);
            padding: 1.5rem; /* Smaller padding */
        }

        .card-header h2 {
            font-size: 1.6rem; /* Smaller font */
            font-weight: 600;
            color: var(--text-light);
            margin: 0 0 1rem;
        }

        .meta-sidebar {
            background: rgba(255, 236, 179, 0.05); /* Yellow-tinted transparent */
            border-radius: 0.6rem;
            padding: 1rem;
        }

        .meta-item {
            background: var(--secondary);
            color: var(--text-light);
            padding: 0.4rem 0.9rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .img-preview {
            max-width: 100%;
            max-height: 400px; /* Taller image */
            border-radius: 0.6rem;
            border: 1px solid var(--accent);
            margin: 1rem 0;
            display: block;
            object-fit: cover;
        }

        .content {
            font-size: 0.9rem;
            line-height: 1.8; /* Larger line height */
            color: var(--text-light);
            background: rgba(255, 236, 179, 0.05); /* Yellow-tinted transparent */
            padding: 1rem;
            border-radius: 0.6rem;
        }

        .btn-back {
            background: var(--primary);
            border: none;
            border-radius: 0.5rem;
            padding: 0.6rem 1.3rem;
            color: #fff;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background: #FFA000;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 193, 7, 0.3); /* Yellow-tinted transparent */
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: var(--card-bg);
            border-radius: 0.8rem;
            padding: 1.5rem;
            max-width: 1000px; /* Wider */
            width: 95%;
            max-height: 90vh; /* Taller */
            overflow-y: auto;
            box-shadow: var(--shadow);
            border: 1px solid var(--accent);
        }

        .modal-header {
            background: var(--secondary);
            padding: 1rem;
            border-radius: 0.6rem 0.6rem 0 0;
            margin: -1.5rem -1.5rem 1rem;
        }

        .modal-header h4 {
            margin: 0;
            color: var(--text-light);
            font-size: 1.3rem;
        }

        .close-modal {
            background: var(--primary);
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            color: #fff;
            font-size: 0.9rem;
            float: right;
        }

        .close-modal:hover {
            background: #FFA000;
        }

        .search-results .card {
            background: rgba(255, 236, 179, 0.05); /* Yellow-tinted transparent */
            border: 1px solid var(--accent);
            border-radius: 0.6rem;
            margin-bottom: 1rem;
        }

        .search-results .card-img-top {
            height: 140px; /* Taller image */
            object-fit: cover;
            border-radius: 0.6rem 0.6rem 0 0;
        }

        .search-results .card-title a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .search-results .card-title a:hover {
            color: var(--primary);
        }

        .search-results .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
            border-radius: 0.5rem;
            padding: 0.4rem 0.9rem;
            font-size: 0.8rem;
        }

        .search-results .btn-outline-primary:hover {
            background: var(--primary);
            color: #fff;
        }

        .footer {
            background: var(--card-bg);
            border-top: 1px solid var(--accent);
            text-align: center;
            padding: 1rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            box-shadow: var(--shadow);
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .container-wrapper {
                grid-template-columns: 1fr;
            }
            .search-form.active {
                flex-direction: column;
            }
            .img-preview {
                max-height: 300px;
            }
            .search-results .card-img-top {
                height: 120px;
            }
            .footer {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
<header class="search-header">
    <div class="container-wrapper">
        <button class="search-toggle" onclick="toggleSearch()"><i class="fas fa-search mr-2"></i>Cari Berita</button>
        <form class="search-form" method="get" action="berita_detail.php">
            <div class="form-group">
                <input type="text" class="form-control" id="search" name="search" placeholder="Kata kunci..." value="<?= htmlspecialchars($search_keyword) ?>">
            </div>
            <div class="form-group">
                <select class="form-control" id="kategori" name="kategori">
                    <option value="0">Semua Kategori</option>
                    <?php
                    mysqli_data_seek($kategori, 0);
                    while ($row = mysqli_fetch_assoc($kategori)):
                    ?>
                        <option value="<?= $row['id'] ?>" <?= $search_kategori == $row['id'] ? 'selected' : '' ?>><?= htmlspecialchars($row['nama_kategori']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-2"></i>Cari</button>
            <?php if (isset($_GET['id'])): ?>
                <input type="hidden" name="id" value="<?= intval($_GET['id']) ?>">
            <?php endif; ?>
        </form>
    </div>
</header>
<div class="container-wrapper">
    <div class="glass-card">
        <div class="card-header">
            <h2><i class="fas fa-newspaper mr-2"></i><?= htmlspecialchars($data['judul']) ?></h2>
        </div>
        <div class="card-body">
            <?php if ($data['gambar']): ?>
                <img src="upload/<?= htmlspecialchars($data['gambar']) ?>" class="img-preview" alt="Gambar Berita">
            <?php endif; ?>
            <div class="content"><?= nl2br(htmlspecialchars($data['isi'])) ?></div>
            <a href="berita_list.php" class="btn-back"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar</a>
        </div>
    </div>
    <aside class="meta-sidebar">
        <div class="meta-item"><i class="fas fa-tag mr-1"></i> <?= htmlspecialchars($data['nama_kategori'] ?: 'Tidak ada kategori') ?></div>
        <div class="meta-item"><i class="fas fa-user mr-1"></i> <?= htmlspecialchars($data['username']) ?></div>
        <div class="meta-item"><i class="fas fa-info-circle mr-1"></i> <?= htmlspecialchars(ucfirst($data['status'])) ?></div>
        <div class="meta-item"><i class="far fa-clock mr-1"></i> <?= htmlspecialchars(date('d F Y H:i', strtotime($data['created_at']))) ?> WIB</div>
    </aside>
</div>
<?php if ($search_performed): ?>
    <div id="searchModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i class="fas fa-list mr-2"></i>Hasil Pencarian Berita</h4>
                <button class="close-modal" onclick="closeModal()">Tutup</button>
            </div>
            <div class="search-results">
                <?php if (count($search_results) === 0): ?>
                    <div class="alert alert-warning" style="background: var(--card-bg); border: 1px solid var(--accent); color: var(--text-light);">Tidak ada berita ditemukan untuk pencarian Anda.</div>
                <?php else: ?>
                    <?php foreach ($search_results as $berita): ?>
                        <div class="card">
                            <?php if ($berita['gambar']): ?>
                                <img src="upload/<?= htmlspecialchars($berita['gambar']) ?>" class="card-img-top" alt="Gambar Berita">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="berita_detail.php?id=<?= $berita['id'] ?>"><?= htmlspecialchars($berita['judul']) ?></a>
                                </h5>
                                <div class="mb-2"><span class="badge badge-info" style="background: var(--secondary); color: var(--text-light);"><i class="fas fa-tag mr-1"></i><?= htmlspecialchars($berita['nama_kategori'] ?: 'Tanpa Kategori') ?></span></div>
                                <div class="mb-2 text-muted" style="font-size: 0.85rem;">
                                    <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($berita['username']) ?>
                                    | <i class="far fa-clock mr-1"></i> <?= date('d M Y', strtotime($berita['created_at'])) ?>
                                </div>
                                <div class="mb-2" style="color: var(--text-muted); font-size: 0.9rem;">
                                    <?= htmlspecialchars(mb_strimwidth(strip_tags($berita['isi']), 0, 80, '...')) ?>
                                </div>
                                <a href="berita_detail.php?id=<?= $berita['id'] ?>" class="btn btn-outline-primary"><i class="fas fa-arrow-right mr-1"></i>Lihat Detail</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="footer">
    Today: 07:45 PM WIB, Sunday, June 15, 2025
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSearch() {
        const form = document.querySelector('.search-form');
        form.classList.toggle('active');
    }
    function openModal() {
        document.getElementById('searchModal').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('searchModal').style.display = 'none';
    }
    <?php if ($search_performed): ?>
        window.onload = openModal;
    <?php endif; ?>
</script>
</body>
</html>