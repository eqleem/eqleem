import { ref, watch } from 'vue';

/**
 * Debounced search input that calls `onSearch` after `delayMs` (default 300).
 *
 * @param {(value: string) => void} onSearch
 * @param {number} [delayMs=300]
 */
export function useDebouncedSearch(onSearch, delayMs = 300) {
    const search = ref('');
    let searchTimer = null;

    watch(search, (value) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            onSearch(value);
        }, delayMs);
    });

    return { search };
}
