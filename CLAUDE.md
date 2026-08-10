# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

A bilingual (Hebrew/English) landing page for the Pozner family, deployed to IONOS shared hosting, with a small PHP admin portal for editing family member content and photos. Tailwind CSS via CDN + vanilla JS for the front end; plain PHP (no framework, no database, no Composer) for the backend. No build step.

## Commands

Local preview (run from repo root — requires PHP; plain static file serving will not execute the templates):

```bash
php -S localhost:8000
```

Then open `http://localhost:8000`. The built-in PHP server does not read `.htaccess`, so the pretty `members/<slug>.html` URLs 404 locally unless you pass a router script reimplementing the two rewrite rules in `.htaccess` (not needed on the real Apache/IONOS host). Lint any PHP file with `php -l <file>`. There is no other build, lint, or test tooling in this repo.

## Architecture

- `index.php` — home page; renders the family grid by looping over `content_load_members()` (from `includes/content.php`) instead of hardcoding member cards.
- `members/view.php` — single dynamic template for every member's profile page, driven by `?slug=`. `.htaccess` rewrites `members/<slug>.html` to this.
- `content/members.json` — the single source of truth for all member data (bilingual name/role/bio/interests, photo path, contact info, `order`). Read/written only through `includes/content.php` (`content_load_members()` / `content_save_members()`), which uses `flock()` for safe concurrent writes. Not directly web-accessible (`content/.htaccess` denies it).
- `uploads/members/<slug>.<ext>` — uploaded member photos, named by slug so a re-upload replaces the file in place. `uploads/.htaccess` blocks script execution in this directory (defense in depth on top of extension/MIME validation in `admin/save.php`).
- `includes/` — shared PHP, not web-accessible (`includes/.htaccess` denies it):
  - `config.php` — admin credentials (bcrypt hash, not plaintext), paths, MyMemory translation endpoint/email, upload limits.
  - `auth.php` — session-based auth (`admin_require_login()`, `admin_attempt_login()`) and CSRF token helpers.
  - `content.php` — JSON content store read/write, slug lookup/generation, HTML-bio excerpting for card previews.
  - `translate.php` — `translate_text()` calls the free MyMemory API (no key/signup); returns `null` on any failure so callers degrade gracefully (leave the field as typed) rather than erroring. MyMemory caps requests at ~500 characters, so text longer than that is split on paragraph/sentence boundaries (`translate_split_chunks()`) and translated in pieces.
- `admin/` — the admin portal, each page calls `admin_require_login()` except `login.php`:
  - `login.php` / `logout.php` — session login against the bcrypt hash in `config.php`.
  - `index.php` — dashboard listing members, links to `edit.php`.
  - `edit.php` — add/edit form. Two Quill WYSIWYG instances (CDN, no build step) for the bio — one forced `direction: rtl` for Hebrew, one LTR for English — synced into hidden inputs on submit.
  - `save.php` — validates CSRF + auth, handles photo upload (extension/size/`getimagesize()` validation), calls `translate_text()` for Hebrew→English fields when "auto-translate" is checked (skips personal names — those are typed by hand, never machine-translated), slugifies + de-duplicates new slugs, writes to `content/members.json`.
- `config.js` — defines `window.POZNER_CONFIG`, read by `script.js` to inject Fillout form URLs into `<iframe data-fillout-src="...">` elements.
- `script.js` — bilingual toggle: swaps `.lang-he` / `.lang-en` visibility, persists choice to `localStorage` (`pozner-lang`), and sets `document.documentElement.dir` to `rtl`/`ltr` to match — every page that includes this script must support both directions, not just hide/show text.
- `.htaccess` (root) — rewrites `index.html` → `index.php` and `members/<slug>.html` → `members/view.php?slug=<slug>` to preserve old bookmarked links; `DirectoryIndex index.php index.html`.
- `.env` — deployment-only reference values; not loaded by any PHP code. Never committed.

### Bilingual pattern

Every piece of user-facing copy is duplicated: one block with class `lang-he` (Hebrew, shown by default, `dir="rtl"`) and a sibling block with class `lang-en hidden` (English, `dir="ltr"`). When adding or editing static markup (not member content, which goes through the admin form), always add both language variants together and keep them in sync.

### Content model

Each record in `content/members.json` has parallel `_he`/`_en` fields for `name`, `full_name`, `role`, `bio` (rich HTML from Quill), and `interests`, plus language-agnostic `slug`, `photo`, `contact_email`, `known_contacts`, `order`, `updated_at`. Names are never auto-translated (machine translation mangles personal names) — only `role`, `bio`, and `interests` go through `translate_text()`. The home page card excerpt is derived from `bio_he`/`bio_en` via `content_excerpt()` (strip tags + truncate) rather than a separate stored field.

### Translation

`translate_text()` uses MyMemory (`api.mymemory.translated.net`), which needs no API key. Anonymous quota is ~5,000 words/day/IP; `TRANSLATE_EMAIL` in `includes/config.php` (set to the site's contact address) raises that to ~50,000/day per MyMemory's policy. Quality is machine-translation-grade — treat it as a first draft.

### Deployment

Files are uploaded as-is (FTP or IONOS panel) to the web space root — see `README.md` for the full checklist, including the PHP requirement, writable-directory permissions for `content/` and `uploads/members/`, and uploading dotfiles (`.htaccess`, `.env`).
