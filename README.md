# Pozner Family Landing Page

This project is a modern, bilingual landing page for the Pozner family in Hebrew and English. It uses plain HTML, Tailwind CSS via CDN, and JavaScript for language switching and Fillout form embeds.

## Files

- `index.html` – home page
- `members/oz.html` – Oz profile page
- `members/havi.html` – Havi profile page
- `members/ron.html` – Ron profile page
- `members/yuval.html` – Yuval profile page
- `config.js` – shared site settings
- `script.js` – language toggle and form embedding
- `.env` – deployment secrets and form URLs
- `.gitignore` – ignored files

## Local preview

Run a local server from the project root:

```bash
python3 -m http.server 8000
```

Open `http://localhost:8000` in your browser.

## Git setup

```bash
git init
git add .
git commit -m "Initial landing page"
```

## IONOS deployment

1. Upload the project files to the root of your IONOS web space using FTP or the IONOS panel.
2. Ensure the file structure is published as-is, including `index.html` and the `members/` directory.
3. If you use custom domain mapping, point the domain to the published folder.
4. Replace the Fillout placeholders in `config.js` and `.env` with your live form URLs.

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
