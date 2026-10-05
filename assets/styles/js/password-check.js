// PASSWORD VALIDATION
document.addEventListener('input', function (event) {
    if (event.target.closest('.registration-card')) {
        validateForm();
    }
});

function validateForm() {
    const password = document.querySelector('#registration_form_plainPassword_first, #user_password_first');
    const confirmPassword = document.querySelector('#registration_form_plainPassword_second, #user_password_second');
    const submitBtn = document.querySelector('.btn-register, .btnSave');
    const errorElement = document.getElementById('passwordError');

    if (!password || !confirmPassword || !submitBtn || !errorElement) return;

    const isValid = password.value.length > 0 && password.value === confirmPassword.value;

    errorElement.textContent = isValid ? 'Passwords match' : 'Passwords do not match';
    errorElement.classList.toggle('success', isValid);
    errorElement.classList.toggle('error', !isValid);

    submitBtn.disabled = !isValid;
    submitBtn.classList.toggle('enabled', isValid);
}
