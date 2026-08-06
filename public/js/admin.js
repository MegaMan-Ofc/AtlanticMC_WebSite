'use strict';

const syncAdminDialogState = () => {
    const openDialog = document.querySelector('.admin-dialog[open]');

    document.documentElement.classList.toggle(
        'admin-dialog-open',
        Boolean(openDialog)
    );
};

document.addEventListener('click', event => {
    const opener = event.target.closest('[data-dialog-open]');

    if (opener) {
        const dialogId = opener.dataset.dialogOpen;
        const dialog = document.getElementById(dialogId);

        if (dialog instanceof HTMLDialogElement) {
            dialog.showModal();
            syncAdminDialogState();
        }

        return;
    }

    const closer = event.target.closest('[data-dialog-close]');

    if (!closer) {
        return;
    }

    const dialog = closer.closest('dialog');

    if (dialog instanceof HTMLDialogElement) {
        dialog.close();
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

    if (
        !(submitter instanceof HTMLElement)
        || !submitter.matches('[data-delete-coupon], [data-delete-product]')
    ) {
        return;
    }

    const form = submitter.closest('form');
    const confirmationMessage = form?.dataset.confirmDelete;

    if (
        confirmationMessage
        && !window.confirm(confirmationMessage)
    ) {
        event.preventDefault();
    }
});
