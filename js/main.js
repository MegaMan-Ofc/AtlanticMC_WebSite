"use strict";

const NOTICE_DURATION_MS = 2500;
let noticeTimer = 0;

class RequestError extends Error {
    constructor(message, payload = null) {
        super(message);
        this.name = "RequestError";
        this.payload = payload;
    }
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
        throw new RequestError("O servidor devolveu uma resposta inválida.");
    }

    if (!response.ok) {
        throw new RequestError(
            payload?.error || "Não foi possível concluir o pedido.",
            payload
        );
    }

    return payload;
}

async function handleCartSubmit(event, form) {
    event.preventDefault();

    const submitter = event.submitter instanceof HTMLButtonElement
        ? event.submitter
        : null;
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

    try {
        const payload = await requestJson(endpoint, formData);
        updateCartCount(payload.data?.cart_count);
        replaceCartPanel(payload.data?.cart_html);
        showNotice(payload.message || "Carrinho atualizado.", "success");
    } catch (error) {
        showNotice(
            error instanceof Error ? error.message : "Não foi possível atualizar o carrinho.",
            "error"
        );
    } finally {
        if (submitter?.isConnected) {
            setButtonBusy(submitter, false);
        }
    }
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
            throw new RequestError("O servidor não devolveu o endereço de pagamento.");
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
            error instanceof Error ? error.message : "Não foi possível iniciar o pagamento.",
            "error"
        );
        setButtonBusy(submitter, false);
    }
}

document.addEventListener("click", async (event) => {
    const languageButton = event.target.closest(".language-button");

    if (languageButton) {
        const flag = languageButton.querySelector(".language-flag");
        const code = languageButton.querySelector(".language-code");

        if (!flag || !code) {
            return;
        }

        const isPortuguese = code.textContent.trim() === "PT";
        code.textContent = isPortuguese ? "ENG" : "PT";
        flag.src = isPortuguese ? flag.dataset.enSrc : flag.dataset.ptSrc;
        languageButton.setAttribute(
            "aria-label",
            isPortuguese ? "Mudar idioma para Português" : "Switch language to English"
        );
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
            label.textContent = "Copied!";
        }

        window.setTimeout(() => {
            if (label && originalLabel !== undefined) {
                label.textContent = originalLabel;
            }
        }, 1200);
    } catch {
        showNotice("Não foi possível copiar o endereço do servidor.", "error");
    }
});

document.addEventListener("submit", (event) => {
    if (!("fetch" in window)) {
        return;
    }

    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
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
