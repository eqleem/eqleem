<script setup>
import { onMounted, reactive, watch } from 'vue';
import Form from '../ui/Form.vue';
import Input from '../ui/Input.vue';
import Textarea from '../ui/Textarea.vue';
import Button from '../ui/Button.vue';
import { ApiError } from '../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../lib/notify.js';

const props = defineProps({
    store: { type: Object, required: true },
    titleLabel: { type: String, required: true },
    titlePlaceholder: { type: String, required: true },
    descriptionLabel: { type: String, default: 'وصف القسم' },
    descriptionPlaceholder: { type: String, required: true },
});

const form = reactive({
    sectionTitle: '',
    sectionDescription: '',
});
const errors = reactive({
    sectionTitle: null,
    sectionDescription: null,
});

onMounted(() => {
    props.store.fetchSettings();
});

watch(
    () => [props.store.settings?.section_title, props.store.settings?.section_description],
    ([sectionTitle, sectionDescription]) => {
        form.sectionTitle = sectionTitle ?? '';
        form.sectionDescription = sectionDescription ?? '';
    },
    { immediate: true },
);

async function submit() {
    const title = form.sectionTitle.trim();
    const description = form.sectionDescription.trim();

    errors.sectionTitle = title.length >= 2 ? null : 'عنوان القسم مطلوب (حرفان على الأقل).';
    errors.sectionDescription = description.length >= 2 ? null : 'وصف القسم مطلوب (حرفان على الأقل).';

    if (errors.sectionTitle || errors.sectionDescription) {
        return;
    }

    try {
        await props.store.updateSettings({
            section_title: title,
            section_description: description,
        });

        notifySuccess('Settings updated successfully.');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.sectionTitle = error.errors?.section_title?.[0] ?? null;
            errors.sectionDescription = error.errors?.section_description?.[0] ?? null;
        }

        notifyApiError(error, 'تعذر حفظ الإعدادات.');
    }
}
</script>

<template>
    <div class="p-4">
        <div v-if="store.settingsLoading && !store.settingsLoaded" class="py-8 flex items-center justify-center"><LoadingSpinner /></div>

        <Form v-else class="!p-0" @submit="submit">
            <div class="space-y-2">
                <Input
                    v-model="form.sectionTitle"
                    name="sectionTitle"
                    :label="titleLabel"
                    :placeholder="titlePlaceholder"
                    :error="errors.sectionTitle"
                />

                <Textarea
                    v-model="form.sectionDescription"
                    name="sectionDescription"
                    :label="descriptionLabel"
                    :placeholder="descriptionPlaceholder"
                    :error="errors.sectionDescription"
                />
            </div>

            <template #footer>
                <Button type="submit" label="حفظ" :disabled="store.saving" />
            </template>
        </Form>
    </div>
</template>
