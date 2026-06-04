<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { http_response_code(403); exit; }

$textsFile = __DIR__ . '/../data/texts.json';
$texts = [
    'bio_1' => trim($_POST['bio_1'] ?? ''),
    'bio_2' => trim($_POST['bio_2'] ?? ''),
];
file_put_contents($textsFile, json_encode($texts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header('Location: panel.php?success=texts');
