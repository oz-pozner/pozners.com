<?php
require_once __DIR__ . '/env.php';

function seo_site_url(): string
{
    return rtrim(getenv('SITE_URL') ?: '', '/');
}

function seo_site_title(): string
{
    return getenv('SITE_TITLE') ?: 'Pozner Family';
}

function seo_absolute_url(string $path): string
{
    $base = seo_site_url();
    return $base === '' ? $path : $base . '/' . ltrim($path, '/');
}

/**
 * Echo <meta>/OG/Twitter/JSON-LD tags for a page's <head>. $opts:
 *   title, description, url, image (absolute URL), type ('website'|'profile'), jsonLd (array)
 */
function seo_render(array $opts): void
{
    $title = $opts['title'] ?? seo_site_title();
    $description = $opts['description'] ?? '';
    $url = $opts['url'] ?? seo_absolute_url('/');
    $image = $opts['image'] ?? '';
    $type = $opts['type'] ?? 'website';
    $siteName = seo_site_title();

    echo '  <meta name="description" content="' . htmlspecialchars($description) . '" />' . "\n";
    echo '  <meta name="robots" content="index, follow" />' . "\n";
    echo '  <link rel="canonical" href="' . htmlspecialchars($url) . '" />' . "\n";

    echo '  <meta property="og:type" content="' . htmlspecialchars($type) . '" />' . "\n";
    echo '  <meta property="og:site_name" content="' . htmlspecialchars($siteName) . '" />' . "\n";
    echo '  <meta property="og:title" content="' . htmlspecialchars($title) . '" />' . "\n";
    echo '  <meta property="og:description" content="' . htmlspecialchars($description) . '" />' . "\n";
    echo '  <meta property="og:url" content="' . htmlspecialchars($url) . '" />' . "\n";
    echo '  <meta property="og:locale" content="he_IL" />' . "\n";
    echo '  <meta property="og:locale:alternate" content="en_US" />' . "\n";
    if ($image !== '') {
        echo '  <meta property="og:image" content="' . htmlspecialchars($image) . '" />' . "\n";
    }

    echo '  <meta name="twitter:card" content="' . ($image !== '' ? 'summary_large_image' : 'summary') . '" />' . "\n";
    echo '  <meta name="twitter:title" content="' . htmlspecialchars($title) . '" />' . "\n";
    echo '  <meta name="twitter:description" content="' . htmlspecialchars($description) . '" />' . "\n";
    if ($image !== '') {
        echo '  <meta name="twitter:image" content="' . htmlspecialchars($image) . '" />' . "\n";
    }

    if (!empty($opts['jsonLd'])) {
        echo '  <script type="application/ld+json">'
            . json_encode($opts['jsonLd'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            . '</script>' . "\n";
    }
}
