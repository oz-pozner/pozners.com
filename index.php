<?php
require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/seo.php';
$members = content_load_members();
$site = content_load_site();
$pages = content_load_pages();

$homeDescription = 'The Pozner family — Oz, Havi, Ron and Yuval — from Kiryat Ono, Israel. A bilingual (Hebrew/English) family site with profiles and contact details.';
$homeUrl = seo_absolute_url('/');
$personList = array_map(function ($m) {
    $entry = [
        '@type' => 'Person',
        'name' => $m['full_name_en'] ?: $m['name_en'],
        'alternateName' => $m['full_name_he'] ?: $m['name_he'],
        'url' => seo_absolute_url('members/' . $m['slug'] . '.html'),
    ];
    if (!empty($m['role_en'])) {
        $entry['jobTitle'] = $m['role_en'];
    }
    if (!empty($m['bio_en'])) {
        $entry['description'] = content_excerpt($m['bio_en'], 300);
    }
    if (!empty($m['photo'])) {
        $entry['image'] = str_starts_with($m['photo'], 'http') ? $m['photo'] : seo_absolute_url($m['photo']);
    }
    return $entry;
}, $members);
$homeJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => seo_site_title(),
    'url' => $homeUrl,
    'description' => $homeDescription,
    'inLanguage' => ['he', 'en'],
    'about' => ['@type' => 'ItemList', 'itemListElement' => $personList],
];
?>
<!DOCTYPE html>
<html lang="he" class="scroll-smooth" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php seo_render([
      'title' => 'Pozner Family | עוז, חווי, רון, יובל',
      'description' => $homeDescription,
      'url' => $homeUrl,
      'type' => 'website',
      'jsonLd' => $homeJsonLd,
  ]); ?>
  <title>Pozner Family | עוז, חווי, רון, יובל</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            accent: '#2dd4bf',
            ink: '#0f172a'
          }
        }
      }
    };
  </script>
</head>

<body class="bg-slate-950 text-slate-100">
  <header class="border-b border-white/10 bg-slate-950/80 backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
      <a href="index.php" class="text-lg font-semibold tracking-wide text-white">Pozner Family</a>
      <div class="hidden items-center gap-6 md:flex">
        <a href="#family" class="text-sm text-slate-300 transition hover:text-teal-300">Family</a>
        <a href="#videos" class="text-sm text-slate-300 transition hover:text-teal-300">Videos</a>
        <?php foreach ($pages as $page): if (empty($page['show_in_nav'])) continue; ?>
        <a href="pages/<?= htmlspecialchars($page['slug']) ?>.html"
          class="text-sm text-slate-300 transition hover:text-teal-300"><?= htmlspecialchars($page['nav_label'] ?: $page['title_en']) ?></a>
        <?php endforeach; ?>
        <a href="#contact" class="text-sm text-slate-300 transition hover:text-teal-300">Contact</a>
        <a href="#forms" class="text-sm text-slate-300 transition hover:text-teal-300">Forms</a>
      </div>
      <div class="flex items-center gap-2">
        <button id="lang-toggle"
          class="rounded-full border border-teal-400/40 px-3 py-2 text-sm font-medium text-teal-300 transition hover:bg-teal-400/10">English</button>
        <button id="nav-toggle" type="button" aria-label="Toggle menu" aria-expanded="false" aria-controls="mobile-menu"
          class="rounded-full border border-white/15 p-2.5 text-slate-200 transition hover:border-teal-300 hover:text-teal-300 md:hidden">
          <i class="fa-solid fa-bars text-base"></i>
        </button>
      </div>
    </nav>
    <div id="mobile-menu" class="hidden border-t border-white/10 px-4 py-4 md:hidden">
      <div class="mx-auto flex max-w-7xl flex-col gap-4">
        <a href="#family" class="text-sm text-slate-300 transition hover:text-teal-300">Family</a>
        <a href="#videos" class="text-sm text-slate-300 transition hover:text-teal-300">Videos</a>
        <?php foreach ($pages as $page): if (empty($page['show_in_nav'])) continue; ?>
        <a href="pages/<?= htmlspecialchars($page['slug']) ?>.html"
          class="text-sm text-slate-300 transition hover:text-teal-300"><?= htmlspecialchars($page['nav_label'] ?: $page['title_en']) ?></a>
        <?php endforeach; ?>
        <a href="#contact" class="text-sm text-slate-300 transition hover:text-teal-300">Contact</a>
        <a href="#forms" class="text-sm text-slate-300 transition hover:text-teal-300">Forms</a>
      </div>
    </div>
  </header>

  <main>
    <section class="mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8 lg:py-28">
      <div class="space-y-6">
        <div class="lang-he">
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300"><?= htmlspecialchars($site['hero_kicker_he']) ?></p>
          <h1 class="text-4xl font-bold leading-tight text-white sm:text-5xl"><?= htmlspecialchars($site['hero_title_he']) ?></h1>
          <p class="max-w-2xl text-lg text-slate-300"><?= htmlspecialchars($site['hero_subtitle_he']) ?></p>
        </div>
        <div class="lang-en hidden">
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300"><?= htmlspecialchars($site['hero_kicker_en']) ?></p>
          <h1 class="text-4xl font-bold leading-tight text-white sm:text-5xl"><?= htmlspecialchars($site['hero_title_en']) ?></h1>
          <p class="max-w-2xl text-lg text-slate-300"><?= htmlspecialchars($site['hero_subtitle_en']) ?></p>
        </div>
        <div class="flex flex-wrap gap-3">
          <a href="#family"
            class="rounded-full bg-teal-400 px-5 py-3 font-medium text-slate-950 transition hover:bg-teal-300">Meet the
            family</a>
          <a href="#contact"
            class="rounded-full border border-white/15 px-5 py-3 font-medium text-slate-100 transition hover:border-teal-300 hover:text-teal-300">Contact
            us</a>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
          <div class="lang-he">
            <p class="text-sm text-slate-400">כתובת</p>
            <p class="mt-2 text-xl font-semibold text-white"><?= htmlspecialchars($site['address_he']) ?></p>
          </div>
          <div class="lang-en hidden">
            <p class="text-sm text-slate-400">Address</p>
            <p class="mt-2 text-xl font-semibold text-white"><?= htmlspecialchars($site['address_en']) ?></p>
          </div>
        </div>
      </div>

      <?php if (!empty($site['hero_image'])): ?>
      <div class="overflow-hidden rounded-3xl border border-white/10 shadow-2xl shadow-teal-500/10">
        <img src="<?= htmlspecialchars($site['hero_image']) ?>"
          alt="Family portrait in a warm home setting" class="h-full w-full object-cover" />
      </div>
      <?php endif; ?>
    </section>

    <section id="family" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-16">
      <div class="mb-10 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <div class="lang-he">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300"><?= htmlspecialchars($site['family_kicker_he']) ?></p>
            <h2 class="text-3xl font-semibold text-white"><?= htmlspecialchars($site['family_title_he']) ?></h2>
          </div>
          <div class="lang-en hidden">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300"><?= htmlspecialchars($site['family_kicker_en']) ?></p>
            <h2 class="text-3xl font-semibold text-white"><?= htmlspecialchars($site['family_title_en']) ?></h2>
          </div>
        </div>
        <div class="lang-he text-sm text-slate-400"><?= htmlspecialchars($site['family_tagline_he']) ?></div>
        <div class="lang-en hidden text-sm text-slate-400"><?= htmlspecialchars($site['family_tagline_en']) ?></div>
      </div>

      <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($members as $member): ?>
        <article class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 shadow-lg shadow-black/20">
          <?php if (!empty($member['photo'])): ?>
          <img src="<?= htmlspecialchars($member['photo']) ?>"
            alt="<?= htmlspecialchars(($member['full_name_en'] ?? $member['name_en'] ?? '')) ?> portrait"
            class="mb-5 h-48 w-full rounded-2xl object-cover" />
          <?php else: ?>
          <div class="mb-5 flex h-48 w-full items-center justify-center rounded-2xl bg-white/5 text-4xl font-semibold text-teal-300">
            <?= htmlspecialchars(mb_substr($member['name_en'] ?? '?', 0, 1)) ?>
          </div>
          <?php endif; ?>
          <div class="lang-he">
            <h3 class="text-xl font-semibold text-white"><?= htmlspecialchars($member['name_he'] ?? '') ?></h3>
            <p class="mt-2 text-sm text-teal-300"><?= htmlspecialchars($member['role_he'] ?? '') ?></p>
            <p class="mt-3 text-sm text-slate-400"><?= htmlspecialchars(content_excerpt($member['bio_he'] ?? '')) ?></p>
          </div>
          <div class="lang-en hidden">
            <h3 class="text-xl font-semibold text-white"><?= htmlspecialchars($member['name_en'] ?? '') ?></h3>
            <p class="mt-2 text-sm text-teal-300"><?= htmlspecialchars($member['role_en'] ?? '') ?></p>
            <p class="mt-3 text-sm text-slate-400"><?= htmlspecialchars(content_excerpt($member['bio_en'] ?? '')) ?></p>
          </div>
          <a href="members/<?= htmlspecialchars($member['slug'] ?? '') ?>.html"
            class="mt-5 inline-flex text-sm font-medium text-teal-300 hover:text-teal-200">Read more →</a>
        </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section id="videos" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-16">
      <div class="mb-8">
        <div class="lang-he">
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300"><i class="fa-brands fa-youtube"></i> סרטונים</p>
          <h2 class="mt-2 text-3xl font-semibold text-white">ערוץ היוטיוב של המשפחה</h2>
          <p class="mt-2 max-w-2xl text-sm text-slate-400">חידות, שחמט ותוכן משעשע מהערוץ
            <a href="<?= htmlspecialchars($site['video_channel_url']) ?>" target="_blank" rel="noopener"
              class="text-teal-300 hover:text-teal-200"><?= htmlspecialchars($site['video_channel_name']) ?></a>.
          </p>
        </div>
        <div class="lang-en hidden">
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300"><i class="fa-brands fa-youtube"></i> Videos</p>
          <h2 class="mt-2 text-3xl font-semibold text-white">Family YouTube Channel</h2>
          <p class="mt-2 max-w-2xl text-sm text-slate-400">Puzzles, chess, and fun from the
            <a href="<?= htmlspecialchars($site['video_channel_url']) ?>" target="_blank" rel="noopener"
              class="text-teal-300 hover:text-teal-200"><?= htmlspecialchars($site['video_channel_name']) ?></a> channel.
          </p>
        </div>
      </div>
      <?php if (!empty($site['video_playlist_id'])): ?>
      <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-900/70 p-2 shadow-lg shadow-black/20 sm:p-3">
        <iframe class="aspect-video w-full rounded-2xl border-0"
          src="https://www.youtube-nocookie.com/embed/videoseries?list=<?= htmlspecialchars($site['video_playlist_id']) ?>&loop=1&rel=0"
          title="<?= htmlspecialchars($site['video_channel_name']) ?> YouTube channel" loading="lazy"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          allowfullscreen></iframe>
      </div>
      <?php endif; ?>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-16">
      <div
        class="grid gap-8 rounded-3xl border border-white/10 bg-gradient-to-br from-slate-900 to-slate-800 p-8 lg:grid-cols-[1fr_0.8fr]">
        <div>
          <div class="lang-he">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300"><?= htmlspecialchars($site['about_kicker_he']) ?></p>
            <h2 class="mt-2 text-3xl font-semibold text-white"><?= htmlspecialchars($site['about_title_he']) ?></h2>
            <p class="mt-4 max-w-2xl text-slate-400"><?= htmlspecialchars($site['about_body_he']) ?></p>
          </div>
          <div class="lang-en hidden">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300"><?= htmlspecialchars($site['about_kicker_en']) ?></p>
            <h2 class="mt-2 text-3xl font-semibold text-white"><?= htmlspecialchars($site['about_title_en']) ?></h2>
            <p class="mt-4 max-w-2xl text-slate-400"><?= htmlspecialchars($site['about_body_en']) ?></p>
          </div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
          <div class="lang-he">
            <h3 class="text-xl font-semibold text-white">שאלות נפוצות</h3>
            <ul class="mt-4 space-y-3 text-sm text-slate-400">
              <li>• כתובת: <?= htmlspecialchars($site['address_he']) ?></li>
              <li>• דוא"ל: <?= htmlspecialchars(getenv('CONTACT_EMAIL') ?: '') ?></li>
              <li>• <?= htmlspecialchars($site['meeting_note_he']) ?></li>
            </ul>
          </div>
          <div class="lang-en hidden">
            <h3 class="text-xl font-semibold text-white">Quick details</h3>
            <ul class="mt-4 space-y-3 text-sm text-slate-400">
              <li>• Address: <?= htmlspecialchars($site['address_en']) ?></li>
              <li>• Email: <?= htmlspecialchars(getenv('CONTACT_EMAIL') ?: '') ?></li>
              <li>• <?= htmlspecialchars($site['meeting_note_en']) ?></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section id="contact" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-16">
      <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-8">
          <div class="lang-he">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300"><?= htmlspecialchars($site['contact_kicker_he']) ?></p>
            <h2 class="mt-2 text-3xl font-semibold text-white"><?= htmlspecialchars($site['contact_title_he']) ?></h2>
            <p class="mt-4 text-slate-400"><?= htmlspecialchars($site['contact_body_he']) ?></p>
          </div>
          <div class="lang-en hidden">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300"><?= htmlspecialchars($site['contact_kicker_en']) ?></p>
            <h2 class="mt-2 text-3xl font-semibold text-white"><?= htmlspecialchars($site['contact_title_en']) ?></h2>
            <p class="mt-4 text-slate-400"><?= htmlspecialchars($site['contact_body_en']) ?></p>
          </div>
          <div class="mt-6 space-y-3 text-sm text-slate-400">
            <p class="lang-he"><i class="fa-solid fa-location-dot w-4 text-teal-300"></i> <?= htmlspecialchars($site['address_he']) ?></p>
            <p class="lang-en hidden"><i class="fa-solid fa-location-dot w-4 text-teal-300"></i> <?= htmlspecialchars($site['address_en']) ?></p>
            <p><i class="fa-solid fa-envelope w-4 text-teal-300"></i> <?= htmlspecialchars(getenv('CONTACT_EMAIL') ?: '') ?></p>
            <p class="lang-he"><i class="fa-solid fa-calendar-days w-4 text-teal-300"></i> <?= htmlspecialchars($site['meeting_note_he']) ?></p>
            <p class="lang-en hidden"><i class="fa-solid fa-calendar-days w-4 text-teal-300"></i> <?= htmlspecialchars($site['meeting_note_en']) ?></p>
          </div>
        </div>

        <div id="forms" class=" grid gap-6 rounded-3xl border border-white/10 bg-slate-900/70 p-8">
          <div class="hidden srounded-2xl border border-white/10 bg-white/5 p-6">
            <div class="lang-he">
              <h3 class="text-xl font-semibold text-white">שלח הודעה</h3>
              <p class="mt-2 text-sm text-slate-400">השתמש בטופס של Fillout כדי לשלוח מסר.</p>
            </div>
            <div class="lang-en hidden">
              <h3 class="text-xl font-semibold text-white">Send a message</h3>
              <p class="mt-2 text-sm text-slate-400">Use the Fillout form to send a note.</p>
            </div>
            <iframe data-fillout-src="contactFormUrl" title="Contact form"
              class="mt-4 h-[560px] w-full rounded-2xl border-0"></iframe>
          </div>

          <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <div class="lang-he">
              <h3 class="text-xl font-semibold text-white">קבע פגישה</h3>
              <p class="mt-2 text-sm text-slate-400">הזמן שיחה קצרה עם המשפחה.</p>
            </div>
            <div class="lang-en hidden">
              <h3 class="text-xl font-semibold text-white">Schedule a meeting</h3>
              <p class="mt-2 text-sm text-slate-400">Book a short conversation with the family.</p>
            </div>
            <!-- <iframe data-fillout-src="meetingFormUrl" title="Meeting form" class="mt-4 h-[560px] w-full rounded-2xl border-0"></iframe> -->
            <div style="width:100%;min-height:500px;" data-fillout-id="7cFLvM7Kezus" data-fillout-embed-type="standard"
              data-fillout-inherit-parameters data-fillout-dynamic-resize></div>
            <script src="https://server.fillout.com/embed/v1/"></script>


          </div>
        </div>
      </div>
    </section>
  </main>

  <footer
    class="border-t border-white/10 px-4 py-8 text-center text-sm text-slate-500 sm:px-6 lg:px-8">
    <p class="lang-he">© <?= date('Y') ?> <?= htmlspecialchars($site['footer_text_he']) ?></p>
    <p class="lang-en hidden">© <?= date('Y') ?> <?= htmlspecialchars($site['footer_text_en']) ?></p>
    <p class="mt-2"><a href="admin/" class="text-slate-600 transition hover:text-teal-300">Admin</a></p>
  </footer>

  <script src="config.js"></script>
  <script src="script.js"></script>
</body>

</html>
