# Pozner Family Landing Page

This project is a bilingual (Hebrew/English) landing page for the Pozner family, with a small PHP admin portal for updating family member pages and photos. It uses Tailwind CSS via CDN, vanilla JavaScript for language switching and Fillout form embeds, and plain PHP (no framework, no database) for content storage, authentication, image uploads, and Hebrew→English auto-translation.

## Files

- `index.php` – home page; renders the family grid from `content/members.json`
- `members/view.php` – single dynamic template for every member's profile page (e.g. `members/oz.html` is rewritten to `members/view.php?slug=oz`)
- `content/members.json` – all member data (bilingual name/role/bio/interests, photo path, contact info) — edited via the admin portal, not by hand
- `uploads/members/` – uploaded member photos
- `admin/` – the admin portal (login, dashboard, add/edit member form with WYSIWYG editor)
- `includes/` – shared PHP: auth/session handling, JSON content read/write, LibreTranslate client, config/credentials
- `config.js` – shared site settings
- `script.js` – language toggle (incl. RTL/LTR `dir` switching) and Fillout form embedding
- `.htaccess` – rewrites old `.html` links to the PHP templates, blocks direct access to `content/` and `includes/`
- `.env` – deployment secrets and form URLs
- `.gitignore` – ignored files

## Local preview

This site now requires PHP (no more `python3 -m http.server` — plain static serving won't execute the PHP templates). From the project root:

```bash
php -S localhost:8000
```

Open `http://localhost:8000` in your browser. Note the built-in PHP server does **not** read `.htaccess`, so the pretty `members/<slug>.html` URLs will 404 locally unless you pass a router script that reimplements the two rewrite rules in `.htaccess` — on real Apache hosting (like IONOS) this isn't needed.

## Admin portal

Go to `/admin/` on the site (linked from the homepage footer) and log in with the credentials that were configured for this site. The dashboard lists every family member; click one to edit it, or use "+ Add member" to create a new profile page.

The edit form has a Hebrew WYSIWYG editor (right-to-left) and an English WYSIWYG editor (left-to-right) for the bio, plus plain fields for name/role/interests/contact, and a photo upload that replaces the member's picture everywhere it appears (home grid + profile page). With "Auto-translate Hebrew → English" checked, saving a Hebrew bio/role/interests automatically fills in the English versions via [MyMemory](https://mymemory.translated.net) — a free translation API that needs no signup or key — uncheck it to keep your own English wording untouched. Machine translation quality is decent but not perfect; treat the auto-filled English as a first draft and edit it if it reads awkwardly.

**To change the admin password:** generate a new bcrypt hash and paste it into `includes/config.php`:

```bash
php -r 'echo password_hash("your-new-password", PASSWORD_BCRYPT), PHP_EOL;'
```

Replace the `ADMIN_PASSWORD_HASH` value with the output.

**Translation quota:** MyMemory's anonymous tier allows ~5,000 words/day per IP address, or ~50,000 words/day when a contact email is attached to requests (`TRANSLATE_EMAIL` in `includes/config.php`, already set to `oz@pozners.com`) — plenty for a family site. Individual requests are also capped at ~500 characters; longer bios are automatically split on paragraph/sentence boundaries and translated in pieces, then rejoined. If the API ever fails or the quota is hit, translation calls fail gracefully — the English fields are just left as-is (blank on a new member, unchanged on an edit) rather than erroring out, so you can always type the English text in by hand.

**Content storage:** because this is FTP-deployed static/PHP hosting with no database and no CI pipeline, edits made through `/admin` on the live server change files directly on that server (`content/members.json`, `uploads/members/`) — they don't automatically sync back into this git repo. If you want a backup or want to carry live edits back into version control, periodically download those two paths over FTP and commit them.

## Git setup

```bash
git init
git add .
git commit -m "Initial landing page"
```

## IONOS deployment

1. Confirm PHP is enabled for your IONOS web space (required now — the site is no longer pure static HTML).
2. Upload the project files to the root of your IONOS web space using FTP or the IONOS panel, including the dotfiles (`.htaccess`, `.env`) — make sure your FTP client is set to show hidden files, or they'll silently be skipped.
3. Ensure `content/` and `uploads/members/` are writable by PHP (typically permissions `775`) so the admin portal can save edits and photo uploads.
4. If you use custom domain mapping, point the domain to the published folder.
5. Replace the Fillout placeholders in `config.js` and `.env` with your live form URLs.
6. Log into `/admin/` once to confirm the login works and the writable directories above are actually writable (try editing a member).

## Environment values

Update `.env` with your real values:

```env
SITE_TITLE=Pozner Family
SITE_URL=https://pozners.com
CONTACT_EMAIL=oz@pozners.com
CONTACT_FORM_URL=https://form.fillout.com/t/your-contact-form
MEETING_FORM_URL=https://form.fillout.com/t/your-meeting-form
```

If you want a more advanced deployment flow, you can also wire these values into a build step later.
