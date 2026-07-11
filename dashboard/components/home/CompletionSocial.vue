<script setup>
import { reactive, ref } from 'vue';
import { storeToRefs } from 'pinia';
import Form from '../ui/Form.vue';
import Input from '../ui/Input.vue';
import Select from '../ui/Select.vue';
import Button from '../ui/Button.vue';
import { useWelcomeStore } from '../../stores/welcome.js';
import { notifySuccess, notifyError } from '../../lib/notify.js';

const store = useWelcomeStore();
const { forms, saving } = storeToRefs(store);

const form = reactive({ network: 'twitter', url: '' });
const errors = reactive({ network: null, url: null });

async function submit() {
    errors.network = null;
    errors.url = null;

    const result = await store.addSocialLink({
        network: form.network,
        url: form.url.trim(),
    });

    if (!result.ok) {
        errors.network = result.errors?.network?.[0] ?? null;
        errors.url = result.errors?.url?.[0] ?? null;
        notifyError(result.message ?? 'تعذر الحفظ');
        return;
    }

    notifySuccess('Saved');

    form.url = '';
    form.network = 'twitter';
    await store.afterStepSaved('home-step-social');
}
</script>

<template>
    <Form class="!p-4" form-class="!space-y-2" @submit="submit">
        <Select
            v-model="form.network"
            name="network"
            label="الشبكة"
            :options="forms.social_networks"
            :error="errors.network"
        />
        <Input
            v-model="form.url"
            name="url"
            label="الرابط"
            placeholder="https://..."
            dir="ltr"
            :error="errors.url"
        />
        <template #footer>
            <Button type="submit" label="إضافة" :loading="saving" />
        </template>
    </Form>
</template>
