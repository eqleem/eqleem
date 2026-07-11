<script setup>
import { reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Form from '../ui/Form.vue';
import Input from '../ui/Input.vue';
import Textarea from '../ui/Textarea.vue';
import Button from '../ui/Button.vue';
import { useWelcomeStore } from '../../stores/welcome.js';
import { useSession, updateTenant } from '../../stores/session.js';

const store = useWelcomeStore();
const { forms, saving } = storeToRefs(store);
const { tenant } = useSession();

const form = reactive({ name: '', bio: '' });
const logoFile = ref(null);
const logoPreview = ref(null);
const errors = reactive({ name: null, bio: null, logo: null });
const message = ref(null);

watch(
    () => forms.value.basic_info,
    (value) => {
        form.name = value?.name ?? '';
        form.bio = value?.bio ?? '';

        if (!logoFile.value) {
            logoPreview.value = value?.logo || null;
        }
    },
    { immediate: true, deep: true },
);

function onLogoChange(event) {
    const file = event.target.files?.[0] ?? null;
    logoFile.value = file;
    errors.logo = null;

    if (logoPreview.value && logoPreview.value.startsWith('blob:')) {
        URL.revokeObjectURL(logoPreview.value);
    }

    logoPreview.value = file ? URL.createObjectURL(file) : forms.value.basic_info?.logo || null;
}

async function submit() {
    errors.name = null;
    errors.bio = null;
    errors.logo = null;
    message.value = null;

    const body = new FormData();
    body.append('name', form.name.trim());
    body.append('bio', form.bio.trim());

    if (logoFile.value) {
        body.append('logo', logoFile.value);
    }

    const result = await store.saveBasicInfo(body);

    if (!result.ok) {
        errors.name = result.errors?.name?.[0] ?? null;
        errors.bio = result.errors?.bio?.[0] ?? null;
        errors.logo = result.errors?.logo?.[0] ?? null;
        message.value = result.message ?? 'تعذر الحفظ';
        return;
    }

    if (tenant.value) {
        updateTenant({
            ...tenant.value,
            name: form.name.trim(),
            logo: store.forms.basic_info.logo || tenant.value.logo,
        });
    }

    logoFile.value = null;
    await store.afterStepSaved('home-step-basic-info');
}
</script>

<template>
    <Form class="!p-4" form-class="!space-y-2" @submit="submit">
        <Input v-model="form.name" name="name" label="اسم الصفحة" placeholder="اسم الصفحة" :error="errors.name" />

        <div class="relative items-center gap-x-2 rounded-md bg-gray-100/75 p-1 lg:flex">
            <span class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-gray-500">الشعار</span>
            <div class="flex flex-1 items-center gap-3 p-2">
                <div class="flex size-20 items-center justify-center overflow-hidden rounded-lg bg-white">
                    <img
                        v-if="logoPreview"
                        :src="logoPreview"
                        alt=""
                        class="size-20 object-cover"
                    >
                    <img v-else :src="'/assets/images/user.png'" alt="" class="size-12 opacity-60">
                </div>
                <label class="cursor-pointer text-sm font-medium text-primary-700 hover:underline">
                    رفع شعار
                    <input type="file" accept="image/*" class="hidden" @change="onLogoChange">
                </label>
            </div>
        </div>
        <p v-if="errors.logo" class="text-sm text-red-600">{{ errors.logo }}</p>

        <Textarea
            v-model="form.bio"
            name="bio"
            label="النبذة"
            placeholder="نبذة قصيرة تظهر أسفل الاسم"
            :rows="3"
            :error="errors.bio"
        />
        <p v-if="message" class="text-sm text-red-600">{{ message }}</p>
        <template #footer>
            <Button type="submit" label="حفظ" :loading="saving" />
        </template>
    </Form>
</template>
