<?php
require_once __DIR__ . '/config.php';

function content_load_members(): array
{
    if (!file_exists(MEMBERS_JSON)) {
        return [];
    }
    $fp = fopen(MEMBERS_JSON, 'r');
    if (!$fp) {
        return [];
    }
    flock($fp, LOCK_SH);
    $raw = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        return [];
    }
    usort($data, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
    return $data;
}

function content_save_members(array $members): bool
{
    if (!is_dir(CONTENT_DIR)) {
        mkdir(CONTENT_DIR, 0775, true);
    }
    $fp = fopen(MEMBERS_JSON, 'c+');
    if (!$fp) {
        return false;
    }
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    $json = json_encode(array_values($members), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    fwrite($fp, $json);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

function content_find_member(array $members, string $slug): ?array
{
    foreach ($members as $m) {
        if (($m['slug'] ?? null) === $slug) {
            return $m;
        }
    }
    return null;
}

function content_slugify(string $text): string
{
    $ascii = preg_replace('/[^a-zA-Z0-9]+/', '-', trim($text));
    $ascii = strtolower(trim((string) $ascii, '-'));
    if ($ascii === '') {
        $ascii = 'member-' . substr(bin2hex(random_bytes(3)), 0, 6);
    }
    return $ascii;
}

/**
 * Plain-text excerpt of a rich-text (HTML) bio, used for home page cards
 * and SEO/AI meta descriptions. Tags are replaced with a space (not just
 * stripped) so e.g. "</p><h2>" doesn't glue two words together.
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
