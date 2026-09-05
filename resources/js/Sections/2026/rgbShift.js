/*
 * Velocity-driven RGB shift for the guest portraits.
 *
 * A follower point chases the cursor with exponential smoothing; the gap
 * between them is large while the cursor moves and decays to nothing when it
 * stops. That gap drives an feOffset on the red channel only — green and blue
 * stay put — so the fringe reads as motion rather than as a filter that is
 * simply switched on.
 *
 * One filter element is shared by every portrait, so exactly one image may wear
 * it at a time. Which one is decided here rather than by a CSS `:hover` rule:
 * the browser can leave a card matching `:hover` or `:focus-visible` after an
 * overlay opens over it and is dismissed from the keyboard, and a stale match
 * would light up a second portrait with the shift meant for the one under the
 * cursor. The pointer logic already knows the answer, so it owns the class.
 */
const FILTER_ID = 'wcm26-rgbshift';
const ACTIVE_CLASS = 'is-shifting';
const EASE = 0.1;
const STRENGTH = 0.3;
const MAX_OFFSET = 30;
const SETTLED = 0.05;

const SVG = `
<svg width="0" height="0" aria-hidden="true" focusable="false" style="position:absolute">
    <filter id="${FILTER_ID}" color-interpolation-filters="sRGB">
        <feOffset in="SourceGraphic" dx="0" dy="0" result="shifted"/>
        <feColorMatrix in="shifted" result="r"
            values="1 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 1 0"/>
        <feColorMatrix in="SourceGraphic" result="gb"
            values="0 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 1 0"/>
        <feBlend in="r" in2="gb" mode="screen"/>
    </filter>
</svg>`;

function supported() {
    if (typeof window === 'undefined' || !window.matchMedia) {
        return false;
    }

    // No cursor means no velocity to read, and reduced motion means don't.
    return !window.matchMedia('(prefers-reduced-motion: reduce)').matches
        && !window.matchMedia('(hover: none)').matches;
}

function filterNode() {
    let node = document.getElementById(FILTER_ID);

    if (! node) {
        const holder = document.createElement('div');
        holder.innerHTML = SVG;
        document.body.appendChild(holder.firstElementChild);
        node = document.getElementById(FILTER_ID);
    }

    return node.querySelector('feOffset');
}

/**
 * @param {HTMLElement} root  container holding the portraits
 * @param {string} selector   which images inside it to animate
 * @returns {function} teardown
 */
export function rgbShift(root, selector = '.wcm26-guest-photo img') {
    if (! root || ! supported()) {
        return () => {};
    }

    const offsetNode = filterNode();
    const mouse = { x: 0, y: 0 };
    const follower = { x: 0, y: 0 };

    let frame = null;
    let hovering = false;
    let active = null;

    const setActive = (image) => {
        if (active === image) {
            return;
        }

        active?.classList.remove(ACTIVE_CLASS);
        active = image;
        active?.classList.add(ACTIVE_CLASS);
    };

    const write = (dx, dy) => {
        offsetNode.setAttribute('dx', dx.toFixed(2));
        offsetNode.setAttribute('dy', dy.toFixed(2));
    };

    function tick() {
        follower.x += (mouse.x - follower.x) * EASE;
        follower.y += (mouse.y - follower.y) * EASE;

        let dx = (mouse.x - follower.x) * STRENGTH;
        let dy = (mouse.y - follower.y) * STRENGTH;

        // Clamp the vector, not each axis, so a fast diagonal flick keeps its
        // direction instead of being squared off.
        const distance = Math.hypot(dx, dy);

        if (distance > MAX_OFFSET) {
            dx = (dx / distance) * MAX_OFFSET;
            dy = (dy / distance) * MAX_OFFSET;
        }

        write(dx, dy);

        // Once the pointer has left and the shift has settled, stop burning frames.
        if (! hovering && distance < SETTLED) {
            write(0, 0);
            frame = null;

            return;
        }

        frame = requestAnimationFrame(tick);
    }

    function start() {
        if (frame === null) {
            frame = requestAnimationFrame(tick);
        }
    }

    function onOver(event) {
        const image = event.target.closest(selector);

        if (! image || ! root.contains(image)) {
            return;
        }

        const box = image.getBoundingClientRect();

        setActive(image);
        hovering = true;
        mouse.x = event.clientX - box.left;
        mouse.y = event.clientY - box.top;

        // Land the follower on the cursor so entering an image is not itself a
        // velocity spike.
        follower.x = mouse.x;
        follower.y = mouse.y;

        start();
    }

    function onMove(event) {
        const image = event.target.closest(selector);

        if (! image || ! root.contains(image)) {
            return;
        }

        const box = image.getBoundingClientRect();

        setActive(image);
        mouse.x = event.clientX - box.left;
        mouse.y = event.clientY - box.top;
        start();
    }

    function onOut(event) {
        if (event.target.closest(selector) && ! event.relatedTarget?.closest(selector)) {
            hovering = false;
            setActive(null);
        }
    }

    root.addEventListener('pointerover', onOver);
    root.addEventListener('pointermove', onMove);
    root.addEventListener('pointerout', onOut);
    root.dataset.rgbshift = 'on';

    return () => {
        root.removeEventListener('pointerover', onOver);
        root.removeEventListener('pointermove', onMove);
        root.removeEventListener('pointerout', onOut);
        delete root.dataset.rgbshift;

        if (frame !== null) {
            cancelAnimationFrame(frame);
            frame = null;
        }

        setActive(null);
        write(0, 0);
    };
}
