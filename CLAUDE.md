# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

A bilingual (Hebrew/English) landing page for the Pozner family, deployed to IONOS shared hosting, with a small PHP admin portal for editing family member content and photos. Tailwind CSS via CDN + vanilla JS for the front end; plain PHP (no framework, no database, no Composer) for the backend. No build step.

## Commands

Local preview (run from repo root — requires PHP; plain static file serving will not execute the templates):

```bash
scripts/dev-server.sh
```

Runs `php -S` through `scripts/dev-router.php`, which reimplements `.htaccess`'s rewrites (built-in PHP server doesn't read `.htaccess`), and restarts the server automatically if it crashes — logs to `scripts/dev-server.log`. Open `http://localhost:8000`. Lint any PHP file with `php -l <file>`; lint bash scripts with `bash -n <file>`. There is no other build, lint, or test tooling in this repo.

Before running anything, copy `.env.example` to `.env` and fill in `ADMIN_USERNAME`/`ADMIN_PASSWORD_HASH` at minimum — the admin login fails closed without them.

## Architecture

- `index.php` — home page; renders the family grid by looping over `content_load_members()` (from `includes/content.php`) instead of hardcoding member cards.
- `members/view.php` — single dynamic template for every member's profile page, driven by `?slug=`. `.htaccess` rewrites `members/<slug>.html` to this.
- `content/members.json` — the single source of truth for all member data (bilingual name/role/bio/interests, photo path, contact info, `order`). Read/written only through `includes/content.php` (`content_load_members()` / `content_save_members()`), which uses `flock()` for safe concurrent writes. Not directly web-accessible (`content/.htaccess` denies it).
- `uploads/members/<slug>.<ext>` — uploaded member profile photos, named by slug so a re-upload replaces the file in place. `uploads/content/` — images embedded inline in a bio via the WYSIWYG editor (random filenames, `admin/upload-image.php`). `uploads/.htaccess` blocks script execution in this whole tree (defense in depth on top of extension/MIME validation).
- `includes/` — shared PHP, not web-accessible (`includes/.htaccess` denies it):
  - `env.php` — minimal `.env` loader (no Composer/dotenv dependency); populates `getenv()`/`$_ENV`. Loaded once, automatically, by `config.php`.
  - `config.php` — reads secrets from the environment (`ADMIN_USERNAME`, `ADMIN_PASSWORD_HASH`) rather than hardcoding them, plus non-secret paths/limits/MyMemory endpoint. **Never add a literal credential here** — it belongs in `.env` (gitignored) with a placeholder added to `.env.example`.
  - `auth.php` — session-based auth (`admin_require_login()`, `admin_attempt_login()`, `admin_credentials_configured()`) and CSRF token helpers. Login fails closed (returns `false`) if `.env` credentials are unset, rather than relying on `password_verify()`'s incidental behavior against an empty hash.
  - `content.php` — JSON content store read/write, slug lookup/generation, `content_excerpt()` (HTML→plain-text truncation for card previews and SEO meta descriptions — replaces tags with spaces rather than stripping them, so `</p><h2>` doesn't glue two words together).
  - `translate.php` — `translate_text()` calls the free MyMemory API (no key/signup); returns `null` on any failure so callers degrade gracefully (leave the field as typed) rather than erroring. MyMemory caps requests at ~500 characters, so text longer than that is split on paragraph/sentence boundaries (`translate_split_chunks()`) and translated in pieces.
  - `uploads.php` — shared image-upload validation (`uploads_validate_image()`, `uploads_extension()`) used by both the profile-photo upload in `save.php` and the inline WYSIWYG image upload in `upload-image.php`.
  - `seo.php` — `seo_render()` echoes meta description/robots/canonical/OG/Twitter/JSON-LD tags for a page's `<head>`; `seo_site_url()`/`seo_absolute_url()` build URLs from `SITE_URL` in `.env`.
- `admin/` — the admin portal, each page calls `admin_require_login()` except `login.php`:
  - `login.php` / `logout.php` — session login against the env-sourced bcrypt hash; shows a distinct "not configured" message if `.env` credentials are missing.
  - `index.php` — dashboard listing members, links to `edit.php`.
  - `edit.php` — add/edit form. Two Quill WYSIWYG instances (CDN, no build step) for the bio — one forced `direction: rtl` for Hebrew, one LTR for English — synced into hidden inputs on submit. Toolbar includes link and image buttons; the image handler uploads via `upload-image.php` and inserts the returned URL (not a base64 embed).
  - `upload-image.php` — AJAX endpoint for inline bio images: auth + CSRF, validates via `includes/uploads.php`, saves to `uploads/content/<random>.<ext>`, returns `{url}` JSON.
  - `save.php` — validates CSRF + auth, handles profile photo upload, calls `translate_text()` for Hebrew→English fields when "auto-translate" is checked (skips personal names, and **skips any bio containing `<img>`** — MyMemory isn't HTML-aware and can corrupt image tags), slugifies + de-duplicates new slugs, writes to `content/members.json`.
- `robots.php`, `sitemap.php`, `llms.php` — generated dynamically from `content/members.json` (never go stale), served at `/robots.txt`, `/sitemap.xml`, `/llms.txt` via `.htaccess` rewrites. `llms.php` builds output with an explicit array+`implode("\n", ...)` rather than mixed HTML/PHP template syntax — PHP's "eat the newline after `?>`" rule silently collapses blank lines in that style, which matters for a plain-text file but not for HTML.
- `scripts/dev-router.php` / `scripts/dev-server.sh` — local dev only, not deployed (excluded from `deploy.sh`).
- `scripts/deploy.sh` — deploys git `HEAD`'s tracked files to IONOS over FTP(S) via `lftp mirror --reverse --delete`, credentials from `.env`. **Explicitly excludes `content/` and `uploads/`** — those are live, admin-edited data on the server, and a stale git snapshot must never overwrite them. First deploy still needs those two directories seeded once manually over FTP. Parses `.env` with a `read`-based loop, not `source` — `source` would try to shell-expand the `$` characters in `ADMIN_PASSWORD_HASH` (a bcrypt hash) and break on unquoted spaces in other values.
- `config.js` — defines `window.POZNER_CONFIG`, read by `script.js` to inject Fillout form URLs into `<iframe data-fillout-src="...">` elements.
- `script.js` — bilingual toggle: swaps `.lang-he` / `.lang-en` visibility, persists choice to `localStorage` (`pozner-lang`), and sets `document.documentElement.dir` to `rtl`/`ltr` to match — every page that includes this script must support both directions, not just hide/show text.
- `.htaccess` (root) — rewrites `index.html`→`index.php`, `members/<slug>.html`→`members/view.php?slug=<slug>`, `robots.txt`→`robots.php`, `sitemap.xml`→`sitemap.php`, `llms.txt`→`llms.php`; `DirectoryIndex index.php index.html`.
- `.env` — secrets and deployment values (admin credentials, FTP credentials, site URL, Fillout form URLs). Gitignored, never committed; loaded at runtime by `includes/env.php`. `.env.example` is the tracked template of required keys.

### Bilingual pattern

Every piece of user-facing copy is duplicated: one block with class `lang-he` (Hebrew, shown by default, `dir="rtl"`) and a sibling block with class `lang-en hidden` (English, `dir="ltr"`). When adding or editing static markup (not member content, which goes through the admin form), always add both language variants together and keep them in sync.

### Content model

Each record in `content/members.json` has parallel `_he`/`_en` fields for `name`, `full_name`, `role`, `bio` (rich HTML from Quill), and `interests`, plus language-agnostic `slug`, `photo`, `contact_email`, `known_contacts`, `order`, `updated_at`. Names are never auto-translated (machine translation mangles personal names) — only `role`, `bio`, and `interests` go through `translate_text()`. The home page card excerpt and SEO meta descriptions are both derived from `bio_he`/`bio_en` via `content_excerpt()` rather than a separate stored field.

### Secrets

There is no vault service here (not feasible on IONOS shared hosting) — `.env` at the project root is the single source of secrets, gitignored and uploaded to the server directly over FTP, never through git or a committed file. Never hardcode a credential in PHP; add it to `.env` (and a blank placeholder to `.env.example`) and read it via `getenv()`.

### Translation

`translate_text()` uses MyMemory (`api.mymemory.translated.net`), which needs no API key. Anonymous quota is ~5,000 words/day/IP; `TRANSLATE_EMAIL` in `includes/config.php` (set to the site's contact address) raises that to ~50,000/day per MyMemory's policy. Quality is machine-translation-grade — treat it as a first draft.

### Deployment

First deploy is manual (FTP upload including dotfiles, seed `content/` and `uploads/`); subsequent code deploys use `scripts/deploy.sh`. See `README.md` for the full checklist, including PHP requirement and writable-directory permissions for `content/` and `uploads/`.
