<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/content.php';

admin_require_login();

$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['slug'])) : '';
$pages = content_load_pages();
$page = $slug !== '' ? content_find_page($pages, $slug) : null;
$isNew = !$page;

if (!$isNew) {
    $p = $page;
} else {
    $p = [
        'slug' => '', 'image' => '', 'title_he' => '', 'title_en' => '',
        'nav_label' => '', 'show_in_nav' => true, 'body_he' => '', 'body_en' => '',
    ];
}

$csrfToken = admin_csrf_token();
$field = fn($key) => htmlspecialchars($p[$key] ?? '');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $isNew ? 'Add page' : 'Edit ' . $field('title_en') ?> | Pozner Family Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet" />
  <style>
    .ql-editor { min-height: 220px; font-size: 0.95rem; }
    .rtl-editor .ql-editor { direction: rtl; text-align: right; font-family: inherit; }
    .ltr-editor .ql-editor { direction: ltr; text-align: left; }
  </style>
</head>

<body class="min-h-screen bg-slate-950 text-slate-100">
  <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur">
    <nav class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
      <span class="text-lg font-semibold tracking-wide text-white">Pozner Family Admin</span>
      <div class="flex items-center gap-4">
        <a href="pages.php" class="text-sm text-slate-300 transition hover:text-teal-300">← Pages</a>
        <a href="logout.php" class="text-sm text-slate-300 transition hover:text-teal-300">Log out</a>
      </div>
    </nav>
  </header>

  <main class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
    <h1 class="text-2xl font-bold text-white"><?= $isNew ? 'Add page' : 'Edit ' . $field('title_en') ?></h1>

    <form method="post" action="page-save.php" enctype="multipart/form-data" id="page-form" class="mt-6 space-y-8">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
      <input type="hidden" name="original_slug" value="<?= htmlspecialchars($p['slug']) ?>" />
      <input type="hidden" name="body_he" id="body_he_input" value="<?= $field('body_he') ?>" />
      <input type="hidden" name="body_en" id="body_en_input" value="<?= $field('body_en') ?>" />

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Hero image (optional)</h2>
        <div class="mt-4 flex items-center gap-6">
          <img id="photo-preview" src="<?= $field('image') ?>" alt=""
            class="h-24 w-24 rounded-xl object-cover <?= $p['image'] ? '' : 'hidden' ?>" />
          <div>
            <input type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif"
              onchange="previewPhoto(this)" class="block text-sm text-slate-300" />
            <p class="mt-1 text-xs text-slate-500">JPG, PNG, WEBP or GIF, up to 5MB.</p>
          </div>
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Page identity</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">Slug (used in the URL)</label>
            <input type="text" name="slug" value="<?= $field('slug') ?>" <?= $isNew ? 'required placeholder="e.g. our-story"' : 'readonly' ?>
              class="mt-1 w-full rounded-xl border border-white/10 <?= $isNew ? 'bg-white/5' : 'bg-white/[0.02] text-slate-500' ?> px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Nav label (optional)</label>
            <input type="text" name="nav_label" value="<?= $field('nav_label') ?>" placeholder="Defaults to English title"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
        </div>
        <div class="mt-4 flex items-center gap-6">
          <label class="flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="show_in_nav" value="1" <?= !empty($p['show_in_nav']) ? 'checked' : '' ?>
              class="h-4 w-4 rounded border-white/20 bg-white/5 text-teal-400" />
            Show in site navigation
          </label>
          <label class="flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="auto_translate" value="1" checked
              class="h-4 w-4 rounded border-white/20 bg-white/5 text-teal-400" />
            Auto-translate Hebrew → English on save
          </label>
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Hebrew (עברית)</h2>
        <div class="mt-4">
          <label class="block text-sm text-slate-400">Title</label>
          <input type="text" name="title_he" dir="rtl" value="<?= $field('title_he') ?>" required
            class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
        </div>
        <div class="mt-4">
          <label class="block text-sm text-slate-400">Body</label>
          <div id="body-he-editor" class="rtl-editor mt-1 rounded-xl bg-white text-slate-900"></div>
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">English</h2>
        <p class="mt-1 text-xs text-slate-500">Filled in automatically from Hebrew when "auto-translate" is checked —
          you can still edit it afterwards.</p>
        <div class="mt-4">
          <label class="block text-sm text-slate-400">Title</label>
          <input type="text" name="title_en" value="<?= $field('title_en') ?>" required
            class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
        </div>
        <div class="mt-4">
          <label class="block text-sm text-slate-400">Body</label>
          <div id="body-en-editor" class="ltr-editor mt-1 rounded-xl bg-white text-slate-900"></div>
        </div>
      </section>

      <div class="flex items-center gap-4">
        <button type="submit"
          class="rounded-full bg-teal-400 px-6 py-3 font-medium text-slate-950 transition hover:bg-teal-300">Save</button>
        <a href="pages.php" class="text-sm text-slate-400 transition hover:text-teal-300">Cancel</a>
      </div>
    </form>
  </main>

  <script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
  <script>
    function uploadImageHandler() {
      const quill = this.quill;
      const input = document.createElement('input');
      input.setAttribute('type', 'file');
      input.setAttribute('accept', 'image/png,image/jpeg,image/webp,image/gif');
      input.click();
      input.onchange = () => {
        const file = input.files[0];
        if (!file) return;
        const range = quill.getSelection(true);
        const formData = new FormData();
        formData.append('image', file);
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
        quill.enable(false);
        fetch('upload-image.php', { method: 'POST', body: formData })
          .then((res) => res.json())
          .then((data) => {
            quill.enable(true);
            if (data.url) {
              quill.insertEmbed(range.index, 'image', data.url, 'user');
              quill.setSelection(range.index + 1);
            } else {
              alert(data.error || 'Image upload failed.');
            }
          })
          .catch(() => {
            quill.enable(true);
            alert('Image upload failed.');
          });
      };
    }

    const toolbarOptions = [
      ['bold', 'italic', 'underline'],
      [{ list: 'ordered' }, { list: 'bullet' }],
      ['link', 'image'],
      [{ direction: 'rtl' }],
      ['clean'],
    ];
    const quillModules = () => ({
      toolbar: { container: toolbarOptions, handlers: { image: uploadImageHandler } },
    });

    const bodyHe = new Quill('#body-he-editor', {
      theme: 'snow',
      modules: quillModules(),
    });
    bodyHe.format('direction', 'rtl');
    bodyHe.root.innerHTML = document.getElementById('body_he_input').value;

    const bodyEn = new Quill('#body-en-editor', {
      theme: 'snow',
      modules: quillModules(),
    });
    bodyEn.root.innerHTML = document.getElementById('body_en_input').value;

    document.getElementById('page-form').addEventListener('submit', () => {
      document.getElementById('body_he_input').value = bodyHe.root.innerHTML;
      document.getElementById('body_en_input').value = bodyEn.root.innerHTML;
    });

    function previewPhoto(input) {
      const preview = document.getElementById('photo-preview');
      if (input.files && input.files[0]) {
        preview.src = URL.createObjectURL(input.files[0]);
        preview.classList.remove('hidden');
      }
    }
  </script>
</body>

</html>
