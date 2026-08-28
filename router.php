<?php
// Local dev router - emulates .htaccess clean URL rewrites
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$decoded = urldecode($uri);
$file = __DIR__ . $decoded;

// Serve existing files directly (images, css, js, fonts, videos)
if ($decoded !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // Let PHP built-in server handle it
}

// SPA routes - serve index.php (per-page SEO)
$spaRoutesEs = ['equipo','anilladora','estilos','tatuajes','piercings','dibujos-cuadros','contacto','blog'];
$spaRoutesEn = ['team','piercer','styles','tattoos','piercings','art','contact','blog'];
$cleanUri = trim($uri, '/');

// Redirect old English URLs (Spanish slugs) to the translated ones (301)
$enSlugRedirects = [
    'en/equipo' => '/en/team/', 'en/anilladora' => '/en/piercer/',
    'en/estilos' => '/en/styles/', 'en/tatuajes' => '/en/tattoos/',
    'en/dibujos-cuadros' => '/en/art/', 'en/contacto' => '/en/contact/',
];
if (isset($enSlugRedirects[$cleanUri])) {
    header('Location: ' . $enSlugRedirects[$cleanUri], true, 301);
    exit;
}

// Spanish routes
if ($cleanUri === '' || in_array($cleanUri, $spaRoutesEs)) {
    include __DIR__ . '/index.php';
    exit;
}
// English routes: /en and /en/<translated-route>
if ($cleanUri === 'en' || preg_match('#^en/('.implode('|', $spaRoutesEn).')$#', $cleanUri)) {
    include __DIR__ . '/index.php';
    exit;
}

// 404 for anything else
http_response_code(404);
echo '404 Not Found';
