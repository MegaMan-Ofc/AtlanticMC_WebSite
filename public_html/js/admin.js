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


const setAdminDiscountState = (control, enabled) => {
    const state = control.querySelector('[data-admin-discount-enabled]');
    const fields = control.querySelector('[data-admin-discount-fields]');
    const price = control.querySelector('[data-admin-discount-price]');
    const toggle = control.querySelector('[data-admin-discount-toggle]');
    const label = control.querySelector('[data-admin-discount-toggle-label]');

    if (!(state instanceof HTMLInputElement)
        || !(fields instanceof HTMLElement)
        || !(price instanceof HTMLInputElement)
        || !(toggle instanceof HTMLButtonElement)) {
        return;
    }

    state.value = enabled ? '1' : '0';
    fields.hidden = !enabled;
    price.disabled = !enabled;
    price.required = enabled;
    toggle.setAttribute('aria-pressed', String(enabled));
    toggle.classList.toggle('button--primary', enabled);
    toggle.classList.toggle('button--ghost', !enabled);
    control.classList.toggle('is-active', enabled);

    if (label instanceof HTMLElement) {
        label.textContent = enabled
            ? (toggle.dataset.disableLabel ?? '')
            : (toggle.dataset.enableLabel ?? '');
    }

    if (enabled) {
        price.focus();
    }
};

document.addEventListener('click', event => {
    const toggle = event.target.closest('[data-admin-discount-toggle]');

    if (!(toggle instanceof HTMLButtonElement)) {
        return;
    }

    const control = toggle.closest('[data-admin-discount-control]');

    if (!(control instanceof HTMLElement)) {
        return;
    }

    setAdminDiscountState(
        control,
        toggle.getAttribute('aria-pressed') !== 'true'
    );
});

const adminRecommendedDialog = document.getElementById('admin-recommended-dialog');
const adminRecommendedGrid = document.querySelector('[data-admin-recommended-grid]');
let adminRecommendedDraggedSlot = null;

const updateAdminRecommendedSlotMetadata = () => {
    if (!(adminRecommendedGrid instanceof HTMLElement)) {
        return;
    }

    const template = adminRecommendedGrid.dataset.slotTemplate ?? 'Slot :slot';

    [...adminRecommendedGrid.querySelectorAll('[data-admin-recommended-slot]')]
        .forEach((slot, index) => {
            if (!(slot instanceof HTMLElement)) {
                return;
            }

            const position = index + 1;
            const label = slot.querySelector('[data-admin-recommended-slot-label]');
            const editButton = slot.querySelector('[data-admin-recommended-edit]');
            const slotInput = slot.querySelector('[data-admin-recommended-slot-input]');

            if (label instanceof HTMLElement) {
                label.textContent = template.replace(':slot', String(position));
            }

            if (editButton instanceof HTMLElement) {
                editButton.dataset.slot = String(position);
            }

            if (slotInput instanceof HTMLInputElement) {
                slotInput.value = String(position);
            }
        });
};

const setAdminRecommendedState = (message, isError = false) => {
    const target = document.querySelector('[data-admin-recommended-state]');

    if (!(target instanceof HTMLElement)) {
        return;
    }

    target.textContent = message;
    target.classList.toggle('is-error', isError);
};

const saveAdminRecommendedOrder = async () => {
    if (!(adminRecommendedGrid instanceof HTMLElement)) {
        return;
    }

    const endpoint = adminRecommendedGrid.dataset.reorderUrl;
    const csrfToken = adminRecommendedGrid.dataset.csrfToken;

    if (!endpoint || !csrfToken) {
        return;
    }

    const productIds = [...adminRecommendedGrid.querySelectorAll('[data-admin-recommended-slot]')]
        .map(slot => slot instanceof HTMLElement ? (slot.dataset.productId ?? '0') : '0');

    setAdminRecommendedState(adminRecommendedGrid.dataset.savingLabel ?? '');

    try {
        const body = new FormData();
        body.set('csrf_token', csrfToken);
        body.set('product_ids', productIds.join(','));

        const response = await fetch(endpoint, {
            method: 'POST',
            body,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const payload = await response.json();

        if (!response.ok || payload?.ok !== true) {
            throw new Error(payload?.error ?? 'Unable to save order.');
        }

        setAdminRecommendedState(adminRecommendedGrid.dataset.savedLabel ?? '');
    } catch (error) {
        setAdminRecommendedState(adminRecommendedGrid.dataset.errorLabel ?? '', true);
        window.setTimeout(() => window.location.reload(), 1200);
    }
};

document.addEventListener('click', event => {
    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    const editButton = target.closest('[data-admin-recommended-edit]');

    if (!(editButton instanceof HTMLElement)
        || !(adminRecommendedDialog instanceof HTMLDialogElement)) {
        return;
    }

    const slotField = adminRecommendedDialog.querySelector('[data-admin-recommended-dialog-slot]');
    const productSelect = adminRecommendedDialog.querySelector('[data-admin-recommended-product-select]');

    if (slotField instanceof HTMLInputElement) {
        slotField.value = editButton.dataset.slot ?? '1';
    }

    if (productSelect instanceof HTMLSelectElement) {
        const productId = editButton.dataset.productId ?? '';
        const optionExists = [...productSelect.options].some(option => option.value === productId);

        if (optionExists) {
            productSelect.value = productId;
        } else if (productSelect.options.length > 0) {
            productSelect.selectedIndex = 0;
        }
    }

    adminRecommendedDialog.showModal();
    syncAdminDialogState();
});

if (adminRecommendedGrid instanceof HTMLElement) {
    adminRecommendedGrid.dataset.slotTemplate = adminRecommendedGrid.querySelector('[data-admin-recommended-slot-label]')?.textContent
        ?.replace(/\d+/, ':slot')
        .trim() ?? 'Slot :slot';

    adminRecommendedGrid.addEventListener('dragstart', event => {
        const slot = event.target instanceof Element
            ? event.target.closest('[data-admin-recommended-slot]')
            : null;

        if (!(slot instanceof HTMLElement) || slot.dataset.productId === '0') {
            event.preventDefault();
            return;
        }

        adminRecommendedDraggedSlot = slot;
        slot.classList.add('is-dragging');
        event.dataTransfer?.setData('text/plain', slot.dataset.productId ?? '');
        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
        }
    });

    adminRecommendedGrid.addEventListener('dragover', event => {
        if (!(adminRecommendedDraggedSlot instanceof HTMLElement)) {
            return;
        }

        const target = event.target instanceof Element
            ? event.target.closest('[data-admin-recommended-slot]')
            : null;

        if (!(target instanceof HTMLElement) || target === adminRecommendedDraggedSlot) {
            return;
        }

        event.preventDefault();
        adminRecommendedGrid.querySelectorAll('.is-drag-target').forEach(slot => slot.classList.remove('is-drag-target'));
        target.classList.add('is-drag-target');
    });

    adminRecommendedGrid.addEventListener('drop', event => {
        if (!(adminRecommendedDraggedSlot instanceof HTMLElement)) {
            return;
        }

        const target = event.target instanceof Element
            ? event.target.closest('[data-admin-recommended-slot]')
            : null;

        if (!(target instanceof HTMLElement) || target === adminRecommendedDraggedSlot) {
            return;
        }

        event.preventDefault();
        const targetRect = target.getBoundingClientRect();
        const insertAfter = event.clientX > targetRect.left + (targetRect.width / 2)
            || event.clientY > targetRect.top + (targetRect.height / 2);

        adminRecommendedGrid.insertBefore(
            adminRecommendedDraggedSlot,
            insertAfter ? target.nextSibling : target
        );

        updateAdminRecommendedSlotMetadata();
        saveAdminRecommendedOrder();
    });

    adminRecommendedGrid.addEventListener('dragend', () => {
        adminRecommendedGrid.querySelectorAll('.is-dragging, .is-drag-target')
            .forEach(slot => slot.classList.remove('is-dragging', 'is-drag-target'));
        adminRecommendedDraggedSlot = null;
    });
}

const adminHomeLayout = document.querySelector('[data-admin-home-layout]');
let adminHomeDraggedCategory = null;
let adminHomeDragOrigin = null;
let adminHomeDragNextSibling = null;

const adminHomeDirectCategoryCards = zone => {
    if (!(zone instanceof HTMLElement)) {
        return [];
    }

    return [...zone.children].filter(child => child instanceof HTMLElement && child.matches('[data-admin-home-category]'));
};

const updateAdminHomeLayoutEmptyStates = () => {
    if (!(adminHomeLayout instanceof HTMLElement)) {
        return;
    }

    adminHomeLayout.querySelectorAll('[data-admin-home-zone]').forEach(zone => {
        if (!(zone instanceof HTMLElement)) {
            return;
        }

        const emptyState = [...zone.children].find(child => child instanceof HTMLElement && child.matches('[data-admin-home-empty]'));
        const hasCategory = adminHomeDirectCategoryCards(zone).length > 0;

        zone.classList.toggle('has-category', hasCategory);

        if (emptyState instanceof HTMLElement) {
            emptyState.hidden = hasCategory;
        }
    });
};

const setAdminHomeLayoutState = (message, isError = false) => {
    const target = document.querySelector('[data-admin-home-layout-state]');

    if (!(target instanceof HTMLElement)) {
        return;
    }

    target.textContent = message;
    target.classList.toggle('is-error', isError);
};

const saveAdminHomeLayout = async () => {
    if (!(adminHomeLayout instanceof HTMLElement)) {
        return;
    }

    const endpoint = adminHomeLayout.dataset.saveUrl;
    const csrfToken = adminHomeLayout.dataset.csrfToken;
    const topZone = adminHomeLayout.querySelector('[data-admin-home-zone="top"]');
    const gridZone = adminHomeLayout.querySelector('[data-admin-home-zone="grid"]');
    const bottomZone = adminHomeLayout.querySelector('[data-admin-home-zone="bottom"]');

    if (!endpoint || !csrfToken
        || !(topZone instanceof HTMLElement)
        || !(gridZone instanceof HTMLElement)
        || !(bottomZone instanceof HTMLElement)) {
        return;
    }

    const topCategory = adminHomeDirectCategoryCards(topZone)[0] ?? null;
    const bottomCategory = adminHomeDirectCategoryCards(bottomZone)[0] ?? null;
    const gridIds = adminHomeDirectCategoryCards(gridZone).map(card => card.dataset.categoryId ?? '');

    setAdminHomeLayoutState(adminHomeLayout.dataset.savingLabel ?? '');

    try {
        const body = new FormData();
        body.set('csrf_token', csrfToken);
        body.set('top_category_id', topCategory instanceof HTMLElement ? (topCategory.dataset.categoryId ?? '0') : '0');
        body.set('grid_category_ids', gridIds.join(','));
        body.set('bottom_category_id', bottomCategory instanceof HTMLElement ? (bottomCategory.dataset.categoryId ?? '0') : '0');

        const response = await fetch(endpoint, {
            method: 'POST',
            body,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const payload = await response.json();

        if (!response.ok || payload?.ok !== true) {
            throw new Error(payload?.error ?? 'Unable to save homepage category layout.');
        }

        setAdminHomeLayoutState(adminHomeLayout.dataset.savedLabel ?? '');
    } catch (error) {
        setAdminHomeLayoutState(adminHomeLayout.dataset.errorLabel ?? '', true);
        window.setTimeout(() => window.location.reload(), 1200);
    }
};

const clearAdminHomeDragTargets = () => {
    if (!(adminHomeLayout instanceof HTMLElement)) {
        return;
    }

    adminHomeLayout.querySelectorAll('.is-drag-target')
        .forEach(element => element.classList.remove('is-drag-target'));
};

if (adminHomeLayout instanceof HTMLElement) {
    updateAdminHomeLayoutEmptyStates();

    adminHomeLayout.addEventListener('dragstart', event => {
        const category = event.target instanceof Element
            ? event.target.closest('[data-admin-home-category]')
            : null;

        if (!(category instanceof HTMLElement)) {
            return;
        }

        const origin = category.parentElement;

        if (!(origin instanceof HTMLElement) || !origin.matches('[data-admin-home-zone]')) {
            event.preventDefault();
            return;
        }

        adminHomeDraggedCategory = category;
        adminHomeDragOrigin = origin;
        adminHomeDragNextSibling = category.nextElementSibling;
        category.classList.add('is-dragging');

        event.dataTransfer?.setData('text/plain', category.dataset.categoryId ?? '');
        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
        }
    });

    adminHomeLayout.addEventListener('dragover', event => {
        if (!(adminHomeDraggedCategory instanceof HTMLElement)) {
            return;
        }

        const zone = event.target instanceof Element
            ? event.target.closest('[data-admin-home-zone]')
            : null;

        if (!(zone instanceof HTMLElement)) {
            return;
        }

        event.preventDefault();
        clearAdminHomeDragTargets();

        const targetCategory = event.target instanceof Element
            ? event.target.closest('[data-admin-home-category]')
            : null;

        if (zone.dataset.adminHomeZone === 'grid'
            && targetCategory instanceof HTMLElement
            && targetCategory !== adminHomeDraggedCategory) {
            targetCategory.classList.add('is-drag-target');
        } else {
            zone.classList.add('is-drag-target');
        }
    });

    adminHomeLayout.addEventListener('drop', event => {
        if (!(adminHomeDraggedCategory instanceof HTMLElement)
            || !(adminHomeDragOrigin instanceof HTMLElement)) {
            return;
        }

        const targetZone = event.target instanceof Element
            ? event.target.closest('[data-admin-home-zone]')
            : null;

        if (!(targetZone instanceof HTMLElement)) {
            return;
        }

        event.preventDefault();
        const targetType = targetZone.dataset.adminHomeZone ?? 'grid';
        const sourceType = adminHomeDragOrigin.dataset.adminHomeZone ?? 'grid';

        if (targetZone === adminHomeDragOrigin && targetType !== 'grid') {
            clearAdminHomeDragTargets();
            return;
        }

        if (targetType === 'grid') {
            const targetCategory = event.target instanceof Element
                ? event.target.closest('[data-admin-home-category]')
                : null;

            if (targetCategory instanceof HTMLElement && targetCategory !== adminHomeDraggedCategory) {
                const rect = targetCategory.getBoundingClientRect();
                const insertAfter = event.clientX > rect.left + (rect.width / 2)
                    || event.clientY > rect.top + (rect.height / 2);

                targetZone.insertBefore(
                    adminHomeDraggedCategory,
                    insertAfter ? targetCategory.nextSibling : targetCategory
                );
            } else {
                targetZone.appendChild(adminHomeDraggedCategory);
            }
        } else {
            const occupyingCategory = adminHomeDirectCategoryCards(targetZone)
                .find(category => category !== adminHomeDraggedCategory) ?? null;

            if (occupyingCategory instanceof HTMLElement) {
                if (sourceType === 'grid') {
                    const reference = adminHomeDragNextSibling instanceof Element
                        && adminHomeDragNextSibling.parentElement === adminHomeDragOrigin
                        ? adminHomeDragNextSibling
                        : null;
                    adminHomeDragOrigin.insertBefore(occupyingCategory, reference);
                } else {
                    adminHomeDragOrigin.appendChild(occupyingCategory);
                }
            }

            targetZone.appendChild(adminHomeDraggedCategory);
        }

        clearAdminHomeDragTargets();
        updateAdminHomeLayoutEmptyStates();
        saveAdminHomeLayout();
    });

    adminHomeLayout.addEventListener('dragend', () => {
        adminHomeLayout.querySelectorAll('.is-dragging')
            .forEach(element => element.classList.remove('is-dragging'));
        clearAdminHomeDragTargets();
        adminHomeDraggedCategory = null;
        adminHomeDragOrigin = null;
        adminHomeDragNextSibling = null;
    });
}

const loadAdminTraffic = async days => {
    const adminTrafficWidget = document.querySelector('[data-admin-traffic-widget]');

    if (!(adminTrafficWidget instanceof HTMLElement)) {
        return;
    }

    const endpoint = adminTrafficWidget.dataset.endpoint;

    if (!endpoint) {
        return;
    }

    const url = new URL(endpoint, window.location.href);
    url.searchParams.set('days', String(days));
    adminTrafficWidget.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const payload = await response.json();
        const html = payload?.data?.html;

        if (!response.ok || payload?.ok !== true || typeof html !== 'string') {
            throw new Error(payload?.error ?? 'Unable to load traffic analytics.');
        }

        adminTrafficWidget.innerHTML = html;
    } catch (error) {
        const fallback = new URL(window.location.href);
        fallback.searchParams.set('section', 'overview');
        window.location.assign(fallback);
    } finally {
        if (adminTrafficWidget.isConnected) {
            adminTrafficWidget.removeAttribute('aria-busy');
        }
    }
};

const loadAdminAnalytics = async days => {
    const dashboard = document.querySelector('[data-admin-analytics-dashboard]');

    if (!(dashboard instanceof HTMLElement)) {
        return;
    }

    const endpoint = dashboard.dataset.endpoint;

    if (!endpoint) {
        return;
    }

    const url = new URL(endpoint, window.location.href);
    url.searchParams.set('days', String(days));
    dashboard.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const payload = await response.json();
        const html = payload?.data?.html;

        if (!response.ok || payload?.ok !== true || typeof html !== 'string') {
            throw new Error(payload?.error ?? 'Unable to load analytics.');
        }

        dashboard.innerHTML = html;

        const browserUrl = new URL(window.location.href);
        browserUrl.searchParams.set('section', 'overview');
        browserUrl.searchParams.set('analytics_days', String(payload?.data?.days ?? days));
        window.history.replaceState({}, '', browserUrl);
    } catch (error) {
        const fallback = new URL(window.location.href);
        fallback.searchParams.set('section', 'overview');
        fallback.searchParams.set('analytics_days', String(days));
        window.location.assign(fallback);
    } finally {
        if (dashboard.isConnected) {
            dashboard.removeAttribute('aria-busy');
        }
    }
};

document.addEventListener('click', event => {
    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    const trafficToggle = target.closest('[data-admin-traffic-toggle]');

    if (trafficToggle instanceof HTMLButtonElement) {
        const days = Number.parseInt(trafficToggle.dataset.days ?? '7', 10);
        loadAdminTraffic(Number.isFinite(days) ? days : 7);
        return;
    }

    const periodButton = target.closest('[data-admin-analytics-period]');

    if (periodButton instanceof HTMLButtonElement) {
        const days = Number.parseInt(periodButton.dataset.adminAnalyticsPeriod ?? '30', 10);
        loadAdminAnalytics(Number.isFinite(days) ? days : 30);
    }
});
