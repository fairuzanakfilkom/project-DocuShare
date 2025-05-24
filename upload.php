<?php
session_start();

$allowedTypes = ['pdf', 'doc', 'docx', 'txt'];
$uploadDir = 'uploads/';
$filesJson = 'files.json';

if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Gagal mengunggah file.';
    header('Location: dashboard.php');
    exit;
}

$file = $_FILES['document'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

if (!in_array(strtolower($ext), $allowedTypes)) {
    $_SESSION['error'] = 'Tipe file tidak diperbolehkan.';
    header('Location: dashboard.php');
    exit;
}

$filename = basename($file['name']);
$targetPath = $uploadDir . time() . '_' . $filename;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Tambahkan nama file ke files.json
    $data = file_exists($filesJson) ? json_decode(file_get_contents($filesJson), true) : [];
    $data['files'][] = $filename; // Simpan nama aslinya saja
    file_put_contents($filesJson, json_encode($data, JSON_PRETTY_PRINT));

    $_SESSION['success'] = 'File berhasil diunggah.';
} else {
    $_SESSION['error'] = 'Terjadi kesalahan saat menyimpan file.';
}

header('Location: dashboard.php');
<?php
$kategoriFile = 'data/kategori.json';
$kategori = file_exists($kategoriFile) ? json_decode(file_get_contents($kategoriFile), true) : [];
?>

<form action="upload.php" method="post" enctype="multipart/form-data">
  <label>Judul Berkas:</label>
  <input type="text" name="judul" required><br><br>

  <label>Pilih File:</label>
  <input type="file" name="berkas" required><br><br>

  <label>Kategori:</label>
  <select name="kategori" required>
    <option value="">-- Pilih Kategori --</option>
    <?php foreach ($kategori as $id => $nama): ?>
      <option value="<?= $nama ?>"><?= htmlspecialchars($nama) ?></option>
    <?php endforeach; ?>
  </select><br><br>

  <button type="submit">Unggah</button>
</form>
// proses unggah di upload.php
$judul = $_POST['judul'];
$kategori = $_POST['kategori']; // simpan ini

// struktur data berkas
$berkasBaru = [
  'judul' => $judul,
  'nama_file' => $namaBaru,
  'kategori' => $kategori,
  'tanggal' => date('Y-m-d H:i:s')
];


exit;
