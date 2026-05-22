(function () {
    const button = document.querySelector('[data-theme-toggle]');

    function setTheme(theme) {
        document.documentElement.dataset.theme = theme;
        localStorage.setItem('academy-theme', theme);

        if (button) {
            button.textContent = theme === 'dark' ? 'Claro' : 'Dark';
        }
    }

    const savedTheme = localStorage.getItem('academy-theme') || 'light';
    setTheme(savedTheme);

    if (button) {
        button.addEventListener('click', () => {
            setTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
        });
    }
})();
