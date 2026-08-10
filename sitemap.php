<?php
require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/seo.php';

header('Content-Type: application/xml; charset=UTF-8');
$members = content_load_members();
$base = seo_absolute_url('/');
?>
<?= '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?= htmlspecialchars($base) ?></loc>
  </url>
<?php foreach ($members as $member): ?>
  <url>
    <loc><?= htmlspecialchars($base . 'members/' . $member['slug'] . '.html') ?></loc>
<?php if (!empty($member['updated_at'])): ?>
    <lastmod><?= htmlspecialchars(date('Y-m-d', strtotime($member['updated_at']))) ?></lastmod>
<?php endif; ?>
  </url>
<?php endforeach; ?>
</urlset>
