<?php
require_once __DIR__ . '/env.php';

// Central configuration for the admin backend.
// Not directly web-accessible (blocked via .htaccess).
//
// Credentials live in .env at the project root - a gitignored file that is
// never committed and, in production, is uploaded to the server directly
// (never through git). See .env.example for the required keys. This file
// only reads them; it never hardcodes a username, password, or hash.

define('ADMIN_USERNAME', getenv('ADMIN_USERNAME') ?: '');
define('ADMIN_PASSWORD_HASH', getenv('ADMIN_PASSWORD_HASH') ?: '');

define('BASE_DIR', dirname(__DIR__));
define('CONTENT_DIR', BASE_DIR . '/content');
define('MEMBERS_JSON', CONTENT_DIR . '/members.json');
define('SITE_JSON', CONTENT_DIR . '/site.json');
define('PAGES_JSON', CONTENT_DIR . '/pages.json');
define('UPLOADS_DIR', BASE_DIR . '/uploads/members');
define('UPLOADS_URL_BASE', 'uploads/members');
// Optional hero image per custom sub-page (admin/page-edit.php).
define('UPLOADS_PAGES_DIR', BASE_DIR . '/uploads/pages');
define('UPLOADS_PAGES_URL_BASE', 'uploads/pages');
// Inline images embedded in a bio/body via the WYSIWYG editor (as opposed to
// the one photo per member/page above).
define('UPLOADS_CONTENT_DIR', BASE_DIR . '/uploads/content');
define('UPLOADS_CONTENT_URL_BASE', 'uploads/content');

// MyMemory (mymemory.translated.net) is used for Hebrew -> English
// auto-translation. It's free and needs no signup or API key. Anonymous
// requests are capped at ~5,000 words/day per IP and ~500 characters per
// request (translate_text() chunks longer text automatically); associating
// an email address raises the daily cap to ~50,000 words - safe to leave as
// the site's own contact address.
define('TRANSLATE_API_URL', 'https://api.mymemory.translated.net/get');
define('TRANSLATE_EMAIL', 'oz@pozners.com');

define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'webp', 'gif']);
