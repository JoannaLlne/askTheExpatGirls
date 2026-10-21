// Mot de passe visible / caché
function initPasswordToggle() {
    document.querySelectorAll('.password-wrapper').forEach(wrapper => {
        const input = wrapper.querySelector('input');
        const toggle = wrapper.querySelector('.toggle-password');

        if (!input || !toggle || toggle.dataset.bound) {
            return;
        }
        toggle.dataset.bound = 'true';

        const eyeIcon = toggle.querySelector('.fa-eye');
        const eyeSlashIcon = toggle.querySelector('.fa-eye-slash');

        toggle.addEventListener('click', () => {
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';

            eyeIcon.style.display = isHidden ? 'none' : 'inline-block';
            eyeSlashIcon.style.display = isHidden ? 'inline-block' : 'none';
            toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
    });
}

function initCharCounter() {
    document.querySelectorAll('[data-char-counter]').forEach(field => {
        const maxLength = parseInt(field.dataset.charCounter, 10);
        const counterId = field.dataset.charCounterTarget;
        const counter = document.getElementById(counterId);

        if (!counter || field.dataset.bound) {
            return;
        }
        field.dataset.bound = 'true';

        const updateCounter = () => {
            const remaining = maxLength - field.value.length;
            counter.textContent = `${field.value.length} / ${maxLength}`;
            counter.classList.toggle('char-counter--warning', remaining <= 20);
        };

        field.addEventListener('input', updateCounter);
        updateCounter(); // valeur initiale, au cas où le champ est pré-rempli
    });
}

function initScrollTop() {
    const button = document.getElementById('scroll-top');
    if (!button || button.dataset.bound) {
        return;
    }
    button.dataset.bound = 'true';

    window.addEventListener('scroll', () => {
        button.classList.toggle('scroll-top--visible', window.scrollY > 400);
    });

    button.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

function initFonctionnalites() {
    initPasswordToggle();
    initCharCounter();
    initScrollTop();
}

// Chargement classique
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFonctionnalites);
} else {
    initFonctionnalites();
}

// Navigation avec Symfony UX Turbo
document.addEventListener('turbo:load', initFonctionnalites);
