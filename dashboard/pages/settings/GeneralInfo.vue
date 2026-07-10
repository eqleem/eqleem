<script setup>
import { reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Select from '../../components/ui/Select.vue';
import Button from '../../components/ui/Button.vue';
import Modal from '../../components/ui/Modal.vue';
import { socialNetworks } from '../../data/settings.js';
import { openModal, closeModal } from '../../lib/modal.js';

// Port of resources/views/admin/settings/info/general-info.blade.php (dummy data).
const profile = reactive({
    name: 'متجري',
    logoPreview: null,
});

const contact = reactive({
    phone: '0501234567',
    email: 'hello@example.com',
    whatsapp: '966500000000',
    country: 'السعودية',
    city: 'الرياض',
});

const socialLinks = ref([
    { id: '1', network: 'instagram', url: 'https://instagram.com/mystore' },
    { id: '2', network: 'twitter', url: 'https://x.com/mystore' },
]);

const newSocial = reactive({ network: 'twitter', url: '' });
const saved = ref(null);

function flash(key) {
    saved.value = key;
    setTimeout(() => {
        if (saved.value === key) {
            saved.value = null;
        }
    }, 2000);
}

function submitProfile() {
    flash('profile');
}

function submitContact() {
    flash('contact');
}

function addSocialLink() {
    if (!newSocial.url.trim()) {
        return;
    }

    socialLinks.value.push({
        id: String(Date.now()),
        network: newSocial.network,
        url: newSocial.url.trim(),
    });
    newSocial.url = '';
    newSocial.network = 'twitter';
    closeModal('add-social-link');
}

function deleteSocialLink(id) {
    if (!confirm('هل أنت متأكد من حذف هذا الرابط؟')) {
        return;
    }

    socialLinks.value = socialLinks.value.filter((item) => item.id !== id);
}
</script>

<template>
    <SettingsShell title="معلومات النشاط">
        <MainBox title="معلومات الصفحة" subtitle="تعديل اسم وشعار الصفحة .">
            <Form @submit="submitProfile">
                <Input v-model="profile.name" name="name" label="اسم الصفحة" placeholder="اسم الصفحة" />

                <div class="relative items-center gap-x-2 rounded-md bg-gray-100/75 p-1 lg:flex">
                    <span class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-gray-500">الشعار</span>
                    <div class="flex items-center gap-3 p-2">
                        <div class="flex size-20 items-center justify-center overflow-hidden rounded-lg bg-white">
                            <img
                                v-if="profile.logoPreview"
                                :src="profile.logoPreview"
                                alt=""
                                class="size-20 object-cover"
                            >
                            <img v-else :src="`/assets/images/t-logo.png`" alt="" class="size-12 opacity-60">
                        </div>
                        <p class="text-xs text-gray-400">رفع الشعار (قريباً)</p>
                    </div>
                </div>

                <template #footer>
                    <div class="flex items-center gap-3">
                        <span v-if="saved === 'profile'" class="text-sm text-emerald-600">تم الحفظ.</span>
                        <Button type="submit" label="حفظ" />
                    </div>
                </template>
            </Form>
        </MainBox>

        <MainBox title="بيانات الاتصال" subtitle="معلومات التواصل التي تظهر في صفحتك.">
            <Form @submit="submitContact">
                <Input v-model="contact.phone" name="phone" label="رقم الجوال للاتصال" placeholder="05xxxxxxxx" dir="ltr" />
                <Input v-model="contact.email" name="email" label="البريد الإلكتروني" placeholder="hello@example.com" dir="ltr" />
                <Input v-model="contact.whatsapp" name="whatsapp" label="جوال الواتساب" placeholder="966500000000" dir="ltr" />
                <Input v-model="contact.country" name="country" label="الدولة" placeholder="السعودية" />
                <Input v-model="contact.city" name="city" label="المدينة" placeholder="الرياض" />

                <template #footer>
                    <div class="flex items-center gap-3">
                        <span v-if="saved === 'contact'" class="text-sm text-emerald-600">تم الحفظ.</span>
                        <Button type="submit" label="حفظ" />
                    </div>
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
                <Select v-model="newSocial.network" name="newNetwork" label="الشبكة" :options="socialNetworks" />
                <Input v-model="newSocial.url" name="newUrl" label="الرابط" placeholder="https://..." dir="ltr" />
            </div>
            <div class="flex justify-end gap-2 border-t border-gray-100 p-3 px-4">
                <Button type="button" variant="ghost" label="إلغاء" @click="closeModal('add-social-link')" />
                <Button type="button" label="إضافة" @click="addSocialLink" />
            </div>
        </Modal>
    </SettingsShell>
</template>
