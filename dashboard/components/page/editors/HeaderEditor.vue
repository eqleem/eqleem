<script setup>
import { computed, reactive, ref, watch } from 'vue';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Textarea from '../../ui/Textarea.vue';
import Button from '../../ui/Button.vue';
import Icon from '../../ui/Icon.vue';
import Select from '../../ui/Select.vue';
import CountrySelect from '../../ui/CountrySelect.vue';
import FileCrop from '../../ui/FileCrop.vue';
import { usePageStructureStore } from '../../../stores/pageStructure.js';
import { api, ApiError } from '../../../lib/api.js';
import { notifyApiError } from '../../../lib/notify.js';
import { defaultCountryCode } from '../../../data/countries.js';

const props = defineProps({
    blockId: { type: Number, required: true },
    editor: { type: Object, required: true },
});

const emit = defineEmits(['saved']);
const store = usePageStructureStore();

function normalizeCountry(value) {
    const country = String(value ?? '').trim();

    return /^[A-Za-z]{2}$/.test(country) ? country.toUpperCase() : defaultCountryCode;
}

const form = reactive({
    name: props.editor.name ?? '',
    bio: props.editor.bio ?? '',
    country: normalizeCountry(props.editor.country),
    city: props.editor.city ?? '',
});

const logoFile = ref(null);
const logoPreview = ref(props.editor.logo || null);
const socialLinks = ref([...(props.editor.social_links ?? [])]);
const networks = computed(() => props.editor.social_networks ?? []);
const networkOptions = computed(() => Object.fromEntries(
    networks.value.map((network) => [network.key, network.label]),
));

const socialDraft = reactive({
    network: networks.value[0]?.key ?? 'twitter',
    url: '',
});
const socialError = ref(null);
const socialSaving = ref(false);

const errors = reactive({});
const saving = ref(false);
const dragSocialId = ref(null);

watch(() => props.editor, (value) => {
    form.name = value.name ?? '';
    form.bio = value.bio ?? '';
    form.country = normalizeCountry(value.country);
    form.city = value.city ?? '';
    if (!logoFile.value) {
        logoPreview.value = value.logo || null;
    }
    socialLinks.value = [...(value.social_links ?? [])];

    const firstNetwork = networks.value[0]?.key ?? 'twitter';
    if (!networks.value.some((network) => network.key === socialDraft.network)) {
        socialDraft.network = firstNetwork;
    }
}, { deep: true });

function networkMeta(key) {
    return networks.value.find((item) => item.key === key) ?? null;
}

async function submit() {
    saving.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        const body = new FormData();
        body.append('name', form.name.trim());
        body.append('bio', form.bio.trim());
        body.append('country', form.country.trim());
        body.append('city', form.city.trim());
        if (logoFile.value) {
            body.append('logo', logoFile.value);
        }

        const payload = await store.updateBlock(props.blockId, body);
        emit('saved', payload);
    } catch (error) {
        if (error instanceof ApiError) {
            Object.assign(errors, Object.fromEntries(
                Object.entries(error.errors || {}).map(([key, value]) => [key, value?.[0] ?? null]),
            ));
        }
        notifyApiError(error, 'تعذر الحفظ.');
    } finally {
        saving.value = false;
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
</script>

<template>
    <Form class="!rounded-none !p-4" @submit="submit">
        <div class="space-y-2">
            <Input v-model="form.name" name="name" label="اسم النشاط" placeholder="اسم النشاط" :error="errors.name" />

            <FileCrop
                v-model="logoFile"
                v-model:preview="logoPreview"
                name="logo"
                label="الشعار"
                upload-label="رفع شعار"
                crop-title="قص الشعار"
                shape="square"
                :error="errors.logo"
            />

            <Textarea v-model="form.bio" name="bio" label="النبذة" placeholder="نبذة قصيرة تظهر أسفل الاسم (اتركها فارغة لإخفائها)" :maxlength="250" :rows="3" :error="errors.bio" />

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <CountrySelect v-model="form.country" name="country" :error="errors.country" />
                <Input v-model="form.city" name="city" placeholder="المدينة" :error="errors.city" />
            </div>

            <div class="rounded-xl border border-stone-100 bg-stone-50/60 p-3">
                <p class="mb-2 text-sm font-semibold text-stone-700">روابط السوشال ميديا</p>
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
                <p v-if="socialError" class="mt-2 text-xs text-red-500">{{ socialError }}</p>

                <ul v-if="socialLinks.length" class="mt-3 space-y-1.5">
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
                <p v-else class="mt-3 text-xs text-stone-400">لا توجد روابط بعد. أضف أول رابط تواصل.</p>
            </div>
        </div>

        <template #footer>
            <Button type="submit" label="حفظ" :loading="saving" />
        </template>
    </Form>
</template>
