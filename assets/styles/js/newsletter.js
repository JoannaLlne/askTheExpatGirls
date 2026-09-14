// assets/newsletter.js
document.addEventListener('submit', async (e) => {
    const form = e.target.closest('[data-newsletter-form]');
    if (!form) return;

    e.preventDefault();

    const widget = form.closest('[data-newsletter]');
    const messages = widget.querySelector('[data-newsletter-messages]');
    const submitBtn = form.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        const data = await response.json();

        if (data.success) {
            messages.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            form.reset();
        } else {
            messages.innerHTML = data.errors
                .map(err => `<div class="alert alert-error">${err}</div>`)
                .join('');
        }
    } catch (err) {
        messages.innerHTML = `<div class="alert alert-error">Something went wrong, try again</div>`;
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = "Subscribe";
    }
});
