<script setup>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Button from '../../ui/Button.vue';
import { addProject } from '../../../data/portfolio.js';
import { closeModal } from '../../../lib/modal.js';

const router = useRouter();
const form = reactive({ title: '' });
const errors = reactive({ title: null });

function submit() {
    const title = form.title.trim();

    if (!title) {
        errors.title = 'عنوان المشروع مطلوب.';
        return;
    }

    errors.title = null;
    const project = addProject({ title });
    form.title = '';
    closeModal('add-portfolio-project');
    router.push(`/manage/portfolio/detail/${project.uuid}`);
}
</script>

<template>
    <Form class="!rounded-none" @submit="submit">
        <Input
            v-model="form.title"
            name="title"
            label="عنوان المشروع"
            placeholder="اكتب عنوان المشروع"
            :error="errors.title"
        />

        <template #footer>
            <Button type="submit" label="حفظ" />
        </template>
    </Form>
</template>
