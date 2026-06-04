<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { http_response_code(403); exit; }

$textsFile = __DIR__ . '/../data/texts.json';
$texts = file_exists($textsFile) ? json_decode(file_get_contents($textsFile), true) : [];

// Update only non-empty fields
foreach ($_POST as $key => $value) {
    if (strpos($key, '_') !== false) { // Only process defined fields
        $texts[$key] = trim($value);
    }
}

file_put_contents($textsFile, json_encode($texts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header('Location: panel.php?success=texts');
