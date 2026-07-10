<script setup>
import { computed, reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Empty from '../../components/ui/Empty.vue';
import Modal from '../../components/ui/Modal.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Select from '../../components/ui/Select.vue';
import Toggle from '../../components/ui/Toggle.vue';
import Button from '../../components/ui/Button.vue';
import Switch from '../../components/settings/Switch.vue';
import { shippingMethods as seed } from '../../data/settings.js';
import { openModal, closeModal } from '../../lib/modal.js';

// Port of resources/views/admin/settings/shipping-options/shipping-options.blade.php (dummy data).
const methods = ref(seed.map((item) => ({ ...item })));
const customOptions = ref([
    { id: '1', name: 'مندوب الرياض', price: 25, country_label: 'السعودية', cities_summary: 'الرياض', active: true },
    { id: '2', name: 'شحن الخليج', price: 45, country_label: 'دول الخليج', cities_summary: 'كل المدن', active: false },
]);

const editingCustomId = ref(null);
const methodForm = reactive({ label: '', domestic_price: '', gulf_price: '', international_price: '' });
const customForm = reactive({ name: '', price: '', country: 'SA', active: true });

const customModalTitle = computed(() => (editingCustomId.value ? 'تعديل خدمة شحن' : 'أضف خدمة شحن'));

function openMethod(slug) {
    Object.assign(methodForm, { label: '', domestic_price: '', gulf_price: '', international_price: '' });
    openModal(`shipping-method-${slug}`);
}

function toggleMethod(method) {
    method.active = !method.active;
}

function toggleCustom(option) {
    option.active = !option.active;
}

function openCustomForm(option = null) {
    editingCustomId.value = option?.id ?? null;
    Object.assign(customForm, {
        name: option?.name ?? '',
        price: option?.price != null ? String(option.price) : '',
        country: 'SA',
        active: option?.active ?? true,
    });
    openModal('custom-shipping-form');
}

function saveMethod(slug) {
    closeModal(`shipping-method-${slug}`);
}

function saveCustom() {
    if (!customForm.name.trim()) {
        return;
    }

    if (editingCustomId.value) {
        const index = customOptions.value.findIndex((item) => item.id === editingCustomId.value);

        if (index !== -1) {
            customOptions.value[index] = {
                ...customOptions.value[index],
                name: customForm.name.trim(),
                price: Number(customForm.price) || 0,
                active: customForm.active,
            };
        }
    } else {
        customOptions.value.push({
            id: String(Date.now()),
            name: customForm.name.trim(),
            price: Number(customForm.price) || 0,
            country_label: 'السعودية',
            cities_summary: 'كل المدن',
            active: customForm.active,
        });
    }

    closeModal('custom-shipping-form');
}
</script>

<template>
    <SettingsShell title="وسائل الشحن">
        <MainBox title="وسائل الشحن" subtitle="قم بتفعيل وتخصيص طرق الشحن المتاحة لعملائك.">
            <template #icon>
                <img :src="`/assets/icons/ecommerce/018-cart.svg`" alt="" class="h-6 w-6">
            </template>

            <div class="divide-y divide-dotted divide-gray-200 border-t border-dotted border-gray-200">
                <div
                    v-for="method in methods"
                    :key="method.slug"
                    class="group flex items-center gap-4 px-4 py-4 transition hover:bg-gray-50/80"
                >
                    <button
                        type="button"
                        class="flex min-w-0 flex-1 items-center gap-4 text-start"
                        @click="openMethod(method.slug)"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-800">{{ method.name }}</p>
                            <p class="mt-0.5 line-clamp-2 text-xs text-gray-500">{{ method.description }}</p>
                        </div>
                        <div class="shrink-0 rounded-lg border border-gray-100 bg-white p-2">
                            <img :src="`/${method.icon}`" :alt="method.name" class="h-8 w-auto max-w-[72px] object-contain">
                        </div>
                    </button>
                    <Switch
                        :model-value="method.active"
                        :label="method.active ? `تعطيل ${method.name}` : `تفعيل ${method.name}`"
                        @update:model-value="toggleMethod(method)"
                    />
                </div>
            </div>
        </MainBox>

        <MainBox title="خيارات الشحن المخصصة" subtitle="مناديب وشركات الشحن الخاصة المتعاقد معهم خارج المنصة.">
            <template #actions>
                <Button label="أضف خدمة شحن" @click="openCustomForm()" />
            </template>

            <div class="divide-y divide-dotted divide-gray-200 border-t border-dotted border-gray-200">
                <Empty v-if="customOptions.length === 0" subtitle="سيتم عرض خيارات الشحن المخصصة هنا بعد إضافتها.">
                    لا توجد خيارات شحن مخصصة.
                </Empty>

                <div
                    v-for="option in customOptions"
                    :key="option.id"
                    class="group flex items-center gap-4 px-4 py-4 transition hover:bg-gray-50/80"
                >
                    <button
                        type="button"
                        class="flex min-w-0 flex-1 items-center gap-4 text-start"
                        @click="openCustomForm(option)"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50">
                            <svg class="h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM17 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" /><path d="M5 17H3V7h11v10h-1M14 9h4l3 4v4h-2" /></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm font-semibold"
                                :class="option.active ? 'text-gray-800' : 'text-gray-400 line-through'"
                            >
                                {{ option.name }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-gray-500">
                                {{ option.price }} ر.س · {{ option.country_label }}
                                <template v-if="option.cities_summary"> · {{ option.cities_summary }}</template>
                            </p>
                        </div>
                    </button>
                    <Switch
                        :model-value="option.active"
                        :label="option.active ? `تعطيل ${option.name}` : `تفعيل ${option.name}`"
                        @update:model-value="toggleCustom(option)"
                    />
                </div>
            </div>
        </MainBox>

        <Modal
            v-for="method in methods"
            :key="`ship-modal-${method.slug}`"
            :title="method.name"
            size="3xl"
            :name="`shipping-method-${method.slug}`"
        >
            <Form class="!rounded-none" @submit="saveMethod(method.slug)">
                <Input v-model="methodForm.label" name="label" label="العنوان الظاهر" :placeholder="method.name" />
                <Input v-model="methodForm.domestic_price" name="domestic_price" label="سعر الشحن الداخلي" placeholder="0" dir="ltr" />
                <Input v-model="methodForm.gulf_price" name="gulf_price" label="سعر الشحن الخليجي" placeholder="0" dir="ltr" />
                <Input v-model="methodForm.international_price" name="international_price" label="سعر الشحن الدولي" placeholder="0" dir="ltr" />
                <template #footer>
                    <div class="flex gap-2">
                        <Button type="button" variant="ghost" label="إلغاء" @click="closeModal(`shipping-method-${method.slug}`)" />
                        <Button type="submit" label="حفظ" />
                    </div>
                </template>
            </Form>
        </Modal>

        <Modal :title="customModalTitle" size="3xl" name="custom-shipping-form">
            <Form class="!rounded-none" @submit="saveCustom">
                <Toggle v-model="customForm.active" name="active" label="الحالة" />
                <Input v-model="customForm.name" name="name" label="اسم الخدمة" placeholder="مندوب الرياض" />
                <Input v-model="customForm.price" name="price" label="السعر" placeholder="25" dir="ltr" />
                <Select
                    v-model="customForm.country"
                    name="country"
                    label="الدولة"
                    :options="{ SA: 'السعودية', AE: 'الإمارات', ALL: 'كل الدول' }"
                />
                <template #footer>
                    <div class="flex gap-2">
                        <Button type="button" variant="ghost" label="إلغاء" @click="closeModal('custom-shipping-form')" />
                        <Button type="submit" :label="editingCustomId ? 'تعديل' : 'إضافة'" />
                    </div>
                </template>
            </Form>
        </Modal>
    </SettingsShell>
</template>
