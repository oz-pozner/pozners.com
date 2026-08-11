<?php
/**
 * Dev-only router for `php -S`, which does not read .htaccess. Reimplements
 * the same rewrite rules as the project's .htaccess so local preview
 * behaves like the real Apache/IONOS host. Not used in production.
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$docRoot = $_SERVER['DOCUMENT_ROOT'];

$routes = [
    '#^/index\.html$#' => '/index.php',
    '#^/robots\.txt$#' => '/robots.php',
    '#^/sitemap\.xml$#' => '/sitemap.php',
    '#^/llms\.txt$#' => '/llms.php',
];
foreach ($routes as $pattern => $target) {
    if (preg_match($pattern, $uri)) {
        require $docRoot . $target;
        return true;
    }
}

if (preg_match('#^/members/([a-zA-Z0-9_-]+)\.html$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require $docRoot . '/members/view.php';
    return true;
}

if (preg_match('#^/pages/([a-zA-Z0-9_-]+)\.html$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require $docRoot . '/pages/view.php';
    return true;
}

return false;
