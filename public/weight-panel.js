document.querySelectorAll('[data-weight-panel]').forEach((panel) => {
    const form = panel.querySelector('[data-weight-form]');
    const toggle = panel.querySelector('[data-weight-toggle]');

    function openPanel() {
        panel.classList.add('editing');
    }

    panel.addEventListener('click', (event) => {
        if (event.target.closest('form')) {
            return;
        }

        openPanel();
    });

    if (toggle) {
        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            panel.classList.toggle('editing');
        });
    }

    if (form) {
        form.addEventListener('click', (event) => event.stopPropagation());
    }
});
