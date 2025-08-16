<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:../index.php');
    exit;
}
$timeout_duration = 3600;
// Check for session timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('location:../index.php');
    exit;
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();
// $exp = ($_SESSION['LAST_ACTIVITY']) > $timeout_duration;
// var_dump($_SESSION['LAST_ACTIVITY']);
// var_dump($exp);
include '../conf/config.php';
// Statistik berita
date_default_timezone_set('Asia/Jakarta');
$jml_berita = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM berita"))[0];
$jml_draft = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM berita WHERE status='draft'"))[0];
$jml_published = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM berita WHERE status='published'"))[0];
$jml_rejected = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM berita WHERE status='rejected'"))[0];
$level = $_SESSION['level'];
$user_id = $_SESSION['user_id'];
// Filter berita
$where = '';
if ($level == 'wartawan') {
    $where = "WHERE b.id_pengirim='$user_id'";
} else if ($level == 'editor') {
    $where = "WHERE b.status='draft'";
} else {
    $where = '';
}
$query = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, u.username FROM berita b LEFT JOIN kategori k ON b.id_kategori=k.id LEFT JOIN tb_users u ON b.id_pengirim=u.id $where ORDER BY b.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AdminLTE 3 | Berita</title>
  <!-- Google Font: Roboto -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
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
      font-family: 'Roboto', sans-serif;
      background-attachment: fixed;
      font-size: 1.15rem;
      display: flex;
      flex-direction: column;
    }

    /* Link dan highlight selaras kuning */
    a, a:visited, a:active {
      color: var(--text-light);
    }
    a:hover {
      color: var(--primary);
    }

    /* Highlight, badge, dan border selaras kuning */
    .badge, .highlight, .border-warning, .border-info {
      background: var(--secondary) !important;
      color: var(--text-light) !important;
      border-color: var(--accent) !important;
    }

    /* Table header dan alert selaras kuning */
    .table thead th, th {
      background: linear-gradient(90deg, var(--secondary) 80%, var(--primary) 100%) !important;
      color: var(--text-light) !important;
      border: none !important;
    }
    .alert, .alert-info, .alert-warning, .alert-primary {
      background: var(--card-bg) !important;
      color: var(--text-light) !important;
      border: 1px solid var(--accent) !important;
    }

    .content-wrapper {
      background: var(--card-bg);
      border-radius: 1.8rem; /* Slightly smaller radius */
      box-shadow: var(--shadow);
      padding: 3rem 2.5rem; /* Increased padding */
      margin-top: 2.5rem;
      font-size: 1.18rem;
      border: 2px solid var(--accent);
      flex-grow: 1;
    }

    /* FORM TAMBAH BERITA KUNING */
    form, .card, .modal-content, .form-tambah-berita {
      background: var(--card-bg) !important;
      border-radius: 1.2rem !important; /* Adjusted radius */
      border: 2px solid var(--accent) !important;
      box-shadow: var(--shadow);
      color: var(--text-light) !important;
    }
    .form-group label, .form-label {
      color: var(--text-muted) !important;
      font-weight: 600;
    }
    .form-control, input, textarea, select {
      background: #FFF9E6 !important; /* Very light yellow */
      border: 1.5px solid var(--accent) !important;
      border-radius: 0.8rem !important; /* Smaller radius */
      color: var(--text-light) !important;
      font-size: 1.08rem;
    }
    .form-control:focus, input:focus, textarea:focus, select:focus {
      border-color: var(--primary) !important;
      box-shadow: var(--glow);
    }

    .btn-tambah, .btn-primary, button[type='submit'] {
      background: var(--primary) !important;
      color: #fff !important;
      border-radius: 1.2rem !important;
      font-weight: 600;
      box-shadow: var(--shadow);
      border: none !important;
      transition: background 0.2s, box-shadow 0.2s;
    }
    .btn-tambah:hover, .btn-primary:hover, button[type='submit']:hover {
      background: #FFA000 !important; /* Darker yellow */
      color: #fff !important;
      box-shadow: 0 4px 16px rgba(255, 193, 7, 0.4);
    }

    .small-box {
      border-radius: 1rem; /* Slightly smaller radius */
      box-shadow: var(--shadow);
      overflow: hidden;
      position: relative;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .small-box.bg-info,
    .small-box.bg-warning,
    .small-box.bg-success,
    .small-box.bg-danger {
      background: var(--primary) !important;
      color: #fff !important;
      box-shadow: var(--shadow);
    }
    .small-box .icon > i, .small-box .icon > svg {
      color: #fff !important;
      opacity: 0.9;
    }
    .small-box .inner h3, .small-box .inner p {
      color: #fff !important;
      text-shadow: 0 1px 8px rgba(0, 0, 0, 0.15);
    }
    .small-box:hover {
      transform: translateY(-4px) scale(1.03); /* Subtler scale */
      box-shadow: 0 10px 36px rgba(255, 193, 7, 0.3);
    }

    .table {
      background: rgba(255, 193, 7, 0.1) !important; /* Yellow-tinted transparent */
      border-radius: 0.8rem;
      overflow: hidden;
      color: var(--text-light);
      font-size: 1.1rem;
    }
    .table thead th {
      background: linear-gradient(90deg, var(--secondary) 80%, var(--primary) 100%) !important;
      color: var(--text-light);
      border: none;
    }
    .table tbody tr:nth-child(even) {
      background: rgba(255, 236, 179, 0.15) !important; /* Light yellow transparent */
    }
    .table tbody tr:hover {
      background: rgba(255, 193, 7, 0.12) !important; /* Yellow transparent on hover */
    }

    .btn, .badge {
      border-radius: 0.6rem !important; /* Smaller radius */
      font-size: 1.07rem;
      font-weight: 500;
      padding: 0.7em 1.3em;
    }
    .btn-primary, .btn-info {
      background: var(--primary) !important;
      border: none;
      color: #fff;
    }
    .btn-warning {
      background: var(--secondary) !important;
      border: none;
      color: var(--text-light);
    }
    .btn-danger {
      background: linear-gradient(90deg, #d32f2f 60%, #b71c1c 100%) !important; /* Retain red gradient */
      border: none;
      color: #fff;
    }
    .btn-success {
      background: linear-gradient(90deg, #388e3c 60%, #4caf50 100%) !important; /* Retain green gradient */
      border: none;
      color: #fff;
    }
    .card, .box, .table-responsive {
      background: var(--card-bg) !important;
      border-radius: 0.8rem;
      box-shadow: var(--shadow);
    }
    .alert, .callout {
      border-radius: 0.8rem;
    }
    .btn:hover, .btn:focus {
      filter: brightness(1.1);
      box-shadow: 0 4px 20px rgba(255, 193, 7, 0.3);
    }

    /* Scrollbar styling */
    ::-webkit-scrollbar {
      width: 10px;
      background: var(--secondary);
      border-radius: 8px;
    }
    ::-webkit-scrollbar-thumb {
      background: var(--primary);
      border-radius: 8px;
    }

    .footer {
      background: var(--card-bg);
      border-top: 1px solid var(--accent);
      text-align: center;
      padding: 1rem;
      font-size: 0.85rem;
      color: var(--text-muted);
      box-shadow: var(--shadow);
      margin-top: 2.5rem;
    }

    @media (max-width: 768px) {
      .content-wrapper {
        padding: 2rem 1.5rem;
      }
      .small-box {
        border-radius: 0.8rem;
      }
      .table {
        font-size: 1rem;
      }
      .btn, .badge {
        font-size: 0.95rem;
        padding: 0.5em 1em;
      }
      .footer {
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
<div class="content-wrapper">
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= $jml_berita ?></h3>
              <p>Total Berita</p>
            </div>
            <div class="icon"><i class="fas fa-newspaper"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= $jml_draft ?></h3>
              <p>Draft</p>
            </div>
            <div class="icon"><i class="fas fa-edit"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= $jml_published ?></h3>
              <p>Published</p>
            </div>
            <div class="icon"><i class="fas fa-check"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3><?= $jml_rejected ?></h3>
              <p>Rejected</p>
            </div>
            <div class="icon"><i class="fas fa-times"></i></div>
          </div>
        </div>
      </div>
      <!-- Daftar berita lengkap -->
      <div class="card mt-4 shadow">
        <div class="card-header bg-primary d-flex align-items-center">
          <i class="fas fa-newspaper fa-lg mr-2"></i>
          <h3 class="card-title mb-0">Daftar Berita</h3>
          <span class="ml-2 text-white-50"> Semua berita terbaru, lengkap dengan aksi!</span>
        </div>
        <div class="card-body">
          <?php if($level=='wartawan'): ?>
            <a href="../berita_form.php" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Tambah Berita</a>
          <?php endif; ?>
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
              <thead class="thead-dark">
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
              <?php while($row = mysqli_fetch_assoc($query)): ?>
                <tr>
                  <td><i class="far fa-file-alt text-info"></i> <b><?= htmlspecialchars($row['judul']) ?></b></td>
                  <td><span class="badge badge-info"><i class="fas fa-tag"></i> <?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                  <td>
                    <span class="d-flex align-items-center">
                      <span class="avatar bg-secondary text-white rounded-circle mr-2" style="width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;">
                        <i class="fas fa-user"></i>
                      </span> <?= htmlspecialchars($row['username']) ?>
                    </span>
                  </td>
                  <td>
                    <?php
                      $status = $row['status'];
                      $badge = 'secondary';
                      if ($status == 'published') $badge = 'success';
                      elseif ($status == 'draft') $badge = 'warning';
                      elseif ($status == 'rejected') $badge = 'danger';
                    ?>
                    <span class="badge badge-<?= $badge ?> text-uppercase"><i class="fas fa-circle"></i> <?= htmlspecialchars($status) ?></span>
                  </td>
                  <td><?php if($row['gambar']): ?><img src="../upload/<?= htmlspecialchars($row['gambar']) ?>" width="60" class="img-thumbnail shadow-sm"><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                  <td>
                    <?php if($level=='wartawan' && $row['status']=='draft' && $row['id_pengirim']==$user_id): ?>
                      <a href="../berita_form.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning mb-1"><i class="fas fa-edit"></i> Edit</a>
                      <a href="../berita_hapus.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Hapus berita?')"><i class="fas fa-trash"></i> Hapus</a>
                    <?php endif; ?>
                    <?php if($level=='editor' && $row['status']=='draft'): ?>
                      <a href="../berita_approval.php?id=<?= $row['id'] ?>&aksi=publish" class="btn btn-sm btn-success mb-1"><i class="fas fa-upload"></i> Publish</a>
                      <a href="../berita_approval.php?id=<?= $row['id'] ?>&aksi=reject" class="btn btn-sm btn-danger mb-1"><i class="fas fa-times"></i> Reject</a>
                    <?php endif; ?>
                    <a href="../berita_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info mb-1"><i class="fas fa-eye"></i> Detail</a>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<div class="footer">
  Today: 07:36 PM WIB, Sunday, June 15, 2025
</div>
</div>
<!-- JS -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
</body>
</html>