<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Button from '../../ui/Button.vue';
import { useUnitRentalStore } from '../../../stores/unit-rental.js';
import { ApiError } from '../../../lib/api.js';
import { closeModal } from '../../../lib/modal.js';

const store = useUnitRentalStore();
const router = useRouter();
const form = reactive({ title: '' });
const errors = reactive({ title: null });
const submitting = ref(false);

async function submit() {
    const title = form.title.trim();

    if (!title) {
        errors.title = 'اسم نوع الوحدة مطلوب.';
        return;
    }

    errors.title = null;
    submitting.value = true;

    try {
        const product = await store.createUnit(title);
        form.title = '';
        closeModal('add-unit');
        router.push(`/manage/unit-rental/detail/${product.uuid}`);
    } catch (error) {
        errors.title = error instanceof ApiError
            ? (error.errors?.title?.[0] ?? error.message)
            : 'تعذر إنشاء نوع الوحدة.';
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
            label="اسم نوع الوحدة"
            placeholder="اكتب اسم نوع الوحدة"
            :error="errors.title"
        />

        <template #footer>
            <Button type="submit" label="حفظ" :disabled="submitting || store.saving" />
        </template>
    </Form>
</template>
