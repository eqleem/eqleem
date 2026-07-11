<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Button from '../../ui/Button.vue';
import { useNewsletterStore } from '../../../stores/newsletter.js';
import { ApiError } from '../../../lib/api.js';
import { closeModal } from '../../../lib/modal.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const store = useNewsletterStore();
const router = useRouter();
const form = reactive({ title: '' });
const errors = reactive({ title: null });
const submitting = ref(false);

async function submit() {
    const title = form.title.trim();

    if (!title) {
        errors.title = 'عنوان النشرة مطلوب.';
        return;
    }

    errors.title = null;
    submitting.value = true;

    try {
        const issue = await store.createIssue(title);
        form.title = '';
        notifySuccess('Saved');

        closeModal('add-newsletter');
        router.push(`/manage/newsletter/detail/${issue.uuid}`);
    } catch (error) {
        errors.title = error instanceof ApiError
            ? (error.errors?.title?.[0] ?? error.message)
            : 'تعذر إنشاء النشرة.';
        notifyApiError(error, 'تعذر إنشاء النشرة.');
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
            label="عنوان النشرة"
            placeholder="اكتب عنوان النشرة البريدية"
            :error="errors.title"
        />

        <template #footer>
            <Button type="submit" label="حفظ" :disabled="submitting || store.saving" />
        </template>
    </Form>
</template>
