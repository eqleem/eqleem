<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import Input from '../../ui/Input.vue';
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
const editingId = ref(null);
const socialDraft = reactive({
    network: 'twitter',
    url: '',
});
const socialError = ref(null);
const socialSaving = ref(false);
const dragSocialId = ref(null);
const reorderBusyId = ref(null);

const networkOptions = computed(() => {
    if (networks.value.length) {
        return networks.value.map((network) => ({
            key: network.key,
            label: network.label,
            icon: network.icon || 'ri:link',
        }));
    }

    return Object.entries(fallbackNetworks).map(([key, label]) => ({
        key,
        label,
        icon: 'ri:link',
    }));
});

const isEditing = computed(() => editingId.value !== null);

const formTitle = computed(() => (isEditing.value ? 'تعديل الحساب' : 'إضافة حساب جديد'));

const submitLabel = computed(() => (isEditing.value ? 'حفظ التعديل' : 'إضافة'));

function networkMeta(key) {
    return networkOptions.value.find((item) => item.key === key)
        ?? { key, label: fallbackNetworks[key] ?? key, icon: 'ri:link' };
}

function resetDraft() {
    editingId.value = null;
    socialDraft.url = '';
    socialError.value = null;

    const firstNetwork = networkOptions.value[0]?.key ?? 'twitter';

    if (!networkOptions.value.some((item) => item.key === socialDraft.network)) {
        socialDraft.network = firstNetwork;
    }
}

function applyEditor(editor) {
    socialLinks.value = [...(editor?.social_links ?? [])];
    networks.value = Array.isArray(editor?.social_networks) ? [...editor.social_networks] : [];

    const firstNetwork = networkOptions.value[0]?.key ?? 'twitter';

    if (!networkOptions.value.some((item) => item.key === socialDraft.network)) {
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
        resetDraft();
    } catch (error) {
        loadError.value = error instanceof ApiError ? error.message : 'تعذر تحميل روابط الشبكات.';
    } finally {
        loading.value = false;
    }
}

function startEdit(link) {
    editingId.value = link.id;
    socialDraft.network = link.network;
    socialDraft.url = link.url ?? '';
    socialError.value = null;
}

function cancelEdit() {
    resetDraft();
}

async function submitSocial() {
    const url = socialDraft.url.trim();

    if (!socialDraft.network || !url) {
        socialError.value = 'اختر الشبكة وأدخل المعرف أو الرابط.';
        return;
    }

    socialError.value = null;
    socialSaving.value = true;

    try {
        const payload = isEditing.value
            ? await api(`/page/header/social/${editingId.value}`, {
                method: 'PUT',
                body: { network: socialDraft.network, url },
            })
            : await api('/page/header/social', {
                method: 'POST',
                body: { network: socialDraft.network, url },
            });

        socialLinks.value = Array.isArray(payload?.data) ? payload.data : [];
        resetDraft();
    } catch (error) {
        socialError.value = error instanceof ApiError
            ? error.message
            : (isEditing.value ? 'تعذر تحديث الرابط.' : 'تعذر إضافة الرابط.');
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

        if (editingId.value === id) {
            resetDraft();
        }
    } catch {
        // ignore
    }
}

function onSocialDragStart(event, id) {
    dragSocialId.value = id;
    event.dataTransfer.effectAllowed = 'move';
}

async function reorderSocialLinks(ids, activeId) {
    const previous = [...socialLinks.value];
    socialLinks.value = ids.map((id) => previous.find((link) => link.id === id)).filter(Boolean);
    reorderBusyId.value = activeId;

    try {
        const payload = await api('/page/header/social/reorder', {
            method: 'PUT',
            body: { order: ids },
        });
        socialLinks.value = Array.isArray(payload?.data) ? payload.data : socialLinks.value;
    } catch {
        socialLinks.value = previous;
    } finally {
        reorderBusyId.value = null;
    }
}

async function onSocialDrop(event, targetId) {
    event.preventDefault();
    const sourceId = dragSocialId.value;
    dragSocialId.value = null;

    if (!sourceId || sourceId === targetId || reorderBusyId.value !== null) {
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
    await reorderSocialLinks(ids, sourceId);
}

async function moveSocialLink(linkId, direction) {
    const ids = socialLinks.value.map((link) => link.id);
    const from = ids.indexOf(linkId);
    const to = from + direction;

    if (from === -1 || to < 0 || to >= ids.length || reorderBusyId.value !== null) {
        return;
    }

    ids.splice(from, 1);
    ids.splice(to, 0, linkId);
    await reorderSocialLinks(ids, linkId);
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
    <div class="space-y-4 p-4">
        <div v-if="loading" class="flex items-center justify-center py-10">
            <LoadingSpinner size="sm" />
        </div>

        <p v-else-if="loadError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ loadError }}</p>

        <template v-else>
            <div
                class="space-y-3 rounded-xl border p-3 transition"
                :class="isEditing
                    ? 'border-primary-200 bg-primary-50/40'
                    : 'border-stone-200 bg-stone-50/80'"
            >
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-800">{{ formTitle }}</p>
                        <p class="text-xs text-stone-400">
                            {{ isEditing ? 'عدّل الشبكة أو المعرف ثم احفظ التغييرات.' : 'اختر الشبكة وأضف المعرف أو الرابط الكامل.' }}
                        </p>
                    </div>
                    <button
                        v-if="isEditing"
                        type="button"
                        class="shrink-0 cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-medium text-stone-500 transition hover:bg-white hover:text-stone-700"
                        @click="cancelEdit"
                    >
                        إلغاء
                    </button>
                </div>

                <div class="grid grid-cols-5 gap-1.5 sm:grid-cols-5">
                    <button
                        v-for="network in networkOptions"
                        :key="network.key"
                        type="button"
                        class="flex flex-col items-center gap-1 rounded-lg border px-1.5 py-2 text-center transition"
                        :class="socialDraft.network === network.key
                            ? 'border-primary-300 bg-white text-primary-700 shadow-sm ring-1 ring-primary-200'
                            : 'border-transparent bg-white/70 text-stone-500 hover:border-stone-200 hover:bg-white hover:text-stone-700'"
                        :aria-pressed="socialDraft.network === network.key"
                        :aria-label="network.label"
                        :title="network.label"
                        @click="socialDraft.network = network.key"
                    >
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full"
                            :class="socialDraft.network === network.key ? 'bg-primary-100 text-primary-700' : 'bg-stone-100 text-stone-500'"
                        >
                            <iconify-icon :icon="network.icon" class="text-base"></iconify-icon>
                        </span>
                        <span class="max-w-full truncate text-[10px] font-medium leading-tight">{{ network.label }}</span>
                    </button>
                </div>

                <form class="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_auto]" @submit.prevent="submitSocial">
                    <Input
                        v-model="socialDraft.url"
                        name="social_url"
                        label="المعرف أو الرابط"
                        placeholder="https://... أو @username"
                        dir="ltr"
                    />
                    <div class="flex items-end">
                        <Button
                            type="submit"
                            class="w-full sm:w-auto"
                            :label="submitLabel"
                            :loading="socialSaving"
                        />
                    </div>
                </form>

                <p v-if="socialError" class="text-xs text-red-500">{{ socialError }}</p>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between gap-2 px-0.5">
                    <p class="text-xs font-semibold text-stone-500">الحسابات المضافة</p>
                    <span
                        v-if="socialLinks.length"
                        class="rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-medium text-stone-500"
                    >
                        {{ socialLinks.length }}
                    </span>
                </div>

                <ul v-if="socialLinks.length" class="space-y-1.5">
                    <li
                        v-for="(link, index) in socialLinks"
                        :key="link.id"
                        class="group flex items-center gap-2 rounded-xl border bg-white px-2.5 py-2.5 transition"
                        :class="editingId === link.id
                            ? 'border-primary-200 ring-1 ring-primary-100'
                            : 'border-stone-100 hover:border-stone-200'"
                        @dragover.prevent
                        @drop="onSocialDrop($event, link.id)"
                    >
                        <button
                            type="button"
                            draggable="true"
                            class="hidden cursor-grab rounded-md p-1 text-stone-300 transition hover:bg-stone-100 hover:text-stone-500 active:cursor-grabbing sm:block"
                            aria-label="سحب لإعادة الترتيب"
                            @dragstart="onSocialDragStart($event, link.id)"
                            @dragend="dragSocialId = null"
                        >
                            <Icon name="grip-vertical" class="h-4 w-4" />
                        </button>

                        <div class="flex shrink-0 items-center sm:hidden">
                            <button
                                type="button"
                                class="rounded-md p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600 disabled:cursor-not-allowed disabled:opacity-25"
                                aria-label="نقل رابط التواصل للأعلى"
                                :disabled="index === 0 || reorderBusyId !== null"
                                @click.stop="moveSocialLink(link.id, -1)"
                            >
                                <Icon name="arrow-up" class="h-4 w-4" />
                            </button>
                            <button
                                type="button"
                                class="rounded-md p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600 disabled:cursor-not-allowed disabled:opacity-25"
                                aria-label="نقل رابط التواصل للأسفل"
                                :disabled="index === socialLinks.length - 1 || reorderBusyId !== null"
                                @click.stop="moveSocialLink(link.id, 1)"
                            >
                                <Icon name="arrow-down" class="h-4 w-4" />
                            </button>
                        </div>

                        <button
                            type="button"
                            class="flex min-w-0 flex-1 cursor-pointer items-center gap-2.5 text-start transition hover:text-primary-700"
                            @click="startEdit(link)"
                        >
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-stone-100 text-stone-600">
                                <iconify-icon :icon="networkMeta(link.network).icon" class="text-lg"></iconify-icon>
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium text-stone-800">
                                    {{ networkMeta(link.network).label }}
                                </span>
                                <span class="block truncate text-xs text-stone-400" dir="ltr">{{ link.url }}</span>
                            </span>
                        </button>

                        <div class="flex shrink-0 items-center gap-0.5">
                            <button
                                type="button"
                                class="cursor-pointer rounded-md p-1.5 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600"
                                aria-label="تعديل"
                                @click="startEdit(link)"
                            >
                                <Icon name="pencil" class="h-4 w-4" />
                            </button>
                            <button
                                type="button"
                                class="cursor-pointer rounded-md p-1.5 text-red-400 transition hover:bg-red-50 hover:text-red-500"
                                aria-label="حذف"
                                @click="deleteSocial(link.id)"
                            >
                                <Icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </li>
                </ul>

                <div
                    v-else
                    class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-stone-200 bg-white px-4 py-8 text-center"
                >
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 text-stone-400">
                        <iconify-icon icon="ri:share-forward-fill" class="text-xl"></iconify-icon>
                    </span>
                    <p class="text-sm font-medium text-stone-600">لا توجد حسابات بعد</p>
                    <p class="max-w-xs text-xs text-stone-400">أضف أول رابط تواصل ليظهر في أعلى صفحتك.</p>
                </div>
            </div>
        </template>
    </div>
</template>
