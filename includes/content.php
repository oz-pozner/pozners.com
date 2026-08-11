<?php
require_once __DIR__ . '/config.php';

/**
 * Generic JSON file read with a shared-lock, used by every content store
 * (members, site, pages) so they don't each reimplement file I/O.
 */
function content_load_json(string $path, $default = [])
{
    if (!file_exists($path)) {
        return $default;
    }
    $fp = fopen($path, 'r');
    if (!$fp) {
        return $default;
    }
    flock($fp, LOCK_SH);
    $raw = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    $data = json_decode((string) $raw, true);
    return $data ?? $default;
}

/**
 * Generic JSON file write with an exclusive lock.
 */
function content_save_json(string $path, $data): bool
{
    if (!is_dir(CONTENT_DIR)) {
        mkdir(CONTENT_DIR, 0775, true);
    }
    $fp = fopen($path, 'c+');
    if (!$fp) {
        return false;
    }
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    fwrite($fp, $json);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

function content_load_members(): array
{
    $data = content_load_json(MEMBERS_JSON, []);
    if (!is_array($data)) {
        return [];
    }
    usort($data, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
    return $data;
}

function content_save_members(array $members): bool
{
    return content_save_json(MEMBERS_JSON, array_values($members));
}

function content_find_member(array $members, string $slug): ?array
{
    return content_find_by_slug($members, $slug);
}

/**
 * The site's singleton home-page content (hero, about, contact copy, etc).
 * Missing keys fall back to content_site_defaults() so older/partial
 * site.json files (or a fresh install) still render sensible copy.
 */
function content_load_site(): array
{
    $data = content_load_json(SITE_JSON, []);
    return array_merge(content_site_defaults(), is_array($data) ? $data : []);
}

function content_save_site(array $site): bool
{
    return content_save_json(SITE_JSON, $site);
}

function content_site_defaults(): array
{
    return [
        'hero_kicker_he' => 'משפחה', 'hero_kicker_en' => 'Family',
        'hero_title_he' => 'ברוכים הבאים', 'hero_title_en' => 'Welcome',
        'hero_subtitle_he' => '', 'hero_subtitle_en' => '',
        'hero_image' => '',
        'address_he' => '', 'address_en' => '',
        'family_kicker_he' => 'המשפחה', 'family_kicker_en' => 'The Family',
        'family_title_he' => '', 'family_title_en' => '',
        'family_tagline_he' => '', 'family_tagline_en' => '',
        'video_channel_url' => '', 'video_channel_name' => '', 'video_playlist_id' => '',
        'about_kicker_he' => 'על הבית', 'about_kicker_en' => 'About',
        'about_title_he' => '', 'about_title_en' => '',
        'about_body_he' => '', 'about_body_en' => '',
        'meeting_note_he' => '', 'meeting_note_en' => '',
        'contact_kicker_he' => 'יצירת קשר', 'contact_kicker_en' => 'Get in touch',
        'contact_title_he' => '', 'contact_title_en' => '',
        'contact_body_he' => '', 'contact_body_en' => '',
        'footer_text_he' => '', 'footer_text_en' => '',
    ];
}

/**
 * Custom sub-pages created through the admin (beyond member profiles),
 * e.g. "Our Story" or a photo gallery page.
 */
function content_load_pages(): array
{
    $data = content_load_json(PAGES_JSON, []);
    if (!is_array($data)) {
        return [];
    }
    usort($data, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
    return $data;
}

function content_save_pages(array $pages): bool
{
    return content_save_json(PAGES_JSON, array_values($pages));
}

function content_find_page(array $pages, string $slug): ?array
{
    return content_find_by_slug($pages, $slug);
}

function content_find_by_slug(array $records, string $slug): ?array
{
    foreach ($records as $r) {
        if (($r['slug'] ?? null) === $slug) {
            return $r;
        }
    }
    return null;
}

function content_slugify(string $text, string $fallbackPrefix = 'item'): string
{
    $ascii = preg_replace('/[^a-zA-Z0-9]+/', '-', trim($text));
    $ascii = strtolower(trim((string) $ascii, '-'));
    if ($ascii === '') {
        $ascii = $fallbackPrefix . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
    }
    return $ascii;
}

/**
 * Plain-text excerpt of a rich-text (HTML) bio/body, used for home page
 * cards and SEO/AI meta descriptions. Tags are replaced with a space (not
 * just stripped) so e.g. "</p><h2>" doesn't glue two words together.
 */
function content_excerpt(string $html, int $maxChars = 160): string
{
    $text = preg_replace('/<[^>]+>/', ' ', $html);
    $text = html_entity_decode(trim((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    if (mb_strlen($text, 'UTF-8') > $maxChars) {
        $text = mb_substr($text, 0, $maxChars, 'UTF-8') . '…';
    }
    return $text;
}
