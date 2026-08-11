"use strict";

const SMART_HEADER_DOWN_DISTANCE = 8;
const SMART_HEADER_UP_DISTANCE = 2;
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
    let lastScrollY = window.scrollY;
    let direction = "";
    let directionDistance = 0;
    let frameRequested = false;

    const measure = () => {
        const primaryRect = primary.getBoundingClientRect();
        primaryBottom = window.scrollY + primaryRect.bottom;
        header.style.setProperty("--header-secondary-height", `${secondary.offsetHeight}px`);
    };

    const showSecondary = () => {
        secondary.classList.remove("is-hidden");
        secondary.inert = false;
    };

    const hideSecondary = () => {
        secondary.classList.add("is-hidden");
        secondary.inert = true;
    };

    const setFixed = (fixed) => {
        header.classList.toggle("is-secondary-fixed", fixed);
        secondary.classList.toggle("is-fixed", fixed);

        if (!fixed) {
            showSecondary();
        }
    };

    const updatePosition = () => {
        frameRequested = false;

        const currentScrollY = Math.max(0, window.scrollY);
        const fixed = currentScrollY + SMART_HEADER_TOP_TOLERANCE >= primaryBottom;
        setFixed(fixed);

        const delta = currentScrollY - lastScrollY;

        if (Math.abs(delta) < 0.5) {
            lastScrollY = currentScrollY;
            return;
        }

        const nextDirection = delta > 0 ? "down" : "up";

        if (nextDirection !== direction) {
            direction = nextDirection;
            directionDistance = Math.abs(delta);
        } else {
            directionDistance += Math.abs(delta);
        }

        if (!fixed || currentScrollY <= SMART_HEADER_TOP_TOLERANCE) {
            showSecondary();
        } else if (direction === "up" && directionDistance >= SMART_HEADER_UP_DISTANCE) {
            showSecondary();
        } else if (direction === "down" && directionDistance >= SMART_HEADER_DOWN_DISTANCE) {
            hideSecondary();
        }

        lastScrollY = currentScrollY;
    };

    const requestUpdate = () => {
        if (frameRequested) {
            return;
        }

        frameRequested = true;
        window.requestAnimationFrame(updatePosition);
    };

    const shouldApplyInitialOffset = () => {
        if (header.dataset.initialPrimaryOffset !== "1" || window.location.hash !== "") {
            return false;
        }

        const navigation = performance.getEntriesByType("navigation")[0];

        if (navigation?.type === "back_forward") {
            return false;
        }

        return window.scrollY <= SMART_HEADER_TOP_TOLERANCE;
    };

    const start = () => {
        measure();

        if (shouldApplyInitialOffset()) {
            const root = document.documentElement;
            const previousScrollBehavior = root.style.scrollBehavior;
            root.style.scrollBehavior = "auto";
            window.scrollTo(0, Math.max(0, Math.round(primaryBottom)));
            root.style.scrollBehavior = previousScrollBehavior;
        }

        lastScrollY = Math.max(0, window.scrollY);
        direction = "";
        directionDistance = 0;
        updatePosition();

        window.addEventListener("scroll", requestUpdate, { passive: true });
        window.addEventListener("resize", () => {
            measure();
            requestUpdate();
        }, { passive: true });
    };

    window.requestAnimationFrame(() => {
        window.requestAnimationFrame(start);
    });
}

initializeSmartHeader();
