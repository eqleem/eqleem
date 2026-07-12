<script setup>
import { computed, provide, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

// Port of resources/views/ui/tab/group.blade.php — optional query-param sync.
const props = defineProps({
    modelValue: { type: String, default: null },
    active: { type: String, default: '' },
    urlKey: { type: String, default: null },
    validTabs: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);
const route = useRoute();
const router = useRouter();

const defaultTab = computed(() => props.modelValue ?? props.active ?? '');
const activeTab = ref(defaultTab.value);

function syncFromUrl() {
    if (!props.urlKey) {
        return;
    }

    const param = route.query[props.urlKey];

    if (typeof param === 'string' && (props.validTabs.length === 0 || props.validTabs.includes(param))) {
        activeTab.value = param;
        emit('update:modelValue', param);
    }
}

function setTab(id) {
    activeTab.value = id;
    emit('update:modelValue', id);

    if (!props.urlKey) {
        return;
    }

    const query = { ...route.query };

    if (id === defaultTab.value) {
        delete query[props.urlKey];
    } else {
        query[props.urlKey] = id;
    }

    router.replace({ query });
}

watch(() => props.modelValue, (value) => {
    if (value != null) {
        activeTab.value = value;
    }
});

watch(() => route.query[props.urlKey], syncFromUrl, { immediate: true });

provide('tabs', {
    activeTab,
    setTab,
});
</script>

<template>
    <div class="rounded-b-2xl">
        <div v-if="$slots.nav" class="overflow-x-auto whitespace-nowrap rounded-t-lg text-sm text-stone-600">
            <slot name="nav" />
        </div>
        <div class="rounded-b-2xl [&>*:first-child]:rounded-ts-none">
            <slot />
        </div>
    </div>
</template>
