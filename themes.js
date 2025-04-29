(function(){
  const STORAGE_KEY = 'calendarTheme';

  // On load: read saved theme, apply it
  document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem(STORAGE_KEY) || 'light';
    applyTheme(saved);

    const sel = document.getElementById('theme-select');
    if (sel) {
      sel.value = saved;
      sel.addEventListener('change', e => {
        applyTheme(e.target.value);
        localStorage.setItem(STORAGE_KEY, e.target.value);
      });
    }
  });

  function applyTheme(theme) {
    document.body.classList.toggle('theme-dark', theme === 'dark');
    document.body.classList.toggle('theme-light', theme === 'light');
    document.body.classList.toggle('theme-ocean', theme === 'ocean');
    document.body.classList.toggle('theme-forest', theme === 'forest');
    document.body.classList.toggle('theme-cherry', theme === 'cherry');
    document.body.classList.toggle('theme-midnight', theme === 'midnight');
  }
})();










