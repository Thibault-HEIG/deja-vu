<?php
/**
 * Proxy endpoint: calls ClearBackdrop API to remove the background of an uploaded photo.
 *
 * Endpoint : POST removeBackground.php
 * Params   : multipart/form-data with "photo" (file)
 * Response : JSON { "tmpFile": "img/tmp/tmp_xyz.png" } on success
 *            JSON { "error": "Description" } on failure
 */

header('Content-Type: application/json');

const CLEARBACKDROP_API = 'https://clearbackdrop.com/api/v1/remove-background';
$CLEARBACKDROP_KEY = getenv('BG_REMOVER_API') ?: '';

$tmpDir = __DIR__ . '/../../public/img/tmp';

// 1. Basic validation: verify an image was uploaded without errors
if (empty($_FILES['photo']['tmp_name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Aucune image reçue ou erreur de téléchargement.']);
    exit;
}

if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0775, true);
}

// 2. Call ClearBackdrop API
// Forwarding $_FILES['photo']['name'] preserves the original file extension required by the API
$ch = curl_init(CLEARBACKDROP_API);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['X-API-Key: ' . $CLEARBACKDROP_KEY],
    CURLOPT_POSTFIELDS     => [
        'image' => new CURLFile(
            $_FILES['photo']['tmp_name'],
            $_FILES['photo']['type'] ?: null,
            $_FILES['photo']['name']
        ),
    ],
    CURLOPT_TIMEOUT        => 30,
]);

$response   = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false || $httpStatus !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'Échec du détourage par le service externe.']);
    exit;
}

// 3. Save the returned PNG into the temporary directory for client preview
$tmpFilename = uniqid('tmp_') . '.png';
file_put_contents($tmpDir . '/' . $tmpFilename, $response);

echo json_encode(['tmpFile' => 'img/tmp/' . $tmpFilename]);

