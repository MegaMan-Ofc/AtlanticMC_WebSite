'use strict';


const adminImagePreviewUrls = new WeakMap();

const slugifyAdminValue = value => {
    return value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 100);
};

const updateAdminImagePreview = input => {
    const form = input.closest('form');
    const preview = form?.querySelector('[data-admin-image-preview]');
    const file = input.files?.[0];

    if (!(preview instanceof HTMLElement) || !(file instanceof File)) {
        return;
    }

    const previousUrl = adminImagePreviewUrls.get(input);

    if (previousUrl) {
        URL.revokeObjectURL(previousUrl);
    }

    const objectUrl = URL.createObjectURL(file);
    adminImagePreviewUrls.set(input, objectUrl);

    let image = preview.querySelector('[data-admin-image-preview-image]');

    if (!(image instanceof HTMLImageElement)) {
        image = document.createElement('img');
        image.dataset.adminImagePreviewImage = '';
        image.alt = '';
        preview.replaceChildren(image);
    }

    image.src = objectUrl;
};

const adminFilterControllers = new WeakMap();
const adminFilterTimers = new WeakMap();

const syncAdminDialogState = () => {
    const openDialog = document.querySelector('.admin-dialog[open]');

    document.documentElement.classList.toggle(
        'admin-dialog-open',
        Boolean(openDialog)
    );
};

const findAdminFilterForm = results => {
    for (const form of document.querySelectorAll(
        '[data-admin-filter-form]'
    )) {
        if (form.dataset.resultsTarget === results.id) {
            return form;
        }
    }

    return null;
};

const setAdminFilterLoading = (form, loading) => {
    const results = document.getElementById(
        form.dataset.resultsTarget ?? ''
    );

    form.classList.toggle('is-loading', loading);
    results?.setAttribute('aria-busy', String(loading));
};

const adminFilterParameters = form => {
    return new URLSearchParams(new FormData(form));
};

const cancelAdminFilterTimer = form => {
    const timer = adminFilterTimers.get(form);

    if (timer) {
        window.clearTimeout(timer);
        adminFilterTimers.delete(form);
    }
};

const clearAdminFilterFields = form => {
    for (const field of form.elements) {
        if (
            !(field instanceof HTMLInputElement)
            && !(field instanceof HTMLSelectElement)
        ) {
            continue;
        }

        if (
            field instanceof HTMLInputElement
            && field.type === 'hidden'
        ) {
            continue;
        }

        if (field instanceof HTMLSelectElement) {
            field.selectedIndex = 0;
        } else {
            field.value = '';
        }
    }
};

const loadAdminResults = async (
    form,
    parameters = null,
    historyMode = 'replace'
) => {
    const endpoint = form.dataset.ajaxEndpoint;
    const results = document.getElementById(
        form.dataset.resultsTarget ?? ''
    );
    const count = document.getElementById(
        form.dataset.countTarget ?? ''
    );

    if (!endpoint || !results) {
        form.submit();
        return;
    }

    cancelAdminFilterTimer(form);
    adminFilterControllers.get(form)?.abort();

    const controller = new AbortController();

    const requestParameters = parameters instanceof URLSearchParams
        ? new URLSearchParams(parameters)
        : adminFilterParameters(form);

    adminFilterControllers.set(form, controller);
    setAdminFilterLoading(form, true);

    try {
        const requestUrl = new URL(
            endpoint,
            window.location.href
        );

        requestUrl.search = requestParameters.toString();

        const response = await fetch(
            requestUrl,
            {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            }
        );

        const payload = await response.json();

        if (!response.ok) {
            const redirectUrl = payload?.data?.redirect_url;

            if (typeof redirectUrl === 'string') {
                window.location.assign(redirectUrl);
                return;
            }

            throw new Error(
                payload?.error ?? 'Unable to load results.'
            );
        }

        const html = payload?.data?.html;
        const nextUrlValue = payload?.data?.url;
        const countLabel = payload?.data?.count_label;

        if (typeof html !== 'string') {
            throw new Error(
                'Invalid administrator response.'
            );
        }

        results.innerHTML = html;

        if (
            count
            && typeof countLabel === 'string'
        ) {
            count.textContent = countLabel;
        }

        if (typeof nextUrlValue === 'string') {
            const nextUrl = new URL(
                nextUrlValue,
                window.location.href
            );

            if (nextUrl.origin === window.location.origin) {
                window.history[
                    historyMode === 'push'
                        ? 'pushState'
                        : 'replaceState'
                ](
                    {
                        adminFilter: true,
                    },
                    '',
                    nextUrl
                );
            }
        }
    } catch (error) {
        if (
            error instanceof DOMException
            && error.name === 'AbortError'
        ) {
            return;
        }

        const fallbackUrl = new URL(
            form.action,
            window.location.href
        );

        fallbackUrl.search = requestParameters.toString();

        window.location.assign(fallbackUrl);
    } finally {
        if (adminFilterControllers.get(form) === controller) {
            adminFilterControllers.delete(form);
            setAdminFilterLoading(form, false);
        }
    }
};

const queueAdminFilter = form => {
    const previousTimer = adminFilterTimers.get(form);

    if (previousTimer) {
        window.clearTimeout(previousTimer);
    }

    const timer = window.setTimeout(
        () => {
            adminFilterTimers.delete(form);
            loadAdminResults(form);
        },
        350
    );

    adminFilterTimers.set(form, timer);
};

document.addEventListener('click', event => {
    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    const clearButton = target.closest(
        '[data-admin-filter-clear]'
    );

    if (clearButton) {
        const form = clearButton.closest(
            '[data-admin-filter-form]'
        );

        if (form instanceof HTMLFormElement) {
            event.preventDefault();
            clearAdminFilterFields(form);
            loadAdminResults(form, null, 'push');
        }

        return;
    }

    const paginationLink = target.closest(
        '[data-admin-pagination] a'
    );

    if (paginationLink instanceof HTMLAnchorElement) {
        const results = paginationLink.closest(
            '[data-admin-results]'
        );

        if (results instanceof HTMLElement) {
            const form = findAdminFilterForm(results);

            if (form instanceof HTMLFormElement) {
                event.preventDefault();

                const pageUrl = new URL(
                    paginationLink.href,
                    window.location.href
                );

                loadAdminResults(
                    form,
                    pageUrl.searchParams,
                    'push'
                );
            }
        }

        return;
    }

    const opener = target.closest('[data-dialog-open]');

    if (opener) {
        const dialogId = opener.dataset.dialogOpen;

        const dialog = dialogId
            ? document.getElementById(dialogId)
            : null;

        if (dialog instanceof HTMLDialogElement) {
            dialog.showModal();
            syncAdminDialogState();
        }

        return;
    }

    const closer = target.closest('[data-dialog-close]');

    if (closer) {
        const dialog = closer.closest('dialog');

        if (dialog instanceof HTMLDialogElement) {
            dialog.close();
        }

        return;
    }

    if (
        target instanceof HTMLDialogElement
        && target.matches('.admin-dialog')
    ) {
        target.close();
    }
});

document.addEventListener(
    'close',
    event => {
        if (
            event.target instanceof HTMLDialogElement
            && event.target.matches('.admin-dialog')
        ) {
            syncAdminDialogState();
        }
    },
    true
);

document.addEventListener('submit', event => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (form.matches('[data-admin-filter-form]')) {
        event.preventDefault();
        loadAdminResults(form, null, 'push');
        return;
    }

    const submitter = event.submitter;

    if (
        !(submitter instanceof HTMLElement)
        || !submitter.matches(
            '[data-delete-coupon], [data-delete-product], [data-delete-category]'
        )
    ) {
        return;
    }

    const confirmationMessage = form.dataset.confirmDelete;

    if (
        confirmationMessage
        && !window.confirm(confirmationMessage)
    ) {
        event.preventDefault();
    }
});

document.addEventListener('input', event => {
    const field = event.target;

    if (!(field instanceof HTMLInputElement)) {
        return;
    }

    if (field.matches('[data-admin-slug-target]')) {
        field.dataset.adminSlugManual = '1';
    }

    if (field.matches('[data-admin-slug-source]')) {
        const form = field.closest('form');
        const target = form?.querySelector('[data-admin-slug-target]');

        if (
            target instanceof HTMLInputElement
            && target.dataset.adminSlugManual !== '1'
        ) {
            target.value = slugifyAdminValue(field.value);
        }
    }

    if (
        field.type === 'hidden'
        || field.type === 'date'
        || field.type === 'file'
    ) {
        return;
    }

    const form = field.closest(
        '[data-admin-filter-form]'
    );

    if (form instanceof HTMLFormElement) {
        queueAdminFilter(form);
    }
});

document.addEventListener('change', event => {
    const field = event.target;

    if (
        field instanceof HTMLInputElement
        && field.type === 'file'
        && field.matches('[data-admin-image-input]')
    ) {
        updateAdminImagePreview(field);
        return;
    }

    if (
        !(field instanceof HTMLSelectElement)
        && !(
            field instanceof HTMLInputElement
            && field.type === 'date'
        )
    ) {
        return;
    }

    const form = field.closest(
        '[data-admin-filter-form]'
    );

    if (form instanceof HTMLFormElement) {
        loadAdminResults(form);
    }
});

window.addEventListener('popstate', () => {
    if (
        document.querySelector('[data-admin-filter-form]')
    ) {
        window.location.reload();
    }
});
