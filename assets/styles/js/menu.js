document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.nav-li-dropdown').forEach((item) => {
        const toggle = item.querySelector('.nav-dropdown-toggle');

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = item.classList.contains('open');

            document.querySelectorAll('.nav-li-dropdown.open').forEach((el) => {
                if (el !== item) el.classList.remove('open');
            });

            item.classList.toggle('open', !isOpen);
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.nav-li-dropdown.open').forEach((el) => {
            el.classList.remove('open');
        });
    });
});
