<?php
// Local dev router - emulates .htaccess clean URL rewrites
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$decoded = urldecode($uri);
$file = __DIR__ . $decoded;

// Serve existing files directly (images, css, js, fonts, videos)
if ($decoded !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // Let PHP built-in server handle it
}

// SPA routes - serve index.html
$spaRoutes = ['equipo','anilladora','estilos','tatuajes','piercings','walkins','dibujos-cuadros','contacto','blog'];
$cleanUri = trim($uri, '/');

if ($cleanUri === '' || in_array($cleanUri, $spaRoutes)) {
    include __DIR__ . '/index.html';
    exit;
}

// 404 for anything else
http_response_code(404);
echo '404 Not Found';
