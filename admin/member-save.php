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
    header('Location: members.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Invalid request.');
}
if (!admin_verify_csrf($_POST['csrf_token'] ?? null)) {
    fail('Session expired, please try again.');
}

$members = content_load_members();
$originalSlug = preg_replace('/[^a-z0-9-]/', '', strtolower(trim($_POST['original_slug'] ?? '')));
$isNew = $originalSlug === '';

$nameHe = trim($_POST['name_he'] ?? '');
$nameEn = trim($_POST['name_en'] ?? '');
if ($nameHe === '' || $nameEn === '') {
    fail('First name (Hebrew and English) is required.');
}

// Determine the slug: locked to the existing value when editing, generated
// (and de-duplicated) from user input or the English name when creating.
if ($isNew) {
    $slugSource = trim($_POST['slug'] ?? '') !== '' ? $_POST['slug'] : $nameEn;
    $slug = content_slugify($slugSource, 'member');
    $baseSlug = $slug;
    $suffix = 2;
    while (content_find_member($members, $slug)) {
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }
} else {
    $slug = $originalSlug;
    if (!content_find_member($members, $slug)) {
        fail('Member not found.');
    }
}

$autoTranslate = ($_POST['auto_translate'] ?? '') === '1';
$roleHe = trim($_POST['role_he'] ?? '');
$bioHe = trim($_POST['bio_he'] ?? '');
$interestsHe = trim($_POST['interests_he'] ?? '');

$roleEn = trim($_POST['role_en'] ?? '');
$bioEn = trim($_POST['bio_en'] ?? '');
$interestsEn = trim($_POST['interests_en'] ?? '');

if ($autoTranslate) {
    if ($roleHe !== '') {
        $translated = translate_text($roleHe, 'he', 'en');
        if ($translated !== null) {
            $roleEn = $translated;
        }
    }
    // Skip machine translation when the bio has inline images - the
    // translation API isn't HTML-aware and can corrupt <img> tags.
    if ($bioHe !== '' && !str_contains($bioHe, '<img')) {
        $translated = translate_text($bioHe, 'he', 'en');
        if ($translated !== null) {
            $bioEn = $translated;
        }
    }
    if ($interestsHe !== '') {
        $translated = translate_text($interestsHe, 'he', 'en');
        if ($translated !== null) {
            $interestsEn = $translated;
        }
    }
}

// Photo upload (optional - keeps the existing photo when none is supplied).
$existing = content_find_member($members, $slug);
$photo = $existing['photo'] ?? '';

if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $validationError = uploads_validate_image($_FILES['photo']);
    if ($validationError !== null) {
        fail($validationError);
    }
    $ext = uploads_extension($_FILES['photo']);
    $tmpPath = $_FILES['photo']['tmp_name'];

    if (!is_dir(UPLOADS_DIR)) {
        mkdir(UPLOADS_DIR, 0775, true);
    }
    // Remove any previously uploaded photo for this slug (format may have changed).
    foreach (ALLOWED_IMAGE_EXT as $oldExt) {
        $oldPath = UPLOADS_DIR . '/' . $slug . '.' . $oldExt;
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }
    $destPath = UPLOADS_DIR . '/' . $slug . '.' . $ext;
    if (!move_uploaded_file($tmpPath, $destPath)) {
        fail('Could not save the uploaded photo.');
    }
    $photo = UPLOADS_URL_BASE . '/' . $slug . '.' . $ext . '?v=' . time();
}

$record = [
    'slug' => $slug,
    'order' => $existing['order'] ?? (count($members) + 1),
    'photo' => $photo,
    'name_he' => $nameHe,
    'name_en' => $nameEn,
    'full_name_he' => trim($_POST['full_name_he'] ?? ''),
    'full_name_en' => trim($_POST['full_name_en'] ?? ''),
    'role_he' => $roleHe,
    'role_en' => $roleEn,
    'bio_he' => $bioHe,
    'bio_en' => $bioEn,
    'interests_he' => $interestsHe,
    'interests_en' => $interestsEn,
    'contact_email' => trim($_POST['contact_email'] ?? ''),
    'known_contacts' => trim($_POST['known_contacts'] ?? ''),
    'updated_at' => date('c'),
];

$replaced = false;
foreach ($members as $i => $m) {
    if (($m['slug'] ?? null) === $slug) {
        $members[$i] = $record;
        $replaced = true;
        break;
    }
}
if (!$replaced) {
    $members[] = $record;
}

if (!content_save_members($members)) {
    fail('Could not save changes - check that the content/ directory is writable.');
}

admin_session_start();
$_SESSION['admin_flash'] = ($isNew ? 'Added ' : 'Updated ') . $nameEn . '.';
header('Location: members.php');
exit;
