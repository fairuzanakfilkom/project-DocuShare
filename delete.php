<?php
include 'functions.php';
$id = $_GET['id'];
$files = getAllFiles();
foreach ($files as $i => $f) {
    if ($f['id'] == $id) {
        if ($f['type'] == 'upload' && file_exists($f['path'])) {
            unlink($f['path']);
        }
        unset($files[$i]);
        break;
    }
}
saveAllFiles(array_values($files));
header('Location: index.php');
?>
