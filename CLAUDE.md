# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

A bilingual (Hebrew/English) family site for the Pozners, deployed to IONOS shared hosting, fully editable through a small PHP admin portal — home page content, family member profiles, and any number of custom sub-pages. Tailwind CSS via CDN + vanilla JS for the front end; plain PHP (no framework, no database, no Composer) for the backend. No build step. See `requirements.ai` for the full chronological history of what was requested and why — update it when new requirements come in.

## Commands

Local preview (run from repo root — requires PHP; plain static file serving will not execute the templates):

```bash
scripts/dev-server.sh
```

Runs `php -S` through `scripts/dev-router.php`, which reimplements `.htaccess`'s rewrites (built-in PHP server doesn't read `.htaccess`), and restarts the server automatically if it crashes — logs to `scripts/dev-server.log`. Open `http://localhost:8000`. Lint any PHP file with `php -l <file>`; lint bash scripts with `bash -n <file>`. There is no other build, lint, or test tooling in this repo.

Before running anything, copy `.env.example` to `.env` and fill in `ADMIN_USERNAME`/`ADMIN_PASSWORD_HASH` at minimum — the admin login fails closed without them.

## Architecture

- `index.php` — home page; renders hero/about/contact/footer/nav from `content_load_site()`, the family grid from `content_load_members()`, and appends nav links for `content_load_pages()` (all from `includes/content.php`) instead of hardcoding any of it.
- `members/view.php` — single dynamic template for every member's profile page, driven by `?slug=`. `.htaccess` rewrites `members/<slug>.html` to this.
- `pages/view.php` — single dynamic template for every custom sub-page, driven by `?slug=` (title + optional hero image + rich WYSIWYG body). `.htaccess` rewrites `pages/<slug>.html` to this. Structurally a simplified sibling of `members/view.php` — no role/interests/contact fields, just title + body.
- `content/site.json` — the home page's singleton editable copy (hero, address, family-section header, video channel, about, contact, footer). Missing keys fall back to `content_site_defaults()` in `includes/content.php`, so partial/older files still render.
- `content/members.json` — all member data (bilingual name/role/bio/interests, photo path, contact info, `order`).
- `content/pages.json` — custom sub-pages (bilingual title/body, optional hero image, `nav_label`, `show_in_nav`, `order`).
- None of the `content/*.json` files are meant to be hand-edited — go through `/admin`. All three are read/written through generic helpers in `includes/content.php` (`content_load_json()` / `content_save_json()`, using `flock()` for safe concurrent writes), wrapped by type-specific functions (`content_load_members()`/`content_save_members()`, `content_load_site()`/`content_save_site()`, `content_load_pages()`/`content_save_pages()`). Not directly web-accessible (`content/.htaccess` denies it). `content_find_by_slug()` backs both `content_find_member()` and `content_find_page()`.
- `uploads/members/<slug>.<ext>` — member profile photos; `uploads/pages/<slug>.<ext>` — page hero images (both named by slug so a re-upload replaces the file in place). `uploads/content/` — images embedded inline in a bio/body via the WYSIWYG editor (random filenames, `admin/upload-image.php`, shared by both member and page editors). `uploads/.htaccess` blocks script execution in this whole tree (defense in depth on top of extension/MIME validation).
- `includes/` — shared PHP, not web-accessible (`includes/.htaccess` denies it):
  - `env.php` — minimal `.env` loader (no Composer/dotenv dependency); populates `getenv()`/`$_ENV`. Loaded once, automatically, by `config.php`.
  - `config.php` — reads secrets from the environment (`ADMIN_USERNAME`, `ADMIN_PASSWORD_HASH`) rather than hardcoding them, plus non-secret paths/limits/MyMemory endpoint, plus `SITE_JSON`/`PAGES_JSON`/`UPLOADS_PAGES_*` constants. **Never add a literal credential here** — it belongs in `.env` (gitignored) with a placeholder added to `.env.example`.
  - `auth.php` — session-based auth (`admin_require_login()`, `admin_attempt_login()`, `admin_credentials_configured()`) and CSRF token helpers. Login fails closed (returns `false`) if `.env` credentials are unset, rather than relying on `password_verify()`'s incidental behavior against an empty hash.
  - `content.php` — see content stores above, plus slug lookup/generation (`content_slugify($text, $fallbackPrefix)` — pass `'member'`/`'page'` so the random-fallback slug reads e.g. `page-a1b2c3` not `item-a1b2c3`) and `content_excerpt()` (HTML→plain-text truncation for card previews and SEO meta descriptions — replaces tags with spaces rather than stripping them, so `</p><h2>` doesn't glue two words together).
  - `translate.php` — `translate_text()` calls the free MyMemory API (no key/signup); returns `null` on any failure so callers degrade gracefully (leave the field as typed) rather than erroring. MyMemory caps requests at ~500 characters, so text longer than that is split on paragraph/sentence boundaries (`translate_split_chunks()`) and translated in pieces.
  - `uploads.php` — shared image-upload validation (`uploads_validate_image()`, `uploads_extension()`) used by the profile-photo upload, the page hero-image upload, and the inline WYSIWYG image upload.
  - `seo.php` — `seo_render()` echoes meta description/robots/canonical/OG/Twitter/JSON-LD tags for a page's `<head>`; `seo_site_url()`/`seo_absolute_url()` build URLs from `SITE_URL` in `.env`.
- `admin/` — the admin portal, each page calls `admin_require_login()` except `login.php`. Restructured as a hub since it's no longer just member management:
  - `login.php` / `logout.php` — session login against the env-sourced bcrypt hash; shows a distinct "not configured" message if `.env` credentials are missing.
  - `index.php` — hub linking to the three areas below, with live counts.
  - `site.php` — single GET+POST form (like `login.php`'s pattern, not a list+edit+save trio, since there's only one site record) editing `content/site.json` directly against `content_site_defaults()`'s keys.
  - `members.php` / `member-edit.php` / `member-save.php` — family member list / add-edit form / save handler (renamed from the pre-hub `index.php`/`edit.php`/`save.php`).
  - `pages.php` / `page-edit.php` / `page-save.php` — custom sub-page list / add-edit form / save handler. Structurally parallel to the member trio (same slug-generate-and-dedupe logic, same Quill WYSIWYG setup, same translate-on-save pattern including the `<img>` skip), just with title/body instead of name/role/bio/interests/contact, plus `nav_label` and `show_in_nav`.
  - `upload-image.php` — AJAX endpoint for inline WYSIWYG images (bio or page body): auth + CSRF, validates via `includes/uploads.php`, saves to `uploads/content/<random>.<ext>`, returns `{url}` JSON. Shared by `member-edit.php` and `page-edit.php`.
  - Every edit/save file's Quill toolbar includes link and image buttons; the image handler uploads via `upload-image.php` and inserts the returned URL (not a base64 embed). Auto-translate skips any bio/body containing `<img>` — MyMemory isn't HTML-aware and can corrupt image tags.
- `robots.php`, `sitemap.php`, `llms.php` — generated dynamically from the content stores (never go stale — include members **and** pages), served at `/robots.txt`, `/sitemap.xml`, `/llms.txt` via `.htaccess` rewrites. `llms.php` builds output with an explicit array+`implode("\n", ...)` rather than mixed HTML/PHP template syntax — PHP's "eat the newline after `?>`" rule silently collapses blank lines in that style, which matters for a plain-text file but not for HTML.
- `scripts/dev-router.php` / `scripts/dev-server.sh` — local dev only, not deployed (excluded from `deploy.sh`). The server binds `0.0.0.0` by default (not just `localhost`) and prints the LAN IP, so it's reachable from a phone on the same Wi-Fi for mobile testing; auto-restarts if the PHP process crashes.
- `scripts/deploy.sh` — deploys git `HEAD`'s tracked files to IONOS over FTP(S) via `lftp mirror --reverse --delete`, credentials from `.env`. **Explicitly excludes `content/` and `uploads/`** — those are live, admin-edited data on the server, and a stale git snapshot must never overwrite them. First deploy still needs those directories seeded once manually over FTP. Parses `.env` with a `read`-based loop, not `source` — `source` would try to shell-expand the `$` characters in `ADMIN_PASSWORD_HASH` (a bcrypt hash) and break on unquoted spaces in other values.
- `config.js` — defines `window.POZNER_CONFIG`, read by `script.js` to inject Fillout form URLs into `<iframe data-fillout-src="...">` elements.
- `script.js` — bilingual toggle: swaps `.lang-he` / `.lang-en` visibility, persists choice to `localStorage` (`pozner-lang`), and sets `document.documentElement.dir` to `rtl`/`ltr` to match — every page that includes this script must support both directions, not just hide/show text. Also drives the mobile nav menu (`#nav-toggle`/`#mobile-menu`, only present on `index.php`).
- `.htaccess` (root) — rewrites `index.html`→`index.php`, `members/<slug>.html`→`members/view.php?slug=<slug>`, `pages/<slug>.html`→`pages/view.php?slug=<slug>`, `robots.txt`→`robots.php`, `sitemap.xml`→`sitemap.php`, `llms.txt`→`llms.php`; `DirectoryIndex index.php index.html`.
- `.env` — secrets and deployment values (admin credentials, FTP credentials, site URL, Fillout form URLs). Gitignored, never committed; loaded at runtime by `includes/env.php`. `.env.example` is the tracked template of required keys.
- `requirements.ai` — compact, chronological log of every requirement requested across this project's AI-assisted sessions. Append to it (don't rewrite history) when new requirements come in.

### Bilingual pattern

Every piece of user-facing copy is duplicated: one block with class `lang-he` (Hebrew, shown by default, `dir="rtl"`) and a sibling block with class `lang-en hidden` (English, `dir="ltr"`). When adding or editing static markup (not member content, which goes through the admin form), always add both language variants together and keep them in sync.

### Content model

Each record in `content/members.json` has parallel `_he`/`_en` fields for `name`, `full_name`, `role`, `bio` (rich HTML from Quill), and `interests`, plus language-agnostic `slug`, `photo`, `contact_email`, `known_contacts`, `order`, `updated_at`. Names are never auto-translated (machine translation mangles personal names) — only `role`, `bio`, and `interests` go through `translate_text()`. The home page card excerpt and SEO meta descriptions are both derived from `bio_he`/`bio_en` via `content_excerpt()` rather than a separate stored field.

Each record in `content/pages.json` has parallel `title_he`/`title_en` and `body_he`/`body_en` (rich HTML), plus `slug`, `image`, `nav_label` (single, not bilingual — matches the existing nav's convention of untranslated English-ish labels; falls back to `title_en`), `show_in_nav`, `order`, `updated_at`. Unlike member names, page titles *do* go through auto-translate (they're generic phrases, not personal names).

`content/site.json` is a flat, non-bilingual-structured object — every key is either suffixed `_he`/`_en` directly (no nesting) or is language-agnostic (`hero_image`, `video_channel_url`, `video_channel_name`, `video_playlist_id`). See `content_site_defaults()` for the full key list.

### Secrets

There is no vault service here (not feasible on IONOS shared hosting) — `.env` at the project root is the single source of secrets, gitignored and uploaded to the server directly over FTP, never through git or a committed file. Never hardcode a credential in PHP; add it to `.env` (and a blank placeholder to `.env.example`) and read it via `getenv()`.

### Translation

`translate_text()` uses MyMemory (`api.mymemory.translated.net`), which needs no API key. Anonymous quota is ~5,000 words/day/IP; `TRANSLATE_EMAIL` in `includes/config.php` (set to the site's contact address) raises that to ~50,000/day per MyMemory's policy. Quality is machine-translation-grade — treat it as a first draft.

### Deployment

First deploy is manual (FTP upload including dotfiles, seed `content/` and `uploads/`); subsequent code deploys use `scripts/deploy.sh`. See `README.md` for the full checklist, including PHP requirement and writable-directory permissions for `content/` and `uploads/`.
