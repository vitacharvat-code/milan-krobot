<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { http_response_code(403); exit; }

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$order = $input['order'] ?? [];

if (empty($order)) { http_response_code(400); exit; }

$galleryFile = __DIR__ . '/../data/gallery.json';
$gallery = json_decode(file_get_contents($galleryFile), true);
$photos = $gallery['photos'] ?? [];

// Reorder photos
$reorderedPhotos = [];
foreach ($order as $filename) {
    $photo = array_values(array_filter($photos, fn($p) => $p['filename'] === $filename))[0] ?? null;
    if ($photo) $reorderedPhotos[] = $photo;
}

file_put_contents($galleryFile, json_encode(['photos' => $reorderedPhotos], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['success' => true]);
