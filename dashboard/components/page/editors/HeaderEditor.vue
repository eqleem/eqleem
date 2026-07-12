<script setup>
import { computed, reactive, ref, watch } from 'vue';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Textarea from '../../ui/Textarea.vue';
import Button from '../../ui/Button.vue';
import Icon from '../../ui/Icon.vue';
import Select from '../../ui/Select.vue';
import FileCrop from '../../ui/FileCrop.vue';
import { usePageStructureStore } from '../../../stores/pageStructure.js';
import { api, ApiError } from '../../../lib/api.js';
import { notifyApiError } from '../../../lib/notify.js';

const props = defineProps({
    blockId: { type: Number, required: true },
    editor: { type: Object, required: true },
});

const emit = defineEmits(['saved']);
const store = usePageStructureStore();

const form = reactive({
    name: props.editor.name ?? '',
    bio: props.editor.bio ?? '',
    country: props.editor.country ?? '',
    city: props.editor.city ?? '',
});

const logoFile = ref(null);
const logoPreview = ref(props.editor.logo || null);
const socialLinks = ref([...(props.editor.social_links ?? [])]);
const networks = computed(() => props.editor.social_networks ?? []);
const networkOptions = computed(() => Object.fromEntries(
    networks.value.map((network) => [network.key, network.label]),
));

const socialModal = ref(false);
const newNetwork = ref(networks.value[0]?.key ?? 'twitter');
const newUrl = ref('');
const socialError = ref(null);
const socialSaving = ref(false);

const errors = reactive({});
const saving = ref(false);
const dragSocialId = ref(null);

watch(() => props.editor, (value) => {
    form.name = value.name ?? '';
    form.bio = value.bio ?? '';
    form.country = value.country ?? '';
    form.city = value.city ?? '';
    if (!logoFile.value) {
        logoPreview.value = value.logo || null;
    }
    socialLinks.value = [...(value.social_links ?? [])];
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
    socialError.value = null;
    socialSaving.value = true;

    try {
        const payload = await api('/page/header/social', {
            method: 'POST',
            body: { network: newNetwork.value, url: newUrl.value },
        });
        socialLinks.value = Array.isArray(payload?.data) ? payload.data : [];
        socialModal.value = false;
        newUrl.value = '';
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
            <Input v-model="form.name" name="name" label="اسم الصفحة" placeholder="اسم الصفحة" :error="errors.name" />

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
            <Input v-model="form.country" name="country" label="الدولة" placeholder="السعودية" :error="errors.country" />
            <Input v-model="form.city" name="city" label="المدينة" placeholder="الرياض" :error="errors.city" />

            <div class="space-y-2">
                <div class="my-4 flex items-center justify-between border-b border-dotted border-stone-100 pb-2">
                    <p class="text-xs font-semibold text-stone-500">روابط التواصل</p>
                    <Button type="button" variant="secondary" label="إضافة رابط" @click="socialModal = true">
                        <template #icon><Icon name="plus" class="h-4 w-4" /></template>
                    </Button>
                </div>

                <p v-if="!socialLinks.length" class="py-2 text-xs text-stone-400">لا توجد روابط بعد. أضف أول رابط تواصل.</p>
                <ul v-else class="space-y-1.5">
                    <li
                        v-for="link in socialLinks"
                        :key="link.id"
                        class="group flex items-center gap-2 rounded-lg border border-stone-100 bg-white px-2 py-2 transition hover:border-stone-200"
                        draggable="true"
                        @dragstart="onSocialDragStart($event, link.id)"
                        @dragover.prevent
                        @drop="onSocialDrop($event, link.id)"
                    >
                        <button type="button" class="cursor-grab rounded-md p-1 text-stone-300" aria-label="سحب">
                            <Icon name="grip-vertical" class="h-4 w-4" />
                        </button>
                        <div class="flex min-w-0 flex-1 flex-col">
                            <span class="truncate text-sm font-medium text-stone-800">{{ networkMeta(link.network)?.label ?? link.network }}</span>
                            <span class="truncate text-xs text-stone-400" dir="ltr">{{ link.url }}</span>
                        </div>
                        <button
                            type="button"
                            class="shrink-0 rounded-lg p-1 text-red-400/80 hover:bg-red-50 hover:text-red-500"
                            aria-label="حذف"
                            @click="deleteSocial(link.id)"
                        >
                            <Icon name="trash" class="h-4 w-4" />
                        </button>
                    </li>
                </ul>
            </div>
        </div>


        <template #footer>
            <Button type="submit" label="حفظ" :loading="saving" />
        </template>
    </Form>

    <div v-if="socialModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-stone-800/75" @click="socialModal = false"></div>
        <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-stone-100 p-3 px-4">
                <p class="text-sm font-semibold text-stone-600">إضافة رابط تواصل</p>
                <button type="button" class="rounded-md bg-stone-100 p-1 text-stone-400 hover:bg-stone-200" @click="socialModal = false">
                    <Icon name="x" class="h-4 w-4" />
                </button>
            </div>
            <div class="space-y-3 p-4">
                <Select v-model="newNetwork" name="newNetwork" label="الشبكة" :options="networkOptions" />
                <Input v-model="newUrl" name="newUrl" label="الرابط" placeholder="https://..." dir="ltr" />
                <p v-if="socialError" class="text-sm text-red-500">{{ socialError }}</p>
            </div>
            <div class="flex justify-end gap-2 border-t border-stone-100 p-3 px-4">
                <Button type="button" variant="ghost" label="إلغاء" @click="socialModal = false" />
                <Button type="button" label="إضافة" :loading="socialSaving" @click="addSocial" />
            </div>
        </div>
    </div>
</template>
