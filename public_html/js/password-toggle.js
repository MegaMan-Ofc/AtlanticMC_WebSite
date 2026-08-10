'use strict';

document.querySelectorAll('[data-password-toggle]').forEach(button => {
    const inputId = button.getAttribute('aria-controls');
    const input = document.getElementById(inputId);

    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    button.addEventListener('click', () => {
        const showPassword = input.type === 'password';
        const label = showPassword
            ? button.dataset.hideLabel
            : button.dataset.showLabel;

        input.type = showPassword ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(showPassword));
        button.setAttribute('aria-label', label ?? '');
        button.title = label ?? '';

        const icon = button.querySelector('i');
        icon?.classList.toggle('fa-eye', !showPassword);
        icon?.classList.toggle('fa-eye-slash', showPassword);
        input.focus();
    });
});
