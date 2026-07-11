<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Button from '../../ui/Button.vue';
import { useFormsStore } from '../../../stores/forms.js';
import { ApiError } from '../../../lib/api.js';
import { closeModal } from '../../../lib/modal.js';

const store = useFormsStore();
const router = useRouter();
const form = reactive({ title: '' });
const errors = reactive({ title: null });
const submitting = ref(false);

async function submit() {
    const title = form.title.trim();

    if (!title) {
        errors.title = 'اسم النموذج مطلوب.';
        return;
    }

    errors.title = null;
    submitting.value = true;

    try {
        const item = await store.createForm(title);
        form.title = '';
        closeModal('add-form');
        router.push(`/manage/forms/detail/${item.uuid}`);
    } catch (error) {
        errors.title = error instanceof ApiError
            ? (error.errors?.title?.[0] ?? error.message)
            : 'تعذر إنشاء النموذج.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <Form class="!rounded-none" @submit="submit">
        <Input
            v-model="form.title"
            name="title"
            label="اسم النموذج"
            placeholder="اكتب اسم النموذج"
            :error="errors.title"
        />

        <template #footer>
            <Button type="submit" label="حفظ" :disabled="submitting || store.saving" />
        </template>
    </Form>
</template>
