(function () {
  const toggle = document.getElementById('lang-toggle');
  const savedLanguage = localStorage.getItem('pozner-lang') || 'he';

  function setLanguage(lang) {
    const isHebrew = lang === 'he';
    document.querySelectorAll('.lang-he').forEach((el) => el.classList.toggle('hidden', !isHebrew));
    document.querySelectorAll('.lang-en').forEach((el) => el.classList.toggle('hidden', isHebrew));
    document.documentElement.lang = lang;
    document.documentElement.dir = isHebrew ? 'rtl' : 'ltr';
    localStorage.setItem('pozner-lang', lang);
    if (toggle) {
      toggle.textContent = isHebrew ? 'English' : 'עברית';
    }
  }

  document.querySelectorAll('[data-fillout-src]').forEach((frame) => {
    const key = frame.dataset.filloutSrc;
    const url = window.POZNER_CONFIG?.[key];
    if (url) {
      frame.src = url;
    }
  });

  if (toggle) {
    toggle.addEventListener('click', () => {
      const next = document.documentElement.lang === 'he' ? 'en' : 'he';
      setLanguage(next);
    });
  }

  setLanguage(savedLanguage);
})();
