<?php
// Central configuration for the admin backend.
// Not directly web-accessible (blocked via .htaccess).

define('ADMIN_USERNAME', 'PozAdmin');
// bcrypt hash of the admin password - never store the plaintext password.
define('ADMIN_PASSWORD_HASH', '$2y$12$oNowe1WkKO0w4oputPWrROAPWhDYHmdoCmM.w8njFe3jsCrKrPg0K');

define('BASE_DIR', dirname(__DIR__));
define('CONTENT_DIR', BASE_DIR . '/content');
define('MEMBERS_JSON', CONTENT_DIR . '/members.json');
define('UPLOADS_DIR', BASE_DIR . '/uploads/members');
define('UPLOADS_URL_BASE', 'uploads/members');
// Inline images embedded in a bio via the WYSIWYG editor (as opposed to the
// one profile photo per member above).
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
