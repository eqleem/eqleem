<script setup>
import { reactive } from 'vue';
import Form from '../ui/Form.vue';
import Input from '../ui/Input.vue';
import Button from '../ui/Button.vue';
import { addClient } from '../../data/clients.js';
import { closeModal } from '../../lib/modal.js';

// Port of resources/views/admin/clients/add-client.blade.php (dummy submit).
const form = reactive({ name: '', phone: '', email: '' });

function submit() {
    if (!form.name || !form.phone) {
        return;
    }

    addClient({ name: form.name, phone: form.phone, email: form.email });

    form.name = '';
    form.phone = '';
    form.email = '';

    closeModal('add-client');
}
</script>

<template>
    <Form @submit="submit">
        <Input v-model="form.name" name="name" label="الاسم" placeholder="الاسم" />
        <Input v-model="form.phone" name="phone" type="number" label="رقم الجوال" placeholder="123456789" dir="ltr" />
        <Input v-model="form.email" name="email" type="email" label="البريد الإلكتروني" placeholder="client@email.com" dir="ltr" />

        <template #footer>
            <Button type="submit" label="حفظ" />
        </template>
    </Form>
</template>
