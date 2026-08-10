<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/content.php';

admin_require_login();

$members = content_load_members();

admin_session_start();
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin | Pozner Family</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-950 text-slate-100">
  <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur">
    <nav class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
      <span class="text-lg font-semibold tracking-wide text-white">Pozner Family Admin</span>
      <div class="flex items-center gap-4">
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
      <h1 class="text-2xl font-bold text-white">Family members</h1>
      <a href="edit.php"
        class="rounded-full bg-teal-400 px-5 py-2.5 text-sm font-medium text-slate-950 transition hover:bg-teal-300">+
        Add member</a>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
      <?php foreach ($members as $member): ?>
      <a href="edit.php?slug=<?= urlencode($member['slug']) ?>"
        class="flex items-center gap-4 rounded-2xl border border-white/10 bg-slate-900/70 p-4 transition hover:border-teal-400/40">
        <img src="<?= htmlspecialchars($member['photo'] ?? '') ?>" alt=""
          class="h-16 w-16 flex-none rounded-xl object-cover" />
        <div class="min-w-0">
          <p class="truncate font-semibold text-white"><?= htmlspecialchars($member['full_name_en'] ?? $member['name_en'] ?? '') ?>
            <span class="text-slate-400">· <?= htmlspecialchars($member['full_name_he'] ?? $member['name_he'] ?? '') ?></span>
          </p>
          <p class="truncate text-sm text-slate-400"><?= htmlspecialchars($member['role_en'] ?? '') ?></p>
        </div>
      </a>
      <?php endforeach; ?>
      <?php if (!$members): ?>
      <p class="text-sm text-slate-400">No family members yet. Add the first one.</p>
      <?php endif; ?>
    </div>
  </main>
</body>

</html>
