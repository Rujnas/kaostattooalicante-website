<?php
/**
 * Endpoint para procesar y guardar las consultas del formulario de contacto:
 * 1. Valida los datos recibidos y protege contra spam.
 * 2. Guarda las imágenes adjuntas en `uploads/consultas/` con nombres seguros y únicos.
 * 3. Almacena la consulta en la base de datos MySQL (tabla `consultas`).
 * 4. Reenvía la consulta por correo electrónico a FormSubmit (kaostattooalc@gmail.com)
 *    con los archivos adjuntos y los enlaces directos a las imágenes guardadas.
 */

header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
header('X-Frame-Options: SAMEORIGIN');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
header('Content-Type: application/json; charset=utf-8');

// Solo peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// 1. Filtro antispam: honeypot
if (!empty($_POST['website'])) {
    // Respuesta simulada para bots sin hacer nada
    echo json_encode(['success' => true]);
    exit;
}

// 2. Extraer y sanitizar campos
$tipo      = trim($_POST['Tipo'] ?? $_POST['tipo'] ?? '');
$zona      = trim($_POST['Zona'] ?? $_POST['zona'] ?? '');
$idea      = trim($_POST['Idea / Estilo'] ?? $_POST['idea'] ?? '');
$nombre    = trim($_POST['Nombre'] ?? $_POST['nombre'] ?? '');
$email     = trim($_POST['Email'] ?? $_POST['email'] ?? '');
$telefono  = trim($_POST['Teléfono'] ?? $_POST['telefono'] ?? '');
$idioma    = trim($_POST['idioma'] ?? 'es');

// Validaciones básicas de campos obligatorios
if ($nombre === '' || $email === '' || $telefono === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email no válido']);
    exit;
}

// IP del cliente (para trazabilidad y registro de consentimiento RGPD)
$ip = $_SERVER['HTTP_CF_CONNECTING_IP']
    ?? $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR']
    ?? '';
if (strpos($ip, ',') !== false) {
    $ip = trim(explode(',', $ip)[0]);
}
$ip = substr($ip, 0, 45);

// 3. Procesar y guardar imágenes adjuntas
$uploadDir = __DIR__ . '/uploads/consultas/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$savedImages    = []; // Rutas relativas para BBDD y URLs
$savedFullPaths = []; // Rutas locales completas para adjuntar por cURL
$allowedMimes   = ['image/jpeg', 'image/png', 'image/webp'];
$allowedExts    = ['jpg', 'jpeg', 'png', 'webp'];
$maxFileSize    = 5 * 1024 * 1024; // 5 MB por archivo
$maxFiles       = 4;

// Normalizar archivos de $_FILES (soporta attachment, attachment[], etc.)
$incomingFiles = [];
foreach ($_FILES as $inputKey => $fileData) {
    if (is_array($fileData['name'])) {
        $count = count($fileData['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($fileData['error'][$i] === UPLOAD_ERR_OK) {
                $incomingFiles[] = [
                    'name'     => $fileData['name'][$i],
                    'type'     => $fileData['type'][$i],
                    'tmp_name' => $fileData['tmp_name'][$i],
                    'error'    => $fileData['error'][$i],
                    'size'     => $fileData['size'][$i],
                ];
            }
        }
    } elseif ($fileData['error'] === UPLOAD_ERR_OK) {
        $incomingFiles[] = $fileData;
    }
}

// Limitar a máximo 4 imágenes
$incomingFiles = array_slice($incomingFiles, 0, $maxFiles);

foreach ($incomingFiles as $file) {
    if ($file['size'] > $maxFileSize) {
        continue;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext === 'jpeg') {
        $ext = 'jpg';
    }

    if (!in_array($ext, $allowedExts, true)) {
        continue;
    }

    // Validar tipo MIME real del contenido
    $realMime = null;
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        $realMime = mime_content_type($file['tmp_name']);
    } else {
        $realMime = $file['type'];
    }

    if (!in_array($realMime, $allowedMimes, true)) {
        continue;
    }

    // Generar nombre de archivo único y seguro
    try {
        $randomHash = bin2hex(random_bytes(6));
    } catch (Exception $e) {
        $randomHash = substr(md5(uniqid((string)mt_rand(), true)), 0, 12);
    }

    $safeFilename = date('Ymd_His') . '_' . $randomHash . '.' . $ext;
    $targetPath   = $uploadDir . $safeFilename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $savedImages[] = 'uploads/consultas/' . $safeFilename;
        $savedFullPaths[] = [
            'path' => $targetPath,
            'name' => $file['name'],
            'mime' => $realMime,
        ];
    }
}

// 4. Guardar en Base de Datos MySQL
$dbSaved = false;
$configFile = __DIR__ . '/config.db.php';

if (file_exists($configFile)) {
    try {
        $dbConfig = require $configFile;
        if (!empty($dbConfig['user']) && strpos($dbConfig['user'], 'TU_USUARIO') === false) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $dbConfig['host'] ?? 'localhost',
                $dbConfig['dbname'] ?? 'u589481109_DatosForm',
                $dbConfig['charset'] ?? 'utf8mb4'
            );

            $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT            => 5,
            ]);

            $stmt = $pdo->prepare('
                INSERT INTO consultas (
                    tipo, zona, idea, nombre, email, telefono, imagenes, idioma, politica_aceptada, ip
                ) VALUES (
                    :tipo, :zona, :idea, :nombre, :email, :telefono, :imagenes, :idioma, :politica_aceptada, :ip
                )
            ');

            $stmt->execute([
                ':tipo'              => mb_substr($tipo, 0, 50),
                ':zona'              => mb_substr($zona, 0, 100),
                ':idea'              => $idea !== '' ? $idea : null,
                ':nombre'            => mb_substr($nombre, 0, 150),
                ':email'             => mb_substr($email, 0, 150),
                ':telefono'          => mb_substr($telefono, 0, 50),
                ':imagenes'          => !empty($savedImages) ? json_encode($savedImages, JSON_UNESCAPED_SLASHES) : null,
                ':idioma'            => mb_substr($idioma, 0, 5),
                ':politica_aceptada' => 1,
                ':ip'                => $ip,
            ]);

            $dbSaved = true;
        }
    } catch (Throwable $e) {
        error_log('Error al guardar consulta en BBDD Kaos Tattoo: ' . $e->getMessage());
    }
}

// 5. Reenviar consulta a FormSubmit (y/o correo de respaldo)
$emailSent = false;

// Construir URLs públicas de las fotos si las hay
$imageUrls = [];
if (!empty($savedImages)) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'kaostattooalicante.es';
    foreach ($savedImages as $rel) {
        $imageUrls[] = $protocol . $host . '/' . $rel;
    }
}

// Preparar los campos para FormSubmit
$postFields = [
    '_captcha'  => 'false',
    '_template' => 'table',
    '_subject'  => 'Nueva consulta de ' . ($tipo !== '' ? $tipo : 'proyecto') . ' - ' . $nombre,
    'Tipo'      => $tipo,
    'Zona'      => $zona,
    'Nombre'    => $nombre,
    'Email'     => $email,
    'Teléfono'  => $telefono,
];

if ($idea !== '') {
    $postFields['Idea / Estilo'] = $idea;
}

if (!empty($imageUrls)) {
    $postFields['Fotos (enlaces web)'] = implode("\n", $imageUrls);
}

// Adjuntar los archivos para FormSubmit mediante cURL
if (function_exists('curl_init')) {
    if (!empty($savedFullPaths)) {
        foreach ($savedFullPaths as $idx => $f) {
            $postFields['attachment[' . $idx . ']'] = new CURLFile($f['path'], $f['mime'], $f['name']);
        }
    }

    $ch = curl_init('https://formsubmit.co/ajax/kaostattooalc@gmail.com');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_USERAGENT      => 'KaosTattooAlicante/1.0',
    ]);

    $curlResponse = curl_exec($ch);
    $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        $json = json_decode($curlResponse, true);
        if ($json && (!empty($json['success']) || (isset($json['message']) && stripos($json['message'], 'success') !== false))) {
            $emailSent = true;
        }
    }
}

// Respaldo de correo si FormSubmit falló por red
if (!$emailSent) {
    $to = 'kaostattooalc@gmail.com';
    $subject = '=?UTF-8?B?' . base64_encode('Nueva consulta de ' . $tipo . ' - ' . $nombre) . '?=';
    $body = "Nueva consulta recibida desde la web:\n\n"
          . "Nombre: $nombre\n"
          . "Email: $email\n"
          . "Teléfono: $telefono\n"
          . "Tipo: $tipo\n"
          . "Zona: $zona\n";
    if ($idea !== '') {
        $body .= "Idea / Estilo: $idea\n";
    }
    if (!empty($imageUrls)) {
        $body .= "\nImágenes guardadas:\n" . implode("\n", $imageUrls) . "\n";
    }
    $headers = "From: Kaos Tattoo Web <web@kaostattooalicante.es>\r\n"
             . "Reply-To: $email\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n";
    if (@mail($to, $subject, $body, $headers)) {
        $emailSent = true;
    }
}

// 6. Respuesta al cliente
// Si se guardó en BBDD o se envió el correo, consideramos la operación exitosa para no perder al cliente.
if ($dbSaved || $emailSent) {
    echo json_encode([
        'success'   => true,
        'db_saved'  => $dbSaved,
        'email_sent'=> $emailSent,
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'No se pudo procesar la consulta. Inténtalo de nuevo.'
    ]);
}
