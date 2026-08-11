<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/content.php';

admin_require_login();

$memberCount = count(content_load_members());
$pageCount = count(content_load_pages());
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin | Pozner Family</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css" />
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
    <h1 class="text-2xl font-bold text-white">What would you like to update?</h1>
    <p class="mt-1 text-sm text-slate-400">Everything here saves straight to the live site.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
      <a href="site.php"
        class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 transition hover:border-teal-400/40">
        <i class="fa-solid fa-house text-2xl text-teal-300"></i>
        <p class="mt-4 text-lg font-semibold text-white">Site Content</p>
        <p class="mt-1 text-sm text-slate-400">Home page text: hero, about, contact, video channel, address.</p>
      </a>
      <a href="members.php"
        class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 transition hover:border-teal-400/40">
        <i class="fa-solid fa-people-group text-2xl text-teal-300"></i>
        <p class="mt-4 text-lg font-semibold text-white">Family Members</p>
        <p class="mt-1 text-sm text-slate-400"><?= $memberCount ?> member<?= $memberCount === 1 ? '' : 's' ?> — profile
          pages, photos, bios.</p>
      </a>
      <a href="pages.php"
        class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 transition hover:border-teal-400/40">
        <i class="fa-solid fa-file-lines text-2xl text-teal-300"></i>
        <p class="mt-4 text-lg font-semibold text-white">Pages</p>
        <p class="mt-1 text-sm text-slate-400"><?= $pageCount ?> custom sub-page<?= $pageCount === 1 ? '' : 's' ?> — add
          new pages like "Our Story" or a gallery.</p>
      </a>
    </div>
  </main>
</body>

</html>
