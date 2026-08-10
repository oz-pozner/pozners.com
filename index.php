<?php
require_once __DIR__ . '/includes/content.php';
$members = content_load_members();
?>
<!DOCTYPE html>
<html lang="he" class="scroll-smooth" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description"
    content="The Pozner family landing page in Hebrew and English, featuring Oz, Havi, Ron and Yuval from Kiryat Ono, Israel." />
  <title>Pozner Family | עוז, חווי, רון, יובל</title>
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
        <a href="#contact" class="text-sm text-slate-300 transition hover:text-teal-300">Contact</a>
        <a href="#forms" class="text-sm text-slate-300 transition hover:text-teal-300">Forms</a>
      </div>
      <button id="lang-toggle"
        class="rounded-full border border-teal-400/40 px-3 py-2 text-sm font-medium text-teal-300 transition hover:bg-teal-400/10">English</button>
    </nav>
  </header>

  <main>
    <section class="mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8 lg:py-28">
      <div class="space-y-6">
        <div class="lang-he">
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300">משפחה • קריית אונו</p>
          <h1 class="text-4xl font-bold leading-tight text-white sm:text-5xl">ברוכים הבאים לאתר משפחת פוזנר</h1>
          <p class="max-w-2xl text-lg text-slate-300">
            משפחה חמה, יצירתית ומחוברת, שמביאה יחד אהבה, מקצוענות, וניהול חיים בהרמוניה ברחבי ישראל.
          </p>
        </div>
        <div class="lang-en hidden">
          <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300">Family • Kiryat Ono</p>
          <h1 class="text-4xl font-bold leading-tight text-white sm:text-5xl">Welcome to the Pozner family</h1>
          <p class="max-w-2xl text-lg text-slate-300">
            A warm, creative, and connected family that brings together love, professionalism, and meaningful living
            across Israel.
          </p>
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
            <p class="mt-2 text-xl font-semibold text-white">יצחק רבין 5, דירה 11, קריית אונו</p>
          </div>
          <div class="lang-en hidden">
            <p class="text-sm text-slate-400">Address</p>
            <p class="mt-2 text-xl font-semibold text-white">Yitzhak Rabin 5, Apt 11, Kiryat Ono</p>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-3xl border border-white/10 shadow-2xl shadow-teal-500/10">
        <img src="https://images.unsplash.com/photo-1511895426328-dc8714191300?auto=format&fit=crop&w=1200&q=80"
          alt="Family portrait in a warm home setting" class="h-full w-full object-cover" />
      </div>
    </section>

    <section id="family" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-16">
      <div class="mb-10 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <div class="lang-he">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300">המשפחה</p>
            <h2 class="text-3xl font-semibold text-white">עוז, חווי, רון ויובל</h2>
          </div>
          <div class="lang-en hidden">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300">The Family</p>
            <h2 class="text-3xl font-semibold text-white">Oz, Havi, Ron and Yuval</h2>
          </div>
        </div>
        <div class="lang-he text-sm text-slate-400">כל אחד עם מסלול אישי, כישרון וערך ייחודי.</div>
        <div class="lang-en hidden text-sm text-slate-400">Each member brings a unique path, talent, and contribution.
        </div>
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

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-16">
      <div
        class="grid gap-8 rounded-3xl border border-white/10 bg-gradient-to-br from-slate-900 to-slate-800 p-8 lg:grid-cols-[1fr_0.8fr]">
        <div>
          <div class="lang-he">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300">על הבית</p>
            <h2 class="mt-2 text-3xl font-semibold text-white">משפחה שמכבדת ערכים, חינוך ותנועה משותפת</h2>
            <p class="mt-4 max-w-2xl text-slate-400">המשפחה נשענת על קשר, חינוך, מעורבות, התפתחות, וסביבה שמאפשרת לכל
              אחד למצוא את קולו.</p>
          </div>
          <div class="lang-en hidden">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300">About the Home</p>
            <h2 class="mt-2 text-3xl font-semibold text-white">A family grounded in values, education, and shared growth
            </h2>
            <p class="mt-4 max-w-2xl text-slate-400">The family is built on connection, education, engagement, and a
              home environment that allows everyone to find their own voice.</p>
          </div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
          <div class="lang-he">
            <h3 class="text-xl font-semibold text-white">שאלות נפוצות</h3>
            <ul class="mt-4 space-y-3 text-sm text-slate-400">
              <li>• כתובת: יצחק רבין 5, דירה 11, קריית אונו</li>
              <li>• דוא"ל: oz@pozners.com</li>
              <li>• קשר למפגש: הזמינו פגישה דרך הטופס</li>
            </ul>
          </div>
          <div class="lang-en hidden">
            <h3 class="text-xl font-semibold text-white">Quick details</h3>
            <ul class="mt-4 space-y-3 text-sm text-slate-400">
              <li>• Address: Yitzhak Rabin 5, Apartment 11, Kiryat Ono</li>
              <li>• Email: oz@pozners.com</li>
              <li>• Meeting requests can be booked through the form below</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section id="contact" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-16">
      <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-3xl border border-white/10 bg-slate-900/70 p-8">
          <div class="lang-he">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300">יצירת קשר</p>
            <h2 class="mt-2 text-3xl font-semibold text-white">נשמע מעניין?</h2>
            <p class="mt-4 text-slate-400">לשאלות, הצעות, או סתם להכיר, כתבו אלינו. אנו נשמח לשמוע.</p>
          </div>
          <div class="lang-en hidden">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-teal-300">Get in touch</p>
            <h2 class="mt-2 text-3xl font-semibold text-white">Would you like to connect?</h2>
            <p class="mt-4 text-slate-400">For questions, ideas, or simply to say hello, reach out. We would love to
              hear from you.</p>
          </div>
          <div class="mt-6 space-y-3 text-sm text-slate-400">
            <p>📍 Yitzhak Rabin 5, Apt 11, Kiryat Ono</p>
            <p>✉️ oz@pozners.com</p>
            <p>🗓️ Meetings available on request</p>
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
            <div style="width:100%;height:500px;" data-fillout-id="7cFLvM7Kezus" data-fillout-embed-type="standard"
              data-fillout-inherit-parameters data-fillout-dynamic-resize></div>
            <script src="https://server.fillout.com/embed/v1/"></script>


          </div>
        </div>
      </div>
    </section>
  </main>

  <footer
    class="border-t border-white/10 px-4 py-8 text-center text-sm text-slate-500 sm:px-6 lg:px-8">
    <p>© 2026 Pozner Family • Kiryat Ono, Israel</p>
    <p class="mt-2"><a href="admin/" class="text-slate-600 transition hover:text-teal-300">Admin</a></p>
  </footer>

  <script src="config.js"></script>
  <script src="script.js"></script>
</body>

</html>
