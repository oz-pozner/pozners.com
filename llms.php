<?php
require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/seo.php';

header('Content-Type: text/plain; charset=UTF-8');
$members = content_load_members();
$pages = content_load_pages();
$base = seo_absolute_url('/');
$names = implode(', ', array_map(fn($m) => $m['full_name_en'] ?: $m['name_en'], $members));

$lines = [];
$lines[] = '# ' . seo_site_title();
$lines[] = '';
$lines[] = '> A bilingual (Hebrew/English) family website for the Pozner family of Kiryat Ono, Israel: ' . $names . '.';
$lines[] = '';
$lines[] = 'This site introduces each family member with a short bio, role/interests, and contact';
$lines[] = 'details, plus a contact form. Content is available in Hebrew and English on every page.';
$lines[] = '';
$lines[] = '## Pages';
$lines[] = '';
$lines[] = '- [Home](' . $base . '): family overview, address, and contact form.';
foreach ($members as $member) {
    $name = $member['full_name_en'] ?: $member['name_en'];
    $desc = content_excerpt($member['bio_en'] ?: $member['bio_he'], 160);
    $lines[] = '- [' . $name . '](' . $base . 'members/' . $member['slug'] . '.html)' . ($desc !== '' ? ': ' . $desc : '');
}
foreach ($pages as $page) {
    $name = $page['title_en'] ?: $page['title_he'];
    $desc = content_excerpt($page['body_en'] ?: $page['body_he'], 160);
    $lines[] = '- [' . $name . '](' . $base . 'pages/' . $page['slug'] . '.html)' . ($desc !== '' ? ': ' . $desc : '');
}

echo implode("\n", $lines) . "\n";
