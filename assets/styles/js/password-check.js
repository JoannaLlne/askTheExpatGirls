// PASSWORD VALIDATION
document.querySelector('.registration-card').addEventListener('input', function () {
    validateForm();
});

function validateForm() {
    const password = document.getElementById('registration_form_plainPassword_first');
    const confirmPassword = document.getElementById('registration_form_plainPassword_second');
    const submitBtn = document.querySelector('.btn-register');
    const errorElement = document.getElementById('passwordError');

    if (!password || !confirmPassword) return; // sécurité si les champs n'existent pas encore

    let isValid = password.value.length > 0 && password.value === confirmPassword.value;

    if (!isValid) {
        errorElement.textContent = 'Passwords do not match';
        errorElement.classList.remove('success');
        errorElement.classList.add('error');
    } else {
        errorElement.textContent = 'Passwords match';
        errorElement.classList.remove('error');
        errorElement.classList.add('success');
    }

    submitBtn.disabled = !isValid;
    submitBtn.classList.toggle('enabled', isValid);
}
