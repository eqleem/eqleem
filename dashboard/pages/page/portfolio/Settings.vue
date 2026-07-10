<script setup>
import { reactive } from 'vue';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/portfolio/Shell.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import { settings } from '../../../data/portfolio.js';

// Port of resources/views/admin/page/content/portfolio/customize.blade.php
const form = reactive({
    sectionTitle: settings.sectionTitle,
    sectionDescription: settings.sectionDescription,
});
const errors = reactive({
    sectionTitle: null,
    sectionDescription: null,
});
const saved = reactive({ show: false });

function submit() {
    const title = form.sectionTitle.trim();
    const description = form.sectionDescription.trim();

    errors.sectionTitle = title.length >= 2 ? null : 'عنوان القسم مطلوب (حرفان على الأقل).';
    errors.sectionDescription = description.length >= 2 ? null : 'وصف القسم مطلوب (حرفان على الأقل).';

    if (errors.sectionTitle || errors.sectionDescription) {
        return;
    }

    settings.sectionTitle = title;
    settings.sectionDescription = description;
    saved.show = true;
    setTimeout(() => {
        saved.show = false;
    }, 2000);
}
</script>

<template>
    <ManageLayout>
        <Shell>
            <div class="p-4">
                <Form class="!p-0" @submit="submit">
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
                        <div class="flex items-center gap-3">
                            <span v-if="saved.show" class="text-sm text-emerald-600">تم حفظ الإعدادات.</span>
                            <Button type="submit" label="حفظ" />
                        </div>
                    </template>
                </Form>
            </div>
        </Shell>
    </ManageLayout>
</template>
