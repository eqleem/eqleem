<script setup>
import { onMounted, reactive, watch } from 'vue';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/portfolio/Shell.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import { usePortfolioStore } from '../../../stores/portfolio.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const store = usePortfolioStore();

const form = reactive({
    sectionTitle: '',
    sectionDescription: '',
});
const errors = reactive({
    sectionTitle: null,
    sectionDescription: null,
});
onMounted(() => {
    store.fetchSettings();
});

watch(() => store.settings, (settings) => {
    form.sectionTitle = settings.section_title ?? '';
    form.sectionDescription = settings.section_description ?? '';
}, { immediate: true, deep: true });

async function submit() {
    const title = form.sectionTitle.trim();
    const description = form.sectionDescription.trim();

    errors.sectionTitle = title.length >= 2 ? null : 'عنوان القسم مطلوب (حرفان على الأقل).';
    errors.sectionDescription = description.length >= 2 ? null : 'وصف القسم مطلوب (حرفان على الأقل).';

    if (errors.sectionTitle || errors.sectionDescription) {
        return;
    }

    try {
        await store.updateSettings({
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
    <ManageLayout>
        <Shell>
            <div class="p-4">
                <div v-if="store.settingsLoading && !store.settingsLoaded" class="py-8 flex items-center justify-center"><LoadingSpinner /></div>

                <Form v-else class="!p-0" @submit="submit">
                    <div class="space-y-2">
                        <Input
                            v-model="form.sectionTitle"
                            name="sectionTitle"
                            label="عنوان قسم الأعمال"
                            placeholder="معرض الأعمال"
                            :error="errors.sectionTitle"
                        />

                        <Textarea
                            v-model="form.sectionDescription"
                            name="sectionDescription"
                            label="وصف القسم"
                            placeholder="عرض وإدارة مشاريعك وأعمالك السابقة"
                            :rows="3"
                            :error="errors.sectionDescription"
                        />
                    </div>

                    <template #footer>
                        <Button type="submit" label="حفظ" :disabled="store.saving" />
                    </template>
                </Form>
            </div>
        </Shell>
    </ManageLayout>
</template>
