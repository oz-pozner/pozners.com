<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/content.php';

admin_require_login();

$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['slug'])) : '';
$members = content_load_members();
$member = $slug !== '' ? content_find_member($members, $slug) : null;
$isNew = !$member;

if (!$isNew) {
    $m = $member;
} else {
    $m = [
        'slug' => '', 'photo' => '', 'name_he' => '', 'name_en' => '',
        'full_name_he' => '', 'full_name_en' => '', 'role_he' => '', 'role_en' => '',
        'bio_he' => '', 'bio_en' => '', 'interests_he' => '', 'interests_en' => '',
        'contact_email' => '', 'known_contacts' => '',
    ];
}

$csrfToken = admin_csrf_token();
$field = fn($key) => htmlspecialchars($m[$key] ?? '');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $isNew ? 'Add member' : 'Edit ' . $field('name_en') ?> | Pozner Family Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet" />
  <style>
    .ql-editor { min-height: 140px; font-size: 0.95rem; }
    .rtl-editor .ql-editor { direction: rtl; text-align: right; font-family: inherit; }
    .ltr-editor .ql-editor { direction: ltr; text-align: left; }
  </style>
</head>

<body class="min-h-screen bg-slate-950 text-slate-100">
  <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur">
    <nav class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
      <span class="text-lg font-semibold tracking-wide text-white">Pozner Family Admin</span>
      <div class="flex items-center gap-4">
        <a href="index.php" class="text-sm text-slate-300 transition hover:text-teal-300">← Members</a>
        <a href="logout.php" class="text-sm text-slate-300 transition hover:text-teal-300">Log out</a>
      </div>
    </nav>
  </header>

  <main class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
    <h1 class="text-2xl font-bold text-white"><?= $isNew ? 'Add family member' : 'Edit ' . $field('name_en') ?></h1>

    <form method="post" action="save.php" enctype="multipart/form-data" id="member-form" class="mt-6 space-y-8">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
      <input type="hidden" name="original_slug" value="<?= htmlspecialchars($m['slug']) ?>" />
      <input type="hidden" name="bio_he" id="bio_he_input" value="<?= $field('bio_he') ?>" />
      <input type="hidden" name="bio_en" id="bio_en_input" value="<?= $field('bio_en') ?>" />

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Photo</h2>
        <div class="mt-4 flex items-center gap-6">
          <img id="photo-preview" src="<?= $field('photo') ?>" alt=""
            class="h-24 w-24 rounded-xl object-cover <?= $m['photo'] ? '' : 'hidden' ?>" />
          <div>
            <input type="file" name="photo" accept="image/png,image/jpeg,image/webp,image/gif"
              onchange="previewPhoto(this)" class="block text-sm text-slate-300" />
            <p class="mt-1 text-xs text-slate-500">JPG, PNG, WEBP or GIF, up to 5MB. Replaces the current picture
              everywhere it's used.</p>
          </div>
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Page identity</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">Slug (used in the URL)</label>
            <input type="text" name="slug" value="<?= $field('slug') ?>" <?= $isNew ? 'required placeholder="e.g. noa"' : 'readonly' ?>
              class="mt-1 w-full rounded-xl border border-white/10 <?= $isNew ? 'bg-white/5' : 'bg-white/[0.02] text-slate-500' ?> px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div class="flex items-end">
            <label class="flex items-center gap-2 text-sm text-slate-300">
              <input type="checkbox" name="auto_translate" value="1" checked
                class="h-4 w-4 rounded border-white/20 bg-white/5 text-teal-400" />
              Auto-translate Hebrew → English on save
            </label>
          </div>
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Hebrew (עברית)</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">First name</label>
            <input type="text" name="name_he" dir="rtl" value="<?= $field('name_he') ?>" required
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Full name</label>
            <input type="text" name="full_name_he" dir="rtl" value="<?= $field('full_name_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
        </div>
        <div class="mt-4">
          <label class="block text-sm text-slate-400">Role / title</label>
          <input type="text" name="role_he" dir="rtl" value="<?= $field('role_he') ?>"
            class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
        </div>
        <div class="mt-4">
          <label class="block text-sm text-slate-400">Bio</label>
          <div id="bio-he-editor" class="rtl-editor mt-1 rounded-xl bg-white text-slate-900"></div>
        </div>
        <div class="mt-4">
          <label class="block text-sm text-slate-400">Interests</label>
          <input type="text" name="interests_he" dir="rtl" value="<?= $field('interests_he') ?>"
            class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">English</h2>
        <p class="mt-1 text-xs text-slate-500">Filled in automatically from Hebrew when "auto-translate" is checked —
          you can still edit it afterwards.</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">First name</label>
            <input type="text" name="name_en" value="<?= $field('name_en') ?>" required
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Full name</label>
            <input type="text" name="full_name_en" value="<?= $field('full_name_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
        </div>
        <div class="mt-4">
          <label class="block text-sm text-slate-400">Role / title</label>
          <input type="text" name="role_en" value="<?= $field('role_en') ?>"
            class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
        </div>
        <div class="mt-4">
          <label class="block text-sm text-slate-400">Bio</label>
          <div id="bio-en-editor" class="ltr-editor mt-1 rounded-xl bg-white text-slate-900"></div>
        </div>
        <div class="mt-4">
          <label class="block text-sm text-slate-400">Interests</label>
          <input type="text" name="interests_en" value="<?= $field('interests_en') ?>"
            class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Contact</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">Email</label>
            <input type="email" name="contact_email" value="<?= $field('contact_email') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Known contacts (optional)</label>
            <input type="text" name="known_contacts" value="<?= $field('known_contacts') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
        </div>
      </section>

      <div class="flex items-center gap-4">
        <button type="submit"
          class="rounded-full bg-teal-400 px-6 py-3 font-medium text-slate-950 transition hover:bg-teal-300">Save</button>
        <a href="index.php" class="text-sm text-slate-400 transition hover:text-teal-300">Cancel</a>
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

    const bioHe = new Quill('#bio-he-editor', {
      theme: 'snow',
      modules: quillModules(),
    });
    bioHe.format('direction', 'rtl');
    bioHe.root.innerHTML = document.getElementById('bio_he_input').value;

    const bioEn = new Quill('#bio-en-editor', {
      theme: 'snow',
      modules: quillModules(),
    });
    bioEn.root.innerHTML = document.getElementById('bio_en_input').value;

    document.getElementById('member-form').addEventListener('submit', () => {
      document.getElementById('bio_he_input').value = bioHe.root.innerHTML;
      document.getElementById('bio_en_input').value = bioEn.root.innerHTML;
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
