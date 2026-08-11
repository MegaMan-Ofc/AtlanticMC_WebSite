"use strict";

const SMART_HEADER_SCROLL_FACTOR = 0.6;
const SMART_HEADER_TOP_TOLERANCE = 1;

function initializeSmartHeader() {
    const header = document.querySelector("[data-smart-header]");
    const primary = header?.querySelector("[data-header-primary]");
    const secondary = header?.querySelector("[data-header-secondary]");

    if (!(header instanceof HTMLElement)
        || !(primary instanceof HTMLElement)
        || !(secondary instanceof HTMLElement)) {
        return;
    }

    let primaryBottom = 0;
    let secondaryHeight = 0;
    let secondaryOffset = 0;
    let secondaryFixed = false;
    let lastScrollY = window.scrollY;
    let frameRequested = false;

    const clampSecondaryOffset = () => {
        secondaryOffset = Math.max(
            0,
            Math.min(secondaryHeight, secondaryOffset)
        );
    };

    const applySecondaryOffset = () => {
        clampSecondaryOffset();

        if (!secondaryFixed) {
            secondary.style.removeProperty("transform");
            secondary.classList.remove("is-hidden");
            secondary.inert = false;
            return;
        }

        secondary.style.transform =
            `translate3d(0, ${-secondaryOffset}px, 0)`;

        const fullyHidden =
            secondaryHeight > 0
            && secondaryOffset >= secondaryHeight - 0.5;

        secondary.classList.toggle("is-hidden", fullyHidden);
        secondary.inert = fullyHidden;
    };

    const measure = () => {
        const wasFullyHidden =
            secondary.classList.contains("is-hidden");

        const primaryRect =
            primary.getBoundingClientRect();

        primaryBottom =
            window.scrollY + primaryRect.bottom;

        secondaryHeight =
            secondary.offsetHeight;

        header.style.setProperty(
            "--header-secondary-height",
            `${secondaryHeight}px`
        );

        if (wasFullyHidden) {
            secondaryOffset = secondaryHeight;
        }

        applySecondaryOffset();
    };

    const setFixed = (fixed) => {
        if (secondaryFixed === fixed) {
            return;
        }

        secondaryFixed = fixed;

        header.classList.toggle(
            "is-secondary-fixed",
            fixed
        );

        secondary.classList.toggle(
            "is-fixed",
            fixed
        );

        if (!fixed) {
            secondaryOffset = 0;
        }

        applySecondaryOffset();
    };

    const updatePosition = () => {
        frameRequested = false;

        const currentScrollY =
            Math.max(0, window.scrollY);

        const fixed =
            currentScrollY + SMART_HEADER_TOP_TOLERANCE
            >= primaryBottom;

        const delta =
            currentScrollY - lastScrollY;

        setFixed(fixed);

        if (
            !fixed
            || currentScrollY <= SMART_HEADER_TOP_TOLERANCE
        ) {
            secondaryOffset = 0;
        } else if (Math.abs(delta) >= 0.5) {
            secondaryOffset +=
                delta * SMART_HEADER_SCROLL_FACTOR;
        }

        applySecondaryOffset();

        lastScrollY = currentScrollY;
    };

    const requestUpdate = () => {
        if (frameRequested) {
            return;
        }

        frameRequested = true;

        window.requestAnimationFrame(
            updatePosition
        );
    };

    const shouldApplyInitialOffset = () => {
        if (
            header.dataset.initialPrimaryOffset !== "1"
            || window.location.hash !== ""
        ) {
            return false;
        }

        const navigation =
            performance.getEntriesByType("navigation")[0];

        if (navigation?.type === "back_forward") {
            return false;
        }

        return window.scrollY <=
            SMART_HEADER_TOP_TOLERANCE;
    };

    const start = () => {
        measure();

        if (shouldApplyInitialOffset()) {
            const root =
                document.documentElement;

            const previousScrollBehavior =
                root.style.scrollBehavior;

            root.style.scrollBehavior = "auto";

            window.scrollTo(
                0,
                Math.max(
                    0,
                    Math.round(primaryBottom)
                )
            );

            root.style.scrollBehavior =
                previousScrollBehavior;
        }

        lastScrollY =
            Math.max(0, window.scrollY);

        secondaryOffset = 0;

        updatePosition();

        window.addEventListener(
            "scroll",
            requestUpdate,
            { passive: true }
        );

        window.addEventListener(
            "resize",
            () => {
                measure();
                requestUpdate();
            },
            { passive: true }
        );
    };

    window.requestAnimationFrame(() => {
        window.requestAnimationFrame(start);
    });
}

initializeSmartHeader();
