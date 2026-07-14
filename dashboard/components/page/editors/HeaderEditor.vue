<script setup>
import { reactive, ref, watch } from 'vue';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Textarea from '../../ui/Textarea.vue';
import Button from '../../ui/Button.vue';
import CountrySelect from '../../ui/CountrySelect.vue';
import BrandMarkField from '../../ui/BrandMarkField.vue';
import { usePageStructureStore } from '../../../stores/pageStructure.js';
import { ApiError } from '../../../lib/api.js';
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

function brandMarkFromEditor(editor) {
    const mark = editor?.brand_mark;

    if (mark && typeof mark === 'object' && mark.type) {
        return {
            type: mark.type,
            value: mark.value ?? '',
            color: mark.color ?? '',
            url: mark.type === 'image' ? (mark.url || editor?.logo || null) : null,
            file: null,
        };
    }

    if (editor?.logo) {
        return {
            type: 'image',
            value: '',
            color: '',
            url: editor.logo,
            file: null,
        };
    }

    return {
        type: null,
        value: '',
        color: '',
        url: null,
        file: null,
    };
}

const form = reactive({
    name: props.editor.name ?? '',
    bio: props.editor.bio ?? '',
    country: normalizeCountry(props.editor.country),
    city: props.editor.city ?? '',
});

const brandMark = ref(brandMarkFromEditor(props.editor));
const errors = reactive({});
const saving = ref(false);

watch(() => props.editor, (value) => {
    form.name = value.name ?? '';
    form.bio = value.bio ?? '';
    form.country = normalizeCountry(value.country);
    form.city = value.city ?? '';
    if (!brandMark.value?.file) {
        brandMark.value = brandMarkFromEditor(value);
    }
}, { deep: true });

async function submit() {
    saving.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        const body = new FormData();
        body.append('name', form.name.trim());
        body.append('bio', form.bio.trim());
        body.append('country', form.country.trim());
        body.append('city', form.city.trim());

        const mark = brandMark.value ?? {};

        if (mark.type === 'image' && mark.file) {
            body.append('logo', mark.file);
            body.append('brand_mark_type', 'image');
        } else if (mark.type === 'emoji' || mark.type === 'icon') {
            body.append('brand_mark_type', mark.type);
            body.append('brand_mark_value', mark.value ?? '');
            if (mark.type === 'icon') {
                body.append('brand_mark_color', mark.color ?? '');
            }
        } else if (mark.type === 'none') {
            body.append('brand_mark_type', 'none');
            body.append('remove_logo', '1');
        }

        const payload = await store.updateBlock(props.blockId, body);
        brandMark.value = brandMarkFromEditor(payload?.editor ?? props.editor);
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
</script>

<template>
    <Form class="!rounded-none !p-4" @submit="submit">
        <div class="space-y-2">
            <Input v-model="form.name" name="name" label="اسم النشاط" placeholder="اسم النشاط" :error="errors.name" />

            <BrandMarkField
                v-model="brandMark"
                name="logo"
                label="الشعار"
                :error="errors.logo || errors.brand_mark_value || errors.brand_mark_type"
            />

            <Textarea v-model="form.bio" name="bio" label="النبذة" placeholder="نبذة قصيرة تظهر أسفل الاسم (اتركها فارغة لإخفائها)" :maxlength="250" :rows="3" :error="errors.bio" />

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <CountrySelect v-model="form.country" name="country" :error="errors.country" />
                <Input v-model="form.city" name="city" placeholder="المدينة" :error="errors.city" />
            </div>
        </div>

        <template #footer>
            <Button type="submit" label="حفظ" :loading="saving" />
        </template>
    </Form>
</template>
