<?php
require_once __DIR__ . '/config.php';

/**
 * Validate an entry from $_FILES as an acceptable image upload.
 * Returns an error message, or null if the file is valid.
 */
function uploads_validate_image(array $file): ?string
{
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        return 'Image is too large (max 5MB).';
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMAGE_EXT, true) || @getimagesize($file['tmp_name']) === false) {
        return 'Image must be a JPG, PNG, WEBP or GIF file.';
    }
    return null;
}

function uploads_extension(array $file): string
{
    return strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
}
