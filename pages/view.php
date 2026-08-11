<?php
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['slug'])) : '';
$pages = content_load_pages();
$page = $slug !== '' ? content_find_page($pages, $slug) : null;

if (!$page) {
    http_response_code(404);
    ?>
<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="robots" content="noindex" />
  <title>Not found | לא נמצא</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-950 text-slate-100">
  <main class="mx-auto flex min-h-screen max-w-2xl flex-col items-center justify-center px-4 text-center">
    <p class="text-lg">הדף לא נמצא • Page not found</p>
    <a href="../index.php" class="mt-6 text-sm font-medium text-teal-300">← Back home</a>
  </main>
</body>

</html>
<?php
    exit;
}

$pageTitle = ($page['title_he'] ?? '') . ' | ' . ($page['title_en'] ?? '');
$pageUrl = seo_absolute_url('pages/' . $page['slug'] . '.html');
$pageDescription = content_excerpt($page['body_en'] ?: $page['body_he'] ?? '', 300);
$pageImage = !empty($page['image'])
    ? (str_starts_with($page['image'], 'http') ? $page['image'] : seo_absolute_url($page['image']))
    : '';
$pageJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => $page['title_en'] ?: $page['title_he'],
    'url' => $pageUrl,
];
if ($pageDescription !== '') {
    $pageJsonLd['description'] = $pageDescription;
}
if ($pageImage !== '') {
    $pageJsonLd['image'] = $pageImage;
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php seo_render([
      'title' => $pageTitle,
      'description' => $pageDescription,
      'url' => $pageUrl,
      'image' => $pageImage,
      'type' => 'article',
      'jsonLd' => $pageJsonLd,
  ]); ?>
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-950 text-slate-100">
  <main class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-8 flex items-center justify-between">
      <a href="../index.php" class="text-sm font-medium text-teal-300">
        <span class="lang-he">→ חזרה לדף הבית</span>
        <span class="lang-en hidden">← Back home</span>
      </a>
      <button id="lang-toggle"
        class="rounded-full border border-teal-400/40 px-3 py-2 text-sm font-medium text-teal-300 transition hover:bg-teal-400/10">English</button>
    </div>

    <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-900/80 shadow-2xl shadow-black/20">
      <?php if ($pageImage !== ''): ?>
      <img src="<?= htmlspecialchars($pageImage) ?>" alt="" class="h-64 w-full object-cover sm:h-80" />
      <?php endif; ?>
      <div class="p-8">
        <div class="lang-he space-y-4">
          <h1 class="text-4xl font-bold text-white"><?= htmlspecialchars($page['title_he'] ?? '') ?></h1>
          <div class="mt-6 text-lg leading-relaxed text-slate-300 [&_a]:text-teal-300 [&_a]:underline [&_h2]:mt-6 [&_h2]:text-2xl [&_h2]:font-semibold [&_h2]:text-white [&_li]:mt-1 [&_p]:mt-4 [&_ul]:list-disc [&_ul]:ps-5">
            <?= $page['body_he'] ?? '' ?>
          </div>
        </div>
        <div class="lang-en hidden space-y-4">
          <h1 class="text-4xl font-bold text-white"><?= htmlspecialchars($page['title_en'] ?? '') ?></h1>
          <div class="mt-6 text-lg leading-relaxed text-slate-300 [&_a]:text-teal-300 [&_a]:underline [&_h2]:mt-6 [&_h2]:text-2xl [&_h2]:font-semibold [&_h2]:text-white [&_li]:mt-1 [&_p]:mt-4 [&_ul]:list-disc [&_ul]:ps-5">
            <?= $page['body_en'] ?? '' ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../script.js"></script>
</body>

</html>
