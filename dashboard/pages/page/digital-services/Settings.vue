<script setup>
import { onMounted, reactive, watch } from 'vue';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/digital-services/Shell.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import { useDigitalServicesStore } from '../../../stores/digital-services.js';
import { ApiError } from '../../../lib/api.js';

const store = useDigitalServicesStore();

const form = reactive({
    sectionTitle: '',
    sectionDescription: '',
});
const errors = reactive({
    sectionTitle: null,
    sectionDescription: null,
});
const saved = reactive({ show: false });

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

        saved.show = true;
        setTimeout(() => {
            saved.show = false;
        }, 2000);
    } catch (error) {
        if (error instanceof ApiError) {
            errors.sectionTitle = error.errors?.section_title?.[0] ?? null;
            errors.sectionDescription = error.errors?.section_description?.[0] ?? null;
        }
    }
}
</script>

<template>
    <ManageLayout>
        <Shell>
            <div class="p-4">
                <div v-if="store.settingsLoading && !store.settingsLoaded" class="py-8 text-center text-sm text-gray-500">
                    جاري التحميل…
                </div>

                <Form v-else class="!p-0" @submit="submit">
                    <div class="space-y-2">
                        <Input
                            v-model="form.sectionTitle"
                            name="sectionTitle"
                            label="عنوان قسم الخدمات الرقمية"
                            placeholder="الخدمات الرقمية"
                            :error="errors.sectionTitle"
                        />

                        <Textarea
                            v-model="form.sectionDescription"
                            name="sectionDescription"
                            label="وصف القسم"
                            placeholder="خدمات رقمية احترافية مع مدة تسليم واضحة"
                            :rows="3"
                            :error="errors.sectionDescription"
                        />
                    </div>

                    <template #footer>
                        <div class="flex items-center gap-3">
                            <span v-if="saved.show" class="text-sm text-emerald-600">تم حفظ الإعدادات.</span>
                            <Button type="submit" label="حفظ" :disabled="store.saving" />
                        </div>
                    </template>
                </Form>
            </div>
        </Shell>
    </ManageLayout>
</template>
