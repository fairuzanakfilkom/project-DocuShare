<?php
session_start();

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$filesData = [];
if (file_exists('files.json')) {
    $jsonContent = file_get_contents('files.json');
    $filesData = json_decode($jsonContent, true);
    if ($filesData === null) {
        $filesData = ['files' => []];
    }
}

$files = $filesData['files'] ?? [];
$totalFiles = count($files);
$recentFiles = array_slice($files, 0, 5);

$username = $_SESSION['username'] ?? 'User';
$profilePic = $_SESSION['profile_pic'] ?? null;
$initial = strtoupper(substr($username, 0, 1));

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
    height: 100vh;
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

  .sidebar nav a:hover,
  .sidebar nav a.active {
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

  /* Tombol edit dan hapus pada daftar berkas */
  .btn.edit-btn {
    background-color: var(--kuning);
    color: var(--biru-cerah);
    padding: 6px 12px;
    border-radius: 5px;
    font-weight: 700;
    text-decoration: none;
  }

  .btn.edit-btn:hover {
    background-color: #d4ac0d;
  }

  .btn.delete-btn {
    background-color: var(--ungu);
    color: var(--putih);
    padding: 6px 12px;
    border-radius: 5px;
    font-weight: 700;
    text-decoration: none;
  }

  .btn.delete-btn:hover {
    background-color: #5c2a85;
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
      flex-direction: row;
      justify-content: space-around;
      padding: 10px 0;
    }
    .sidebar h2 {
      display: none;
    }
    .main-content {
      padding: 20px;
    }
  }
</style>

</head>
<body>
  <div class="container">
    <aside class="sidebar">
      <h2>DocuShare</h2>
      <nav>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="kategori.php">Kategori</a>
        <a href="logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main-content
">
<header class="header">
<div class="app-name">DocuShare</div>
<div class="user-profile" title="<?= htmlspecialchars($username) ?>">
<?php if ($profilePic && file_exists($profilePic)): ?>
<img src="<?= htmlspecialchars($profilePic) ?>" alt="Foto Profil" />
<?php else: ?>
<div class="placeholder"><?= $initial ?></div>
<?php endif; ?>
<span><?= htmlspecialchars($username) ?></span>
</div>
</header>  <?php if ($successMsg): ?>
    <div class="notification success"><?= htmlspecialchars($successMsg) ?></div>
  <?php endif; ?>
  <?php if ($errorMsg): ?>
    <div class="notification error"><?= htmlspecialchars($errorMsg) ?></div>
  <?php endif; ?>

  <div class="welcome">Selamat datang, <?= htmlspecialchars($username) ?>!</div>

  <div class="stats">
    <div class="total-files">Total Berkas: <?= $totalFiles ?></div>
    <div class="recent-files">
      <h3>Berkas Terbaru</h3>
      <ul>
        <?php foreach ($recentFiles as $file): ?>
          <li style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #ccc;">
            <div>
              <?= htmlspecialchars($file['name']) ?>
              <small style="color:#666; font-style: italic;">
                (<?= htmlspecialchars($file['category'] ?? 'Tanpa Kategori') ?>)
              </small>
            </div>
            <div style="display: flex; gap: 10px;">
              <a href="edit.php?file=<?= urlencode($file['name']) ?>"
                 class="btn edit-btn"
                 title="Edit Berkas <?= htmlspecialchars($file['name']) ?>">
                Edit
              </a>
              <a href="hapus.php?file=<?= urlencode($file['name']) ?>"
                 class="btn delete-btn"
                 title="Hapus Berkas <?= htmlspecialchars($file['name']) ?>"
                 onclick="return confirm('Apakah Anda yakin ingin menghapus berkas <?= htmlspecialchars(addslashes($file['name'])) ?>?');">
                Hapus
              </a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="action-panel">
    <button class="btn create" onclick="window.location.href='create.php'">
      + Buat Berkas Baru
    </button>
    <button class="btn upload" onclick="document.getElementById('uploadModal').style.display = 'flex'">
      Unggah Berkas
    </button>
  </div>

  <!-- Modal Upload -->
  <div id="uploadModal">
    <form method="POST" action="upload.php" enctype="multipart/form-data">
      <h3>Unggah Berkas Baru</h3>
      <input type="file" name="uploadedFile" required />
      <div style="margin-top: 15px; display: flex; justify-content: flex-end; gap: 10px;">
        <button type="submit" class="btn upload">Unggah</button>
        <button type="button" class="btn" style="background:#ccc; color:#333;" onclick="document.getElementById('uploadModal').style.display='none'">Batal</button>
      </div>
    </form>
  </div>

</main>
</div> <footer> &copy; <?= date('Y') ?> DocuShare. All rights reserved. | <a href="privacy.php">Kebijakan Privasi</a> | <a href="terms.php">Syarat & Ketentuan</a> </footer> <script> // Klik di luar modal untuk tutup window.onclick = function(event) { var modal = document.getElementById('uploadModal'); if (event.target == modal) { modal.style.display = "none"; } } </script> </body> </html> ```