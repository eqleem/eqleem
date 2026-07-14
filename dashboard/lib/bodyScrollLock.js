/**
 * Ref-counted body scroll lock for modals/overlays.
 * Keeps the page fixed while nested dialogs or teleported dropdowns scroll.
 */

let locks = 0;
let savedScrollY = 0;
let savedBodyStyles = null;

function scrollbarWidth() {
    return Math.max(0, window.innerWidth - document.documentElement.clientWidth);
}

export function lockBodyScroll() {
    locks += 1;

    if (locks !== 1) {
        return;
    }

    savedScrollY = window.scrollY || window.pageYOffset || 0;
    savedBodyStyles = {
        position: document.body.style.position,
        top: document.body.style.top,
        left: document.body.style.left,
        right: document.body.style.right,
        width: document.body.style.width,
        overflow: document.body.style.overflow,
        paddingRight: document.body.style.paddingRight,
        htmlOverflow: document.documentElement.style.overflow,
    };

    const pad = scrollbarWidth();

    document.documentElement.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.top = `-${savedScrollY}px`;
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%';
    document.body.style.overflow = 'hidden';

    if (pad > 0) {
        document.body.style.paddingRight = `${pad}px`;
    }
}

export function unlockBodyScroll() {
    if (locks === 0) {
        return;
    }

    locks -= 1;

    if (locks !== 0 || !savedBodyStyles) {
        return;
    }

    const scrollY = savedScrollY;
    const styles = savedBodyStyles;
    savedBodyStyles = null;

    document.documentElement.style.overflow = styles.htmlOverflow;
    document.body.style.position = styles.position;
    document.body.style.top = styles.top;
    document.body.style.left = styles.left;
    document.body.style.right = styles.right;
    document.body.style.width = styles.width;
    document.body.style.overflow = styles.overflow;
    document.body.style.paddingRight = styles.paddingRight;

    window.scrollTo(0, scrollY);
}

export function isBodyScrollLocked() {
    return locks > 0;
}
