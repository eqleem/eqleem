<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import Form from '../ui/Form.vue';
import Input from '../ui/Input.vue';
import Button from '../ui/Button.vue';
import { ApiError } from '../../lib/api.js';
import { closeModal } from '../../lib/modal.js';
import { notifySuccess, notifyApiError } from '../../lib/notify.js';

const props = defineProps({
    store: { type: Object, required: true },
    createFn: { type: Function, required: true },
    modalName: { type: String, required: true },
    detailPath: { type: Function, required: true },
    label: { type: String, required: true },
    placeholder: { type: String, required: true },
    info: { type: String, default: '' },
    requiredError: { type: String, required: true },
    failError: { type: String, required: true },
});

const router = useRouter();
const form = reactive({ title: '' });
const errors = reactive({ title: null });
const submitting = ref(false);

async function submit() {
    const title = form.title.trim();

    if (!title) {
        errors.title = props.requiredError;
        return;
    }

    errors.title = null;
    submitting.value = true;

    try {
        const item = await props.createFn(title);
        form.title = '';
        notifySuccess('Saved');

        closeModal(props.modalName);
        router.push(props.detailPath(item));
    } catch (error) {
        errors.title = error instanceof ApiError
            ? (error.errors?.title?.[0] ?? error.message)
            : props.failError;
        notifyApiError(error, props.failError);
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
            :label="label"
            :placeholder="placeholder"
            :info="info"
            :error="errors.title"
        />

        <template #footer>
            <Button type="submit" label="حفظ" :disabled="submitting || store.saving" />
        </template>
    </Form>
</template>
