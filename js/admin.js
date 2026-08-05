'use strict';

const syncAdminDialogState = () => {
    document.documentElement.classList.toggle('admin-dialog-open', Boolean(document.querySelector('.admin-dialog[open]')));
};

document.addEventListener('click', event => {
    const opener = event.target.closest('[data-dialog-open]');

    if (opener) {
        const dialog = document.getElementById(opener.dataset.dialogOpen);

        if (dialog instanceof HTMLDialogElement) {
            dialog.showModal();
            syncAdminDialogState();
        }

        return;
    }

    const closer = event.target.closest('[data-dialog-close]');

    if (closer) {
        const dialog = closer.closest('dialog');

        if (dialog instanceof HTMLDialogElement) {
            dialog.close();
        }
    }
});

document.querySelectorAll('.admin-dialog').forEach(dialog => {
    dialog.addEventListener('click', event => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    dialog.addEventListener('close', syncAdminDialogState);
});

document.addEventListener('submit', event => {
    const submitter = event.submitter;

    if (!(submitter instanceof HTMLElement) || !submitter.matches('[data-delete-coupon]')) {
        return;
    }

    const form = submitter.closest('form');
    const message = form?.dataset.confirmDelete;

    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});
