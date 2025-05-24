<?php
session_start();

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$filesData = [];
if (file_exists('files.json')) {
    $filesData = json_decode(file_get_contents('files.json'), true);
}

$files = $filesData['files'] ?? [];
$totalFiles = count($files);
$recentFiles = array_slice($files, 0, 5);

$username = $_SESSION['username'] ?? 'User';
$profilePic = $_SESSION['profile_pic'] ?? null;
$initial = strtoupper(substr($username, 0, 1));

// Ambil pesan notifikasi upload jika ada
$successMsg = $_SESSION['success'] ?? null;
$errorMsg = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>DocuShare - Dashboard</title>
<style>
  :root {
    --biru-cerah: #2a9df4;
    --kuning: #f5c518;
    --ungu: #7b3fbf;
    --putih: #fff;
    --abu-muda: #f4f6fb;
    --abu-tua: #777;
    --placeholder-bg: #ddd;
    --placeholder-color: #555;
    --success-bg: #d4edda;
    --success-color: #155724;
    --error-bg: #f8d7da;
    --error-color: #721c24;
  }

  body, html {
    margin: 0; padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--abu-muda);
    color: #222;
  }

  a {
    color: inherit;
    text-decoration: none;
  }

  .container {
    display: flex;
    height: 100vh;
  }

  .sidebar {
    background-color: var(--biru-cerah);
    width: 220px;
    display: flex;
    flex-direction: column;
    padding: 20px 10px;
    color: var(--putih);
    transition: transform 0.3s ease-in-out;
  }
  .sidebar h2 {
    text-align: center;
    margin-bottom: 40px;
    font-weight: 700;
    font-size: 1.6rem;
  }
  .sidebar nav a {
    display: block;
    padding: 15px 20px;
    border-radius: 6px;
    margin-bottom: 10px;
    font-weight: 600;
    transition: background-color 0.3s;
  }
  .sidebar nav a:hover, .sidebar nav a.active {
    background-color: var(--kuning);
    color: var(--biru-cerah);
    font-weight: 700;
  }

  .header {
    background-color: var(--putih);
    height: 60px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  .header .app-name {
    font-weight: 700;
    font-size: 1.4rem;
    color: var(--biru-cerah);
  }
  .header .user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
  }
  .header .user-profile img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
  }
  /* Placeholder foto profil jika kosong */
  .header .user-profile .placeholder {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: var(--placeholder-bg);
    color: var(--placeholder-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    user-select: none;
  }
  .header .user-profile span {
    font-weight: 600;
    color: var(--ungu);
  }

  .main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: var(--putih);
    padding: 25px 40px;
    overflow-y: auto;
  }

  .welcome {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 25px;
    color: var(--biru-cerah);
  }

  /* Notifikasi */
  .notification {
    padding: 12px 18px;
    margin-bottom: 25px;
    border-radius: 6px;
    font-weight: 600;
  }
  .notification.success {
    background-color: var(--success-bg);
    color: var(--success-color);
  }
  .notification.error {
    background-color: var(--error-bg);
    color: var(--error-color);
  }

  .stats {
    display: flex;
    gap: 40px;
    margin-bottom: 25px;
    flex-wrap: wrap;
  }
  .total-files {
    flex: 1 1 200px;
    background-color: var(--abu-muda);
    border-radius: 10px;
    padding: 20px;
    font-weight: 700;
    font-size: 1.2rem;
    color: var(--ungu);
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .recent-files {
    flex: 3 1 350px;
    background-color: var(--abu-muda);
    border-radius: 10px;
    padding: 20px;
    color: #333;
  }
  .recent-files h3 {
    margin-top: 0;
    color: var(--biru-cerah);
  }
  .recent-files ul {
    list-style: none;
    padding-left: 0;
    margin: 10px 0 0 0;
  }
  .recent-files ul li {
    padding: 6px 0;
    border-bottom: 1px solid #ccc;
  }

  .action-panel {
    margin-bottom: 30px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
  }
  .btn {
    padding: 12px 25px;
    border-radius: 6px;
    border: none;
    font-weight: 700;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background-color 0.3s;
    user-select: none;
  }
  .btn.create {
    background-color: var(--kuning);
    color: var(--biru-cerah);
  }
  .btn.create:hover {
    background-color: #d4ac0d;
  }
  .btn.upload {
    background-color: var(--ungu);
    color: var(--putih);
  }
  .btn.upload:hover {
    background-color: #5c2a85;
  }
  .btn .icon {
    font-weight: 900;
    font-size: 1.3rem;
  }

  /* Modal upload */
  #uploadModal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }
  #uploadModal form {
    background: var(--putih);
    padding: 20px 25px;
    border-radius: 8px;
    min-width: 320px;
    box-shadow: 0 0 10px rgba(0,0,0,0.25);
  }
  #uploadModal form h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: var(--biru-cerah);
  }
  #uploadModal input[type="file"] {
    width: 100%;
  }
  #uploadModal button {
    cursor: pointer;
  }

  footer {
    background-color: var(--biru-cerah);
    color: var(--putih);
    text-align: center;
    padding: 14px 10px;
    font-size: 0.9rem;
    margin-top: auto;
  }
  footer a {
    color: var(--kuning);
    margin: 0 10px;
    font-weight: 600;
  }

  @media (max-width: 768px) {
    .container {
      flex-direction: column;
      height: auto;
    }
    .sidebar {
      width: 100%;
      height: auto;
      flex-direction: row;
      justify-content: space-around;
      padding: 10px 0;
    }
    .sidebar h2 {
      display: none;
    }
    .sidebar nav {
      display: flex;
      gap: 10px;
      width: 100%;
      justify-content: center;
    }
    .sidebar nav a {
      margin-bottom: 0;
      padding: 10px 12px;
      font-size: 0.9rem;
    }
    .main-content {
      padding: 20px;
    }
    .stats {
      flex-direction: column;
    }
    .recent-files, .total-files {
      flex: 1 1 auto;
      margin-bottom: 20px;
    }
    .action-panel {
      flex-direction: column;
    }
    .btn {
      width: 100%;
      justify-content: center;
    }
  }
</style>
</head>
<body>

<div class="container">
  <aside class="sidebar">
    <h2>DocuShare</h2>
    <nav>
      <a href="#" class="active">Dashboard</a>
      <a href="#">Pengaturan</a>
      <a href="#">Berkas Saya</a>
      <a href="#">Notifikasi</a>
      <a href="logout.php">Logout</a>
    </nav>
  </aside>

  <div style="flex:1; display:flex; flex-direction: column;">
    <header class="header">
      <div class="app-name">DocuShare</div>
      <div class="user-profile" title="Profil Pengguna">
        <span><?= htmlspecialchars($username) ?></span>
        <?php if ($profilePic): ?>
          <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profil Pengguna" />
        <?php else: ?>
          <div class="placeholder"><?= htmlspecialchars($initial) ?></div>
        <?php endif; ?>
      </div>
    </header>

    <main class="main-content">
      <?php if ($successMsg): ?>
        <div class="notification success"><?= htmlspecialchars($successMsg) ?></div>
      <?php endif; ?>
      <?php if ($errorMsg): ?>
        <div class="notification error"><?= htmlspecialchars($errorMsg) ?></div>
      <?php endif; ?>

      <div class="welcome">Selamat Datang, <?= htmlspecialchars($username) ?></div>

      <div class="stats">
        <div class="total-files">
          <span>📁</span> Total Berkas: <strong id="totalFiles"><?= $totalFiles ?></strong>
        </div>
        <div class="recent-files">
          <h3>Berkas Terbaru</h3>
          <ul id="recentFilesList">
            <?php if ($totalFiles > 0): ?>
              <?php foreach ($recentFiles as $file): ?>
                <li><?= htmlspecialchars($file) ?></li>
              <?php endforeach; ?>
            <?php else: ?>
              <li>Tidak ada berkas.</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>

      <div class="action-panel">
        <button class="btn create" id="btnCreate">
          <span class="icon">＋</span> Buat Berkas Baru
        </button>
        <button class="btn upload" id="btnUpload">Unggah Berkas</button>
      </div>

      <!-- Modal Upload -->
      <div id="uploadModal">
        <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
          <h3>Unggah Berkas Baru</h3>
          <input type="file" name="document" accept=".pdf,.doc,.docx,.txt" required />
          <div style="margin-top: 15px; display:flex; justify-content:flex-end; gap: 10px;">
            <button type="button" id="cancelUpload" style="background:#ccc; border:none; padding:8px 15px; border-radius:5px;">Batal</button>
            <button type="submit" class="btn upload" style="padding:8px 15px;">Unggah</button>
          </div>
        </form>
      </div>
    </main>

    <footer>
      <a href="#">Kebijakan Privasi</a> | <a href="#">Syarat dan Ketentuan</a><br />
      © 2025 DocuShare
    </footer>
  </div>
</div>

<script>
  document.getElementById('btnCreate').addEventListener('click', () => {
    alert('Fungsi Buat Berkas Baru sedang dalam pengembangan.');
  });

  const uploadModal = document.getElementById('uploadModal');
  const btnUpload = document.getElementById('btnUpload');
  const cancelUpload = document.getElementById('cancelUpload');

  btnUpload.addEventListener('click', () => {
    uploadModal.style.display = 'flex';
  });

  cancelUpload.addEventListener('click', () => {
    uploadModal.style.display = 'none';
  });

  uploadModal.addEventListener('click', (e) => {
    if (e.target === uploadModal) {
      uploadModal.style.display = 'none';
    }
  });
</script>

</body>
</html>