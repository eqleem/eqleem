<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import Form from '../../ui/Form.vue';
import Input from '../../ui/Input.vue';
import Button from '../../ui/Button.vue';
import { useOnDemandServicesStore } from '../../../stores/on-demand-services.js';
import { ApiError } from '../../../lib/api.js';
import { closeModal } from '../../../lib/modal.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const store = useOnDemandServicesStore();
const router = useRouter();
const form = reactive({ title: '' });
const errors = reactive({ title: null });
const submitting = ref(false);

async function submit() {
    const title = form.title.trim();

    if (!title) {
        errors.title = 'اسم الخدمة مطلوب.';
        return;
    }

    errors.title = null;
    submitting.value = true;

    try {
        const service = await store.createOnDemandService(title);
        form.title = '';
        notifySuccess('Saved');

        closeModal('add-on-demand-service');
        router.push(`/manage/on-demand-services/detail/${service.uuid}`);
    } catch (error) {
        errors.title = error instanceof ApiError
            ? (error.errors?.title?.[0] ?? error.message)
            : 'تعذر إنشاء الخدمة.';
        notifyApiError(error, 'تعذر إنشاء الخدمة.');
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
            label="اسم الخدمة"
            placeholder="مثال : تركيب أرضيات، طباعة لافتات، تفصيل ستائر .. الخ"
            info="اكتب اسم الخدمة، يمكن إكمال البيانات ورفع الصور بعد الإضافة"
            :error="errors.title"
        />

        <template #footer>
            <Button type="submit" label="حفظ" :disabled="submitting || store.saving" />
        </template>
    </Form>
</template>
