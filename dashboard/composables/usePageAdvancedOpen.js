import { ref, watch } from 'vue';

const STORAGE_KEY = 'eqleem.pages.advancedOpen';

function readStoredOpen() {
    if (typeof window === 'undefined') {
        return false;
    }

    try {
        const stored = window.localStorage.getItem(STORAGE_KEY);

        if (stored === null) {
            return false;
        }

        return JSON.parse(stored) === true;
    } catch {
        return false;
    }
}

function writeStoredOpen(value) {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(Boolean(value)));
    } catch {
        // ignore quota / private mode
    }
}

const open = ref(readStoredOpen());

watch(open, (value) => {
    writeStoredOpen(value);
});

export function usePageAdvancedOpen() {
    function toggle() {
        open.value = !open.value;
    }

    function expand() {
        open.value = true;
    }

    return {
        open,
        toggle,
        expand,
    };
}
