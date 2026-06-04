<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { http_response_code(403); exit; }

$galleryFile = __DIR__ . '/../data/gallery.json';
$imagesDir   = __DIR__ . '/../assets/images/';
$allowed     = ['image/jpeg', 'image/png', 'image/webp'];
$maxSize     = 10 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['photo'])) {
    header('Location: panel.php');
    exit;
}

$file = $_FILES['photo'];

// Validate
if ($file['error'] !== UPLOAD_ERR_OK) {
    header('Location: panel.php?error=upload');
    exit;
}
if ($file['size'] > $maxSize) {
    header('Location: panel.php?error=size');
    exit;
}
$mime = mime_content_type($file['tmp_name']);
if (!in_array($mime, $allowed)) {
    header('Location: panel.php?error=type');
    exit;
}

// Sanitize filename
$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
$filename = $basename . '.' . strtolower($ext);

// Avoid overwrite
$i = 1;
while (file_exists($imagesDir . $filename)) {
    $filename = $basename . '_' . $i . '.' . strtolower($ext);
    $i++;
}

if (!move_uploaded_file($file['tmp_name'], $imagesDir . $filename)) {
    header('Location: panel.php?error=save');
    exit;
}

// Detect landscape
$size = getimagesize($imagesDir . $filename);
$wide = $size && ($size[0] / $size[1] > 1.3);

// Update gallery.json
$gallery = json_decode(file_get_contents($galleryFile), true);
$gallery['photos'][] = [
    'filename' => $filename,
    'alt'      => 'Milan Krobot',
    'wide'     => $wide,
];
file_put_contents($galleryFile, json_encode($gallery, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header('Location: panel.php?success=upload');
