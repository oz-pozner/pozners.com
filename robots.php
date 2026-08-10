<?php
require_once __DIR__ . '/includes/seo.php';

header('Content-Type: text/plain; charset=UTF-8');
$base = seo_absolute_url('/');
?>
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /includes/
Disallow: /content/
Disallow: /uploads/content/

Sitemap: <?= $base ?>sitemap.xml
