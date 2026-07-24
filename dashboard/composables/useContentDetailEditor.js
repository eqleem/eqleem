import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ApiError } from '../lib/api.js';

/**
 * Shared load / route-id watch / body-editor helpers for content Detail pages.
 *
 * @param {{
 *   fetchItem: (uuid: string) => Promise<any>,
 *   onLoaded: (item: any, ctx: { syncEditor: boolean }) => void,
 * }} options
 */
export function useContentDetailEditor({ fetchItem, onLoaded }) {
    const route = useRoute();
    const uuid = computed(() => String(route.params.id));
    const notFound = ref(false);
    const bodyEditor = ref(null);
    const bodySeed = ref('');

    function syncEditor(html) {
        bodySeed.value = html ?? '';
        nextTick(() => {
            bodyEditor.value?.setData?.(bodySeed.value);
        });
    }

    function readBody() {
        try {
            return bodyEditor.value?.getData?.() ?? bodySeed.value ?? '';
        } catch {
            return bodySeed.value ?? '';
        }
    }

    async function load({ syncEditor: shouldSync = true } = {}) {
        notFound.value = false;

        try {
            const item = await fetchItem(uuid.value);
            onLoaded(item, { syncEditor: shouldSync });
        } catch (error) {
            notFound.value = error instanceof ApiError && error.status === 404;
        }
    }

    onMounted(() => {
        load();
    });

    watch(() => route.params.id, async (id) => {
        if (!id) {
            return;
        }

        await load();
    });

    return {
        uuid,
        notFound,
        bodyEditor,
        bodySeed,
        readBody,
        syncEditor,
        load,
    };
}
