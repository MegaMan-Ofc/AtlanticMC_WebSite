"use strict";

const NOTICE_DURATION_MS = 2500;
const CART_AUTO_UPDATE_DELAY_MS = 180;
let noticeTimer = 0;
let cartAutoUpdateTimer = 0;
let searchRequestController = null;

class RequestError extends Error {
    constructor(message, payload = null) {
        super(message);
        this.name = "RequestError";
        this.payload = payload;
    }
}

function siteMessage(name, fallback) {
    const notice = document.querySelector("[data-site-notice]");
    const value = notice?.dataset?.[name];

    return typeof value === "string" && value !== "" ? value : fallback;
}

async function copyText(value) {
    if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(value);
        return;
    }

    const input = document.createElement("textarea");
    input.value = value;
    input.setAttribute("readonly", "");
    input.style.position = "fixed";
    input.style.opacity = "0";
    document.body.appendChild(input);
    input.select();

    const copied = document.execCommand("copy");
    input.remove();

    if (!copied) {
        throw new Error("Clipboard access is unavailable.");
    }
}

function showNotice(message, type = "info") {
    const notice = document.querySelector("[data-site-notice]");

    if (!notice || !message) {
        return;
    }

    window.clearTimeout(noticeTimer);
    notice.textContent = message;
    notice.className = `site-notice site-notice--${type}`;
    notice.hidden = false;

    noticeTimer = window.setTimeout(() => {
        notice.hidden = true;
    }, NOTICE_DURATION_MS);
}

function setButtonBusy(button, busy) {
    if (!(button instanceof HTMLButtonElement)) {
        return;
    }

    button.disabled = busy;
    button.setAttribute("aria-busy", busy ? "true" : "false");
}

function updateCartCount(count) {
    const normalizedCount = Math.max(0, Number.parseInt(String(count), 10) || 0);

    document.querySelectorAll("[data-cart-count]").forEach((element) => {
        element.textContent = String(normalizedCount);
        element.hidden = normalizedCount === 0;
    });
}

function replaceCartPanel(html) {
    const currentPanel = document.querySelector("[data-cart-panel]");

    if (!currentPanel || typeof html !== "string" || html.trim() === "") {
        return;
    }

    const template = document.createElement("template");
    template.innerHTML = html.trim();
    const replacement = template.content.firstElementChild;

    if (replacement) {
        currentPanel.replaceWith(replacement);
        initializeQuantitySteppers(replacement);
    }
}

async function requestJson(url, formData) {
    const response = await fetch(url, {
        method: "POST",
        body: formData,
        credentials: "same-origin",
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
        },
    });

    let payload = null;

    try {
        payload = await response.json();
    } catch {
        throw new RequestError(
            siteMessage("messageInvalidResponse", "The server returned an invalid response.")
        );
    }

    if (!response.ok) {
        throw new RequestError(
            payload?.error || siteMessage("messageRequestFailed", "Unable to complete the request."),
            payload
        );
    }

    return payload;
}

function searchUrlFromForm(form) {
    const url = new URL(form.action || window.location.href, window.location.href);
    const parameters = new URLSearchParams();

    new FormData(form).forEach((value, key) => {
        const normalizedValue = String(value).trim();

        if (normalizedValue !== "") {
            parameters.set(key, normalizedValue);
        }
    });

    url.search = parameters.toString();
    return url;
}

async function navigateSearch(url, { pushHistory = true } = {}) {
    const shell = document.querySelector("[data-search-shell]");

    if (!shell) {
        window.location.assign(url);
        return;
    }

    searchRequestController?.abort();
    searchRequestController = new AbortController();
    shell.setAttribute("aria-busy", "true");

    try {
        const response = await fetch(url, {
            credentials: "same-origin",
            headers: {
                Accept: "text/html",
                "X-Requested-With": "XMLHttpRequest",
            },
            signal: searchRequestController.signal,
        });

        if (!response.ok) {
            throw new RequestError(
                siteMessage("messageRequestFailed", "Unable to complete the request.")
            );
        }

        const html = await response.text();
        const documentResult = new DOMParser().parseFromString(html, "text/html");
        const replacement = documentResult.querySelector("[data-search-shell]");

        if (!replacement) {
            throw new RequestError(
                siteMessage("messageInvalidResponse", "The server returned an invalid response.")
            );
        }

        shell.replaceWith(replacement);
        initializeQuantitySteppers(replacement);

        if (documentResult.title) {
            document.title = documentResult.title;
        }

        if (pushHistory) {
            window.history.pushState({ atlanticSearch: true }, "", url);
        }
    } catch (error) {
        if (error instanceof DOMException && error.name === "AbortError") {
            return;
        }

        shell.removeAttribute("aria-busy");
        showNotice(
            error instanceof Error
                ? error.message
                : siteMessage("messageRequestFailed", "Unable to complete the request."),
            "error"
        );
    }
}

async function handleSearchSubmit(event, form) {
    event.preventDefault();
    await navigateSearch(searchUrlFromForm(form));
}

async function handleLanguageSubmit(event, form) {
    event.preventDefault();

    const submitter = event.submitter instanceof HTMLButtonElement
        ? event.submitter
        : form.querySelector('button[type="submit"]');
    const endpoint = form.dataset.ajaxUrl;

    if (!endpoint) {
        form.submit();
        return;
    }

    setButtonBusy(submitter, true);

    try {
        await requestJson(endpoint, new FormData(form));
        window.location.reload();
    } catch (error) {
        showNotice(
            error instanceof Error
                ? error.message
                : siteMessage("messageLanguageFailed", "Unable to change the language."),
            "error"
        );
        setButtonBusy(submitter, false);
    }
}

function setCartPanelBusy(busy) {
    const panel = document.querySelector("[data-cart-panel]");

    if (!panel) {
        return;
    }

    if (busy) {
        panel.setAttribute("aria-busy", "true");
        return;
    }

    panel.removeAttribute("aria-busy");
}

function clampQuantityInput(input, value = input.value) {
    if (!(input instanceof HTMLInputElement)) {
        return 1;
    }

    const min = Number.parseInt(input.min, 10) || 1;
    const max = Number.parseInt(input.max, 10) || Number.MAX_SAFE_INTEGER;
    const parsed = Number.parseInt(String(value), 10);
    const normalized = Math.min(max, Math.max(min, Number.isFinite(parsed) ? parsed : min));

    input.value = String(normalized);

    const stepper = input.closest("[data-quantity-stepper]");
    const decreaseButton = stepper?.querySelector("[data-quantity-decrease]");
    const increaseButton = stepper?.querySelector("[data-quantity-increase]");

    if (decreaseButton instanceof HTMLButtonElement) {
        decreaseButton.disabled = normalized <= min;
    }

    if (increaseButton instanceof HTMLButtonElement) {
        increaseButton.disabled = normalized >= max;
    }

    return normalized;
}

function trackProductCardInteraction(target) {
    if (!(target instanceof Element)) {
        return;
    }

    const interactiveTarget = target.closest('button, a, input, select, [role="button"]');
    const card = target.closest('[data-product-analytics]');

    if (!(interactiveTarget instanceof Element) || !(card instanceof HTMLElement)) {
        return;
    }

    if (card.dataset.analyticsTracked === '1') {
        return;
    }

    const endpoint = card.dataset.productAnalyticsEndpoint;
    const productId = card.dataset.productId;
    const csrfInput = card.querySelector('input[name="csrf_token"]');
    const csrfToken = csrfInput instanceof HTMLInputElement ? csrfInput.value : '';

    if (!endpoint || !productId || !csrfToken) {
        return;
    }

    card.dataset.analyticsTracked = '1';
    const body = new FormData();
    body.set('product_id', productId);
    body.set('csrf_token', csrfToken);

    if (navigator.sendBeacon) {
        navigator.sendBeacon(endpoint, body);
        return;
    }

    fetch(endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        body,
        keepalive: true,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    }).catch(() => {});
}

function initializeQuantitySteppers(root = document) {
    root.querySelectorAll("[data-quantity-input]").forEach((input) => {
        if (input instanceof HTMLInputElement) {
            clampQuantityInput(input);
        }
    });
}

async function submitCartForm(form, submitter = null, { showSuccess = true } = {}) {
    const operation = submitter?.dataset.cartOperation
        || form.dataset.cartOperation
        || "";
    const endpoint = form.dataset.ajaxUrl;

    if (!endpoint || !operation) {
        return;
    }

    const formData = new FormData(form);
    formData.set("operation", operation);
    formData.set("render_cart", form.dataset.renderCart === "1" ? "1" : "0");

    if (submitter?.name) {
        formData.set(submitter.name, submitter.value);
    }

    setButtonBusy(submitter, true);

    if (form.dataset.renderCart === "1") {
        setCartPanelBusy(true);
    }

    try {
        const payload = await requestJson(endpoint, formData);
        updateCartCount(payload.data?.cart_count);
        replaceCartPanel(payload.data?.cart_html);

        if (showSuccess) {
            showNotice(
                payload.message || siteMessage("messageCartUpdated", "Cart updated."),
                "success"
            );
        }
    } catch (error) {
        showNotice(
            error instanceof Error
                ? error.message
                : siteMessage("messageCartFailed", "Unable to update the cart."),
            "error"
        );
    } finally {
        setCartPanelBusy(false);

        if (submitter?.isConnected) {
            setButtonBusy(submitter, false);
        }
    }
}

function scheduleCartAutoUpdate(form) {
    window.clearTimeout(cartAutoUpdateTimer);
    cartAutoUpdateTimer = window.setTimeout(() => {
        void submitCartForm(form, null, { showSuccess: false });
    }, CART_AUTO_UPDATE_DELAY_MS);
}

async function handleCartSubmit(event, form) {
    event.preventDefault();
    window.clearTimeout(cartAutoUpdateTimer);

    const submitter = event.submitter instanceof HTMLButtonElement
        ? event.submitter
        : null;

    await submitCartForm(form, submitter);
}

async function handleCheckoutSubmit(event, form) {
    event.preventDefault();

    const submitter = event.submitter instanceof HTMLButtonElement
        ? event.submitter
        : form.querySelector('button[type="submit"]');
    const endpoint = form.dataset.ajaxUrl;

    if (!endpoint) {
        return;
    }

    setButtonBusy(submitter, true);

    try {
        const payload = await requestJson(endpoint, new FormData(form));
        const redirectUrl = payload.data?.redirect_url;

        if (typeof redirectUrl !== "string" || redirectUrl === "") {
            throw new RequestError(
                siteMessage("messageCheckoutUrlMissing", "The server did not return the payment address.")
            );
        }

        window.location.assign(redirectUrl);
    } catch (error) {
        const redirectUrl = error instanceof RequestError
            ? error.payload?.data?.redirect_url
            : null;

        if (typeof redirectUrl === "string" && redirectUrl !== "") {
            window.location.assign(redirectUrl);
            return;
        }

        showNotice(
            error instanceof Error
                ? error.message
                : siteMessage("messageCheckoutFailed", "Unable to start the payment."),
            "error"
        );
        setButtonBusy(submitter, false);
    }
}

document.addEventListener("click", async (event) => {
    trackProductCardInteraction(event.target);

    const quantityButton = event.target.closest("[data-quantity-increase], [data-quantity-decrease]");

    if (quantityButton instanceof HTMLButtonElement) {
        const stepper = quantityButton.closest("[data-quantity-stepper]");
        const input = stepper?.querySelector("[data-quantity-input]");

        if (input instanceof HTMLInputElement) {
            const current = clampQuantityInput(input);
            const delta = quantityButton.matches("[data-quantity-increase]") ? 1 : -1;
            clampQuantityInput(input, current + delta);
            input.dispatchEvent(new Event("change", { bubbles: true }));
        }

        return;
    }

    const searchLink = event.target.closest("[data-search-link]");

    if (searchLink instanceof HTMLAnchorElement && document.querySelector("[data-search-shell]")) {
        event.preventDefault();
        await navigateSearch(searchLink.href);
        return;
    }

    const copyButton = event.target.closest("[data-copy-value]");

    if (!copyButton) {
        return;
    }

    const value = copyButton.dataset.copyValue?.trim();

    if (!value) {
        return;
    }

    const label = copyButton.querySelector("strong") ?? copyButton.querySelector("span");
    const originalLabel = label?.textContent;

    try {
        await copyText(value);

        if (label) {
            label.textContent = siteMessage("messageCopied", "Copied!");
        }

        window.setTimeout(() => {
            if (label && originalLabel !== undefined) {
                label.textContent = originalLabel;
            }
        }, 1200);
    } catch {
        showNotice(
            siteMessage("messageCopyFailed", "Unable to copy the server address."),
            "error"
        );
    }
});

document.addEventListener("change", (event) => {
    const input = event.target;

    if (!(input instanceof HTMLInputElement) || !input.matches("[data-quantity-input]")) {
        return;
    }

    clampQuantityInput(input);

    const stepper = input.closest("[data-cart-auto-update]");
    const form = input.closest("form[data-ajax-cart]");

    if (stepper && form instanceof HTMLFormElement) {
        scheduleCartAutoUpdate(form);
    }
});

window.addEventListener("popstate", () => {
    const shell = document.querySelector("[data-search-shell]");

    if (!shell) {
        return;
    }

    const searchPath = new URL(shell.dataset.searchPath || window.location.href, window.location.href).pathname;

    if (window.location.pathname === searchPath) {
        void navigateSearch(window.location.href, { pushHistory: false });
        return;
    }

    window.location.reload();
});

document.addEventListener("submit", (event) => {
    if (!("fetch" in window)) {
        return;
    }

    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (form.matches("[data-ajax-language]")) {
        void handleLanguageSubmit(event, form);
        return;
    }

    if (form.matches("[data-ajax-search]")) {
        void handleSearchSubmit(event, form);
        return;
    }

    if (form.matches("[data-ajax-cart]")) {
        void handleCartSubmit(event, form);
        return;
    }

    if (form.matches("[data-ajax-checkout]")) {
        void handleCheckoutSubmit(event, form);
    }
});


initializeQuantitySteppers();
