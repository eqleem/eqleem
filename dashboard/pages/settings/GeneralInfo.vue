<script setup>
import { onMounted, reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Select from '../../components/ui/Select.vue';
import Button from '../../components/ui/Button.vue';
import Modal from '../../components/ui/Modal.vue';
import FileCrop from '../../components/ui/FileCrop.vue';
import { socialNetworks as fallbackNetworks } from '../../data/settings.js';
import { openModal, closeModal } from '../../lib/modal.js';
import { api, ApiError } from '../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../lib/notify.js';
import { updateTenant, useSession } from '../../stores/session.js';

const { tenant } = useSession();

const profile = reactive({
    name: '',
});

const logoFile = ref(null);
const logoPreview = ref(null);

const contact = reactive({
    phone: '',
    email: '',
    whatsapp: '',
    country: '',
    city: '',
});

const socialLinks = ref([]);
const socialNetworks = ref({ ...fallbackNetworks });
const newSocial = reactive({ network: 'twitter', url: '' });

const loading = ref(true);
const saving = reactive({ profile: false, contact: false, social: false });
const message = ref(null);
const errors = reactive({
    name: null,
    logo: null,
    phone: null,
    email: null,
    whatsapp: null,
    country: null,
    city: null,
    network: null,
    url: null,
});

function applyPayload(payload) {
    const data = payload?.data ?? payload;

    profile.name = data.name ?? '';

    if (!logoFile.value) {
        logoPreview.value = data.logo ?? null;
    }

    Object.assign(contact, {
        phone: data.contact?.phone ?? '',
        email: data.contact?.email ?? '',
        whatsapp: data.contact?.whatsapp ?? '',
        country: data.contact?.country ?? '',
        city: data.contact?.city ?? '',
    });

    socialLinks.value = [...(data.social_links ?? [])];

    if (data.social_networks) {
        socialNetworks.value = Object.fromEntries(
            Object.entries(data.social_networks).map(([key, value]) => [
                key,
                typeof value === 'string' ? value : (value?.label ?? key),
            ]),
        );
    }

    if (!Object.keys(socialNetworks.value).includes(newSocial.network)) {
        newSocial.network = Object.keys(socialNetworks.value)[0] ?? 'twitter';
    }

    if (tenant.value) {
        updateTenant({
            ...tenant.value,
            name: profile.name,
            logo: data.logo ?? logoPreview.value,
        });
    }
}

function onLogoChange() {
    errors.logo = null;
}

async function load() {
    loading.value = true;
    message.value = null;

    try {
        applyPayload(await api('/settings/general-info'));
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر تحميل معلومات النشاط.';
    } finally {
        loading.value = false;
    }
}

async function submitProfile() {
    saving.profile = true;
    message.value = null;
    errors.name = null;
    errors.logo = null;

    try {
        const body = new FormData();
        body.append('name', profile.name.trim());

        if (logoFile.value) {
            body.append('logo', logoFile.value);
        }

        const payload = await api('/settings/general-info/basic', {
            method: 'POST',
            body,
        });
        logoFile.value = null;
        applyPayload(payload);
        notifySuccess('تم الحفظ.');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.name = error.errors?.name?.[0] ?? null;
            errors.logo = error.errors?.logo?.[0] ?? null;
        }

        notifyApiError(error, 'تعذر حفظ معلومات الصفحة.');
    } finally {
        saving.profile = false;
    }
}

async function submitContact() {
    saving.contact = true;
    message.value = null;
    errors.phone = null;
    errors.email = null;
    errors.whatsapp = null;
    errors.country = null;
    errors.city = null;

    try {
        applyPayload(await api('/settings/general-info/contact', {
            method: 'PUT',
            body: {
                phone: contact.phone.trim() || null,
                email: contact.email.trim() || null,
                whatsapp: contact.whatsapp.trim() || null,
                country: contact.country.trim() || null,
                city: contact.city.trim() || null,
            },
        }));
        notifySuccess('تم الحفظ.');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.phone = error.errors?.phone?.[0] ?? null;
            errors.email = error.errors?.email?.[0] ?? null;
            errors.whatsapp = error.errors?.whatsapp?.[0] ?? null;
            errors.country = error.errors?.country?.[0] ?? null;
            errors.city = error.errors?.city?.[0] ?? null;
        }

        notifyApiError(error, 'تعذر حفظ بيانات الاتصال.');
    } finally {
        saving.contact = false;
    }
}

async function addSocialLink() {
    saving.social = true;
    message.value = null;
    errors.network = null;
    errors.url = null;

    try {
        applyPayload(await api('/settings/general-info/social', {
            method: 'POST',
            body: {
                network: newSocial.network,
                url: newSocial.url.trim(),
            },
        }));
        newSocial.url = '';
        notifySuccess('تم الحفظ.');
        closeModal('add-social-link');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.network = error.errors?.network?.[0] ?? null;
            errors.url = error.errors?.url?.[0] ?? null;
        }

        notifyApiError(error, 'تعذر إضافة الرابط.');
    } finally {
        saving.social = false;
    }
}

async function deleteSocialLink(id) {
    if (!confirm('هل أنت متأكد من حذف هذا الرابط؟')) {
        return;
    }

    message.value = null;

    try {
        applyPayload(await api(`/settings/general-info/social/${id}`, { method: 'DELETE' }));
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر حذف الرابط.';
    }
}

onMounted(load);
</script>

<template>
    <SettingsShell title="معلومات النشاط">
        <p v-if="message" class="mb-4 text-sm text-red-500">{{ message }}</p>
        <div v-if="loading" class="mb-4 flex items-center justify-center"><LoadingSpinner size="sm" /></div>

        <MainBox title="معلومات الصفحة" subtitle="تعديل اسم وشعار الصفحة .">
            <Form @submit="submitProfile">
                <Input
                    v-model="profile.name"
                    name="name"
                    label="اسم الصفحة"
                    placeholder="اسم الصفحة"
                    :error="errors.name"
                />

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

                <template #footer>
                    <Button type="submit" label="حفظ" :disabled="loading || saving.profile" />
                </template>
            </Form>
        </MainBox>

        <MainBox title="بيانات الاتصال" subtitle="معلومات التواصل التي تظهر في صفحتك.">
            <Form @submit="submitContact">
                <Input v-model="contact.phone" name="phone" label="رقم الجوال للاتصال" placeholder="05xxxxxxxx" dir="ltr" :error="errors.phone" />
                <Input v-model="contact.email" name="email" label="البريد الإلكتروني" placeholder="hello@example.com" dir="ltr" :error="errors.email" />
                <Input v-model="contact.whatsapp" name="whatsapp" label="جوال الواتساب" placeholder="966500000000" dir="ltr" :error="errors.whatsapp" />
                <Input v-model="contact.country" name="country" label="الدولة" placeholder="السعودية" :error="errors.country" />
                <Input v-model="contact.city" name="city" label="المدينة" placeholder="الرياض" :error="errors.city" />

                <template #footer>
                    <Button type="submit" label="حفظ" :disabled="loading || saving.contact" />
                </template>
            </Form>
        </MainBox>

        <MainBox title="حسابات السوشال ميديا" subtitle="روابط حساباتك على شبكات التواصل.">
            <div class="space-y-2 px-4 pb-4">
                <div class="my-4 flex items-center justify-between border-b border-dotted border-gray-100 pb-2">
                    <p class="text-xs font-semibold text-gray-500">روابط التواصل</p>
                    <Button type="button" variant="secondary" label="إضافة رابط" @click="openModal('add-social-link')" />
                </div>

                <p v-if="socialLinks.length === 0" class="py-2 text-xs text-gray-400">لا توجد روابط بعد. أضف أول رابط تواصل.</p>

                <ul v-else class="space-y-1.5">
                    <li
                        v-for="link in socialLinks"
                        :key="link.id"
                        class="group flex items-center gap-2 rounded-lg border border-gray-100 bg-white px-2 py-2 transition hover:border-gray-200"
                    >
                        <div class="flex min-w-0 flex-1 flex-col">
                            <span class="truncate text-sm font-medium text-gray-800">{{ socialNetworks[link.network] ?? link.network }}</span>
                            <span class="truncate text-xs text-gray-400" dir="ltr">{{ link.url }}</span>
                        </div>
                        <button
                            type="button"
                            class="shrink-0 rounded-lg p-1 text-red-400/80 transition hover:bg-red-50 hover:text-red-500"
                            aria-label="حذف الرابط"
                            @click="deleteSocialLink(link.id)"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M4 7h16M9 7V5h6v2M10 11v6M14 11v6M6 7l1 12h10l1-12" /></svg>
                        </button>
                    </li>
                </ul>
            </div>
        </MainBox>

        <Modal title="إضافة رابط تواصل" size="md" name="add-social-link">
            <div class="space-y-3 p-4">
                <Select v-model="newSocial.network" name="newNetwork" label="الشبكة" :options="socialNetworks" :error="errors.network" />
                <Input v-model="newSocial.url" name="newUrl" label="الرابط" placeholder="https://..." dir="ltr" :error="errors.url" />
            </div>
            <div class="flex justify-end gap-2 border-t border-gray-100 p-3 px-4">
                <Button type="button" variant="ghost" label="إلغاء" @click="closeModal('add-social-link')" />
                <Button type="button" label="إضافة" :disabled="saving.social" @click="addSocialLink" />
            </div>
        </Modal>
    </SettingsShell>
</template>
