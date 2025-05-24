<?php
include 'functions.php';
$files = getAllFiles();
$id = $_GET['id'];
foreach ($files as &$f) {
    if ($f['id'] == $id) {
        $file = $f;
        break;
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($files as &$f) {
        if ($f['id'] == $id) {
            $f['name'] = $_POST['name'];
            break;
        }
    }
    saveAllFiles($files);
    header('Location: index.php');
}
?>
<form method="post">
    <input type="text" name="name" value="<?= $file['name'] ?>" required>
    <button type="submit">Update</button>
</form>
