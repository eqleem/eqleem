<script setup>
import { reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Form from '../ui/Form.vue';
import Input from '../ui/Input.vue';
import Button from '../ui/Button.vue';
import { useWelcomeStore } from '../../stores/welcome.js';

const store = useWelcomeStore();
const { forms, saving } = storeToRefs(store);

const form = reactive({ phone: '', email: '', country: '', city: '' });
const errors = reactive({ phone: null, email: null, country: null, city: null });
const message = ref(null);

watch(
    () => forms.value.contact,
    (value) => {
        form.phone = value?.phone ?? '';
        form.email = value?.email ?? '';
        form.country = value?.country ?? '';
        form.city = value?.city ?? '';
    },
    { immediate: true, deep: true },
);

async function submit() {
    Object.keys(errors).forEach((key) => {
        errors[key] = null;
    });
    message.value = null;

    const result = await store.saveContact({
        phone: form.phone.trim(),
        email: form.email.trim(),
        country: form.country.trim(),
        city: form.city.trim(),
    });

    if (!result.ok) {
        errors.phone = result.errors?.phone?.[0] ?? null;
        errors.email = result.errors?.email?.[0] ?? null;
        errors.country = result.errors?.country?.[0] ?? null;
        errors.city = result.errors?.city?.[0] ?? null;
        message.value = result.message ?? 'تعذر الحفظ';
        return;
    }

    await store.afterStepSaved('home-step-contact');
}
</script>

<template>
    <Form class="!p-4" form-class="!space-y-2" @submit="submit">
        <Input v-model="form.phone" name="phone" label="رقم الجوال" placeholder="0501234567" dir="ltr" :error="errors.phone" />
        <Input
            v-model="form.email"
            name="email"
            type="email"
            label="البريد الإلكتروني"
            placeholder="hello@example.com"
            dir="ltr"
            :error="errors.email"
        />
        <Input v-model="form.country" name="country" label="الدولة" placeholder="السعودية" :error="errors.country" />
        <Input v-model="form.city" name="city" label="المدينة" placeholder="الرياض" :error="errors.city" />
        <p v-if="message" class="text-sm text-red-600">{{ message }}</p>
        <template #footer>
            <Button type="submit" label="حفظ" :loading="saving" />
        </template>
    </Form>
</template>
