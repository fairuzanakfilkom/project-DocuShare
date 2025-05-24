<?php
include 'functions.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $files = getAllFiles();
    $files[] = [
        'id' => uniqid(),
        'name' => $name,
        'type' => 'text',
        'path' => null
    ];
    saveAllFiles($files);
    header('Location: index.php');
}
?>
<form method="post">
    <input type="text" name="name" placeholder="Nama berkas baru" required>
    <button type="submit">Simpan</button>
</form>
