<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import Form from '../ui/Form.vue';
import Input from '../ui/Input.vue';
import Button from '../ui/Button.vue';
import { ApiError } from '../../lib/api.js';
import { closeModal } from '../../lib/modal.js';
import { useClientsStore } from '../../stores/clients.js';

const clientsStore = useClientsStore();
const router = useRouter();

const form = reactive({ name: '', phone: '', email: '' });
const errors = reactive({ name: null, phone: null, email: null });
const message = ref(null);
const saving = ref(false);

async function submit() {
    saving.value = true;
    message.value = null;
    errors.name = null;
    errors.phone = null;
    errors.email = null;

    try {
        const { client } = await clientsStore.createClient({
            name: form.name.trim(),
            phone: String(form.phone).trim(),
            email: form.email.trim() || null,
        });

        form.name = '';
        form.phone = '';
        form.email = '';

        closeModal('add-client');
        await router.push(`/clients/${client.uuid}`);
    } catch (error) {
        if (error instanceof ApiError) {
            errors.name = error.errors?.name?.[0] ?? null;
            errors.phone = error.errors?.phone?.[0] ?? null;
            errors.email = error.errors?.email?.[0] ?? null;
            message.value = error.message;
        } else {
            message.value = 'تعذر حفظ العميل.';
        }
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Form @submit="submit">
        <Input
            v-model="form.name"
            name="name"
            label="الاسم"
            placeholder="الاسم"
            :error="errors.name"
        />
        <Input
            v-model="form.phone"
            name="phone"
            type="number"
            label="رقم الجوال"
            placeholder="123456789"
            dir="ltr"
            :error="errors.phone"
        />
        <Input
            v-model="form.email"
            name="email"
            type="email"
            label="البريد الإلكتروني"
            placeholder="client@email.com"
            dir="ltr"
            :error="errors.email"
        />

        <template #footer>
            <div class="flex items-center gap-3">
                <p v-if="message" class="text-sm text-red-600">{{ message }}</p>
                <Button type="submit" label="حفظ" :loading="saving" :disabled="saving" />
            </div>
        </template>
    </Form>
</template>
