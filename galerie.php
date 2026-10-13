<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$directory = __DIR__ . DIRECTORY_SEPARATOR . 'IMAGE' . DIRECTORY_SEPARATOR . 'galerie';
$allowedTypes = [
    'jpg' => ['image', 'image/jpeg'],
    'jpeg' => ['image', 'image/jpeg'],
    'png' => ['image', 'image/png'],
    'webp' => ['image', 'image/webp'],
    'gif' => ['image', 'image/gif'],
    'mp4' => ['video', 'video/mp4'],
    'webm' => ['video', 'video/webm'],
    'mov' => ['video', 'video/quicktime'],
];

if (!is_dir($directory) || !is_readable($directory)) {
    http_response_code(500);
    echo json_encode(['error' => 'Le dossier IMAGE/galerie est introuvable ou inaccessible.']);
    exit;
}

$entries = scandir($directory);
if ($entries === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Impossible de lire le dossier IMAGE/galerie.']);
    exit;
}

$media = [];
foreach ($entries as $entry) {
    $path = $directory . DIRECTORY_SEPARATOR . $entry;
    $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));

    if ($entry === '.' || $entry === '..' || is_link($path) || !is_file($path) || !is_readable($path) || !isset($allowedTypes[$extension])) {
        continue;
    }

    $media[] = [
        'name' => $entry,
        'type' => $allowedTypes[$extension][0],
        'mime' => $allowedTypes[$extension][1],
        'src' => 'IMAGE/galerie/' . rawurlencode($entry),
    ];
}

usort($media, static function ($first, $second) {
    $naturalOrder = strnatcasecmp($first['name'], $second['name']);
    return $naturalOrder !== 0 ? $naturalOrder : strcmp($first['name'], $second['name']);
});

echo json_encode($media, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
