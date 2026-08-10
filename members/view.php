<?php
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/seo.php';

$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['slug'])) : '';
$members = content_load_members();
$member = $slug !== '' ? content_find_member($members, $slug) : null;

if (!$member) {
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
    <p class="text-lg">בן/בת המשפחה לא נמצא/ה • Family member not found</p>
    <a href="../index.php" class="mt-6 text-sm font-medium text-teal-300">← Back home</a>
  </main>
</body>

</html>
<?php
    exit;
}

$memberTitle = ($member['full_name_he'] ?? $member['name_he'] ?? '') . ' | ' . ($member['full_name_en'] ?? $member['name_en'] ?? '');
$memberUrl = seo_absolute_url('members/' . $member['slug'] . '.html');
$memberDescription = content_excerpt($member['bio_en'] ?: $member['bio_he'] ?? '', 300);
$memberImage = !empty($member['photo'])
    ? (str_starts_with($member['photo'], 'http') ? $member['photo'] : seo_absolute_url($member['photo']))
    : '';
$memberJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $member['full_name_en'] ?: $member['name_en'],
    'alternateName' => $member['full_name_he'] ?: $member['name_he'],
    'url' => $memberUrl,
];
if (!empty($member['role_en'])) {
    $memberJsonLd['jobTitle'] = $member['role_en'];
}
if ($memberDescription !== '') {
    $memberJsonLd['description'] = $memberDescription;
}
if ($memberImage !== '') {
    $memberJsonLd['image'] = $memberImage;
}
if (!empty($member['contact_email'])) {
    $memberJsonLd['email'] = $member['contact_email'];
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php seo_render([
      'title' => $memberTitle,
      'description' => $memberDescription,
      'url' => $memberUrl,
      'image' => $memberImage,
      'type' => 'profile',
      'jsonLd' => $memberJsonLd,
  ]); ?>
  <title><?= htmlspecialchars($memberTitle) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-950 text-slate-100">
  <main class="mx-auto flex min-h-screen max-w-5xl flex-col justify-center px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-8 flex items-center justify-between">
      <a href="../index.php" class="text-sm font-medium text-teal-300">
        <span class="lang-he">→ חזרה לדף הבית</span>
        <span class="lang-en hidden">← Back home</span>
      </a>
      <button id="lang-toggle"
        class="rounded-full border border-teal-400/40 px-3 py-2 text-sm font-medium text-teal-300 transition hover:bg-teal-400/10">English</button>
    </div>
    <div
      class="grid gap-8 rounded-3xl border border-white/10 bg-slate-900/80 p-8 shadow-2xl shadow-black/20 lg:grid-cols-[0.9fr_1.1fr]">
      <?php if (!empty($member['photo'])): ?>
      <img src="<?= htmlspecialchars($member['photo']) ?>"
        alt="<?= htmlspecialchars($member['full_name_en'] ?? $member['name_en'] ?? '') ?>"
        class="h-full min-h-[320px] w-full rounded-2xl object-cover" />
      <?php else: ?>
      <div class="flex h-full min-h-[320px] w-full items-center justify-center rounded-2xl bg-white/5 text-6xl font-semibold text-teal-300">
        <?= htmlspecialchars(mb_substr($member['name_en'] ?? '?', 0, 1)) ?>
      </div>
      <?php endif; ?>
      <div>
        <div class="lang-he">
          <p class="text-sm uppercase tracking-[0.3em] text-teal-300"><?= htmlspecialchars($member['name_he'] ?? '') ?></p>
          <h1 class="mt-2 text-4xl font-bold text-white"><?= htmlspecialchars($member['full_name_he'] ?? '') ?></h1>
          <div class="mt-4 text-lg text-slate-400"><?= $member['bio_he'] ?? '' ?></div>
          <div class="mt-6 space-y-3 text-sm text-slate-300">
            <p><span class="font-semibold text-white">תפקיד:</span> <?= htmlspecialchars($member['role_he'] ?? '') ?></p>
            <?php if (!empty($member['interests_he'])): ?>
            <p><span class="font-semibold text-white">תחומי עניין:</span> <?= htmlspecialchars($member['interests_he']) ?></p>
            <?php endif; ?>
            <?php if (!empty($member['contact_email'])): ?>
            <p><span class="font-semibold text-white">יצירת קשר:</span> <?= htmlspecialchars($member['contact_email']) ?></p>
            <?php endif; ?>
            <?php if (!empty($member['known_contacts'])): ?>
            <p><span class="font-semibold text-white">אנשי קשר נוספים:</span> <?= htmlspecialchars($member['known_contacts']) ?></p>
            <?php endif; ?>
          </div>
        </div>
        <div class="lang-en hidden">
          <p class="text-sm uppercase tracking-[0.3em] text-teal-300"><?= htmlspecialchars($member['name_en'] ?? '') ?></p>
          <h1 class="mt-2 text-4xl font-bold text-white"><?= htmlspecialchars($member['full_name_en'] ?? '') ?></h1>
          <div class="mt-4 text-lg text-slate-400"><?= $member['bio_en'] ?? '' ?></div>
          <div class="mt-6 space-y-3 text-sm text-slate-300">
            <p><span class="font-semibold text-white">Role:</span> <?= htmlspecialchars($member['role_en'] ?? '') ?></p>
            <?php if (!empty($member['interests_en'])): ?>
            <p><span class="font-semibold text-white">Interests:</span> <?= htmlspecialchars($member['interests_en']) ?></p>
            <?php endif; ?>
            <?php if (!empty($member['contact_email'])): ?>
            <p><span class="font-semibold text-white">Contact:</span> <?= htmlspecialchars($member['contact_email']) ?></p>
            <?php endif; ?>
            <?php if (!empty($member['known_contacts'])): ?>
            <p><span class="font-semibold text-white">Known contacts:</span> <?= htmlspecialchars($member['known_contacts']) ?></p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../script.js"></script>
</body>

</html>
