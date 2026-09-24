<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
 * The document root is the project root itself (cPanel's /public_html, where
 * the document root cannot be pointed at the public/ folder). This front
 * controller therefore also serves everything that normally lives in public/:
 *
 *   - static assets:   /<path>            -> public/<path>
 *                      (vite build, favicon, logos, robots.txt, ...)
 *   - uploaded files:  /storage/<path>    -> storage/app/public/<path>
 *
 * Any other request is handed to Laravel.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');

// The app may be served from a subdirectory (e.g. localhost/laamtex), so strip
// the base path before mapping the request to a file in public/ or
// storage/app/public. PHP's built-in server (php artisan serve) sets
// SCRIPT_NAME to the request path, so only trust it as a real script
// location when it ends with a front controller (index.php / server.php).
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$basePath = '';

if (preg_match('~(?:index|server)\.php$~', $script)) {
    $scriptDir = rtrim(dirname($script), '/');

    if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
        $basePath = $scriptDir;
    }
}

if ($basePath !== '') {
    $uri = substr($uri, strlen($basePath)) ?: '/';
}

if ($uri !== '/' && ! str_contains($uri, '..')) {
    $file = str_starts_with($uri, '/storage/')
        ? __DIR__.'/storage/app/public/'.substr($uri, strlen('/storage/'))
        : __DIR__.'/public'.$uri;

    if (is_file($file) && is_readable($file)) {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'mjs' => 'application/javascript',
            'json' => 'application/json',
            'webp' => 'image/webp',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'avif' => 'image/avif',
            'txt' => 'text/plain',
            'xml' => 'application/xml',
            'pdf' => 'application/pdf',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'eot' => 'application/vnd.ms-fontobject',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mp3' => 'audio/mpeg',
        ];

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        header('Content-Type: '.($mimeTypes[$extension] ?? 'application/octet-stream'));
        header('Content-Length: '.filesize($file));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: public, max-age='.(str_starts_with($uri, '/build/') ? '31536000, immutable' : '86400'));

        readfile($file);
        exit;
    }
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Request::capture());