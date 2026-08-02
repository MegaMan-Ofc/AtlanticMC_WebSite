"use strict";

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

document.addEventListener("click", async (event) => {
    const languageButton = event.target.closest("[data-language-toggle]");

    if (languageButton) {
        const label = languageButton.querySelector("[data-language-label]");
        const flag = languageButton.querySelector("[data-language-flag]");
        const isPortuguese = label?.textContent.trim() === "PT";

        if (label) {
            label.textContent = isPortuguese ? "ENG" : "PT";
        }

        if (flag) {
            flag.textContent = isPortuguese ? "🇬🇧" : "🇵🇹";
        }

        languageButton.setAttribute(
            "aria-label",
            isPortuguese ? "Change language to Portuguese" : "Change language to English"
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

        copyButton.setAttribute("aria-label", "Server address copied");

        window.setTimeout(() => {
            if (label && originalLabel !== undefined) {
                label.textContent = originalLabel;
            }

            copyButton.setAttribute("aria-label", "Copy the Minecraft server address");
        }, 1200);
    } catch (error) {
        console.error(error);
    }
});
