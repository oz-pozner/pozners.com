<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/translate.php';
require_once __DIR__ . '/../includes/uploads.php';

admin_require_login();

function fail(string $message): void
{
    admin_session_start();
    $_SESSION['admin_flash'] = $message;
    header('Location: pages.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Invalid request.');
}
if (!admin_verify_csrf($_POST['csrf_token'] ?? null)) {
    fail('Session expired, please try again.');
}

$pages = content_load_pages();
$originalSlug = preg_replace('/[^a-z0-9-]/', '', strtolower(trim($_POST['original_slug'] ?? '')));
$isNew = $originalSlug === '';

$titleHe = trim($_POST['title_he'] ?? '');
$titleEn = trim($_POST['title_en'] ?? '');
if ($titleHe === '' || $titleEn === '') {
    fail('Title (Hebrew and English) is required.');
}

// Determine the slug: locked to the existing value when editing, generated
// (and de-duplicated) from user input or the English title when creating.
if ($isNew) {
    $slugSource = trim($_POST['slug'] ?? '') !== '' ? $_POST['slug'] : $titleEn;
    $slug = content_slugify($slugSource, 'page');
    $baseSlug = $slug;
    $suffix = 2;
    while (content_find_page($pages, $slug)) {
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }
} else {
    $slug = $originalSlug;
    if (!content_find_page($pages, $slug)) {
        fail('Page not found.');
    }
}

$autoTranslate = ($_POST['auto_translate'] ?? '') === '1';
$bodyHe = trim($_POST['body_he'] ?? '');
$bodyEn = trim($_POST['body_en'] ?? '');

if ($autoTranslate) {
    if ($titleHe !== '') {
        $translated = translate_text($titleHe, 'he', 'en');
        if ($translated !== null) {
            $titleEn = $translated;
        }
    }
    // Skip machine translation when the body has inline images - the
    // translation API isn't HTML-aware and can corrupt <img> tags.
    if ($bodyHe !== '' && !str_contains($bodyHe, '<img')) {
        $translated = translate_text($bodyHe, 'he', 'en');
        if ($translated !== null) {
            $bodyEn = $translated;
        }
    }
}

// Hero image upload (optional - keeps the existing image when none is supplied).
$existing = content_find_page($pages, $slug);
$image = $existing['image'] ?? '';

if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $validationError = uploads_validate_image($_FILES['image']);
    if ($validationError !== null) {
        fail($validationError);
    }
    $ext = uploads_extension($_FILES['image']);
    $tmpPath = $_FILES['image']['tmp_name'];

    if (!is_dir(UPLOADS_PAGES_DIR)) {
        mkdir(UPLOADS_PAGES_DIR, 0775, true);
    }
    foreach (ALLOWED_IMAGE_EXT as $oldExt) {
        $oldPath = UPLOADS_PAGES_DIR . '/' . $slug . '.' . $oldExt;
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }
    $destPath = UPLOADS_PAGES_DIR . '/' . $slug . '.' . $ext;
    if (!move_uploaded_file($tmpPath, $destPath)) {
        fail('Could not save the uploaded image.');
    }
    $image = UPLOADS_PAGES_URL_BASE . '/' . $slug . '.' . $ext . '?v=' . time();
}

$record = [
    'slug' => $slug,
    'order' => $existing['order'] ?? (count($pages) + 1),
    'image' => $image,
    'title_he' => $titleHe,
    'title_en' => $titleEn,
    'nav_label' => trim($_POST['nav_label'] ?? ''),
    'show_in_nav' => ($_POST['show_in_nav'] ?? '') === '1',
    'body_he' => $bodyHe,
    'body_en' => $bodyEn,
    'updated_at' => date('c'),
];

$replaced = false;
foreach ($pages as $i => $existingPage) {
    if (($existingPage['slug'] ?? null) === $slug) {
        $pages[$i] = $record;
        $replaced = true;
        break;
    }
}
if (!$replaced) {
    $pages[] = $record;
}

if (!content_save_pages($pages)) {
    fail('Could not save changes - check that the content/ directory is writable.');
}

admin_session_start();
$_SESSION['admin_flash'] = ($isNew ? 'Added ' : 'Updated ') . $titleEn . '.';
header('Location: pages.php');
exit;
