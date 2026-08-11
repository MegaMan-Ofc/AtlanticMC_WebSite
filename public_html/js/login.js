"use strict";

function updateRecipientPlatformForm() {
    const input = document.querySelector("[data-recipient-username]");
    const label = document.querySelector("[data-recipient-username-label]");
    const help = document.querySelector("[data-recipient-username-help]");
    const selectedPlatform = document.querySelector('input[name="platform"]:checked');

    if (!(input instanceof HTMLInputElement) || !(selectedPlatform instanceof HTMLInputElement)) {
        return;
    }

    const bedrock = selectedPlatform.value === "bedrock";

    input.minLength = bedrock ? 2 : 3;
    input.maxLength = 16;
    input.placeholder = bedrock
        ? input.dataset.bedrockPlaceholder || ""
        : input.dataset.javaPlaceholder || "";
    input.pattern = bedrock ? "[A-Za-z0-9_ ]+" : "[A-Za-z0-9_]+";

    if (label) {
        label.textContent = bedrock
            ? input.dataset.bedrockLabel || ""
            : input.dataset.javaLabel || "";
    }

    if (help) {
        help.textContent = bedrock
            ? input.dataset.bedrockHelp || ""
            : input.dataset.javaHelp || "";
    }
}

document.addEventListener("change", (event) => {
    const target = event.target;

    if (target instanceof HTMLInputElement && target.name === "platform") {
        updateRecipientPlatformForm();
    }
});

document.addEventListener("DOMContentLoaded", updateRecipientPlatformForm);
