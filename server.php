<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

$baseDir = __DIR__;

// 1. Direct path check (e.g. /public/frontEnd/css/... or /favicon.ico)
$targetFile = null;
if ($uri !== '/' && file_exists($baseDir . $uri) && !is_dir($baseDir . $uri)) {
    $targetFile = $baseDir . $uri;
} elseif ($uri !== '/' && file_exists($baseDir . '/public' . $uri) && !is_dir($baseDir . '/public' . $uri)) {
    // 2. URI without /public prefix (e.g. /frontEnd/css/...)
    $targetFile = $baseDir . '/public' . $uri;
} elseif (str_starts_with($uri, '/public/')) {
    // 3. Fallback for /public/... inside public directory
    $stripped = $baseDir . '/public/' . substr($uri, 8);
    if (file_exists($stripped) && !is_dir($stripped)) {
        $targetFile = $stripped;
    }
}

if ($targetFile) {
    $mimes = [
        'css'  => 'text/css',
        'js'   => 'text/javascript',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'eot'  => 'application/vnd.ms-fontobject',
        'otf'  => 'font/otf',
        'mp4'  => 'video/mp4',
        'pdf'  => 'application/pdf',
    ];

    $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $mime = $mimes[$ext] ?? (function_exists('mime_content_type') ? mime_content_type($targetFile) : 'application/octet-stream');

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($targetFile));
    readfile($targetFile);
    exit;
}

// Pass all other requests to Laravel's front controller
require_once $baseDir . '/index.php';
