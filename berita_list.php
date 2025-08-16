<?php
include_once 'conf/config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$level = $_SESSION['level'];
$user_id = $_SESSION['user_id'];

// Filter berita
$where = '';
if ($level == 'wartawan') {
    $where = "WHERE b.id_pengirim='$user_id'";
}
if ($level == 'editor') {
    $where = "WHERE b.status='draft'";
}
$query = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, u.username FROM berita b LEFT JOIN tb_kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_pengirim=u.id $where ORDER BY b.created_at DESC");

// Count status for chart
$status_counts = ['draft' => 0, 'published' => 0, 'rejected' => 0];
while ($row = mysqli_fetch_assoc($query)) {
    $status_counts[$row['status']]++;
}
mysqli_data_seek($query, 0); // Reset pointer for table display
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Berita</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <style>
        :root {
            --primary: #FFC107; /* Deep yellow */
            --secondary: #FFECB3; /* Medium yellow */
            --accent: #FFF3CD; /* Light yellow */
            --bg-gradient: linear-gradient(135deg, #FFF9E6 0%, #FFD54F 50%, #FFC107 100%); /* 135-degree yellow gradient */
            --card-bg: rgba(255, 243, 205, 0.95); /* Semi-transparent light yellow */
            --shadow: 0 8px 24px rgba(255, 193, 7, 0.15); /* Yellow-tinted shadow */
            --text-light: #1C2526; /* Dark gray for readability */
            --text-muted: #6B5B00; /* Muted yellow-brown for secondary text */
            --glow: 0 0 8px rgba(255, 193, 7, 0.25); /* Yellow glow effect */
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            font-family: 'Roboto', sans-serif;
            color: var(--text-light);
            margin: 0;
            padding: 1rem; /* Smaller padding */
            display: flex;
            flex-direction: column;
        }

        .container-wrapper {
            max-width: 1400px; /* Wider than original */
            margin: 0 auto;
            padding: 1rem; /* Smaller padding */
            flex-grow: 1;
        }

        .filter-bar {
            background: var(--card-bg);
            border-radius: 0.8rem; /* Smaller radius */
            padding: 1rem; /* Smaller padding */
            display: flex;
            flex-wrap: wrap;
            gap: 1rem; /* Smaller gap */
            align-items: center;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem; /* Smaller margin */
            justify-content: flex-start; /* Left-aligned for distinction */
        }

        .search-bar {
            flex: 2;
            padding: 0.7rem; /* Smaller padding */
            background: #FFF9E6 !important; /* Very light yellow */
            border: 1px solid var(--accent);
            border-radius: 0.5rem; /* Smaller radius */
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .search-bar:focus {
            border-color: var(--primary);
            box-shadow: var(--glow);
        }

        .btn-filter {
            background: var(--primary);
            border: none;
            border-radius: 0.5rem;
            padding: 0.7rem 1.5rem; /* Smaller padding */
            color: #fff;
            font-size: 0.9rem;
            transition: background 0.2s, transform 0.2s;
        }

        .btn-filter:hover {
            background: #FFA000; /* Darker yellow */
            transform: translateY(-2px);
        }

        .glass-card {
            background: var(--card-bg);
            border-radius: 0.8rem; /* Smaller radius */
            box-shadow: var(--shadow);
            border: 1px solid var(--accent);
            padding: 1.5rem; /* Smaller padding */
        }

        .card-header {
            text-align: center; /* Centered for distinction */
            margin-bottom: 1.2rem; /* Smaller margin */
            border-bottom: 1px solid var(--accent);
            padding-bottom: 0.7rem;
        }

        .card-header h2 {
            font-size: 1.8rem; /* Smaller font */
            font-weight: 600;
            color: var(--text-light);
            margin: 0;
        }

        .table-responsive {
            border-radius: 0.6rem;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.9rem;
            width: 100%;
            background: var(--card-bg);
        }

        .table thead th {
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            color: var(--text-light);
            font-weight: 600;
            border: none;
            padding: 0.8rem;
            text-align: center;
        }

        .table tbody tr {
            background: rgba(255, 236, 179, 0.05); /* Yellow-tinted transparent */
            transition: transform 0.2s;
        }

        .table tbody tr:hover {
            background: rgba(255, 193, 7, 0.1); /* Yellow-tinted hover */
            transform: translateY(-2px);
        }

        .table td {
            padding: 0.8rem;
            text-align: center;
            border-top: 1px solid var(--accent);
            font-size: 0.85rem;
        }

        .img-preview {
            max-width: 90px; /* Smaller preview */
            border-radius: 0.5rem;
            border: 1px solid var(--accent);
            background: #FFF9E6;
            padding: 2px;
        }

        .btn-action {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem; /* Smaller padding */
            font-size: 0.85rem;
            margin: 0.2rem;
            transition: all 0.2s;
        }

        .btn-warning {
            background: var(--primary);
            color: #fff;
            border: none;
        }

        .btn-danger {
            background: linear-gradient(90deg, #d32f2f, #b71c1c); /* Red gradient retained */
            color: #fff;
            border: none;
        }

        .btn-success {
            background: linear-gradient(90deg, #388e3c, #4caf50); /* Green gradient retained */
            color: #fff;
            border: none;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .badge-status {
            font-size: 0.8rem;
            padding: 0.4em 0.9em;
            border-radius: 0.5rem;
            font-weight: 500;
        }

        .badge-draft {
            background: var(--secondary);
            color: var(--text-light);
        }

        .badge-published {
            background: #FFF9E6; /* Very light yellow */
            color: var(--text-light);
            border: 1px solid var(--primary);
        }

        .badge-rejected {
            background: rgba(255, 236, 179, 0.2); /* Yellow-tinted transparent */
            color: var(--text-light);
            border: 1px solid var(--accent);
        }

        .fab {
            position: fixed;
            bottom: 2rem; /* Higher position */
            right: 2rem; /* Right bottom for distinction */
            background: var(--primary);
            border: none;
            border-radius: 50%;
            width: 55px; /* Smaller size */
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            color: #fff;
            font-size: 1.3rem;
            transition: background 0.2s, transform 0.2s;
        }

        .fab:hover {
            background: #FFA000;
            transform: scale(1.1);
        }

        #canvasPanel {
            background: var(--card-bg);
            border-radius: 0.8rem;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            max-width: 650px; /* Wider */
            width: 95%;
        }

        #canvasPanel button {
            background: var(--primary);
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            color: #fff;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        #canvasPanel button:hover {
            background: #FFA000;
        }

        .footer {
            background: var(--card-bg);
            border-top: 1px solid var(--accent);
            text-align: left; /* Left-aligned for distinction */
            padding: 0.8rem; /* Smaller padding */
            font-size: 0.85rem;
            color: var(--text-muted);
            box-shadow: var(--shadow);
            margin-top: 1.5rem; /* Smaller margin */
        }

        @media (max-width: 768px) {
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .search-bar {
                width: 100%;
            }
            .table td, .table th {
                padding: 0.6rem;
                font-size: 0.8rem;
            }
            .btn-action {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
            .img-preview {
                max-width: 70px;
            }
            .footer {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
<div class="container-wrapper">
    <div class="filter-bar">
        <input type="text" class="search-bar" id="searchInput" placeholder="Cari judul berita..." onkeyup="filterTable()">
        <?php if ($level == 'wartawan'): ?>
            <a href="berita_form.php" class="btn btn-filter"><i class="fas fa-plus mr-2"></i>Tambah Berita</a>
        <?php endif; ?>
        <button class="btn btn-filter" onclick="location.reload();"><i class="fas fa-sync-alt mr-2"></i>Refresh</button>
    </div>
    <div class="glass-card">
        <div class="card-header">
            <h2><i class="fas fa-newspaper mr-2"></i>Daftar Berita</h2>
        </div>
        <div class="table-responsive">
            <table class="table" id="newsTable">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Pengirim</th>
                        <th>Status</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori'] ?: 'Tidak ada kategori') ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td>
                            <span class="badge-status badge-<?= $row['status'] == 'draft' ? 'draft' : ($row['status'] == 'published' ? 'published' : 'rejected') ?>">
                                <?= htmlspecialchars(ucfirst($row['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['gambar']): ?>
                                <img src="upload/<?= htmlspecialchars($row['gambar']) ?>" class="img-preview" alt="Gambar Berita">
                            <?php else: ?>
                                <span class="text-muted">Tidak ada gambar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($level == 'wartawan' && $row['status'] == 'draft' && $row['id_pengirim'] == $user_id): ?>
                                <a href="berita_form.php?id=<?= $row['id'] ?>" class="btn btn-action btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="berita_hapus.php?id=<?= $row['id'] ?>" class="btn btn-action btn-danger" onclick="return confirm('Hapus berita?')"><i class="fas fa-trash"></i> Hapus</a>
                            <?php endif; ?>
                            <?php if ($level == 'editor' && $row['status'] == 'draft'): ?>
                                <a href="berita_approval.php?id=<?= $row['id'] ?>&aksi=publish" class="btn btn-action btn-success"><i class="fas fa-check"></i> Publish</a>
                                <a href="berita_approval.php?id=<?= $row['id'] ?>&aksi=reject" class="btn btn-action btn-danger"><i class="fas fa-times"></i> Tolak</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($query) == 0): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada berita.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <button class="fab" onclick="openCanvas()"><i class="fas fa-chart-bar"></i></button>
    <div id="canvasPanel" style="display:none;">
        <button onclick="closeCanvas()">Tutup</button>
        <div id="chartContainer"></div>
    </div>
    <div class="footer">
        Today: 08:18 PM WIB, Sunday, June 15, 2025
    </div>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    function filterTable() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let table = document.getElementById('newsTable');
        let tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            let td = tr[i].getElementsByTagName('td')[0]; // Filter by Judul column
            if (td) {
                let text = td.textContent || td.innerText;
                tr[i].style.display = text.toLowerCase().indexOf(input) > -1 ? '' : 'none';
            }
        }
    }

    function openCanvas() {
        document.getElementById('canvasPanel').style.display = 'block';
        document.getElementById('chartContainer').innerHTML = '<pre><code class="chartjs">{\n  "type": "bar",\n  "data": {\n    "labels": ["Draft", "Published", "Rejected"],\n    "datasets": [{\n      "label": "Jumlah Berita",\n      "data": [<?= $status_counts['draft'] ?>, <?= $status_counts['published'] ?>, <?= $status_counts['rejected'] ?>],\n      "backgroundColor": ["#FFECB3", "#FFF9E6", "#FFD54F"],\n      "borderColor": ["#FFC107", "#FFC107", "#FFC107"],\n      "borderWidth": 1\n    }]\n  },\n  "options": {\n    "scales": {\n      "y": {\n        "beginAtZero": true\n      }\n    }\n  }\n}</code></pre>';
    }

    function closeCanvas() {
        document.getElementById('canvasPanel').style.display = 'none';
    }
</script>
</body>
</html>