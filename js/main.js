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
    const trigger = event.target.closest("[data-copy-value]");

    if (!trigger) {
        return;
    }

    const value = trigger.dataset.copyValue?.trim();

    if (!value) {
        return;
    }

    const label = trigger.querySelector("strong") ?? trigger.querySelector("span");
    const originalLabel = label?.textContent;

    try {
        await copyText(value);

        if (label) {
            label.textContent = "Copied!";
        }

        trigger.setAttribute("aria-label", "Server address copied");

        window.setTimeout(() => {
            if (label && originalLabel !== undefined) {
                label.textContent = originalLabel;
            }

            trigger.setAttribute("aria-label", "Copy the Minecraft server address");
        }, 1200);
    } catch (error) {
        console.error(error);
    }
});
