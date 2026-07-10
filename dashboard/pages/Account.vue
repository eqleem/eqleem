<script setup>
import { reactive, ref, watch, computed } from 'vue';
import Container from '../components/ui/Container.vue';
import MainBox from '../components/ui/MainBox.vue';
import Form from '../components/ui/Form.vue';
import Input from '../components/ui/Input.vue';
import Button from '../components/ui/Button.vue';
import { api, ApiError } from '../lib/api.js';
import { useSession, updateUser } from '../stores/session.js';

const { user } = useSession();
const form = reactive({ name: '', email: '' });
const errors = reactive({ name: null, email: null });
const saving = ref(false);
const message = ref(null);

watch(
    user,
    (value) => {
        if (!value) {
            return;
        }

        form.name = value.name ?? '';
        form.email = value.email ?? '';
    },
    { immediate: true },
);

const canSubmit = computed(() => !saving.value && form.name.trim().length >= 2 && form.email.trim().length > 0);

async function submit() {
    saving.value = true;
    message.value = null;
    errors.name = null;
    errors.email = null;

    try {
        const payload = await api('/account/profile', {
            method: 'PUT',
            body: {
                name: form.name.trim(),
                email: form.email.trim(),
            },
        });

        const updated = payload?.data ?? payload;
        updateUser(updated);
        message.value = payload?.message ?? 'تم تحديث معلومات الحساب بنجاح.';
    } catch (error) {
        if (error instanceof ApiError) {
            errors.name = error.errors?.name?.[0] ?? null;
            errors.email = error.errors?.email?.[0] ?? null;
            message.value = error.message;
        } else {
            message.value = 'تعذر حفظ التعديلات.';
        }
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Container>
        <MainBox title="معلومات الحساب" subtitle="إدارة معلومات حسابك.">
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" viewBox="0 0 24 24" fill="none">
                    <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path opacity=".4" d="M20.59 22c0-3.87-3.85-7-8.59-7s-8.59 3.13-8.59 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </template>

            <Form @submit="submit">
                <Input
                    v-model="form.name"
                    name="name"
                    label="الاسم"
                    placeholder="الاسم"
                    :error="errors.name"
                />
                <Input
                    v-model="form.email"
                    name="email"
                    label="البريد الإلكتروني"
                    placeholder="your@email.com"
                    dir="ltr"
                    :error="errors.email"
                />

                <template #footer>
                    <div class="flex items-center gap-3">
                        <p v-if="message" class="text-sm" :class="errors.name || errors.email ? 'text-red-600' : 'text-emerald-600'">
                            {{ message }}
                        </p>
                        <Button type="submit" label="حفظ" :disabled="!canSubmit" />
                    </div>
                </template>
            </Form>
        </MainBox>
    </Container>
</template>
