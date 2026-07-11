<script setup>
import { reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Form from '../ui/Form.vue';
import Input from '../ui/Input.vue';
import Textarea from '../ui/Textarea.vue';
import Button from '../ui/Button.vue';
import FileCrop from '../ui/FileCrop.vue';
import { useWelcomeStore } from '../../stores/welcome.js';
import { notifySuccess, notifyError } from '../../lib/notify.js';
import { useSession, updateTenant } from '../../stores/session.js';

const store = useWelcomeStore();
const { forms, saving } = storeToRefs(store);
const { tenant } = useSession();

const form = reactive({ name: '', bio: '' });
const logoFile = ref(null);
const logoPreview = ref(null);
const errors = reactive({ name: null, bio: null, logo: null });

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

function onLogoChange() {
    errors.logo = null;
}

async function submit() {
    errors.name = null;
    errors.bio = null;
    errors.logo = null;

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
        notifyError(result.message ?? 'تعذر الحفظ');
        return;
    }

    notifySuccess('Saved');

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

        <FileCrop
            v-model="logoFile"
            v-model:preview="logoPreview"
            name="logo"
            label="الشعار"
            upload-label="رفع شعار"
            crop-title="قص الشعار"
            shape="square"
            :error="errors.logo"
            @change="onLogoChange"
        />

        <Textarea
            v-model="form.bio"
            name="bio"
            label="النبذة"
            placeholder="نبذة قصيرة تظهر أسفل الاسم"
            :rows="3"
            :error="errors.bio"
        />
        <template #footer>
            <Button type="submit" label="حفظ" :loading="saving" />
        </template>
    </Form>
</template>
