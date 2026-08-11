<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/content.php';

admin_require_login();

$error = '';
$site = content_load_site();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Session expired, please try again.';
    } else {
        $updated = content_site_defaults();
        foreach (array_keys($updated) as $key) {
            $updated[$key] = trim($_POST[$key] ?? '');
        }
        if (content_save_site($updated)) {
            $site = $updated;
            admin_session_start();
            $_SESSION['admin_flash'] = 'Site content updated.';
            header('Location: index.php');
            exit;
        }
        $error = 'Could not save changes - check that the content/ directory is writable.';
    }
}

$csrfToken = admin_csrf_token();
$field = fn($key) => htmlspecialchars($site[$key] ?? '');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Site content | Pozner Family Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-950 text-slate-100">
  <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur">
    <nav class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
      <span class="text-lg font-semibold tracking-wide text-white">Pozner Family Admin</span>
      <div class="flex items-center gap-4">
        <a href="index.php" class="text-sm text-slate-300 transition hover:text-teal-300">← Admin home</a>
        <a href="logout.php" class="text-sm text-slate-300 transition hover:text-teal-300">Log out</a>
      </div>
    </nav>
  </header>

  <main class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
    <h1 class="text-2xl font-bold text-white">Site content</h1>
    <p class="mt-1 text-sm text-slate-400">The home page's Hebrew/English copy, address, and video channel.</p>

    <?php if ($error !== ''): ?>
    <p class="mt-4 rounded-xl border border-red-400/30 bg-red-400/10 px-4 py-3 text-sm text-red-300"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="mt-6 space-y-8">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Hero</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">Kicker (Hebrew)</label>
            <input type="text" name="hero_kicker_he" dir="rtl" value="<?= $field('hero_kicker_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Kicker (English)</label>
            <input type="text" name="hero_kicker_en" value="<?= $field('hero_kicker_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Title (Hebrew)</label>
            <input type="text" name="hero_title_he" dir="rtl" value="<?= $field('hero_title_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Title (English)</label>
            <input type="text" name="hero_title_en" value="<?= $field('hero_title_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Subtitle (Hebrew)</label>
            <textarea name="hero_subtitle_he" dir="rtl" rows="3"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none"><?= $field('hero_subtitle_he') ?></textarea>
          </div>
          <div>
            <label class="block text-sm text-slate-400">Subtitle (English)</label>
            <textarea name="hero_subtitle_en" rows="3"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none"><?= $field('hero_subtitle_en') ?></textarea>
          </div>
        </div>
        <div class="mt-4">
          <label class="block text-sm text-slate-400">Hero image URL</label>
          <input type="text" name="hero_image" value="<?= $field('hero_image') ?>"
            class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          <p class="mt-1 text-xs text-slate-500">Leave blank to hide the hero image entirely.</p>
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Address</h2>
        <p class="mt-1 text-xs text-slate-500">Shown in the hero box, the FAQ list, and the contact section.</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">Address (Hebrew)</label>
            <input type="text" name="address_he" dir="rtl" value="<?= $field('address_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Address (English)</label>
            <input type="text" name="address_en" value="<?= $field('address_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Family section</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">Kicker (Hebrew)</label>
            <input type="text" name="family_kicker_he" dir="rtl" value="<?= $field('family_kicker_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Kicker (English)</label>
            <input type="text" name="family_kicker_en" value="<?= $field('family_kicker_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Title (Hebrew)</label>
            <input type="text" name="family_title_he" dir="rtl" value="<?= $field('family_title_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Title (English)</label>
            <input type="text" name="family_title_en" value="<?= $field('family_title_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Tagline (Hebrew)</label>
            <input type="text" name="family_tagline_he" dir="rtl" value="<?= $field('family_tagline_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Tagline (English)</label>
            <input type="text" name="family_tagline_en" value="<?= $field('family_tagline_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Video channel</h2>
        <p class="mt-1 text-xs text-slate-500">The looping YouTube section. Find the playlist ID by taking the
          channel ID (starts with "UC") and swapping the "UC" for "UU".</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">Channel URL</label>
            <input type="text" name="video_channel_url" value="<?= $field('video_channel_url') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Channel name</label>
            <input type="text" name="video_channel_name" value="<?= $field('video_channel_name') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-sm text-slate-400">Uploads playlist ID</label>
            <input type="text" name="video_playlist_id" value="<?= $field('video_playlist_id') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
            <p class="mt-1 text-xs text-slate-500">Leave blank to hide the video section entirely.</p>
          </div>
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">About</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">Kicker (Hebrew)</label>
            <input type="text" name="about_kicker_he" dir="rtl" value="<?= $field('about_kicker_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Kicker (English)</label>
            <input type="text" name="about_kicker_en" value="<?= $field('about_kicker_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Title (Hebrew)</label>
            <input type="text" name="about_title_he" dir="rtl" value="<?= $field('about_title_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Title (English)</label>
            <input type="text" name="about_title_en" value="<?= $field('about_title_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Body (Hebrew)</label>
            <textarea name="about_body_he" dir="rtl" rows="3"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none"><?= $field('about_body_he') ?></textarea>
          </div>
          <div>
            <label class="block text-sm text-slate-400">Body (English)</label>
            <textarea name="about_body_en" rows="3"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none"><?= $field('about_body_en') ?></textarea>
          </div>
        </div>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Contact</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">Kicker (Hebrew)</label>
            <input type="text" name="contact_kicker_he" dir="rtl" value="<?= $field('contact_kicker_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Kicker (English)</label>
            <input type="text" name="contact_kicker_en" value="<?= $field('contact_kicker_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Title (Hebrew)</label>
            <input type="text" name="contact_title_he" dir="rtl" value="<?= $field('contact_title_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Title (English)</label>
            <input type="text" name="contact_title_en" value="<?= $field('contact_title_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Body (Hebrew)</label>
            <textarea name="contact_body_he" dir="rtl" rows="2"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none"><?= $field('contact_body_he') ?></textarea>
          </div>
          <div>
            <label class="block text-sm text-slate-400">Body (English)</label>
            <textarea name="contact_body_en" rows="2"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none"><?= $field('contact_body_en') ?></textarea>
          </div>
          <div>
            <label class="block text-sm text-slate-400">Meeting note (Hebrew)</label>
            <input type="text" name="meeting_note_he" dir="rtl" value="<?= $field('meeting_note_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Meeting note (English)</label>
            <input type="text" name="meeting_note_en" value="<?= $field('meeting_note_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
        </div>
        <p class="mt-3 text-xs text-slate-500">The contact email itself is a deployment setting, not site content —
          it's <code>CONTACT_EMAIL</code> in <code>.env</code>.</p>
      </section>

      <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
        <h2 class="text-lg font-semibold text-white">Footer</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm text-slate-400">Footer text (Hebrew)</label>
            <input type="text" name="footer_text_he" dir="rtl" value="<?= $field('footer_text_he') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-right text-white focus:border-teal-400 focus:outline-none" />
          </div>
          <div>
            <label class="block text-sm text-slate-400">Footer text (English)</label>
            <input type="text" name="footer_text_en" value="<?= $field('footer_text_en') ?>"
              class="mt-1 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-white focus:border-teal-400 focus:outline-none" />
          </div>
        </div>
        <p class="mt-1 text-xs text-slate-500">The copyright year is added automatically.</p>
      </section>

      <div class="flex items-center gap-4">
        <button type="submit"
          class="rounded-full bg-teal-400 px-6 py-3 font-medium text-slate-950 transition hover:bg-teal-300">Save</button>
        <a href="index.php" class="text-sm text-slate-400 transition hover:text-teal-300">Cancel</a>
      </div>
    </form>
  </main>
</body>

</html>
