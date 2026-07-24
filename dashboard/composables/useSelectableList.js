import { computed, ref } from 'vue';

/**
 * Multi-select helpers for content list tables.
 *
 * @param {() => Array<{ id: string | number }>} getItems
 */
export function useSelectableList(getItems) {
    const selectedIds = ref([]);

    const allSelected = computed({
        get: () => {
            const items = getItems();

            return items.length > 0 && items.every((item) => selectedIds.value.includes(String(item.id)));
        },
        set: (value) => {
            selectedIds.value = value ? getItems().map((item) => String(item.id)) : [];
        },
    });

    function toggleOne(id, checked) {
        const key = String(id);

        if (checked) {
            if (!selectedIds.value.includes(key)) {
                selectedIds.value = [...selectedIds.value, key];
            }
            return;
        }

        selectedIds.value = selectedIds.value.filter((item) => item !== key);
    }

    function clearSelection() {
        selectedIds.value = [];
    }

    function removeFromSelection(id) {
        selectedIds.value = selectedIds.value.filter((item) => item !== String(id));
    }

    return {
        selectedIds,
        allSelected,
        toggleOne,
        clearSelection,
        removeFromSelection,
    };
}
