<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Button from '../../ui/Button.vue';
import { useBlogStore } from '../../../stores/blog.js';
import { ApiError } from '../../../lib/api.js';
import { closeModal } from '../../../lib/modal.js';

const store = useBlogStore();
const router = useRouter();
const form = reactive({ title: '' });
const errors = reactive({ title: null });
const submitting = ref(false);

async function submit() {
    const title = form.title.trim();

    if (!title) {
        errors.title = 'عنوان التدوينة مطلوب.';
        return;
    }

    errors.title = null;
    submitting.value = true;

    try {
        const post = await store.createPost(title);
        form.title = '';
        closeModal('add-blog-post');
        router.push(`/manage/blog/detail/${post.uuid}`);
    } catch (error) {
        errors.title = error instanceof ApiError
            ? (error.errors?.title?.[0] ?? error.message)
            : 'تعذر إنشاء التدوينة.';
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
            label="عنوان التدوينة"
            placeholder="اكتب عنوان التدوينة"
            :error="errors.title"
        />

        <template #footer>
            <Button type="submit" label="حفظ" :disabled="submitting || store.saving" />
        </template>
    </Form>
</template>
