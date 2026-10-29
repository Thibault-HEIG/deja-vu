<?php
/**
 * Proxy endpoint: receives an uploaded image, sends it to the ClearBackdrop API
 * as base64, saves the returned transparent PNG to a temp folder, and returns
 * the temp filename as JSON so the front-end can show a preview.
 *
 * Request  : POST multipart/form-data  — field "photo" (the image file)
 * Response : { "tmpFile": "img/tmp/<filename>.png" }  on success
 *            { "error":   "<message>" }               on failure
 */

header('Content-Type: application/json');

const CLEARBACKDROP_API = 'https://clearbackdrop.com/api/v1/remove-background';

const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

$tmpDir = __DIR__ . '/img/tmp';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0775, true);
}

// --- Validate upload ---
if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Aucun fichier reçu.']);
    exit;
}

$mime = mime_content_type($_FILES['photo']['tmp_name']);
if (!in_array($mime, ALLOWED_MIME, true)) {
    http_response_code(415);
    echo json_encode(['error' => 'Format non accepté (JPG, PNG ou WEBP).']);
    exit;
}

// --- Send photo as multipart/form-data to ClearBackdrop ---
$cFile = new CURLFile(
    $_FILES['photo']['tmp_name'],
    $mime,
    $_FILES['photo']['name'] ?? 'photo.' . ($mime === 'image/jpeg' ? 'jpg' : 'png')
);

$ch = curl_init(CLEARBACKDROP_API);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => ['image' => $cFile],
    CURLOPT_TIMEOUT        => 30,
]);

$response   = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError  = curl_error($ch);
// Note: curl_close() is omitted as it is deprecated in PHP 8.5+ (auto-closed)

if ($curlError) {
    error_log('removeBackground.php cURL error: ' . $curlError);
    http_response_code(502);
    echo json_encode(['error' => 'Impossible de joindre l\'API: ' . $curlError]);
    exit;
}

if ($httpStatus !== 200) {
    error_log("removeBackground.php API HTTP {$httpStatus}: " . substr((string)$response, 0, 500));
    http_response_code(502);
    echo json_encode(['error' => 'L\'API a répondu avec le code ' . $httpStatus . '.']);
    exit;
}

// --- Save the returned PNG to the temp folder ---
$tmpFilename = uniqid('tmp_') . '.png';
$tmpPath     = $tmpDir . '/' . $tmpFilename;

if (file_put_contents($tmpPath, $response) === false) {
    error_log('removeBackground.php: Failed to write to ' . $tmpPath);
    http_response_code(500);
    echo json_encode(['error' => 'Impossible de sauvegarder l\'image traitée.']);
    exit;
}

echo json_encode(['tmpFile' => 'img/tmp/' . $tmpFilename]);
