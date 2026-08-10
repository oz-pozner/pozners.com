<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/uploads.php';

header('Content-Type: application/json');

if (!admin_is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in.']);
    exit;
}
if (!admin_verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    echo json_encode(['error' => 'Session expired, please reload the page.']);
    exit;
}
if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No image received.']);
    exit;
}

$file = $_FILES['image'];
$validationError = uploads_validate_image($file);
if ($validationError !== null) {
    http_response_code(400);
    echo json_encode(['error' => $validationError]);
    exit;
}

if (!is_dir(UPLOADS_CONTENT_DIR)) {
    mkdir(UPLOADS_CONTENT_DIR, 0775, true);
}

$ext = uploads_extension($file);
$filename = bin2hex(random_bytes(8)) . '.' . $ext;
$destPath = UPLOADS_CONTENT_DIR . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not save the uploaded image.']);
    exit;
}

echo json_encode(['url' => UPLOADS_CONTENT_URL_BASE . '/' . $filename]);
