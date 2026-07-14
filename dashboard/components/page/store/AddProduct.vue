<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Button from '../../ui/Button.vue';
import { useStoreStore } from '../../../stores/store.js';
import { ApiError } from '../../../lib/api.js';
import { closeModal } from '../../../lib/modal.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const store = useStoreStore();
const router = useRouter();
const form = reactive({ title: '' });
const errors = reactive({ title: null });
const submitting = ref(false);

async function submit() {
    const title = form.title.trim();

    if (!title) {
        errors.title = 'اسم المنتج مطلوب.';
        return;
    }

    errors.title = null;
    submitting.value = true;

    try {
        const product = await store.createProduct(title);
        form.title = '';
        notifySuccess('Saved');

        closeModal('add-store-product');
        router.push(`/manage/store/detail/${product.uuid}`);
    } catch (error) {
        errors.title = error instanceof ApiError
            ? (error.errors?.title?.[0] ?? error.message)
            : 'تعذر إنشاء المنتج.';
        notifyApiError(error, 'تعذر إنشاء المنتج.');
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
            label="اسم المنتج"
            placeholder="مثال : تيشيرت النصر، عود كمبودي فاخر، ساعة Poma .. الخ"
            info="اكتب اسم المنتج، يمكن إكمال البيانات ورفع الصور بعد إضافة المنتج"
            :error="errors.title"
        />

        <template #footer>
            <Button type="submit" label="حفظ" :disabled="submitting || store.saving" />
        </template>
    </Form>
</template>
