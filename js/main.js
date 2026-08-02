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
    const languageButton = event.target.closest(".language-button");

    if (languageButton) {
        const flag = languageButton.querySelector(".language-flag");
        const code = languageButton.querySelector(".language-code");

        if (!flag || !code) {
            return;
        }

        const isPortuguese = code.textContent.trim() === "PT";

        code.textContent = isPortuguese ? "ENG" : "PT";

        flag.src = isPortuguese
            ? "assets/flag-en.png"
            : "assets/flag-pt.png";

        languageButton.setAttribute(
            "aria-label",
            isPortuguese
                ? "Mudar idioma para Português"
                : "Switch language to English"
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

    const label =
        copyButton.querySelector("strong")
        ?? copyButton.querySelector("span");

    const originalLabel = label?.textContent;

    try {
        await copyText(value);

        if (label) {
            label.textContent = "Copied!";
        }

        copyButton.setAttribute(
            "aria-label",
            "Server address copied"
        );

        window.setTimeout(() => {
            if (label && originalLabel !== undefined) {
                label.textContent = originalLabel;
            }

            copyButton.setAttribute(
                "aria-label",
                "Copy the Minecraft server address"
            );
        }, 1200);
    } catch (error) {
        console.error(error);
    }
});
