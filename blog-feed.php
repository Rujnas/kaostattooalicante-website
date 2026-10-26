<?php
/**
 * Kaos Tattoo Alicante - Medium Blog Feed Proxy & Cache
 * Fetches Medium RSS, parses it into JSON, and caches it locally
 * to ensure 100% uptime, zero Cloudflare blockages, and fast response times.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=1800');

$cacheFile = __DIR__ . '/blog-cache.json';
$cacheTtl  = 1800; // 30 minutes in seconds

// If cache is fresh, serve it immediately
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
    $cachedData = @file_get_contents($cacheFile);
    if ($cachedData) {
        echo $cachedData;
        exit;
    }
}

// Otherwise, fetch latest feed from Medium
$feedUrl = 'https://medium.com/@sastreg86/feed';
$xmlString = false;

// 1. Try cURL
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $feedUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; KaosTattooBot/1.0; +https://kaostattooalicante.es)');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $xmlString = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200) {
        $xmlString = false;
    }
}

// 2. Fallback to file_get_contents if curl failed or unavailable
if (!$xmlString && ini_get('allow_url_fopen')) {
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => 8,
            'header'  => "User-Agent: Mozilla/5.0 (compatible; KaosTattooBot/1.0; +https://kaostattooalicante.es)\r\n"
        ]
    ]);
    $xmlString = @file_get_contents($feedUrl, false, $ctx);
}

// If fetching failed, fallback to existing cache even if stale
if (!$xmlString) {
    if (file_exists($cacheFile)) {
        $fallback = @file_get_contents($cacheFile);
        if ($fallback) {
            echo $fallback;
            exit;
        }
    }
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Unable to fetch Medium feed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Parse XML
libxml_use_internal_errors(true);
$xml = @simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
if (!$xml || !isset($xml->channel->item)) {
    if (file_exists($cacheFile)) {
        $fallback = @file_get_contents($cacheFile);
        if ($fallback) {
            echo $fallback;
            exit;
        }
    }
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Invalid RSS XML from Medium'], JSON_UNESCAPED_UNICODE);
    exit;
}

$items = [];
foreach ($xml->channel->item as $item) {
    $title   = trim((string)$item->title);
    $link    = trim((string)$item->link);
    $pubDate = trim((string)$item->pubDate);
    $guid    = trim((string)$item->guid);

    $dc = $item->children('http://purl.org/dc/elements/1.1/');
    $author = $dc ? trim((string)$dc->creator) : '';

    $contentNs = $item->children('http://purl.org/rss/1.0/modules/content/');
    $content = $contentNs ? trim((string)$contentNs->encoded) : '';

    // Extract first image
    $thumbnail = '';
    if ($content && preg_match('/<img[^>]+src=["\'](https:\/\/[^\s"\']+)["\']/i', $content, $m)) {
        if (strpos($m[1], '_/stat') === false && strpos($m[1], 'pixel') === false) {
            $thumbnail = $m[1];
        }
    }

    $description = trim((string)$item->description);
    if (!$description && $content) {
        $clean = preg_replace('/<[^>]+>/', ' ', $content);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $description = mb_substr(trim($clean), 0, 300);
    }

    $items[] = [
        'title'       => $title,
        'pubDate'     => $pubDate,
        'link'        => $link,
        'guid'        => $guid,
        'author'      => $author,
        'thumbnail'   => $thumbnail,
        'description' => $description,
        'content'     => $content
    ];
}

$result = [
    'status' => 'ok',
    'feed'   => [
        'url'   => $feedUrl,
        'title' => (string)$xml->channel->title,
        'link'  => (string)$xml->channel->link
    ],
    'items'  => $items
];

$json = json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if ($json) {
    @file_put_contents($cacheFile, $json);
    echo $json;
} else {
    echo json_encode(['status' => 'error', 'message' => 'JSON encode error']);
}
