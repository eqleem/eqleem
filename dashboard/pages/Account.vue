<script setup>
import { computed, reactive, ref, watch } from 'vue';
import Container from '../components/ui/Container.vue';
import MainBox from '../components/ui/MainBox.vue';
import Form from '../components/ui/Form.vue';
import Input from '../components/ui/Input.vue';
import Button from '../components/ui/Button.vue';
import { FileCrop } from '../components/ui/asyncHeavy.js';
import { api, ApiError } from '../lib/api.js';
import { notifyApiSuccess, notifyApiError } from '../lib/notify.js';
import { useSession, updateUser } from '../stores/session.js';

const { user } = useSession();

const profile = reactive({
    name: '',
    email: '',
    phone: '',
});

const password = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const profileErrors = reactive({
    name: null,
    email: null,
    phone: null,
});

const passwordErrors = reactive({
    current_password: null,
    password: null,
    password_confirmation: null,
});

const avatarFile = ref(null);
const avatarPreview = ref(null);
const avatarError = ref(null);

const savingProfile = ref(false);
const savingPassword = ref(false);
const uploadingAvatar = ref(false);

watch(
    user,
    (value) => {
        if (!value) {
            return;
        }

        profile.name = value.name ?? '';
        profile.email = value.email ?? '';
        profile.phone = value.phone ?? '';
        avatarPreview.value = value.image ?? null;
    },
    { immediate: true },
);

const memberSince = computed(() => {
    if (!user.value?.created_at) {
        return null;
    }

    try {
        return new Intl.DateTimeFormat('ar-SA', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        }).format(new Date(user.value.created_at));
    } catch {
        return null;
    }
});

const canSubmitProfile = computed(() => (
    !savingProfile.value
    && profile.name.trim().length >= 2
    && profile.email.trim().length > 0
));

const canSubmitPassword = computed(() => (
    !savingPassword.value
    && password.current_password.length > 0
    && password.password.length >= 6
    && password.password === password.password_confirmation
));

function applyUser(payload) {
    const updated = payload?.data ?? payload;
    updateUser(updated);
    avatarPreview.value = updated?.image ?? avatarPreview.value;
}

async function submitProfile() {
    savingProfile.value = true;
    profileErrors.name = null;
    profileErrors.email = null;
    profileErrors.phone = null;

    try {
        const payload = await api('/account/profile', {
            method: 'PUT',
            body: {
                name: profile.name.trim(),
                email: profile.email.trim(),
                phone: profile.phone.trim() || null,
            },
        });

        applyUser(payload);
        notifyApiSuccess(payload, 'تم تحديث بيانات الحساب بنجاح.');
    } catch (error) {
        if (error instanceof ApiError) {
            profileErrors.name = error.errors?.name?.[0] ?? null;
            profileErrors.email = error.errors?.email?.[0] ?? null;
            profileErrors.phone = error.errors?.phone?.[0] ?? null;
        }

        notifyApiError(error, 'تعذر حفظ التغييرات.');
    } finally {
        savingProfile.value = false;
    }
}

async function uploadAvatar(file) {
    const selected = file instanceof File ? file : avatarFile.value;

    if (!(selected instanceof File) || uploadingAvatar.value) {
        return;
    }

    uploadingAvatar.value = true;
    avatarError.value = null;

    const body = new FormData();
    body.append('file', selected);

    try {
        const payload = await api('/account/avatar', { method: 'POST', body });
        applyUser(payload);
        avatarFile.value = null;
        notifyApiSuccess(payload, 'تم تحديث صورة الحساب.');
    } catch (error) {
        if (error instanceof ApiError) {
            avatarError.value = error.errors?.file?.[0] ?? null;
        }

        notifyApiError(error, 'تعذر رفع الصورة.');
    } finally {
        uploadingAvatar.value = false;
    }
}

async function submitPassword() {
    savingPassword.value = true;
    passwordErrors.current_password = null;
    passwordErrors.password = null;
    passwordErrors.password_confirmation = null;

    try {
        const payload = await api('/account/password', {
            method: 'PUT',
            body: {
                current_password: password.current_password,
                password: password.password,
                password_confirmation: password.password_confirmation,
            },
        });

        password.current_password = '';
        password.password = '';
        password.password_confirmation = '';
        notifyApiSuccess(payload, 'تم تحديث كلمة المرور بنجاح.');
    } catch (error) {
        if (error instanceof ApiError) {
            passwordErrors.current_password = error.errors?.current_password?.[0] ?? null;
            passwordErrors.password = error.errors?.password?.[0] ?? null;
            passwordErrors.password_confirmation = error.errors?.password_confirmation?.[0] ?? null;
        }

        notifyApiError(error, 'تعذر تحديث كلمة المرور.');
    } finally {
        savingPassword.value = false;
    }
}
</script>

<template>
    <Container title="إدارة الحساب">
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- Identity -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="relative bg-gradient-to-l from-primary-700 via-primary-600 to-primary-500 px-5 py-7 sm:px-8">
                    <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, black 0, transparent 45%), radial-gradient(circle at 80% 0%, black 0, transparent 35%);"></div>
                    <div class="relative flex items-center gap-4 sm:gap-5">
                        <img
                            :src="avatarPreview || '/assets/images/user.png'"
                            :alt="profile.name"
                            class="size-16 shrink-0 rounded-full border-4 border-white/30 object-cover shadow-lg sm:size-20"
                        >
                        <div class="min-w-0 text-white">
                            <h1 class="truncate text-xl font-semibold tracking-tight sm:text-2xl">{{ profile.name || 'حسابك' }}</h1>
                            <p class="mt-0.5 truncate text-sm text-white/80 inline" dir="ltr">{{ profile.email }}</p>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                <span v-if="profile.phone" class="rounded-full bg-white/15 px-2.5 py-1" dir="ltr">{{ profile.phone }}</span>
                                <span v-if="memberSince" class="rounded-full bg-white/15 px-2.5 py-1">عضو منذ {{ memberSince }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal info -->
            <MainBox title="المعلومات الشخصية" subtitle="الصورة والاسم والبريد والجوال.">
                <template #icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-stone-600" viewBox="0 0 24 24" fill="none">
                        <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path opacity=".4" d="M20.59 22c0-3.87-3.85-7-8.59-7s-8.59 3.13-8.59 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </template>

                <Form @submit="submitProfile">
                    <FileCrop
                        v-model="avatarFile"
                        v-model:preview="avatarPreview"
                        name="avatar"
                        label="صورة الحساب"
                        shape="circle"
                        :output-size="512"
                        upload-label="تغيير الصورة"
                        crop-title="قص صورة الحساب"
                        preview-class="size-20 rounded-full object-cover"
                        placeholder="/assets/images/user.png"
                        placeholder-class="size-12 opacity-60"
                        info="JPG / PNG / WebP"
                        :error="avatarError"
                        @change="uploadAvatar"
                    />

                    <div v-if="uploadingAvatar" class="px-1 text-xs text-stone-400">جاري رفع الصورة…</div>

                    <Input
                        v-model="profile.name"
                        name="name"
                        label="الاسم"
                        placeholder="الاسم الكامل"
                        :error="profileErrors.name"
                    />
                    <Input
                        v-model="profile.email"
                        name="email"
                        label="البريد الإلكتروني"
                        placeholder="your@email.com"
                        dir="ltr"
                        :error="profileErrors.email"
                        infoDir="rtl"
                        info="تغيير البريد يعيد حالة التوثيق."
                    />
                    <Input
                        v-model="profile.phone"
                        name="phone"
                        label="رقم الجوال"
                        placeholder="05xxxxxxxx"
                        dir="ltr"
                        :error="profileErrors.phone"
                    />

                    <template #footer>
                        <Button type="submit" label="حفظ التغييرات" :loading="savingProfile" :disabled="!canSubmitProfile" />
                    </template>
                </Form>
            </MainBox>

            <!-- Password -->
            <MainBox v-if="false" title="كلمة المرور" subtitle="حدّث كلمة المرور وأبطل رموز الوصول القديمة.">
                <template #icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-stone-600" viewBox="0 0 24 24" fill="none">
                        <path d="M6 10V8c0-3.31 1-6 6-6s6 2.69 6 6v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M17 22H7c-4 0-5-1-5-5v-2c0-4 1-5 5-5h10c4 0 5 1 5 5v2c0 4-1 5-5 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path opacity=".4" d="M12 18.2a2.2 2.2 0 1 0 0-4.4 2.2 2.2 0 0 0 0 4.4Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </template>

                <Form @submit="submitPassword">
                    <Input
                        v-model="password.current_password"
                        name="current_password"
                        type="password"
                        label="كلمة المرور الحالية"
                        placeholder="••••••••"
                        dir="ltr"
                        :error="passwordErrors.current_password"
                    />
                    <Input
                        v-model="password.password"
                        name="password"
                        type="password"
                        label="كلمة المرور الجديدة"
                        placeholder="••••••••"
                        dir="ltr"
                        infoDir="rtl"
                        :error="passwordErrors.password"
                        info="6 أحرف على الأقل."
                    />
                    <Input
                        v-model="password.password_confirmation"
                        name="password_confirmation"
                        type="password"
                        label="تأكيد كلمة المرور"
                        placeholder="••••••••"
                        dir="ltr"
                        :error="passwordErrors.password_confirmation"
                    />

                    <template #footer>
                        <Button type="submit" label="تحديث كلمة المرور" :loading="savingPassword" :disabled="!canSubmitPassword" />
                    </template>
                </Form>
            </MainBox>
        </div>
    </Container>
</template>
