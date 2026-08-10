# Pozner Family Landing Page

This project is a bilingual (Hebrew/English) landing page for the Pozner family, with a small PHP admin portal for updating family member pages and photos. It uses Tailwind CSS via CDN, vanilla JavaScript for language switching and Fillout form embeds, and plain PHP (no framework, no database) for content storage, authentication, image uploads, and Hebrew→English auto-translation.

## Files

- `index.php` – home page; renders the family grid from `content/members.json`
- `members/view.php` – single dynamic template for every member's profile page (e.g. `members/oz.html` is rewritten to `members/view.php?slug=oz`)
- `content/members.json` – all member data (bilingual name/role/bio/interests, photo path, contact info) — edited via the admin portal, not by hand
- `uploads/members/` – uploaded member photos; `uploads/content/` – images embedded inline in a bio via the WYSIWYG editor
- `admin/` – the admin portal (login, dashboard, add/edit member form with WYSIWYG editor)
- `includes/` – shared PHP: auth/session handling, JSON content read/write, MyMemory translation client, `.env` loader, SEO helper, config
- `robots.php`, `sitemap.php`, `llms.php` – dynamically generated from `content/members.json`, served at `/robots.txt`, `/sitemap.xml`, `/llms.txt` via `.htaccess` rewrites
- `scripts/dev-server.sh` – local dev server with auto-restart; `scripts/deploy.sh` – deploys git HEAD to IONOS over FTP
- `config.js` – shared site settings
- `script.js` – language toggle (incl. RTL/LTR `dir` switching) and Fillout form embedding
- `.htaccess` – rewrites old `.html` links (and `/robots.txt`, `/sitemap.xml`, `/llms.txt`) to the PHP templates, blocks direct access to `content/` and `includes/`
- `.env` – secrets and deployment values (gitignored, never committed); `.env.example` – tracked template listing the required keys
- `.gitignore` – ignored files

## Local preview

This site now requires PHP (no more `python3 -m http.server` — plain static serving won't execute the PHP templates). From the project root:

```bash
scripts/dev-server.sh
```

This runs `php -S` with a router (`scripts/dev-router.php`) that reimplements `.htaccess`'s rewrites, so pretty URLs like `members/oz.html`, `/robots.txt`, `/sitemap.xml`, and `/llms.txt` work locally too — and restarts the server automatically if it crashes. Open `http://localhost:8000`. Logs go to `scripts/dev-server.log`. (Running plain `php -S localhost:8000` also works for quick checks, but without the router or the auto-restart.)

Before running anything, copy `.env.example` to `.env` and fill in at least `ADMIN_USERNAME`/`ADMIN_PASSWORD_HASH` (see Admin portal below) — without them the admin login refuses to work.

## Admin portal

Go to `/admin/` on the site (linked from the homepage footer) and log in with the credentials that were configured for this site. The dashboard lists every family member; click one to edit it, or use "+ Add member" to create a new profile page.

The edit form has a Hebrew WYSIWYG editor (right-to-left) and an English WYSIWYG editor (left-to-right) for the bio, plus plain fields for name/role/interests/contact, and a photo upload that replaces the member's picture everywhere it appears (home grid + profile page). With "Auto-translate Hebrew → English" checked, saving a Hebrew bio/role/interests automatically fills in the English versions via [MyMemory](https://mymemory.translated.net) — a free translation API that needs no signup or key — uncheck it to keep your own English wording untouched. Machine translation quality is decent but not perfect; treat the auto-filled English as a first draft and edit it if it reads awkwardly.

**Credentials live in `.env`, never in code.** `includes/config.php` reads `ADMIN_USERNAME`/`ADMIN_PASSWORD_HASH` from the environment (loaded from `.env` by `includes/env.php`) — there is no username or password anywhere in a committed file, and `.env` is gitignored. If those keys are missing, the login page refuses to work instead of silently allowing a blank login.

**To change the admin password:** generate a new bcrypt hash and update `ADMIN_PASSWORD_HASH` in `.env`:

```bash
php -r 'echo password_hash("your-new-password", PASSWORD_BCRYPT), PHP_EOL;'
```

In production, `.env` is uploaded to the server directly over FTP (a client like Cyberduck/FileZilla, or `scripts/deploy.sh` never touches it since it's excluded from the deploy) — it should never be committed or pass through git.

**Translation quota:** MyMemory's anonymous tier allows ~5,000 words/day per IP address, or ~50,000 words/day when a contact email is attached to requests (`TRANSLATE_EMAIL` in `includes/config.php`, already set to `oz@pozners.com`) — plenty for a family site. Individual requests are also capped at ~500 characters; longer bios are automatically split on paragraph/sentence boundaries and translated in pieces, then rejoined. If the API ever fails or the quota is hit, translation calls fail gracefully — the English fields are just left as-is (blank on a new member, unchanged on an edit) rather than erroring out, so you can always type the English text in by hand.

**Content storage:** because this is FTP-deployed static/PHP hosting with no database and no CI pipeline, edits made through `/admin` on the live server change files directly on that server (`content/members.json`, `uploads/members/`) — they don't automatically sync back into this git repo. If you want a backup or want to carry live edits back into version control, periodically download those two paths over FTP and commit them.

## Git setup

```bash
git init
git add .
git commit -m "Initial landing page"
```

## SEO & AI discovery

Every page renders a meta description, canonical URL, Open Graph/Twitter Card tags, and JSON-LD (`schema.org/WebSite` + `Person` per member) via `includes/seo.php` — see `index.php` and `members/view.php`. Three more endpoints are generated live from `content/members.json` so they never go stale:

- `/robots.txt` (`robots.php`) — allows crawling of public pages, disallows `/admin/`, `/includes/`, `/content/`, `/uploads/content/`.
- `/sitemap.xml` (`sitemap.php`) — home page + every member page.
- `/llms.txt` (`llms.php`) — a short Markdown summary of the site and each family member, following the [llms.txt](https://llmstxt.org) convention for AI crawlers/answer engines.

All of them read `SITE_URL`/`SITE_TITLE` from `.env`, so update those if the domain ever changes.

## Environment values (`.env`)

Copy `.env.example` to `.env` and fill in real values — see that file for the full list (site info, Fillout form URLs, admin credentials, FTP deploy credentials). `.env` is gitignored and, in production, is uploaded to the server directly over FTP — it should never pass through git.

## Deployment

**First deploy (manual, one-time):** upload the whole project to the root of your IONOS web space via FTP or the IONOS panel, including dotfiles (`.htaccess`, `.env` — configure your FTP client to show hidden files first). Confirm PHP is enabled for the plan. Make `content/` and `uploads/` writable by PHP (typically permissions `775`) so the admin portal can save edits and photo uploads. Point your domain at the published folder if using custom domain mapping.

**Subsequent deploys (code only):**

```bash
scripts/deploy.sh
```

This uploads the current git `HEAD`'s tracked files to IONOS over FTP(S), using the `FTP_*` credentials in `.env`. It deliberately **never touches `content/` or `uploads/`** — those hold live data edited directly on the server through `/admin`, and a code deploy overwriting them with a stale git snapshot would silently destroy real edits. If a member's photo or bio was added after the last `git pull` of `content/members.json`/`uploads/`, that's expected: manage content through `/admin`, not through deploys. (If you want a backup of live content in git, periodically download `content/members.json` and `uploads/` over FTP and commit them separately.)

Requires [lftp](https://lftp.yar.ru) (`brew install lftp`) locally. If the working tree has uncommitted changes, the script warns and deploys the last commit anyway — commit first if you want those changes included.
