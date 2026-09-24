function initDropdowns() {
    document.querySelectorAll('.nav-li-dropdown').forEach((item) => {
        const toggle = item.querySelector('.nav-dropdown-toggle');
        if (!toggle || toggle.dataset.bound) return; // évite les doublons si le script tourne 2 fois
        toggle.dataset.bound = 'true';

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = item.classList.contains('open');

            document.querySelectorAll('.nav-li-dropdown.open').forEach((el) => {
                if (el !== item) el.classList.remove('open');
            });

            item.classList.toggle('open', !isOpen);
        });
    });
}

document.addEventListener('DOMContentLoaded', initDropdowns);
document.addEventListener('turbo:load', initDropdowns);

document.addEventListener('click', () => {
    document.querySelectorAll('.nav-li-dropdown.open').forEach((el) => {
        el.classList.remove('open');
    });
});
