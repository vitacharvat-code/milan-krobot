<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { http_response_code(403); exit; }

$galleryFile = __DIR__ . '/../data/gallery.json';
$imagesDir   = __DIR__ . '/../assets/images/';
$filename    = $_POST['filename'] ?? '';

if (empty($filename) || strpos($filename, '/') !== false || strpos($filename, '..') !== false) {
    header('Location: panel.php');
    exit;
}

// Remove from gallery.json
$gallery = json_decode(file_get_contents($galleryFile), true);
$gallery['photos'] = array_values(array_filter(
    $gallery['photos'],
    fn($p) => $p['filename'] !== $filename
));
file_put_contents($galleryFile, json_encode($gallery, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Delete file from disk
$filepath = $imagesDir . urldecode($filename);
if (file_exists($filepath)) {
    unlink($filepath);
}

header('Location: panel.php?success=delete');
