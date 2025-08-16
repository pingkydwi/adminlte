<?php
include 'conf/config.php';
// Ambil 1 berita headline
$headlineQ = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, u.username FROM tb_berita b LEFT JOIN tb_kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_user=u.id WHERE b.status='published' ORDER BY b.created_at DESC LIMIT 1");
$headline = mysqli_fetch_assoc($headlineQ);

// Hindari warning jika tidak ada berita publish
$headline_id = $headline ? $headline['id'] : 0;

// Ambil 3 berita utama lain
$utamaQ = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, u.username FROM tb_berita b LEFT JOIN tb_kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_user=u.id WHERE b.status='published' AND b.id != '$headline_id' ORDER BY b.created_at DESC LIMIT 3");

// Ambil 9 berita lain
$lainQ = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, u.username FROM tb_berita b LEFT JOIN tb_kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_user=u.id WHERE b.status='published' AND b.id != '$headline_id' ORDER BY b.created_at DESC LIMIT 9 OFFSET 3");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Publik</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
    <style>
        :root {
            --primary: #6B46C1;
            --secondary: #9B59B6;
            --accent: #00C4CC;
            --bg-gradient: linear-gradient(135deg, #1A1A2E 0%, #16213E 100%);
            --card-bg: rgba(26, 26, 46, 0.95);
            --shadow: 0 10px 30px rgba(107, 70, 193, 0.15);
            --text-light: #E6E6FA;
            --text-muted: #A0AEC0;
            --glow: 0 0 15px rgba(0, 196, 204, 0.5);
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: var(--text-light);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.01) 1px, transparent 1px),
                             linear-gradient(45deg, rgba(255, 255, 255, 0.01) 1px, transparent 1px),
                             linear-gradient(135deg, rgba(255, 255, 255, 0.01) 1px, transparent 1px),
                             linear-gradient(45deg, rgba(255, 255, 255, 0.01) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: gradientShift 10s infinite alternate;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 0%, 0% 0%, 0% 0%, 0% 0%; }
            100% { background-position: 100% 100%, 100% 100%, 100% 100%, 100% 100%; }
        }

        .container-wrapper {
            height: 100vh;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .glass-card {
            background: var(--card-bg);
            border-radius: 2rem;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 1400px;
            height: 90vh;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            padding: 10rem; /* Increased from 2.5rem to 10rem for 4x scale */
            overflow-y: auto;
            border-image: linear-gradient(to right, var(--primary), var(--accent)) 1;
            animation: cardGlow 4s infinite alternate;
        }

        @keyframes cardGlow {
            0% { box-shadow: var(--shadow), var(--glow); }
            100% { box-shadow: var(--shadow), 0 0 25px rgba(0, 196, 204, 0.7); }
        }

        .headline-img {
            width: 100%;
            max-height: 1600px; /* Increased from 400px to 1600px for 4x scale */
            object-fit: cover;
            border-radius: 4.8rem; /* Increased from 1.2rem to 4.8rem */
            box-shadow: 0 32px 128px rgba(0, 0, 0, 0.18); /* Increased shadow */
            transition: transform 0.3s ease;
        }

        .headline-img:hover {
            transform: scale(1.03);
        }

        .headline-title {
            font-family: 'Poppins', sans-serif;
            font-size: 10rem; /* Increased from 2.5rem to 10rem */
            font-weight: 700;
            color: var(--text-light);
            margin-top: 6rem; /* Increased from 1.5rem to 6rem */
            text-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); /* Increased shadow */
            transition: color 0.3s ease;
        }

        .headline-title:hover {
            color: var(--accent);
        }

        .headline-meta {
            color: var(--text-muted);
            font-size: 4.4rem; /* Increased from 1.1rem to 4.4rem */
            margin-bottom: 4.8rem; /* Increased from 1.2rem to 4.8rem */
            display: flex;
            gap: 4rem; /* Increased from 1rem to 4rem */
        }

        .headline-summary {
            font-size: 4.8rem; /* Increased from 1.2rem to 4.8rem */
            color: var(--text-light);
            margin-bottom: 6rem; /* Increased from 1.5rem to 6rem */
            line-height: 6.4; /* Increased from 1.6 to 6.4 */
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 4.8rem; /* Increased from 1.2rem to 4.8rem */
            padding: 3.2rem 8rem; /* Increased from 0.8rem 2rem to 3.2rem 8rem */
            font-weight: 600;
            color: var(--text-light);
            font-size: 4.8rem; /* Increased from 1.2rem to 4.8rem */
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .btn-primary:hover::before {
            width: 300%;
            height: 300%;
        }

        .btn-primary:hover {
            transform: translateY(-12px); /* Increased from -3px to -12px */
            box-shadow: 0 24px 72px rgba(107, 70, 193, 0.4); /* Increased shadow */
        }

        .card-utama {
            height: 100%;
            border-radius: 4rem; /* Increased from 1rem to 4rem */
            box-shadow: 0 12px 56px rgba(0, 0, 0, 0.07); /* Increased shadow */
            background: rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-utama:hover {
            transform: translateY(-20px); /* Increased from -5px to -20px */
            box-shadow: 0 24px 80px rgba(0, 196, 204, 0.2); /* Increased shadow */
        }

        .card-utama-img {
            width: 100%;
            height: 480px; /* Increased from 120px to 480px */
            object-fit: cover;
            border-radius: 4rem 4rem 0 0; /* Increased from 1rem to 4rem */
            transition: opacity 0.3s ease;
        }

        .card-utama-img:hover {
            opacity: 0.9;
        }

        .card-title {
            font-family: 'Poppins', sans-serif;
            font-size: 4.8rem; /* Increased from 1.2rem to 4.8rem */
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 3.2rem; /* Increased from 0.8rem to 3.2rem */
            transition: color 0.3s ease;
        }

        .card-title:hover {
            color: var(--accent);
        }

        .card-text {
            font-size: 3.88rem; /* Increased from 0.97rem to 3.88rem */
            color: var(--text-muted);
            margin-bottom: 3.2rem; /* Increased from 0.8rem to 3.2rem */
            line-height: 6; /* Increased from 1.5 to 6 */
        }

        .card-meta {
            color: var(--text-muted);
            font-size: 3.8rem; /* Increased from 0.95rem to 3.8rem */
        }

        .card {
            border: none;
            border-radius: 4rem; /* Increased from 1rem to 4rem */
            background: rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-20px); /* Increased from -5px to -20px */
            box-shadow: 0 24px 80px rgba(0, 196, 204, 0.2); /* Increased shadow */
        }

        .card-img-top {
            height: 680px; /* Increased from 170px to 680px */
            object-fit: cover;
            border-radius: 4rem 4rem 0 0; /* Increased from 1rem to 4rem */
            transition: opacity 0.3s ease;
        }

        .card-img-top:hover {
            opacity: 0.9;
        }

        .btn-outline-primary {
            border-color: var(--accent);
            color: var(--accent);
            border-radius: 4.8rem; /* Increased from 1.2rem to 4.8rem */
            padding: 2rem 6rem; /* Increased from 0.5rem 1.5rem to 2rem 6rem */
            font-size: 4rem; /* Increased from 1rem to 4rem */
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--accent);
            color: var(--text-light);
            transform: translateY(-8px); /* Increased from -2px to -8px */
        }

        .alert-info {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4rem; /* Increased from 1rem to 4rem */
            padding: 6rem; /* Increased from 1.5rem to 6rem */
            text-align: center;
            font-size: 4.4rem; /* Increased from default to 4.4rem */
        }

        @media (max-width: 768px) {
            .headline-title { font-size: 7.2rem; } /* Reduced from 10rem to 7.2rem */
            .headline-img { max-height: 880px; } /* Reduced from 1600px to 880px */
            .headline-summary { font-size: 4rem; } /* Reduced from 4.8rem to 4rem */
            .card-utama-img { height: 400px; } /* Reduced from 480px to 400px */
            .card-title { font-size: 4.4rem; } /* Reduced from 4.8rem to 4.4rem */
            .card-img-top { height: 480px; } /* Reduced from 680px to 480px */
            .glass-card { padding: 6rem; height: 95vh; } /* Adjusted padding and height */
            .headline-meta { font-size: 3.6rem; } /* Reduced from 4.4rem to 3.6rem */
            .card-text { font-size: 3.2rem; } /* Reduced from 3.88rem to 3.2rem */
            .card-meta { font-size: 3.2rem; } /* Reduced from 3.8rem to 3.2rem */
            .btn-primary { font-size: 4rem; padding: 2.4rem 6rem; } /* Adjusted button */
            .btn-outline-primary { font-size: 3.2rem; padding: 1.6rem 4.8rem; } /* Adjusted button */
        }
    </style>
</head>
<body>
<div class="container-wrapper">
    <div class="glass-card">
        <!-- Headline -->
        <?php if (!$headline): ?>
            <div class="alert alert-info">Belum ada berita yang dipublikasikan.</div>
        <?php else: ?>
        <div class="row mb-20">
            <div class="col-md-8">
                <?php if ($headline['gambar']): ?>
                    <img src="upload/<?= htmlspecialchars($headline['gambar']) ?>" class="headline-img mb-12" alt="<?= htmlspecialchars($headline['judul']) ?>">
                <?php endif; ?>
                <div class="headline-title"><?= htmlspecialchars($headline['judul']) ?></div>
                <div class="headline-meta">
                    <span><i class="fas fa-folder"></i> <?= htmlspecialchars($headline['nama_kategori']) ?></span>
                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($headline['username']) ?></span>
                    <span><i class="fas fa-clock"></i> <?= date('d M Y', strtotime($headline['created_at'])) ?></span>
                </div>
                <div class="headline-summary">
                    <?= nl2br(htmlspecialchars(substr(strip_tags($headline['isi']), 0, 220))) ?>...
                </div>
                <a href="berita_detail.php?id=<?= $headline['id'] ?>" class="btn btn-primary"><i class="fas fa-book-open mr-8"></i>Baca Selengkapnya</a>
            </div>
            <div class="col-md-4">
                <h5 class="mb-16" style="color: var(--text-muted); font-family: 'Poppins', sans-serif;">Berita Utama Lainnya</h5>
                <?php while ($row = mysqli_fetch_assoc($utamaQ)): ?>
                <div class="card card-utama mb-12">
                    <?php if ($row['gambar']): ?>
                        <img src="upload/<?= htmlspecialchars($row['gambar']) ?>" class="card-utama-img" alt="<?= htmlspecialchars($row['judul']) ?>">
                    <?php endif; ?>
                    <div class="card-body pb-8">
                        <a href="berita_detail.php?id=<?= $row['id'] ?>" class="card-title"><?= htmlspecialchars($row['judul']) ?></a>
                        <div class="card-meta"><i class="fas fa-folder"></i> <?= htmlspecialchars($row['nama_kategori']) ?></div>
                        <div class="card-text"><?= nl2br(htmlspecialchars(substr(strip_tags($row['isi']), 0, 70))) ?>...</div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        <!-- Berita Grid -->
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($lainQ)): ?>
            <div class="col-md-4 mb-16">
                <div class="card">
                    <?php if ($row['gambar']): ?>
                        <img src="upload/<?= htmlspecialchars($row['gambar']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['judul']) ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <a href="berita_detail.php?id=<?= $row['id'] ?>" class="card-title"><?= htmlspecialchars($row['judul']) ?></a>
                        <div class="card-meta"><i class="fas fa-folder"></i> <?= htmlspecialchars($row['nama_kategori']) ?> | <i class="fas fa-user"></i> <?= htmlspecialchars($row['username']) ?></div>
                        <div class="card-text"><?= nl2br(htmlspecialchars(substr(strip_tags($row['isi']), 0, 90))) ?>...</div>
                        <a href="berita_detail.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary"><i class="fas fa-book-reader mr-4"></i>Baca</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($lainQ) == 0): ?>
            <div class="col-12 text-center text-muted">Belum ada berita lain.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>