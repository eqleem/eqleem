<script setup>
// Ported from resources/views/admin/settings/home.blade.php.
// Settings copied from config/settings.php as JSON data (the `components` keys,
// which point at Livewire views, are omitted — not used by the display).
import Container from '../components/ui/Container.vue';
import MainBox from '../components/ui/MainBox.vue';

const settings = [
    { order: 1, slug: 'general-info', name: 'معلومات النشاط', description: 'بيانات النشاط التجاري', icon: 'assets/icons/stationery/011-id-card.svg' },
    { order: 2, slug: 'domain', name: 'الدومين', description: 'تخصيص نطاق الموقع', icon: 'assets/icons/business/015-cloud-network.svg' },
    { order: 3, slug: 'analytics', name: 'ربط الإحصائيات', description: 'لتتبع إحصائيات صفحتك', icon: 'assets/icons/business/030-growth-chart.svg' },
    { order: 4, slug: 'verification', name: 'توثيق الحساب', description: 'بيانات التوثيق بالمستندات الرسمية', icon: 'assets/icons/business/051-shield.svg' },
    { order: 5, slug: 'language-currency', name: 'اللغة والعملة', description: 'اللغة والعملة الافتراضية والمتاحة لصفحتك', icon: 'assets/icons/business/009-web browser.svg' },
    { order: 7, slug: 'branches', name: 'الفروع', description: 'الفروع والمستودعات.', icon: 'assets/icons/business/010-location.svg' },
    { order: 10, slug: 'payment-options', name: 'وسائل الدفع', description: 'تخصيص وسائل الدفع.', icon: 'assets/icons/business/017-atm-card.svg' },
    { order: 11, slug: 'shipping-option', name: 'وسائل الشحن', description: 'تخصيص طرق الشحن المتاحة.', icon: 'assets/icons/ecommerce/018-cart.svg' },
];
</script>

<template>
    <Container>
        <MainBox title="الإعدادات" subtitle="تعديل وتخصيص النظام حسب مشروعك.">
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" viewBox="0 0 24 24" fill="none">
                    <path
                        opacity=".34"
                        d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-miterlimit="10"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M2 12.881v-1.76c0-1.04.85-1.9 1.9-1.9 1.81 0 2.55-1.28 1.64-2.85-.52-.9-.21-2.07.7-2.59l1.73-.99c.79-.47 1.81-.19 2.28.6l.11.19c.9 1.57 2.38 1.57 3.29 0l.11-.19c.47-.79 1.49-1.07 2.28-.6l1.73.99c.91.52 1.22 1.69.7 2.59-.91 1.57-.17 2.85 1.64 2.85 1.04 0 1.9.85 1.9 1.9v1.76c0 1.04-.85 1.9-1.9 1.9-1.81 0-2.55 1.28-1.64 2.85.52.91.21 2.07-.7 2.59l-1.73.99c-.79.47-1.81.19-2.28-.6l-.11-.19c-.9-1.57-2.38-1.57-3.29 0l-.11.19c-.47.79-1.49 1.07-2.28.6l-1.73-.99a1.899 1.899 0 0 1-.7-2.59c.91-1.57.17-2.85-1.64-2.85-1.05 0-1.9-.86-1.9-1.9Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-miterlimit="10"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </template>

            <div class="grid grid-cols-2 gap-5 border-stone-200 border-t-2 border-dotted p-4 px-4 lg:grid-cols-4 lg:p-6 lg:px-8">
                <RouterLink
                    v-for="setting in settings"
                    :key="setting.slug"
                    :to="{ path: `/settings/${setting.slug}`, query: { name: setting.name } }"
                    class="flex items-center gap-x-3 rounded-xl bg-gray-100 p-2 hover:bg-gray-200/80"
                >
                    <div class="shrink-0 rounded-xl bg-white p-2">
                        <img class="h-10 w-10" :src="`/${setting.icon}`" :alt="setting.name">
                    </div>
                    <div class="truncate">
                        <p class="text-sm font-medium text-gray-700">{{ setting.name }}</p>
                        <small class="truncate text-xs font-normal text-gray-500">{{ setting.description }}</small>
                    </div>
                </RouterLink>
            </div>
        </MainBox>
    </Container>
</template>
