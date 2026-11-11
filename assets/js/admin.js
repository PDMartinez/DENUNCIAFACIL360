document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
        document.addEventListener('click', event => {
            if (window.innerWidth <= 850 && sidebar.classList.contains('open') &&
                !sidebar.contains(event.target) && !toggle.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.confirm && !confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
});
