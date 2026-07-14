<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import Input from '../../ui/Input.vue';
import Select from '../../ui/Select.vue';
import Button from '../../ui/Button.vue';
import Icon from '../../ui/Icon.vue';
import { api, ApiError } from '../../../lib/api.js';
import { socialNetworks as fallbackNetworks } from '../../../data/settings.js';

const props = defineProps({
    blockId: { type: Number, required: true },
});

const loading = ref(false);
const loadError = ref(null);
const socialLinks = ref([]);
const networks = ref([]);
const socialDraft = reactive({
    network: 'twitter',
    url: '',
});
const socialError = ref(null);
const socialSaving = ref(false);
const dragSocialId = ref(null);

const networkOptions = computed(() => {
    if (networks.value.length) {
        return Object.fromEntries(
            networks.value.map((network) => [network.key, network.label]),
        );
    }

    return { ...fallbackNetworks };
});

function networkMeta(key) {
    return networks.value.find((item) => item.key === key)
        ?? (fallbackNetworks[key] ? { key, label: fallbackNetworks[key] } : null);
}

function applyEditor(editor) {
    socialLinks.value = [...(editor?.social_links ?? [])];
    networks.value = Array.isArray(editor?.social_networks) ? [...editor.social_networks] : [];

    const firstNetwork = networks.value[0]?.key
        ?? Object.keys(fallbackNetworks)[0]
        ?? 'twitter';

    if (!Object.keys(networkOptions.value).includes(socialDraft.network)) {
        socialDraft.network = firstNetwork;
    }
}

async function load() {
    if (!props.blockId) {
        return;
    }

    loading.value = true;
    loadError.value = null;

    try {
        const payload = await api(`/page/blocks/${props.blockId}`);
        applyEditor(payload?.data?.editor ?? {});
    } catch (error) {
        loadError.value = error instanceof ApiError ? error.message : 'تعذر تحميل روابط الشبكات.';
    } finally {
        loading.value = false;
    }
}

async function addSocial() {
    const url = socialDraft.url.trim();

    if (!socialDraft.network || !url) {
        return;
    }

    socialError.value = null;
    socialSaving.value = true;

    try {
        const payload = await api('/page/header/social', {
            method: 'POST',
            body: { network: socialDraft.network, url },
        });
        socialLinks.value = Array.isArray(payload?.data) ? payload.data : [];
        socialDraft.url = '';
    } catch (error) {
        socialError.value = error instanceof ApiError ? error.message : 'تعذر إضافة الرابط.';
    } finally {
        socialSaving.value = false;
    }
}

async function deleteSocial(id) {
    if (!window.confirm('هل أنت متأكد من حذف هذا الرابط؟')) {
        return;
    }

    try {
        const payload = await api(`/page/header/social/${id}`, { method: 'DELETE' });
        socialLinks.value = Array.isArray(payload?.data) ? payload.data : [];
    } catch {
        // ignore
    }
}

function onSocialDragStart(event, id) {
    dragSocialId.value = id;
    event.dataTransfer.effectAllowed = 'move';
}

async function onSocialDrop(event, targetId) {
    event.preventDefault();
    const sourceId = dragSocialId.value;
    dragSocialId.value = null;

    if (!sourceId || sourceId === targetId) {
        return;
    }

    const ids = socialLinks.value.map((link) => link.id);
    const from = ids.indexOf(sourceId);
    const to = ids.indexOf(targetId);
    if (from === -1 || to === -1) {
        return;
    }

    ids.splice(from, 1);
    ids.splice(to, 0, sourceId);

    try {
        const payload = await api('/page/header/social/reorder', {
            method: 'PUT',
            body: { order: ids },
        });
        socialLinks.value = Array.isArray(payload?.data) ? payload.data : socialLinks.value;
    } catch {
        // ignore
    }
}

onMounted(() => {
    load();
});

watch(() => props.blockId, () => {
    load();
});

defineExpose({ load });
</script>

<template>
    <div class="space-y-3 p-4">
        <div v-if="loading" class="flex items-center justify-center py-8">
            <LoadingSpinner size="sm" />
        </div>

        <p v-else-if="loadError" class="text-sm text-red-500">{{ loadError }}</p>

        <template v-else>
            <div class="space-y-2">
                <Select
                    v-model="socialDraft.network"
                    name="social_network"
                    class="w-full"
                    width="w-full"
                    :options="networkOptions"
                />
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_auto]">
                    <Input
                        v-model="socialDraft.url"
                        name="social_url"
                        label="المعرف أو الرابط"
                        placeholder="https://... أو @username"
                        dir="ltr"
                    />
                    <div class="flex items-end">
                        <Button
                            type="button"
                            variant="secondary"
                            class="w-full sm:w-auto"
                            label="إضافة"
                            :loading="socialSaving"
                            @click="addSocial"
                        />
                    </div>
                </div>
            </div>

            <p v-if="socialError" class="text-xs text-red-500">{{ socialError }}</p>

            <ul v-if="socialLinks.length" class="space-y-1.5">
                <li
                    v-for="link in socialLinks"
                    :key="link.id"
                    class="group flex items-center gap-2 rounded-lg border border-stone-100 bg-white px-2.5 py-2"
                    draggable="true"
                    @dragstart="onSocialDragStart($event, link.id)"
                    @dragover.prevent
                    @drop="onSocialDrop($event, link.id)"
                >
                    <button type="button" class="cursor-grab rounded-md p-1 text-stone-300" aria-label="سحب">
                        <Icon name="grip-vertical" class="h-4 w-4" />
                    </button>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-stone-800">{{ networkMeta(link.network)?.label ?? link.network }}</p>
                        <p class="truncate text-xs text-stone-400" dir="ltr">{{ link.url }}</p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 rounded-md p-1 text-red-400 hover:bg-red-50"
                        aria-label="حذف"
                        @click="deleteSocial(link.id)"
                    >
                        <Icon name="trash" class="h-4 w-4" />
                    </button>
                </li>
            </ul>
            <p v-else class="text-xs text-stone-400">لا توجد روابط بعد. أضف أول رابط تواصل.</p>
        </template>
    </div>
</template>
