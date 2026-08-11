<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/content.php';

admin_require_login();

$pages = content_load_pages();

admin_session_start();
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pages | Pozner Family Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-950 text-slate-100">
  <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur">
    <nav class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
      <span class="text-lg font-semibold tracking-wide text-white">Pozner Family Admin</span>
      <div class="flex items-center gap-4">
        <a href="index.php" class="text-sm text-slate-300 transition hover:text-teal-300">← Admin home</a>
        <a href="../index.php" class="text-sm text-slate-300 transition hover:text-teal-300">View site</a>
        <a href="logout.php" class="text-sm text-slate-300 transition hover:text-teal-300">Log out</a>
      </div>
    </nav>
  </header>

  <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
    <?php if ($flash): ?>
    <p class="mb-6 rounded-xl border border-teal-400/30 bg-teal-400/10 px-4 py-3 text-sm text-teal-200"><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>

    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Pages</h1>
        <p class="mt-1 text-sm text-slate-400">Custom sub-pages beyond the family profiles, e.g. "Our Story" or a
          gallery.</p>
      </div>
      <a href="page-edit.php"
        class="rounded-full bg-teal-400 px-5 py-2.5 text-sm font-medium text-slate-950 transition hover:bg-teal-300">+
        Add page</a>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
      <?php foreach ($pages as $page): ?>
      <a href="page-edit.php?slug=<?= urlencode($page['slug']) ?>"
        class="flex items-center gap-4 rounded-2xl border border-white/10 bg-slate-900/70 p-4 transition hover:border-teal-400/40">
        <?php if (!empty($page['image'])): ?>
        <img src="<?= htmlspecialchars($page['image']) ?>" alt="" class="h-16 w-16 flex-none rounded-xl object-cover" />
        <?php else: ?>
        <div class="flex h-16 w-16 flex-none items-center justify-center rounded-xl bg-white/5 text-teal-300">
          <i class="fa-solid fa-file-lines"></i>
        </div>
        <?php endif; ?>
        <div class="min-w-0">
          <p class="truncate font-semibold text-white"><?= htmlspecialchars($page['title_en'] ?? '') ?>
            <span class="text-slate-400">· <?= htmlspecialchars($page['title_he'] ?? '') ?></span>
          </p>
          <p class="truncate text-sm text-slate-400">/pages/<?= htmlspecialchars($page['slug']) ?>.html
            <?= empty($page['show_in_nav']) ? ' · hidden from nav' : '' ?></p>
        </div>
      </a>
      <?php endforeach; ?>
      <?php if (!$pages): ?>
      <p class="text-sm text-slate-400">No custom pages yet.</p>
      <?php endif; ?>
    </div>
  </main>
</body>

</html>
