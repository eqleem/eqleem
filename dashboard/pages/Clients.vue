<script setup>
import { reactive } from 'vue';
import Container from '../components/ui/Container.vue';
import MainBox from '../components/ui/MainBox.vue';
import Button from '../components/ui/Button.vue';
import Modal from '../components/ui/Modal.vue';
import Form from '../components/ui/Form.vue';
import Input from '../components/ui/Input.vue';
import { openModal, closeModal } from '../lib/modal.js';

const form = reactive({ name: '', phone: '', email: '' });

function saveClient() {
    // Dummy submit — no backend yet.
    form.name = '';
    form.phone = '';
    form.email = '';
    closeModal('add-client');
}
</script>

<template>
    <Container>
        <MainBox title="العملاء" subtitle="العملاء والزبائن، تجدها هنا.">
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-500" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <circle cx="15" cy="6" r="3" fill="currentColor" opacity=".4" />
                    <ellipse cx="16" cy="17" fill="currentColor" opacity=".4" rx="5" ry="3" />
                    <circle cx="9.001" cy="6" r="4" fill="currentColor" />
                    <ellipse cx="9.001" cy="17.001" fill="currentColor" rx="7" ry="4" />
                </svg>
            </template>

            <template #actions>
                <Button type="button" label="إضافة عميل" @click="openModal('add-client')">
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M12 8v8M8 12h8"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M9 22h6c5 0 7-2 7-7V9c0-5-2-7-7-7H9C4 2 2 4 2 9v6c0 5 2 7 7 7Z"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </template>
                </Button>
            </template>

            <div class="p-12 text-center text-sm text-gray-400">لا يوجد عملاء لعرضهم بعد.</div>
        </MainBox>

        <Modal name="add-client" title="إضافة عميل جديد" size="2xl">
            <Form @submit="saveClient">
                <Input v-model="form.name" name="name" label="الاسم" placeholder="الاسم" />
                <Input v-model="form.phone" name="phone" type="number" label="رقم الجوال" placeholder="123456789" dir="ltr" />
                <Input v-model="form.email" name="email" type="email" label="البريد الإلكتروني" placeholder="client@email.com" dir="ltr" />

                <template #footer>
                    <Button type="submit" label="حفظ" />
                </template>
            </Form>
        </Modal>
    </Container>
</template>
